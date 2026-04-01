<?php

namespace Modules\Lastorder\Attendance\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Lastorder\Attendance\Enums\BonusType;
use Modules\Lastorder\Attendance\Models\AttendanceBonus;

interface AttendanceBonusRepositoryInterface
{
    /**
     * 보너스 중복 확인
     */
    public function findByUserDateType(int $userId, string $date, BonusType $type): ?AttendanceBonus;

    /**
     * 보너스 기록 생성
     */
    public function create(array $data): AttendanceBonus;

    /**
     * 사용자별 보너스 이력 (페이지네이션)
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator;
}
