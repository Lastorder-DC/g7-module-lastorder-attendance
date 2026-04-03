<?php

namespace Modules\Lastorder\Attendance\Contracts;

interface AttendanceRepositoryInterface
{
    /**
     * 오늘 특정 사용자의 출석 기록 조회
     */
    public function findTodayByUserId(int $userId): ?object;

    /**
     * 출석 기록 생성
     */
    public function create(array $data): object;

    /**
     * 오늘 출석 순위 (현재 출석 인원수 + 1)
     */
    public function getTodayRank(): int;

    /**
     * 연속출석 일수 계산 (어제까지)
     */
    public function getConsecutiveDays(int $userId): int;

    /**
     * 총 출석 일수
     */
    public function getTotalDays(int $userId): int;

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayAttendances(int $perPage = 20, int $page = 1): object;

    /**
     * 특정 날짜 출석 목록 (관리자, 페이지네이션)
     */
    public function getAttendancesByDate(string $date, int $perPage = 20, int $page = 1): object;

    /**
     * 월별 출석 캘린더 데이터
     */
    public function getMonthlyCalendar(int $userId, int $year, int $month): array;

    /**
     * 내 출석 현황 (연속/총 일수)
     */
    public function getUserStatus(int $userId): array;

    /**
     * 출석 기록 삭제 (관리자)
     */
    public function delete(int $id): bool;

    /**
     * 연속출석 일수 재계산 (관리자)
     */
    public function recalculateConsecutiveDays(int $userId): int;
}
