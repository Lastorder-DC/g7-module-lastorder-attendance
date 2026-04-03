<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Illuminate\Support\Facades\Log;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;

class AutoAttendanceListener implements HookListenerInterface
{
    /**
     * 구독할 훅 목록
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'core.user.after_login' => [
                'method' => 'handle',
                'priority' => 20,
            ],
        ];
    }

    /**
     * 로그인 시 자동출석 처리
     */
    public function handle(...$args): void
    {
        $user = $args[0] ?? null;

        if ($user === null) {
            return;
        }

        try {
            /** @var AttendanceSettingsService $settingsService */
            $settingsService = app(AttendanceSettingsService::class);

            if (!$settingsService->isAutoAttendanceEnabled()) {
                return;
            }

            /** @var AttendanceService $attendanceService */
            $attendanceService = app(AttendanceService::class);

            $canCheckIn = $attendanceService->canCheckIn($user->id);

            if (!$canCheckIn['can_check_in']) {
                return;
            }

            $attendanceService->checkIn(
                $user->id,
                null,
                request()->ip() ?? '0.0.0.0',
                true
            );
        } catch (\Exception $e) {
            Log::error('Auto attendance failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
