# 08. 훅 시스템 및 자동출석

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 07-SETTINGS-SYSTEM](07-SETTINGS-SYSTEM.md)

---

## 1. 훅 시스템 개요

g7 훅 시스템은 WordPress 스타일:
- **Action 훅**: `doAction()` - 부가 작업 (로그, 알림, 캐시)
- **Filter 훅**: `applyFilters()` - 데이터 변형 (`type => 'filter'` 필수)

훅 리스너는 `HookListenerInterface`를 구현하고, `Module::getHookListeners()`에서 등록.

---

## 2. 자동출석 리스너 (AutoAttendanceListener)

### 2.1 목적

사용자가 로그인/자동로그인 시 자동으로 출석 체크를 수행.

### 2.2 구독 훅

```php
<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use Modules\Lastorder\Attendance\Services\AttendanceService;
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;
use Modules\Lastorder\Attendance\Services\AttendanceGreetingService;
use Illuminate\Support\Facades\Log;

class AutoAttendanceListener implements HookListenerInterface
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AttendanceSettingsService $settingsService,
        private AttendanceGreetingService $greetingService,
    ) {}

    /**
     * 구독할 훅 목록
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'auth.login_after' => [
                'method' => 'handle',
                'priority' => 10,
            ],
        ];
    }

    /**
     * 로그인 후 자동출석 처리
     */
    public function handle(mixed ...$args): void
    {
        try {
            // 자동출석 설정 확인
            if (!$this->settingsService->isAutoAttendanceEnabled()) {
                return;
            }

            $user = $args[0] ?? null;
            if (!$user || !isset($user->id)) {
                return;
            }

            // 이미 출석했는지 확인
            if (!$this->attendanceService->canCheckIn($user->id)) {
                return;
            }

            // 랜덤 인삿말로 자동출석
            $greeting = $this->greetingService->getRandomGreeting();
            $ip = request()->ip() ?? '0.0.0.0';

            $this->attendanceService->checkIn(
                userId: $user->id,
                greeting: $greeting,
                ip: $ip,
                isAuto: true
            );

            Log::info('자동출석 완료', [
                'user_id' => $user->id,
                'greeting' => $greeting,
            ]);
        } catch (\Exception $e) {
            // 자동출석 실패는 로그인을 방해하지 않음
            Log::warning('자동출석 실패', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

### 2.3 설계 원칙

- 자동출석 실패가 로그인 프로세스를 방해하면 안 됨 (try-catch)
- 이미 출석한 경우 조용히 skip
- 출석 가능 시간 외에는 자동출석도 불가 (canCheckIn에서 확인)
- `is_auto = true`로 기록하여 수동/자동 출석 구분

---

## 3. 활동 로그 리스너 (AttendanceActivityLogListener)

### 3.1 목적

출석 관련 활동을 g7 활동 로그 시스템에 기록.

### 3.2 구현

```php
<?php

namespace Modules\Lastorder\Attendance\Listeners;

use App\Contracts\Extension\HookListenerInterface;
use App\ActivityLog\Traits\ResolvesActivityLogType;
use Illuminate\Support\Facades\Log;

class AttendanceActivityLogListener implements HookListenerInterface
{
    use ResolvesActivityLogType;

    /**
     * 구독할 훅 목록
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'lastorder-attendance.check_in_after' => [
                'method' => 'onCheckIn',
                'priority' => 10,
            ],
            'lastorder-attendance.attendance_deleted_after' => [
                'method' => 'onDeleted',
                'priority' => 10,
            ],
        ];
    }

    /**
     * 출석 완료 로그
     */
    public function onCheckIn(mixed ...$args): void
    {
        $attendance = $args[0] ?? null;
        if (!$attendance) {
            return;
        }

        $this->logActivity('lastorder-attendance.check_in', [
            'user_id' => $attendance->user_id,
            'date' => $attendance->attended_at,
            'points' => $attendance->total_point,
            'rank' => $attendance->daily_rank,
            'is_auto' => $attendance->is_auto,
        ]);
    }

    /**
     * 출석 기록 삭제 로그 (관리자)
     */
    public function onDeleted(mixed ...$args): void
    {
        $attendance = $args[0] ?? null;
        if (!$attendance) {
            return;
        }

        $this->logActivity('lastorder-attendance.attendance_deleted', [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'date' => $attendance->attended_at,
        ]);
    }
}
```

---

## 4. 모듈이 발행하는 훅

출석 서비스에서 발행하는 Action 훅:

| 훅 이름 | 시점 | 파라미터 | 용도 |
|---------|------|---------|------|
| `lastorder-attendance.check_in_before` | 출석 직전 | user_id, greeting | 출석 전 검증/로깅 |
| `lastorder-attendance.check_in_after` | 출석 완료 후 | Attendance 모델 | 로그, 알림, 통계 |
| `lastorder-attendance.attendance_deleted_after` | 출석 삭제 후 | Attendance 모델 | 로그 |
| `lastorder-attendance.settings_updated_after` | 설정 변경 후 | settings 배열 | 캐시 무효화 |

### 훅 네이밍 규칙

```
[vendor-module].[entity].[action]_[timing]
lastorder-attendance.check_in_before
lastorder-attendance.check_in_after
```

### 서비스에서 훅 발행

```php
// AttendanceService::checkIn() 내부
use Illuminate\Support\Facades\App;

// 훅 매니저를 통해 발행
$hookManager = App::make(\App\Extension\HookManager::class);

// before 훅
$hookManager->doAction('lastorder-attendance.check_in_before', $userId, $greeting);

// ... 출석 로직 ...

// after 훅
$hookManager->doAction('lastorder-attendance.check_in_after', $attendance);
```

---

## 5. Module 클래스에서 리스너 등록

```php
// module.php
public function getHookListeners(): array
{
    return [
        \Modules\Lastorder\Attendance\Listeners\AutoAttendanceListener::class,
        \Modules\Lastorder\Attendance\Listeners\AttendanceActivityLogListener::class,
    ];
}
```

---

## 다음: [09-I18N.md](09-I18N.md) →
