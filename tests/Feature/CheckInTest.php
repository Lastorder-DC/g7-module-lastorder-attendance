<?php

namespace Modules\Lastorder\Attendance\Tests\Feature;

use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceBonusRepositoryInterface;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;
use Modules\Lastorder\Attendance\Services\AttendanceBonusService;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;
use PHPUnit\Framework\TestCase;

class CheckInTest extends TestCase
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
        Mockery::close();
        parent::tearDown();
    }

    public function test_successful_check_in(): void
    {
        $userId = 1;
        $greeting = '좋은 아침이에요~';
        $ip = '127.0.0.1';
        $today = '2025-01-15';
        $basePoint = 10;

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        // Settings: allowed time window covers current time
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('00:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('23:59');
        $this->settingsService->shouldReceive('getSetting')
            ->with('base_point', 10)
            ->andReturn($basePoint);
        $this->settingsService->shouldReceive('getSetting')
            ->with('random_point_enabled', false)
            ->andReturn(false);

        // User has not checked in today (called twice: pre-check + inside transaction)
        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $today)
            ->andReturn(null);

        // Yesterday's attendance for consecutive days
        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, '2025-01-14')
            ->andReturn(null);

        // Total days from repo
        $this->attendanceRepo->shouldReceive('getTotalDays')
            ->with($userId)
            ->andReturn(0);

        // Daily rank
        $this->attendanceRepo->shouldReceive('getDailyRank')
            ->with($today)
            ->andReturn(1);

        // Create attendance record
        $attendance = Mockery::mock(Attendance::class)->makePartial();
        $attendance->id = 1;
        $attendance->user_id = $userId;
        $attendance->attendance_date = Carbon::parse($today);
        $attendance->daily_rank = 1;
        $attendance->consecutive_days = 1;
        $attendance->total_days = 1;
        $attendance->base_point = $basePoint;
        $attendance->random_point = 0;
        $attendance->total_point = $basePoint;
        $attendance->greeting = $greeting;
        $attendance->ip_address = $ip;
        $attendance->is_auto = false;

        $this->attendanceRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) use ($userId, $today, $greeting, $ip, $basePoint) {
                return $data['user_id'] === $userId
                    && $data['attendance_date'] === $today
                    && $data['greeting'] === $greeting
                    && $data['base_point'] === $basePoint
                    && $data['random_point'] === 0
                    && $data['total_point'] === $basePoint
                    && $data['daily_rank'] === 1
                    && $data['consecutive_days'] === 1
                    && $data['total_days'] === 1
                    && $data['ip_address'] === $ip
                    && $data['is_auto'] === false;
            }))
            ->andReturn($attendance);

        // Bonus checks
        $this->bonusService->shouldReceive('checkAndGrantRankBonus')
            ->once()
            ->with($attendance);
        $this->bonusService->shouldReceive('checkAndGrantConsecutiveBonus')
            ->once()
            ->with($attendance);

        // Mock DB::transaction to just execute the closure
        \Illuminate\Support\Facades\DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn (callable $callback) => $callback());

        $result = $this->service->checkIn($userId, $greeting, $ip);

        $this->assertInstanceOf(Attendance::class, $result);
        $this->assertEquals($userId, $result->user_id);
        $this->assertEquals(1, $result->daily_rank);
        $this->assertEquals($basePoint, $result->total_point);
        $this->assertFalse($result->is_auto);

        Carbon::setTestNow();
    }

    public function test_duplicate_check_in_throws_exception(): void
    {
        $userId = 1;
        $today = '2025-01-15';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        // Allowed time settings
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('00:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('23:59');

        // User already checked in today
        $existingAttendance = Mockery::mock(Attendance::class);
        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $today)
            ->andReturn($existingAttendance);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('오늘 이미 출석하였습니다.');

        $this->service->checkIn($userId, '안녕하세요!');

        Carbon::setTestNow();
    }

    public function test_check_in_outside_allowed_time_throws_exception(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 3, 0, 0));

        // Restricted time window: 09:00 ~ 18:00
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('09:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('18:00');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('출석 가능 시간이 아닙니다.');

        $this->service->checkIn(1, '좋은 아침이에요~');

        Carbon::setTestNow();
    }

    public function test_check_in_route_requires_auth_middleware(): void
    {
        $routeFile = __DIR__ . '/../../src/routes/api.php';
        $this->assertFileExists($routeFile, 'Route file should exist');

        $routeContent = file_get_contents($routeFile);

        // Verify the check-in route exists within an auth:sanctum middleware group
        $this->assertStringContainsString("auth:sanctum", $routeContent);
        $this->assertStringContainsString('/check-in', $routeContent);

        // Verify check-in is inside the auth middleware block (not in the optional block)
        $authBlock = $this->extractMiddlewareBlock($routeContent, 'auth:sanctum');
        $this->assertNotEmpty($authBlock, 'auth:sanctum middleware block should exist');
        $this->assertStringContainsString('/check-in', $authBlock);

        // Verify check-in is NOT in the optional.sanctum block
        $optionalBlock = $this->extractMiddlewareBlock($routeContent, 'optional.sanctum');
        $this->assertStringNotContainsString('/check-in', $optionalBlock);
    }

    public function test_can_check_in_returns_true_when_allowed(): void
    {
        $userId = 1;
        $today = '2025-01-15';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('00:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('23:59');

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $today)
            ->andReturn(null);

        $this->assertTrue($this->service->canCheckIn($userId));

        Carbon::setTestNow();
    }

    public function test_can_check_in_returns_false_when_already_checked_in(): void
    {
        $userId = 1;
        $today = '2025-01-15';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_start_time', '00:00')
            ->andReturn('00:00');
        $this->settingsService->shouldReceive('getSetting')
            ->with('allowed_end_time', '23:59')
            ->andReturn('23:59');

        $existing = Mockery::mock(Attendance::class);
        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $today)
            ->andReturn($existing);

        $this->assertFalse($this->service->canCheckIn($userId));

        Carbon::setTestNow();
    }

    public function test_has_checked_in_today_returns_false_for_new_user(): void
    {
        $userId = 99;
        $today = '2025-01-15';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $today)
            ->andReturn(null);

        $this->assertFalse($this->service->hasCheckedInToday($userId));

        Carbon::setTestNow();
    }

    public function test_consecutive_days_increments_when_yesterday_attended(): void
    {
        $userId = 1;
        $yesterday = '2025-01-14';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $yesterdayAttendance = Mockery::mock(Attendance::class);
        $yesterdayAttendance->consecutive_days = 5;

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $yesterday)
            ->andReturn($yesterdayAttendance);

        $this->assertEquals(6, $this->service->getConsecutiveDays($userId));

        Carbon::setTestNow();
    }

    public function test_consecutive_days_resets_to_one_when_yesterday_missed(): void
    {
        $userId = 1;
        $yesterday = '2025-01-14';

        Carbon::setTestNow(Carbon::create(2025, 1, 15, 10, 0, 0));

        $this->attendanceRepo->shouldReceive('findByUserAndDate')
            ->with($userId, $yesterday)
            ->andReturn(null);

        $this->assertEquals(1, $this->service->getConsecutiveDays($userId));

        Carbon::setTestNow();
    }

    /**
     * Extract the content of a Route::middleware([...]) block from route file.
     */
    private function extractMiddlewareBlock(string $content, string $middleware): string
    {
        $pattern = "/Route::middleware\(\['" . preg_quote($middleware, '/') . "'\]\)->group\(function\s*\(\)\s*\{(.*?)\}\);/s";
        if (preg_match($pattern, $content, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
