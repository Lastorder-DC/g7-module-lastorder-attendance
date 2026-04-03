<?php

namespace Modules\Lastorder\Attendance\Services;

class AttendanceBonusService
{
    public function __construct(
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 모든 보너스 계산
     *
     * @param int $rank 당일 출석 순위
     * @param int $consecutiveDays 연속출석 일수 (오늘 포함)
     * @return array{rank_point: int, consecutive_point: int, random_point: int, bonus_point: int}
     */
    public function calculateAllBonuses(int $rank, int $consecutiveDays): array
    {
        $rankPoint = $this->calculateRankBonus($rank);
        $consecutivePoint = $this->calculateConsecutiveBonus($consecutiveDays);
        $randomPoint = $this->calculateRandomBonus();

        return [
            'rank_point' => $rankPoint,
            'consecutive_point' => $consecutivePoint,
            'random_point' => $randomPoint,
            'bonus_point' => $rankPoint + $consecutivePoint + $randomPoint,
        ];
    }

    /**
     * 순위 보너스 계산
     */
    public function calculateRankBonus(int $rank): int
    {
        return $this->settingsService->getRankBonus($rank);
    }

    /**
     * 연속출석 보너스 계산
     */
    public function calculateConsecutiveBonus(int $consecutiveDays): int
    {
        return $this->settingsService->getConsecutiveBonus($consecutiveDays);
    }

    /**
     * 랜덤 보너스 계산
     */
    public function calculateRandomBonus(): int
    {
        return $this->settingsService->calculateRandomPoint();
    }
}
