<?php

namespace Modules\Lastorder\Attendance\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(
        protected Attendance $model,
    ) {}

    /**
     * 특정 날짜 출석 조회
     */
    public function findByUserAndDate(int $userId, string $date): ?Attendance
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('attendance_date', $date)
            ->first();
    }

    /**
     * 날짜별 출석 목록 (페이지네이션)
     */
    public function getByDate(string $date, int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('attendance_date', $date)
            ->orderBy('daily_rank')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 사용자 월별 출석 기록
     */
    public function getByUserAndMonth(int $userId, int $year, int $month): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->orderBy('attendance_date')
            ->get();
    }

    /**
     * 연속 출석 일수 계산
     *
     * 지정 날짜로부터 역순으로 연속된 출석 일수를 계산합니다.
     */
    public function getConsecutiveDays(int $userId, string $fromDate): int
    {
        $attendances = $this->model
            ->where('user_id', $userId)
            ->where('attendance_date', '<=', $fromDate)
            ->orderByDesc('attendance_date')
            ->pluck('attendance_date');

        if ($attendances->isEmpty()) {
            return 0;
        }

        $consecutiveDays = 0;
        $expectedDate = \Carbon\Carbon::parse($fromDate);

        foreach ($attendances as $attendanceDate) {
            $date = \Carbon\Carbon::parse($attendanceDate);

            if ($date->toDateString() === $expectedDate->toDateString()) {
                $consecutiveDays++;
                $expectedDate = $expectedDate->subDay();
            } else {
                break;
            }
        }

        return $consecutiveDays;
    }

    /**
     * 총 출석 일수
     */
    public function getTotalDays(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * 당일 출석 순번 (COUNT+1)
     *
     * 현재까지의 출석 수 + 1을 반환하여 다음 출석자의 순번으로 사용합니다.
     */
    public function getDailyRank(string $date): int
    {
        return $this->model
            ->where('attendance_date', $date)
            ->count() + 1;
    }

    /**
     * 날짜별 총 출석 수
     */
    public function getCountByDate(string $date): int
    {
        return $this->model
            ->where('attendance_date', $date)
            ->count();
    }

    /**
     * 출석 기록 생성
     */
    public function create(array $data): Attendance
    {
        return $this->model->create($data);
    }
}
