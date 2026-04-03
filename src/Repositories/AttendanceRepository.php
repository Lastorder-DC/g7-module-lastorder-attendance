<?php

namespace Modules\Lastorder\Attendance\Repositories;

use Carbon\Carbon;
use Modules\Lastorder\Attendance\Contracts\AttendanceRepositoryInterface;
use Modules\Lastorder\Attendance\Models\Attendance;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    /**
     * 오늘 특정 사용자의 출석 기록 조회
     */
    public function findTodayByUserId(int $userId): ?object
    {
        return Attendance::where('user_id', $userId)
            ->where('attended_at', Carbon::today()->toDateString())
            ->first();
    }

    /**
     * 출석 기록 생성
     */
    public function create(array $data): object
    {
        return Attendance::create($data);
    }

    /**
     * 오늘 출석 순위 (현재 출석 인원수 + 1)
     */
    public function getTodayRank(): int
    {
        return Attendance::where('attended_at', Carbon::today()->toDateString())
            ->count() + 1;
    }

    /**
     * 연속출석 일수 계산 (어제까지)
     */
    public function getConsecutiveDays(int $userId): int
    {
        $consecutiveDays = 0;
        $checkDate = Carbon::yesterday();

        while (true) {
            $exists = Attendance::where('user_id', $userId)
                ->where('attended_at', $checkDate->toDateString())
                ->exists();

            if (!$exists) {
                break;
            }

            $consecutiveDays++;
            $checkDate = $checkDate->subDay();
        }

        return $consecutiveDays;
    }

    /**
     * 총 출석 일수
     */
    public function getTotalDays(int $userId): int
    {
        return Attendance::where('user_id', $userId)->count();
    }

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayAttendances(int $perPage = 20, int $page = 1): object
    {
        return Attendance::with('user')
            ->where('attended_at', Carbon::today()->toDateString())
            ->orderBy('daily_rank', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 특정 날짜 출석 목록 (관리자, 페이지네이션)
     */
    public function getAttendancesByDate(string $date, int $perPage = 20, int $page = 1): object
    {
        return Attendance::with('user')
            ->where('attended_at', $date)
            ->orderBy('daily_rank', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * 월별 출석 캘린더 데이터
     */
    public function getMonthlyCalendar(int $userId, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        return Attendance::where('user_id', $userId)
            ->whereBetween('attended_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('attended_at')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();
    }

    /**
     * 내 출석 현황 (연속/총 일수)
     */
    public function getUserStatus(int $userId): array
    {
        $todayAttendance = $this->findTodayByUserId($userId);
        $consecutiveDays = $this->getConsecutiveDays($userId);
        $totalDays = $this->getTotalDays($userId);

        // 오늘 출석했으면 연속일수에 +1 (오늘 포함)
        if ($todayAttendance) {
            $consecutiveDays++;
        }

        return [
            'consecutive_days' => $consecutiveDays,
            'total_days' => $totalDays,
            'has_attended_today' => $todayAttendance !== null,
            'today_attendance' => $todayAttendance,
        ];
    }

    /**
     * 출석 기록 삭제 (관리자)
     */
    public function delete(int $id): bool
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return false;
        }

        return $attendance->delete();
    }

    /**
     * 연속출석 일수 재계산 (관리자)
     */
    public function recalculateConsecutiveDays(int $userId): int
    {
        $consecutiveDays = 0;
        $checkDate = Carbon::today();

        while (true) {
            $exists = Attendance::where('user_id', $userId)
                ->where('attended_at', $checkDate->toDateString())
                ->exists();

            if (!$exists) {
                break;
            }

            $consecutiveDays++;
            $checkDate = $checkDate->subDay();
        }

        return $consecutiveDays;
    }
}
