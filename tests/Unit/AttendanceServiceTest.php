<?php

namespace Modules\Lastorder\Attendance\Tests\Unit;

use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;
use Modules\Lastorder\Attendance\Services\AttendanceBonusService;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;
use PHPUnit\Framework\TestCase;

class AttendanceServiceTest extends TestCase
{
    private MockInterface $attendanceRepo;

    private MockInterface $bonusService;

    private MockInterface $settingsService;

    private AttendanceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendanceRepo = Mockery::mock(AttendanceRepositoryInterface::class);
        $this->bonusService = Mockery::mock(AttendanceBonusService::class);
        $this->settingsService = Mockery::mock(AttendanceSettingsService::class);

        $this->service = new AttendanceService(
            $this->attendanceRepo,
            $this->bonusService,
            $this->settingsService,
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();
        parent::tearDown();
    }

    // ─── isWithinAllowedTime ───────────────────────────────────

    public function test_is_within_allowed_time_returns_true_when_in_range(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 12, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('09:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('18:00');

        $this->assertTrue($this->service->isWithinAllowedTime());
    }

    public function test_is_within_allowed_time_returns_false_when_out_of_range(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 7, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('09:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('18:00');

        $this->assertFalse($this->service->isWithinAllowedTime());
    }

    public function test_is_within_allowed_time_defaults_to_all_day_on_invalid_settings(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 3, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('invalid');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('invalid');

        // Should fallback to 00:00-23:59 and return true
        \Illuminate\Support\Facades\Log::shouldReceive('warning')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'));

        $this->assertTrue($this->service->isWithinAllowedTime());
    }

    // ─── hasCheckedInToday ─────────────────────────────────────

    public function test_has_checked_in_today_returns_true_when_record_exists(): void
    {
        $today = '2025-01-15';
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $existing = Mockery::mock(Attendance::class);
        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with(1, $today)
            ->andReturn($existing);

        $this->assertTrue($this->service->hasCheckedInToday(1));
    }

    public function test_has_checked_in_today_returns_false_when_no_record(): void
    {
        $today = '2025-01-15';
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with(1, $today)
            ->andReturn(null);

        $this->assertFalse($this->service->hasCheckedInToday(1));
    }

    // ─── getConsecutiveDays ────────────────────────────────────

    public function test_get_consecutive_days_returns_1_when_no_yesterday(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with(1, '2025-01-14')
            ->andReturn(null);

        $this->assertEquals(1, $this->service->getConsecutiveDays(1));
    }

    public function test_get_consecutive_days_increments_from_yesterday(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $yesterday = Mockery::mock(Attendance::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $yesterday->shouldReceive('getAttribute')->with('consecutive_days')->andReturn(10);
        $yesterday->shouldReceive('getAttribute')->andReturn(null);
        $yesterday->shouldReceive('getAttributeValue')->with('consecutive_days')->andReturn(10);

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with(1, '2025-01-14')
            ->andReturn($yesterday);

        $this->assertEquals(11, $this->service->getConsecutiveDays(1));
    }

    // ─── getCurrentConsecutiveDays / getCurrentTotalDays ───────

    public function test_get_current_consecutive_days_delegates_to_repository(): void
    {
        $this->attendanceRepo->shouldReceive('getConsecutiveDays')
            ->with(1)
            ->once()
            ->andReturn(5);

        $this->assertEquals(5, $this->service->getCurrentConsecutiveDays(1));
    }

    public function test_get_current_total_days_delegates_to_repository(): void
    {
        $this->attendanceRepo->shouldReceive('getTotalDays')
            ->with(1)
            ->once()
            ->andReturn(42);

        $this->assertEquals(42, $this->service->getCurrentTotalDays(1));
    }

    // ─── getTotalDays ──────────────────────────────────────────

    public function test_get_total_days_adds_one_to_stored_value(): void
    {
        $this->attendanceRepo->shouldReceive('getTotalDays')
            ->with(1)
            ->andReturn(9);

        $this->assertEquals(10, $this->service->getTotalDays(1));
    }

    public function test_get_total_days_returns_1_for_new_user(): void
    {
        $this->attendanceRepo->shouldReceive('getTotalDays')
            ->with(999)
            ->andReturn(0);

        $this->assertEquals(1, $this->service->getTotalDays(999));
    }

    // ─── getDailyRank ──────────────────────────────────────────

    public function test_get_daily_rank_delegates_to_repository(): void
    {
        $this->attendanceRepo->shouldReceive('getDailyRank')
            ->with('2025-01-15')
            ->once()
            ->andReturn(3);

        $this->assertEquals(3, $this->service->getDailyRank('2025-01-15'));
    }

    // ─── canCheckIn ────────────────────────────────────────────

    public function test_can_check_in_returns_true_when_allowed_and_not_checked_in(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')->andReturn('00:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')->andReturn('23:59');

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with(1, '2025-01-15')
            ->andReturn(null);

        $this->assertTrue($this->service->canCheckIn(1));
    }

    public function test_can_check_in_returns_false_outside_time(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 7, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')->andReturn('09:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')->andReturn('18:00');

        $this->assertFalse($this->service->canCheckIn(1));
    }

    // ─── getMonthlyCalendar ────────────────────────────────────

    public function test_get_monthly_calendar_returns_all_days_of_month(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 2, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('getByUserAndMonth')
            ->with(1, 2025, 2)
            ->andReturn(collect([]));

        $calendar = $this->service->getMonthlyCalendar(1, 2025, 2);

        // February 2025 has 28 days
        $this->assertCount(28, $calendar);

        // All days should be absent (no attendance records)
        foreach ($calendar as $date => $info) {
            $this->assertFalse($info['attended']);
            $this->assertNull($info['attendance']);
        }
    }

    public function test_get_monthly_calendar_marks_future_dates(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('getByUserAndMonth')
            ->with(1, 2025, 1)
            ->andReturn(collect([]));

        $calendar = $this->service->getMonthlyCalendar(1, 2025, 1);

        // Jan 15 should not be future, Jan 16 should be future
        $this->assertFalse($calendar['2025-01-15']['is_future']);
        $this->assertTrue($calendar['2025-01-16']['is_future']);
        $this->assertFalse($calendar['2025-01-14']['is_future']);
    }

    // ─── getTodayAttendances ───────────────────────────────────

    public function test_get_today_attendances_delegates_to_repository(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $paginator = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);

        $this->attendanceRepo->shouldReceive('getByDate')
            ->with('2025-01-15', 1, 20)
            ->once()
            ->andReturn($paginator);

        $result = $this->service->getTodayAttendances(1, 20);

        $this->assertSame($paginator, $result);
    }
}
