<?php

namespace Modules\Lastorder\Attendance\Http\Controllers\Api\User;

use App\Http\Controllers\Api\Base\BaseApiController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lastorder\Attendance\Http\Requests\User\CheckInRequest;
use Modules\Lastorder\Attendance\Http\Resources\AttendanceCalendarResource;
use Modules\Lastorder\Attendance\Http\Resources\AttendanceListResource;
use Modules\Lastorder\Attendance\Http\Resources\AttendanceResource;
use Modules\Lastorder\Attendance\Services\AttendanceGreetingService;
use Modules\Lastorder\Attendance\Services\AttendanceService;

/**
 * 사용자 출석 API 컨트롤러
 */
class AttendanceController extends BaseApiController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceGreetingService $greetingService,
    ) {}

    /**
     * 출석 체크
     *
     * POST /api/modules/lastorder-attendance/check-in
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $attendance = $this->attendanceService->checkIn(
                $user->id,
                $request->validated('greeting'),
                $request->ip(),
            );

            $attendance->load(['bonuses', 'user']);

            return $this->successWithResource(
                'common.success',
                new AttendanceResource($attendance),
                201,
            );
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 오늘 출석 목록
     *
     * GET /api/modules/lastorder-attendance/today
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 20);

            $attendances = $this->attendanceService->getTodayAttendances($page, $perPage);

            return $this->successWithResource(
                'common.success',
                AttendanceListResource::collection($attendances),
            );
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 내 출석 현황
     *
     * GET /api/modules/lastorder-attendance/my
     */
    public function my(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $userId = $user->id;

            $hasCheckedIn = $this->attendanceService->hasCheckedInToday($userId);
            $consecutiveDays = $this->attendanceService->getConsecutiveDays($userId);
            $totalDays = $this->attendanceService->getTotalDays($userId);

            return $this->success('common.success', [
                'has_checked_in_today' => $hasCheckedIn,
                'consecutive_days' => $hasCheckedIn ? $consecutiveDays : max($consecutiveDays - 1, 0),
                'total_days' => $hasCheckedIn ? $totalDays : max($totalDays - 1, 0),
            ]);
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 월별 출석 캘린더
     *
     * GET /api/modules/lastorder-attendance/calendar/{year}/{month}
     */
    public function calendar(Request $request, int $year, int $month): JsonResponse
    {
        try {
            if ($month < 1 || $month > 12) {
                return $this->error('common.validation_failed', 422);
            }

            $user = $request->user();
            $calendar = $this->attendanceService->getMonthlyCalendar($user->id, $year, $month);

            return $this->successWithResource(
                'common.success',
                new AttendanceCalendarResource([
                    'year' => $year,
                    'month' => $month,
                    'calendar' => $calendar,
                ]),
            );
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 랜덤 인삿말 조회
     *
     * GET /api/modules/lastorder-attendance/greeting
     */
    public function greeting(): JsonResponse
    {
        try {
            return $this->success('common.success', [
                'greeting' => $this->greetingService->getRandomGreeting(),
            ]);
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 출석 가능 상태 조회
     *
     * GET /api/modules/lastorder-attendance/status
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->success('common.success', [
                'is_within_allowed_time' => $this->attendanceService->isWithinAllowedTime(),
                'has_checked_in_today' => $this->attendanceService->hasCheckedInToday($user->id),
                'can_check_in' => $this->attendanceService->canCheckIn($user->id),
            ]);
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }
}
