<?php

namespace Modules\Lastorder\Attendance\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Lastorder\Attendance\Models\Attendance;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected AttendanceBonusService $bonusService,
        protected AttendanceSettingsService $settingsService,
    ) {}

    /**
     * 출석 체크 (핵심 메서드)
     *
     * 1. 출석 가능 시간 확인
     * 2. 트랜잭션 내에서 중복 출석 확인 + 순위 계산 + 기록 저장 + 보너스 처리
     *
     * @throws \RuntimeException 출석 불가 시
     */
    public function checkIn(int $userId, string $greeting, ?string $ip = null): Attendance
    {
        if (! $this->isWithinAllowedTime()) {
            throw new \RuntimeException('출석 가능 시간이 아닙니다.');
        }

        // 빠른 사전 체크 (대부분의 중복 요청을 트랜잭션 없이 차단)
        if ($this->hasCheckedInToday($userId)) {
            throw new \RuntimeException('오늘 이미 출석하였습니다.');
        }

        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        // 포인트 계산 (트랜잭션 외부 — 설정값 읽기만)
        $basePoint = (int) $this->settingsService->getSetting('base_point', 10);
        $randomPoint = $this->calculateRandomPoint();
        $totalPoint = $basePoint + $randomPoint;

        try {
            return DB::transaction(function () use (
                $userId, $today, $now, $greeting, $ip,
                $basePoint, $randomPoint, $totalPoint,
            ) {
                // 트랜잭션 내 중복 체크 (레이스 컨디션 방지)
                if ($this->hasCheckedInToday($userId)) {
                    throw new \RuntimeException('오늘 이미 출석하였습니다.');
                }

                // 연속/총 출석 일수는 DB 저장값에서 조회 (재계산 불필요)
                $consecutiveDays = $this->getConsecutiveDays($userId);
                $totalDays = $this->getTotalDays($userId);

                // 당일 순위 계산 (트랜잭션 내부에서 수행하여 동시 출석 시 정확성 보장)
                $dailyRank = $this->getDailyRank($today);

                // 출석 기록 저장
                $attendance = $this->attendanceRepository->create([
                    'user_id' => $userId,
                    'attendance_date' => $today,
                    'attendance_time' => $now->toTimeString(),
                    'greeting' => $greeting,
                    'base_point' => $basePoint,
                    'random_point' => $randomPoint,
                    'total_point' => $totalPoint,
                    'daily_rank' => $dailyRank,
                    'consecutive_days' => $consecutiveDays,
                    'total_days' => $totalDays,
                    'ip_address' => $ip,
                    'is_auto' => false,
                ]);

                // 순위 보너스 확인 및 지급
                $this->bonusService->checkAndGrantRankBonus($attendance);

                // 연속출석 보너스 확인 및 지급
                $this->bonusService->checkAndGrantConsecutiveBonus($attendance);

                return $attendance;
            });
        } catch (QueryException $e) {
            // unique 제약조건 위반 시 (동시 요청으로 인한 중복)
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint')) {
                throw new \RuntimeException('오늘 이미 출석하였습니다.');
            }
            throw $e;
        }
    }

    /**
     * 출석 가능 여부 확인
     */
    public function canCheckIn(int $userId): bool
    {
        return $this->isWithinAllowedTime() && ! $this->hasCheckedInToday($userId);
    }

    /**
     * 현재 시간이 출석 가능 시간대인지 확인
     */
    public function isWithinAllowedTime(): bool
    {
        $now = Carbon::now();

        try {
            $startTime = Carbon::createFromFormat('H:i', $this->settingsService->getSetting('allowed_start_time', '00:00'));
            $endTime = Carbon::createFromFormat('H:i', $this->settingsService->getSetting('allowed_end_time', '23:59'));
        } catch (\Exception $e) {
            Log::warning('Attendance: 잘못된 시간 설정값으로 기본 허용 시간을 사용합니다.', [
                'error' => $e->getMessage(),
            ]);
            $startTime = Carbon::createFromFormat('H:i', '00:00');
            $endTime = Carbon::createFromFormat('H:i', '23:59');
        }

        return $now->between($startTime, $endTime);
    }

    /**
     * 오늘 출석했는지 확인
     */
    public function hasCheckedInToday(int $userId): bool
    {
        return $this->attendanceRepository->findByUserAndDate(
            $userId,
            Carbon::today()->toDateString(),
        ) !== null;
    }

    /**
     * 연속 출석 일수 계산
     *
     * 어제 출석했으면 DB에 저장된 연속일수 + 1, 아니면 1(오늘부터 시작)
     * 저장된 값을 활용하여 불필요한 재계산을 방지합니다.
     */
    public function getConsecutiveDays(int $userId): int
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $yesterdayAttendance = $this->attendanceRepository->findByUserAndDate($userId, $yesterday);

        if ($yesterdayAttendance) {
            return $yesterdayAttendance->consecutive_days + 1;
        }

        return 1;
    }

    /**
     * 현재 연속 출석 일수 조회 (보고/표시용)
     *
     * 가장 최근 출석 기록에 저장된 연속 출석 일수를 반환합니다.
     * checkIn 시 사용되는 getConsecutiveDays()와 달리 "다음 값"이 아닌 "현재 값"을 반환합니다.
     */
    public function getCurrentConsecutiveDays(int $userId): int
    {
        return $this->attendanceRepository->getConsecutiveDays($userId);
    }

    /**
     * 현재 총 출석 일수 조회 (보고/표시용)
     *
     * 가장 최근 출석 기록에 저장된 총 출석 일수를 반환합니다.
     */
    public function getCurrentTotalDays(int $userId): int
    {
        return $this->attendanceRepository->getTotalDays($userId);
    }

    /**
     * 총 출석 일수 계산
     *
     * 저장된 최근 총 출석 일수 + 1 (오늘 출석 포함)
     */
    public function getTotalDays(int $userId): int
    {
        return $this->attendanceRepository->getTotalDays($userId) + 1;
    }

    /**
     * 오늘 몇 번째 출석인지 반환
     */
    public function getDailyRank(string $date): int
    {
        return $this->attendanceRepository->getDailyRank($date);
    }

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayAttendances(int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->attendanceRepository->getByDate(
            Carbon::today()->toDateString(),
            $page,
            $perPage,
        );
    }

    /**
     * 월별 출석 캘린더 데이터
     *
     * 해당 월의 각 날짜별 출석 여부를 배열로 반환합니다.
     *
     * @return array<string, array{attended: bool, attendance: ?Attendance}>
     */
    public function getMonthlyCalendar(int $userId, int $year, int $month): array
    {
        $attendances = $this->attendanceRepository->getByUserAndMonth($userId, $year, $month);

        // 출석 기록을 날짜별로 인덱싱
        $attendanceByDate = $attendances->keyBy(fn (Attendance $a) => $a->attendance_date->toDateString());

        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = Carbon::today();

        $calendar = [];

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dateString = $date->toDateString();
            $attendance = $attendanceByDate->get($dateString);

            $calendar[$dateString] = [
                'attended' => $attendance !== null,
                'attendance' => $attendance,
                'is_future' => $date->gt($today),
            ];
        }

        return $calendar;
    }

    /**
     * 랜덤 포인트 계산
     *
     * 랜덤 포인트가 활성화되어 있으면 확률 판정 후 포인트를 반환합니다.
     */
    protected function calculateRandomPoint(): int
    {
        $enabled = $this->settingsService->getSetting('random_point_enabled', false);

        if (! $enabled) {
            return 0;
        }

        $chance = (int) $this->settingsService->getSetting('random_point_chance', 30);

        // 확률 판정 (0~99 중 chance 미만이면 당첨)
        if (random_int(0, 99) >= $chance) {
            return 0;
        }

        $min = (int) $this->settingsService->getSetting('random_point_min', 1);
        $max = (int) $this->settingsService->getSetting('random_point_max', 100);

        if ($min > $max) {
            Log::warning('Attendance: 랜덤 포인트 최소값이 최대값보다 큽니다. 값을 교환합니다.', [
                'min' => $min,
                'max' => $max,
            ]);
            [$min, $max] = [$max, $min];
        }

        return random_int($min, $max);
    }
}
