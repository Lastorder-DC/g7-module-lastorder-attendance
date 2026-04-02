<?php

namespace Modules\Lastorder\Attendance\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Base\AdminBaseController;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Lastorder\Attendance\Http\Resources\AttendanceListResource;
use Modules\Lastorder\Attendance\Repositories\Contracts\AttendanceRepositoryInterface;
use Modules\Lastorder\Attendance\Services\AttendanceService;

/**
 * 관리자 출석 현황 API 컨트롤러
 */
class AttendanceAdminController extends AdminBaseController
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceRepositoryInterface $attendanceRepository,
    ) {}

    /**
     * 출석 현황 목록 (관리자)
     *
     * GET /api/modules/lastorder-attendance/admin/attendance
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $date = $request->query('date', Carbon::today()->toDateString());
            $page = (int) $request->query('page', 1);
            $perPage = (int) $request->query('per_page', 20);

            $attendances = $this->attendanceRepository->getByDate($date, $page, $perPage);

            return $this->success(
                'common.success',
                AttendanceListResource::collection($attendances),
            );
        } catch (\Exception $e) {
            Log::error('Admin attendance index failed', ['error' => $e->getMessage()]);

            return $this->error('common.failed', 500);
        }
    }

    /**
     * 출석 기록 삭제 (관리자)
     *
     * DELETE /api/modules/lastorder-attendance/admin/attendance/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $attendance = $this->attendanceRepository->find($id);

            if (! $attendance) {
                return $this->notFound();
            }

            $attendance->delete();

            return $this->success('common.success');
        } catch (\Exception $e) {
            Log::error('Admin attendance delete failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return $this->error('common.failed', 500);
        }
    }

    /**
     * 특정 회원 연속 출석 일수 재계산 (관리자)
     *
     * POST /api/modules/lastorder-attendance/admin/attendance/recalculate-consecutive/{userId}
     */
    public function recalculateConsecutiveDays(int $userId): JsonResponse
    {
        try {
            $fromDate = Carbon::today()->toDateString();
            $consecutiveDays = $this->attendanceRepository->recalculateConsecutiveDays($userId, $fromDate);

            return $this->success('common.success', [
                'user_id' => $userId,
                'consecutive_days' => $consecutiveDays,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin recalculate consecutive days failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('common.failed', 500);
        }
    }

    /**
     * 특정 회원 총 출석 일수 재계산 (관리자)
     *
     * POST /api/modules/lastorder-attendance/admin/attendance/recalculate-total/{userId}
     */
    public function recalculateTotalDays(int $userId): JsonResponse
    {
        try {
            $totalDays = $this->attendanceRepository->recalculateTotalDays($userId);

            return $this->success('common.success', [
                'user_id' => $userId,
                'total_days' => $totalDays,
            ]);
        } catch (\Exception $e) {
            Log::error('Admin recalculate total days failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return $this->error('common.failed', 500);
        }
    }
}
