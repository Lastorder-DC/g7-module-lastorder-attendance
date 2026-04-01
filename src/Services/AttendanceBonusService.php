<?php

namespace Modules\Lastorder\Attendance\Services;

use Modules\Lastorder\Attendance\Enums\BonusType;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Models\AttendanceBonus;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceBonusRepositoryInterface;

class AttendanceBonusService
{
    public function __construct(
        protected AttendanceBonusRepositoryInterface $bonusRepository,
        protected AttendanceSettingsService $settingsService,
    ) {}

    /**
     * 순위 보너스 확인 및 지급
     *
     * 1~3위에 해당하는 경우 보너스를 지급합니다.
     */
    public function checkAndGrantRankBonus(Attendance $attendance): ?AttendanceBonus
    {
        $bonusType = match ($attendance->daily_rank) {
            1 => BonusType::RANK_1,
            2 => BonusType::RANK_2,
            3 => BonusType::RANK_3,
            default => null,
        };

        if ($bonusType === null) {
            return null;
        }

        $bonusPoint = $this->getRankBonusPoint($attendance->daily_rank);

        if ($bonusPoint <= 0) {
            return null;
        }

        // 중복 지급 방지
        $existing = $this->bonusRepository->findByUserDateType(
            $attendance->user_id,
            $attendance->attendance_date->toDateString(),
            $bonusType,
        );

        if ($existing) {
            return $existing;
        }

        return $this->bonusRepository->create([
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'bonus_type' => $bonusType,
            'bonus_point' => $bonusPoint,
            'bonus_date' => $attendance->attendance_date->toDateString(),
            'description' => $attendance->daily_rank.'위 순위 보너스',
            'created_at' => now(),
        ]);
    }

    /**
     * 연속출석 보너스 확인 및 지급
     *
     * 7일/30일/365일 배수에 해당하는 경우 보너스를 지급합니다.
     *
     * @return AttendanceBonus[] 지급된 보너스 목록
     */
    public function checkAndGrantConsecutiveBonus(Attendance $attendance): array
    {
        $consecutiveDays = $attendance->consecutive_days;
        $bonuses = [];

        $consecutiveTypes = [
            365 => BonusType::YEARLY,
            30 => BonusType::MONTHLY,
            7 => BonusType::WEEKLY,
        ];

        foreach ($consecutiveTypes as $days => $bonusType) {
            if ($consecutiveDays >= $days && $consecutiveDays % $days === 0) {
                $bonusPoint = $this->getConsecutiveBonusPoint($bonusType, $days);

                if ($bonusPoint <= 0) {
                    continue;
                }

                // 중복 지급 방지
                $existing = $this->bonusRepository->findByUserDateType(
                    $attendance->user_id,
                    $attendance->attendance_date->toDateString(),
                    $bonusType,
                );

                if ($existing) {
                    $bonuses[] = $existing;

                    continue;
                }

                $bonuses[] = $this->bonusRepository->create([
                    'user_id' => $attendance->user_id,
                    'attendance_id' => $attendance->id,
                    'bonus_type' => $bonusType,
                    'bonus_point' => $bonusPoint,
                    'bonus_date' => $attendance->attendance_date->toDateString(),
                    'description' => $days.'일 연속출석 보너스',
                    'created_at' => now(),
                ]);
            }
        }

        return $bonuses;
    }

    /**
     * 순위별 보너스 포인트 조회
     */
    public function getRankBonusPoint(int $rank): int
    {
        return match ($rank) {
            1 => (int) $this->settingsService->getSetting('rank_1_bonus', 50),
            2 => (int) $this->settingsService->getSetting('rank_2_bonus', 30),
            3 => (int) $this->settingsService->getSetting('rank_3_bonus', 20),
            default => 0,
        };
    }

    /**
     * 연속출석 보너스 포인트 조회
     */
    public function getConsecutiveBonusPoint(BonusType $type, int $days): int
    {
        return match ($type) {
            BonusType::WEEKLY => (int) $this->settingsService->getSetting('weekly_bonus', 100),
            BonusType::MONTHLY => (int) $this->settingsService->getSetting('monthly_bonus', 500),
            BonusType::YEARLY => (int) $this->settingsService->getSetting('yearly_bonus', 5000),
            default => 0,
        };
    }
}
