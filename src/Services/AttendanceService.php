<?php

namespace Modules\Lastorder\Attendance\Services;

use App\Extension\HookManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Lastorder\Attendance\Contracts\AttendanceRepositoryInterface;

class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $repository,
        private AttendanceBonusService $bonusService,
        private AttendanceSettingsService $settingsService,
        private AttendanceGreetingService $greetingService
    ) {}

    /**
     * 출석 가능 여부 확인
     *
     * @return array{can_check_in: bool, reason: string|null}
     */
    public function canCheckIn(int $userId): array
    {
        // 이미 출석했는지 확인
        $todayAttendance = $this->repository->findTodayByUserId($userId);
        if ($todayAttendance !== null) {
            return [
                'can_check_in' => false,
                'reason' => __('lastorder-attendance::messages.already_checked_in'),
            ];
        }

        // 출석 가능 시간 확인
        if (!$this->settingsService->isWithinAttendanceTime()) {
            return [
                'can_check_in' => false,
                'reason' => __('lastorder-attendance::messages.not_attendance_time'),
            ];
        }

        return [
            'can_check_in' => true,
            'reason' => null,
        ];
    }

    /**
     * 출석 체크
     */
    public function checkIn(int $userId, ?string $greeting, string $ip, bool $isAuto = false): object
    {
        // Before 훅
        HookManager::doAction('lastorder-attendance.attendance.before_checkin', $userId, $greeting);

        $attendance = DB::transaction(function () use ($userId, $greeting, $ip, $isAuto) {
            // 중복 출석 방지 (트랜잭션 내)
            $existing = $this->repository->findTodayByUserId($userId);
            if ($existing !== null) {
                return $existing;
            }

            // 순위 계산
            $rank = $this->repository->getTodayRank();

            // 연속출석 일수 (어제까지) + 오늘 = +1
            $consecutiveDays = $this->repository->getConsecutiveDays($userId) + 1;

            // 총 출석일수 + 오늘 = +1
            $totalDays = $this->repository->getTotalDays($userId) + 1;

            // 인삿말 처리 (빈 값이면 랜덤)
            if (empty($greeting)) {
                $greeting = $this->greetingService->getRandomGreeting();
            }

            // 기본 포인트
            $basePoint = $this->settingsService->getBasePoint();

            // 보너스 계산
            $bonuses = $this->bonusService->calculateAllBonuses($rank, $consecutiveDays);

            // 총 포인트
            $totalPoint = $basePoint + $bonuses['bonus_point'];

            // 출석 기록 생성
            return $this->repository->create([
                'user_id' => $userId,
                'attended_at' => now()->toDateString(),
                'attended_time' => now()->toTimeString(),
                'greeting' => $greeting,
                'base_point' => $basePoint,
                'bonus_point' => $bonuses['bonus_point'],
                'random_point' => $bonuses['random_point'],
                'rank_point' => $bonuses['rank_point'],
                'consecutive_point' => $bonuses['consecutive_point'],
                'total_point' => $totalPoint,
                'daily_rank' => $rank,
                'consecutive_days' => $consecutiveDays,
                'total_days' => $totalDays,
                'ip_address' => $ip,
                'is_auto' => $isAuto,
            ]);
        });

        // After 훅 - 트랜잭션 외부에서 실행 (롤백 시 훅 부작용 방지)
        HookManager::doAction('lastorder-attendance.attendance.after_checkin', $attendance);

        return $attendance;
    }

    /**
     * 오늘 출석 목록 조회
     */
    public function getTodayAttendances(int $perPage = 20, int $page = 1): object
    {
        return $this->repository->getTodayAttendances($perPage, $page);
    }

    /**
     * 날짜별 출석 목록 조회 (관리자)
     */
    public function getAttendancesByDate(string $date, int $perPage = 20, int $page = 1): object
    {
        return $this->repository->getAttendancesByDate($date, $perPage, $page);
    }

    /**
     * 월별 캘린더 조회
     */
    public function getMonthlyCalendar(int $userId, int $year, int $month): array
    {
        return $this->repository->getMonthlyCalendar($userId, $year, $month);
    }

    /**
     * 사용자 출석 현황 조회
     */
    public function getUserStatus(int $userId): array
    {
        return $this->repository->getUserStatus($userId);
    }

    /**
     * 출석 기록 삭제 (관리자)
     */
    public function deleteAttendance(int $id): bool
    {
        HookManager::doAction('lastorder-attendance.attendance.before_delete', $id);

        $result = $this->repository->delete($id);

        HookManager::doAction('lastorder-attendance.attendance.after_delete', $id);

        return $result;
    }

    /**
     * 연속출석 일수 재계산 (관리자)
     */
    public function recalculateConsecutiveDays(int $userId): int
    {
        return $this->repository->recalculateConsecutiveDays($userId);
    }
}
