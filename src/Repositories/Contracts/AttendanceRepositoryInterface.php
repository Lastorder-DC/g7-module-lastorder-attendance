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
     * 저장된 연속 출석 일수 조회 (가장 최근 출석 기록 기준)
     */
    public function getConsecutiveDays(int $userId): int;

    /**
     * 저장된 총 출석 일수 조회 (가장 최근 출석 기록 기준)
     */
    public function getTotalDays(int $userId): int;

    /**
     * 연속 출석 일수 재계산 (DB에 값이 없거나 관리자 수동 재계산 시 사용)
     */
    public function recalculateConsecutiveDays(int $userId, string $fromDate): int;

    /**
     * 총 출석 일수 재계산 (DB에 값이 없거나 관리자 수동 재계산 시 사용)
     */
    public function recalculateTotalDays(int $userId): int;

    /**
     * 당일 출석 순번 (COUNT+1)
     */
    public function getDailyRank(string $date): int;

    /**
     * 날짜별 총 출석 수
     */
    public function getCountByDate(string $date): int;

    /**
     * ID로 출석 기록 조회
     */
    public function find(int $id): ?Attendance;

    /**
     * 출석 기록 생성
     */
    public function create(array $data): Attendance;
}
