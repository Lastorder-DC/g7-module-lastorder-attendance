<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\ActivityLog\Traits\ResolvesActivityLogType;
use App\Contracts\Extension\HookListenerInterface;

/**
 * 출석부 활동 로그 리스너
 *
 * 출석 관련 주요 이벤트를 활동 로그에 기록합니다.
 */
class AttendanceActivityLogListener implements HookListenerInterface
{
    use ResolvesActivityLogType;

    /**
     * 구독할 훅 목록 반환
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'lastorder-attendance.checkin.after' => [
                'method' => 'onCheckIn',
                'priority' => 100,
            ],
            'lastorder-attendance.settings.updated' => [
                'method' => 'onSettingsUpdated',
                'priority' => 100,
            ],
            'lastorder-attendance.attendance.deleted' => [
                'method' => 'onAttendanceDeleted',
                'priority' => 100,
            ],
        ];
    }

    /**
     * 훅 이벤트 처리 (인터페이스 요구사항)
     */
    public function handle(...$args): void
    {
        // 개별 메서드에서 처리
    }

    /**
     * 출석 체크 시 활동 로그 기록
     */
    public function onCheckIn(mixed $attendance = null): void
    {
        if ($attendance === null) {
            return;
        }

        $this->logActivity('lastorder-attendance.checkin', [
            'performed_by' => $attendance->user_id,
            'properties' => [
                'attendance_id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date instanceof \DateTimeInterface
                    ? $attendance->attendance_date->format('Y-m-d')
                    : (string) $attendance->attendance_date,
                'daily_rank' => $attendance->daily_rank,
                'total_point' => $attendance->total_point,
                'consecutive_days' => $attendance->consecutive_days,
                'is_auto' => $attendance->is_auto,
                'ip_address' => $attendance->ip_address,
            ],
        ]);
    }

    /**
     * 설정 변경 시 활동 로그 기록
     */
    public function onSettingsUpdated(mixed $changes = null): void
    {
        $this->logActivity('lastorder-attendance.settings.updated', [
            'performed_by' => auth()->id(),
            'properties' => [
                'changes' => is_array($changes) ? $changes : [],
            ],
        ]);
    }

    /**
     * 출석 기록 삭제 시 활동 로그 기록
     */
    public function onAttendanceDeleted(mixed $attendance = null): void
    {
        if ($attendance === null) {
            return;
        }

        $this->logActivity('lastorder-attendance.attendance.deleted', [
            'performed_by' => auth()->id(),
            'causal_user' => $attendance->user_id ?? null,
            'properties' => [
                'attendance_id' => $attendance->id ?? null,
                'attendance_date' => $attendance->attendance_date instanceof \DateTimeInterface
                    ? $attendance->attendance_date->format('Y-m-d')
                    : (string) ($attendance->attendance_date ?? ''),
                'user_id' => $attendance->user_id ?? null,
            ],
        ]);
    }
}
