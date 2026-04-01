<?php

namespace Modules\Lastorder\Attendance\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Lastorder\Attendance\Models\Attendance;

interface AttendanceRepositoryInterface
{
    /**
     * 특정 날짜 출석 조회
     */
    public function findByUserAndDate(int $userId, string $date): ?Attendance;

    /**
     * 날짜별 출석 목록 (페이지네이션)
     */
    public function getByDate(string $date, int $page = 1, int $perPage = 20): LengthAwarePaginator;

    /**
     * 사용자 월별 출석 기록
     */
    public function getByUserAndMonth(int $userId, int $year, int $month): Collection;

    /**
     * 연속 출석 일수 계산
     */
    public function getConsecutiveDays(int $userId, string $fromDate): int;

    /**
     * 총 출석 일수
     */
    public function getTotalDays(int $userId): int;

    /**
     * 당일 출석 순번 (COUNT+1)
     */
    public function getDailyRank(string $date): int;

    /**
     * 날짜별 총 출석 수
     */
    public function getCountByDate(string $date): int;

    /**
     * 출석 기록 생성
     */
    public function create(array $data): Attendance;
}
