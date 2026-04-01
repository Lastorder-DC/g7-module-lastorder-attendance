<?php

namespace Modules\Lastorder\Attendance\Tests\Feature;

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

class BonusTest extends TestCase
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

        $attendance->id = $data['id'];
        $attendance->user_id = $data['user_id'];
        $attendance->daily_rank = $data['daily_rank'];
        $attendance->consecutive_days = $data['consecutive_days'];
        $attendance->total_days = $data['total_days'];

        // Make attendance_date accessible as property via __get
        $attendance->shouldReceive('__get')->with('attendance_date')->andReturn($data['attendance_date']);
        $attendance->shouldReceive('__get')->with('id')->andReturn($data['id']);
        $attendance->shouldReceive('__get')->with('user_id')->andReturn($data['user_id']);
        $attendance->shouldReceive('__get')->with('daily_rank')->andReturn($data['daily_rank']);
        $attendance->shouldReceive('__get')->with('consecutive_days')->andReturn($data['consecutive_days']);
        $attendance->shouldReceive('__get')->with('total_days')->andReturn($data['total_days']);

        return $attendance;
    }

    private function makeBonus(array $attributes = []): MockInterface
    {
        $bonus = Mockery::mock(AttendanceBonus::class)->makePartial();
        foreach ($attributes as $key => $value) {
            $bonus->{$key} = $value;
        }

        return $bonus;
    }

    // ─── Rank Bonus Tests ──────────────────────────────────────

    public function test_rank_1_bonus_is_granted(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 1]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)
            ->andReturn(50);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::RANK_1)
            ->andReturn(null);

        $expectedBonus = $this->makeBonus([
            'user_id' => 1,
            'bonus_type' => BonusType::RANK_1,
            'bonus_point' => 50,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 1
                    && $data['bonus_type'] === BonusType::RANK_1
                    && $data['bonus_point'] === 50
                    && $data['bonus_date'] === '2025-01-15'
                    && str_contains($data['description'], '1위');
            }))
            ->andReturn($expectedBonus);

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertInstanceOf(AttendanceBonus::class, $result);
        $this->assertEquals(BonusType::RANK_1, $result->bonus_type);
        $this->assertEquals(50, $result->bonus_point);
    }

    public function test_rank_2_bonus_is_granted(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 2]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_2_bonus', 30)
            ->andReturn(30);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::RANK_2)
            ->andReturn(null);

        $expectedBonus = $this->makeBonus([
            'user_id' => 1,
            'bonus_type' => BonusType::RANK_2,
            'bonus_point' => 30,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 1
                    && $data['bonus_type'] === BonusType::RANK_2
                    && $data['bonus_point'] === 30
                    && str_contains($data['description'], '2위');
            }))
            ->andReturn($expectedBonus);

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertInstanceOf(AttendanceBonus::class, $result);
        $this->assertEquals(BonusType::RANK_2, $result->bonus_type);
        $this->assertEquals(30, $result->bonus_point);
    }

    public function test_rank_3_bonus_is_granted(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 3]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_3_bonus', 20)
            ->andReturn(20);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::RANK_3)
            ->andReturn(null);

        $expectedBonus = $this->makeBonus([
            'user_id' => 1,
            'bonus_type' => BonusType::RANK_3,
            'bonus_point' => 20,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['user_id'] === 1
                    && $data['bonus_type'] === BonusType::RANK_3
                    && $data['bonus_point'] === 20
                    && str_contains($data['description'], '3위');
            }))
            ->andReturn($expectedBonus);

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertInstanceOf(AttendanceBonus::class, $result);
        $this->assertEquals(BonusType::RANK_3, $result->bonus_type);
        $this->assertEquals(20, $result->bonus_point);
    }

    public function test_no_bonus_for_rank_4_or_higher(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 4]);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertNull($result);
    }

    public function test_no_bonus_for_rank_10(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 10]);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertNull($result);
    }

    public function test_rank_bonus_not_duplicated(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 1]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)
            ->andReturn(50);

        $existingBonus = $this->makeBonus([
            'user_id' => 1,
            'bonus_type' => BonusType::RANK_1,
            'bonus_point' => 50,
        ]);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::RANK_1)
            ->andReturn($existingBonus);

        // create should NOT be called since bonus already exists
        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertSame($existingBonus, $result);
    }

    // ─── Rank Bonus Point Retrieval ────────────────────────────

    public function test_get_rank_bonus_point_for_each_rank(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)->andReturn(50);
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_2_bonus', 30)->andReturn(30);
        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_3_bonus', 20)->andReturn(20);

        $this->assertEquals(50, $this->service->getRankBonusPoint(1));
        $this->assertEquals(30, $this->service->getRankBonusPoint(2));
        $this->assertEquals(20, $this->service->getRankBonusPoint(3));
        $this->assertEquals(0, $this->service->getRankBonusPoint(4));
        $this->assertEquals(0, $this->service->getRankBonusPoint(99));
    }

    // ─── Consecutive Bonus Tests ───────────────────────────────

    public function test_weekly_consecutive_bonus_at_7_days(): void
    {
        $attendance = $this->makeAttendance(['consecutive_days' => 7]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)
            ->andReturn(100);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::WEEKLY)
            ->andReturn(null);

        $weeklyBonus = $this->makeBonus([
            'bonus_type' => BonusType::WEEKLY,
            'bonus_point' => 100,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['bonus_type'] === BonusType::WEEKLY
                    && $data['bonus_point'] === 100
                    && str_contains($data['description'], '7일');
            }))
            ->andReturn($weeklyBonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);

        $this->assertCount(1, $result);
        $this->assertEquals(BonusType::WEEKLY, $result[0]->bonus_type);
        $this->assertEquals(100, $result[0]->bonus_point);
    }

    public function test_monthly_consecutive_bonus_at_30_days(): void
    {
        $attendance = $this->makeAttendance(['consecutive_days' => 30]);

        // 30 is divisible by both 30 and (not 365), but also not by 7? Actually 30 % 7 != 0
        // So only monthly bonus should be granted
        $this->settingsService->shouldReceive('getSetting')
            ->with('monthly_bonus', 500)
            ->andReturn(500);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::MONTHLY)
            ->andReturn(null);

        $monthlyBonus = $this->makeBonus([
            'bonus_type' => BonusType::MONTHLY,
            'bonus_point' => 500,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['bonus_type'] === BonusType::MONTHLY
                    && $data['bonus_point'] === 500
                    && str_contains($data['description'], '30일');
            }))
            ->andReturn($monthlyBonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);

        $this->assertCount(1, $result);
        $this->assertEquals(BonusType::MONTHLY, $result[0]->bonus_type);
        $this->assertEquals(500, $result[0]->bonus_point);
    }

    public function test_yearly_consecutive_bonus_at_365_days(): void
    {
        // 365 is divisible by 365, not by 30 (365 % 30 = 5), not by 7 (365 % 7 = 1)
        $attendance = $this->makeAttendance(['consecutive_days' => 365]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('yearly_bonus', 5000)
            ->andReturn(5000);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::YEARLY)
            ->andReturn(null);

        $yearlyBonus = $this->makeBonus([
            'bonus_type' => BonusType::YEARLY,
            'bonus_point' => 5000,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data) {
                return $data['bonus_type'] === BonusType::YEARLY
                    && $data['bonus_point'] === 5000
                    && str_contains($data['description'], '365일');
            }))
            ->andReturn($yearlyBonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);

        $this->assertCount(1, $result);
        $this->assertEquals(BonusType::YEARLY, $result[0]->bonus_type);
        $this->assertEquals(5000, $result[0]->bonus_point);
    }

    public function test_no_consecutive_bonus_at_non_milestone_days(): void
    {
        // 5 days is not divisible by 7, 30, or 365
        $attendance = $this->makeAttendance(['consecutive_days' => 5]);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_multiple_bonuses_at_210_days(): void
    {
        // 210 days: 210 % 365 != 0, 210 % 30 == 0, 210 % 7 == 0
        // Both monthly and weekly bonuses should be granted
        $attendance = $this->makeAttendance(['consecutive_days' => 210]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('monthly_bonus', 500)
            ->andReturn(500);
        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)
            ->andReturn(100);

        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::MONTHLY)
            ->andReturn(null);
        $this->bonusRepo->shouldReceive('findByUserDateType')
            ->with(1, '2025-01-15', BonusType::WEEKLY)
            ->andReturn(null);

        $monthlyBonus = $this->makeBonus([
            'bonus_type' => BonusType::MONTHLY,
            'bonus_point' => 500,
        ]);
        $weeklyBonus = $this->makeBonus([
            'bonus_type' => BonusType::WEEKLY,
            'bonus_point' => 100,
        ]);

        $this->bonusRepo->shouldReceive('create')
            ->with(Mockery::on(fn (array $d) => $d['bonus_type'] === BonusType::MONTHLY))
            ->once()
            ->andReturn($monthlyBonus);
        $this->bonusRepo->shouldReceive('create')
            ->with(Mockery::on(fn (array $d) => $d['bonus_type'] === BonusType::WEEKLY))
            ->once()
            ->andReturn($weeklyBonus);

        $result = $this->service->checkAndGrantConsecutiveBonus($attendance);

        $this->assertCount(2, $result);
    }

    public function test_get_consecutive_bonus_point(): void
    {
        $this->settingsService->shouldReceive('getSetting')
            ->with('weekly_bonus', 100)->andReturn(100);
        $this->settingsService->shouldReceive('getSetting')
            ->with('monthly_bonus', 500)->andReturn(500);
        $this->settingsService->shouldReceive('getSetting')
            ->with('yearly_bonus', 5000)->andReturn(5000);

        $this->assertEquals(100, $this->service->getConsecutiveBonusPoint(BonusType::WEEKLY, 7));
        $this->assertEquals(500, $this->service->getConsecutiveBonusPoint(BonusType::MONTHLY, 30));
        $this->assertEquals(5000, $this->service->getConsecutiveBonusPoint(BonusType::YEARLY, 365));
    }

    public function test_rank_bonus_returns_null_when_bonus_point_is_zero(): void
    {
        $attendance = $this->makeAttendance(['daily_rank' => 1]);

        $this->settingsService->shouldReceive('getSetting')
            ->with('rank_1_bonus', 50)
            ->andReturn(0);

        $this->bonusRepo->shouldNotReceive('create');

        $result = $this->service->checkAndGrantRankBonus($attendance);

        $this->assertNull($result);
    }
}
