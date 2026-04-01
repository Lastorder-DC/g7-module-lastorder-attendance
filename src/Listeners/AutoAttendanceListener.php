<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Illuminate\Support\Facades\Log;
use Modules\Lastorder\Attendance\Services\AttendanceGreetingService;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;

/**
 * 로그인 시 자동출석 리스너
 *
 * core.auth.login.after 훅을 구독하여 로그인 시 자동으로 출석 처리합니다.
 */
class AutoAttendanceListener implements HookListenerInterface
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceSettingsService $settingsService,
        private readonly AttendanceGreetingService $greetingService,
    ) {}

    /**
     * 구독할 훅 목록 반환
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'core.auth.login.after' => [
                'method' => 'onLogin',
                'priority' => 50,
            ],
        ];
    }

    /**
     * 훅 이벤트 처리 (인터페이스 요구사항)
     */
    public function handle(...$args): void
    {
        $this->onLogin(...$args);
    }

    /**
     * 로그인 시 자동출석 처리
     *
     * 자동출석이 활성화되어 있고, 아직 출석하지 않은 경우에만 출석합니다.
     */
    public function onLogin(mixed $user = null): void
    {
        try {
            // 자동출석 비활성화 시 무시
            $enabled = $this->settingsService->getSetting('auto_attendance_enabled', false);
            if (! $enabled) {
                return;
            }

            // 사용자 객체가 없으면 무시
            if ($user === null || ! isset($user->id)) {
                return;
            }

            // 이미 출석한 경우 또는 출석 불가 시간이면 무시
            if (! $this->attendanceService->canCheckIn($user->id)) {
                return;
            }

            // 인삿말 결정 (설정된 인삿말 또는 랜덤 인삿말)
            $greeting = $this->settingsService->getSetting('auto_attendance_greeting', '');
            if (empty($greeting)) {
                $greeting = $this->greetingService->getRandomGreeting();
            }

            // 자동출석 수행
            $attendance = $this->attendanceService->checkIn(
                $user->id,
                $greeting,
                request()?->ip(),
            );

            // is_auto 플래그 업데이트
            $attendance->update(['is_auto' => true]);
        } catch (\Exception $e) {
            Log::warning('Auto attendance failed', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
