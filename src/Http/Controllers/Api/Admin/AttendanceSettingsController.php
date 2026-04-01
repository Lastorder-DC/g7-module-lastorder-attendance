<?php

namespace Modules\Lastorder\Attendance\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Base\AdminBaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Lastorder\Attendance\Http\Requests\Admin\UpdateSettingsRequest;
use Modules\Lastorder\Attendance\Http\Resources\AttendanceSettingsResource;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;

/**
 * 관리자 출석부 설정 API 컨트롤러
 */
class AttendanceSettingsController extends AdminBaseController
{
    public function __construct(
        private readonly AttendanceSettingsService $settingsService,
    ) {}

    /**
     * 설정 조회
     *
     * GET /api/modules/lastorder-attendance/admin/settings
     */
    public function index(): JsonResponse
    {
        try {
            $settings = $this->settingsService->getSettings();

            return $this->successWithResource(
                'common.success',
                new AttendanceSettingsResource($settings),
            );
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }

    /**
     * 설정 수정
     *
     * PUT /api/modules/lastorder-attendance/admin/settings
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updateSettings($request->validated());

            $settings = $this->settingsService->getSettings();

            return $this->successWithResource(
                'common.success',
                new AttendanceSettingsResource($settings),
            );
        } catch (Exception $e) {
            return $this->error('common.failed', 500);
        }
    }
}
