<?php

namespace Modules\Lastorder\Attendance\Tests\Unit;

use Carbon\Carbon;
use Mockery;
use Mockery\MockInterface;
use Modules\Lastorder\Attendance\Enums\BonusType;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Models\AttendanceBonus;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceBonusRepositoryInterface;
use Modules\Lastorder\Attendance\Services\AttendanceBonusService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;
use PHPUnit\Framework\TestCase;

class AttendanceBonusServiceTest extends TestCase
{
    private MockInterface $bonusRepo;

    private MockInterface $settingsService;

    private AttendanceBonusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bonusRepo = Mockery::mock(AttendanceBonusRepositoryInterface::class);
        $this->settingsService = Mockery::mock(AttendanceSettingsService::class);

        $this->service = new AttendanceBonusService(
            $this->bonusRepo,
            $this->settingsService,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeAttendance(array $attributes = []): MockInterface
    {
        $defaults = [
            'id' => 1,
            'user_id' => 1,
            'attendance_date' => Carbon::parse('2025-01-15'),
            'daily_rank' => 1,
            'consecutive_days' => 1,
            'total_days' => 1,
        ];

        $data = array_merge($defaults, $attributes);

        $attendance = Mockery::mock(Attendance::class)->shouldIgnoreMissing();
        $attendance->shouldReceive('getAttribute')->with('id')->andReturn($data['id']);
        $attendance->shouldReceive('getAttribute')->with('user_id')->andReturn($data['user_id']);
        $attendance->shouldReceive('getAttribute')->with('attendance_date')->andReturn($data['attendance_date']);
        $attendance->shouldReceive('getAttribute')->with('daily_rank')->andReturn($data['daily_rank']);
        $attendance->shouldReceive('getAttribute')->with('consecutive_days')->andReturn($data['consecutive_days']);
        $attendance->shouldReceive('getAttribute')->with('total_days')->andReturn($data['total_days']);
        $attendance->shouldReceive('getAttribute')->andReturn(null);
        $attendance->shouldReceive('getAttributeValue')->andReturnUsing(function ($key) use ($data) {
            return $data[$key] ?? null;
        });

        $attendance->id = $data['id'];
        $attendance->user_id = $data['user_id'];
        $attendance->daily_rank = $data['daily_rank'];
        $attendance->consecutive_days = $data['consecutive_days'];
        $attendance->total_days = $data['total_days'];

        return $attendance;
    }

    // ─── getRankBonusPoint ─────────────────────────────────────

    public function test_get_rank_bonus_point_for_rank_1(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)->andReturn(50);

        $this->assertEquals(50, $this->service->getRankBonusPoint(1));
    }

    public function test_get_rank_bonus_point_for_rank_2(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_2_bonus', 30)->andReturn(30);

        $this->assertEquals(30, $this->service->getRankBonusPoint(2));
    }

    public function test_get_rank_bonus_point_for_rank_3(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_3_bonus', 20)->andReturn(20);

        $this->assertEquals(20, $this->service->getRankBonusPoint(3));
    }

    public function test_get_rank_bonus_point_returns_0_for_rank_4_plus(): void
    {
        $this->assertEquals(0, $this->service->getRankBonusPoint(4));
        $this->assertEquals(0, $this->service->getRankBonusPoint(100));
    }

    // ─── getConsecutiveBonusPoint ──────────────────────────────

    public function test_get_consecutive_bonus_point_for_weekly(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)->andReturn(100);

        $this->assertEquals(100, $this->service->getConsecutiveBonusPoint(BonusType::WEEKLY, 7));
    }

    public function test_get_consecutive_bonus_point_for_monthly(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('monthly_bonus', 500)->andReturn(500);

        $this->assertEquals(500, $this->service->getConsecutiveBonusPoint(BonusType::MONTHLY, 30));
    }

    public function test_get_consecutive_bonus_point_for_yearly(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('yearly_bonus', 5000)->andReturn(5000);

        $this->assertEquals(5000, $this->service->getConsecutiveBonusPoint(BonusType::YEARLY, 365));
    }

    public function test_get_consecutive_bonus_point_returns_0_for_rank_types(): void
    {
        $this->assertEquals(0, $this->service->getConsecutiveBonusPoint(BonusType::RANK_1, 1));
        $this->assertEquals(0, $this->service->getConsecutiveBonusPoint(BonusType::RANK_2, 2));
        $this->assertEquals(0, $this->service->getConsecutiveBonusPoint(BonusType::RANK_3, 3));
    }

    // ─── checkAndGrantRankBonus ────────────────────────────────

    public function test_rank_bonus_returns_null_for_rank_beyond_3(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 5]);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);
        $this->assertNull($result);
    }

    public function test_rank_bonus_returns_null_when_bonus_point_is_zero(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 1]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)->andReturn(0);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);
        $this->assertNull($result);
    }

    public function test_rank_bonus_skips_duplicate(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 1]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)->andReturn(50);

        $existingBonus = Mockery::mock(AttendanceBonus::class);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::RANK_1)
            ->andReturn($existingBonus);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);
        $this->assertSame($existingBonus, $result);
    }

    // ─── checkAndGrantConsecutiveBonus ──────────────────────────

    public function test_consecutive_bonus_empty_for_non_milestone(): void
    {
        $attendance = $this->makeAttendance(['consecutive_days' => 3]);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_consecutive_bonus_grants_weekly_at_14_days(): void
    {
        $attendance = $this->makeAttendance(['consecutive_days' => 14]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)->andReturn(100);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::WEEKLY)
            ->andReturn(null);

        $bonus = Mockery::mock(AttendanceBonus::class);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (array $d) => $d['bonus_type'] === BonusType::WEEKLY && $d['bonus_point'] === 100))
            ->andReturn($bonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);
        $this->assertCount(1, $result);
    }

    public function test_consecutive_bonus_grants_multiple_at_210_days(): void
    {
        // 210 % 30 == 0 (monthly), 210 % 7 == 0 (weekly), 210 % 365 != 0
        $attendance = $this->makeAttendance(['consecutive_days' => 210]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('monthly_bonus', 500)->andReturn(500);
        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)->andReturn(100);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::MONTHLY)
            ->andReturn(null);
        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::WEEKLY)
            ->andReturn(null);

        $monthlyBonus = Mockery::mock(AttendanceBonus::class);
        $weeklyBonus = Mockery::mock(AttendanceBonus::class);

        $this->bonusRepo->shouldReceive('create')
            ->with(Mockery::on(fn (array $d) => $d['bonus_type'] === BonusType::MONTHLY))
            ->once()->andReturn($monthlyBonus);
        $this->bonusRepo->shouldReceive('create')
            ->with(Mockery::on(fn (array $d) => $d['bonus_type'] === BonusType::WEEKLY))
            ->once()->andReturn($weeklyBonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);
        $this->assertCount(2, $result);
    }
}
