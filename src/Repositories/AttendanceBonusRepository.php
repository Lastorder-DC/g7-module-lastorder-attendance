<?php

namespace Modules\Lastorder\Attendance\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Lastorder\Attendance\Enums\BonusType;
use Modules\Lastorder\Attendance\Models\AttendanceBonus;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceBonusRepositoryInterface;

class AttendanceBonusRepository implements AttendanceBonusRepositoryInterface
{
    public function __construct(
        protected AttendanceBonus $model,
    ) {}

    /**
     * 보너스 중복 확인
     */
    public function findByUserDateType(int $userId, string $date, BonusType $type): ?AttendanceBonus
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('bonus_date', $date)
            ->where('bonus_type', $type)
            ->first();
    }

    /**
     * 보너스 기록 생성
     */
    public function create(array $data): AttendanceBonus
    {
        return $this->model->create($data);
    }

    /**
     * 사용자별 보너스 이력 (페이지네이션)
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
