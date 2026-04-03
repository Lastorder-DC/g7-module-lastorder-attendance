<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Illuminate\Support\Facades\Log;

class AttendanceActivityLogListener implements HookListenerInterface
{
    /**
     * 구독할 훅 목록
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'lastorder-attendance.attendance.after_checkin' => [
                'method' => 'logCheckIn',
                'priority' => 20,
            ],
            'lastorder-attendance.attendance.after_delete' => [
                'method' => 'logDelete',
                'priority' => 20,
            ],
        ];
    }

    /**
     * 출석 체크 활동 로그
     */
    public function logCheckIn(...$args): void
    {
        $attendance = $args[0] ?? null;

        if ($attendance === null) {
            return;
        }

        Log::channel('activity')->info(
            __('lastorder-attendance::messages.activity.check_in'),
            [
                'module' => 'lastorder-attendance',
                'action' => 'check_in',
                'user_id' => $attendance->user_id ?? null,
                'attendance_id' => $attendance->id ?? null,
                'is_auto' => $attendance->is_auto ?? false,
                'total_point' => $attendance->total_point ?? 0,
            ]
        );
    }

    /**
     * 출석 삭제 활동 로그
     */
    public function logDelete(...$args): void
    {
        $attendanceId = $args[0] ?? null;

        Log::channel('activity')->info(
            __('lastorder-attendance::messages.activity.admin_delete'),
            [
                'module' => 'lastorder-attendance',
                'action' => 'admin_delete',
                'attendance_id' => $attendanceId,
            ]
        );
    }
}
