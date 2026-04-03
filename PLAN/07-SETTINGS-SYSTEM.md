# 07. 모듈 설정 시스템

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 06-FRONTEND-ADMIN](06-FRONTEND-ADMIN.md)

---

## 1. 개요

g7 모듈 설정은 **파일 기반 JSON** 시스템을 사용.

```
module_setting('lastorder-attendance', 'key')
  → ModuleSettingsService::get()
    → 1순위: AttendanceSettingsService (모듈 전용)
    → 2순위: 코어 기본 구현 (defaults.json + setting.json)
```

> **절대 금지**: `App\Models\Setting` 사용 (g7에 존재하지 않음)

---

## 2. defaults.json

**위치**: `config/settings/defaults.json`

```json
{
  "_meta": {
    "version": "1.0.0",
    "description": "출석부 모듈 설정",
    "categories": ["basic", "time", "bonus", "random", "greetings"]
  },
  "defaults": {
    "basic": {
      "base_point": 10,
      "auto_attendance_enabled": false
    },
    "time": {
      "time_restriction_enabled": false,
      "start_hour": 0,
      "end_hour": 24
    },
    "bonus": {
      "rank_1st_point": 100,
      "rank_2nd_point": 50,
      "rank_3rd_point": 30,
      "consecutive_weekly_point": 100,
      "consecutive_monthly_point": 500,
      "consecutive_yearly_point": 5000
    },
    "random": {
      "random_point_enabled": true,
      "random_point_min": 1,
      "random_point_max": 200
    },
    "greetings": {
      "default_greetings": [
        "좋은 하루 되세요!",
        "오늘도 화이팅!",
        "출석 완료!",
        "ㅋㅋㅋㅋ",
        "안녕하세요~",
        "쪽쪽아웅",
        "ㅈㅈㅈㅈ"
      ]
    }
  },
  "frontend_schema": {
    "basic": {
      "expose": true,
      "fields": {
        "base_point": { "expose": true },
        "auto_attendance_enabled": { "expose": true }
      }
    },
    "time": {
      "expose": true,
      "fields": {
        "time_restriction_enabled": { "expose": true },
        "start_hour": { "expose": true },
        "end_hour": { "expose": true }
      }
    },
    "bonus": {
      "expose": true,
      "fields": {
        "rank_1st_point": { "expose": true },
        "rank_2nd_point": { "expose": true },
        "rank_3rd_point": { "expose": true },
        "consecutive_weekly_point": { "expose": true },
        "consecutive_monthly_point": { "expose": true },
        "consecutive_yearly_point": { "expose": true }
      }
    },
    "random": {
      "expose": true,
      "fields": {
        "random_point_enabled": { "expose": true },
        "random_point_min": { "expose": true },
        "random_point_max": { "expose": true }
      }
    },
    "greetings": {
      "expose": true,
      "fields": {
        "default_greetings": { "expose": true }
      }
    }
  }
}
```

---

## 3. AttendanceSettingsService

`ModuleSettingsInterface`를 구현하여 코어가 자동으로 위임.

```php
<?php

namespace Modules\Lastorder\Attendance\Services;

use App\Contracts\Extension\ModuleSettingsInterface;
use Illuminate\Support\Arr;

class AttendanceSettingsService implements ModuleSettingsInterface
{
    private const MODULE_IDENTIFIER = 'lastorder-attendance';
    private ?array $defaults = null;
    private ?array $settings = null;

    // ModuleSettingsInterface 구현 메서드들...
    // (module-settings.md 예시 패턴 따름)

    /**
     * 기본 출석 포인트 조회
     */
    public function getBasePoint(): int
    {
        return (int) $this->getSetting('basic.base_point', 10);
    }

    /**
     * 자동출석 사용 여부
     */
    public function isAutoAttendanceEnabled(): bool
    {
        return (bool) $this->getSetting('basic.auto_attendance_enabled', false);
    }

    /**
     * 출석 가능 시간 확인
     */
    public function isWithinAttendanceTime(): bool
    {
        if (!$this->getSetting('time.time_restriction_enabled', false)) {
            return true; // 시간 제한 없음
        }

        $now = (int) now()->format('H');
        $start = (int) $this->getSetting('time.start_hour', 0);
        $end = (int) $this->getSetting('time.end_hour', 24);

        return $now >= $start && $now < $end;
    }

    /**
     * 순위별 보너스 포인트 조회
     */
    public function getRankBonus(int $rank): int
    {
        return match ($rank) {
            1 => (int) $this->getSetting('bonus.rank_1st_point', 100),
            2 => (int) $this->getSetting('bonus.rank_2nd_point', 50),
            3 => (int) $this->getSetting('bonus.rank_3rd_point', 30),
            default => 0,
        };
    }

    /**
     * 연속출석 보너스 포인트 조회
     */
    public function getConsecutiveBonus(int $days): int
    {
        if ($days > 0 && $days % 365 === 0) {
            return (int) $this->getSetting('bonus.consecutive_yearly_point', 5000);
        }
        if ($days > 0 && $days % 30 === 0) {
            return (int) $this->getSetting('bonus.consecutive_monthly_point', 500);
        }
        if ($days > 0 && $days % 7 === 0) {
            return (int) $this->getSetting('bonus.consecutive_weekly_point', 100);
        }
        return 0;
    }

    /**
     * 랜덤 포인트 계산
     */
    public function calculateRandomPoint(): int
    {
        if (!$this->getSetting('random.random_point_enabled', true)) {
            return 0;
        }
        $min = (int) $this->getSetting('random.random_point_min', 1);
        $max = (int) $this->getSetting('random.random_point_max', 200);
        return random_int($min, $max);
    }

    /**
     * 기본 인삿말 목록 조회
     */
    public function getDefaultGreetings(): array
    {
        return $this->getSetting('greetings.default_greetings', []);
    }
}
```

---

## 4. 설정 저장 경로

```
storage/app/modules/lastorder-attendance/settings/
├── basic.json
├── time.json
├── bonus.json
├── random.json
└── greetings.json
```

---

## 5. 설정 항목 상세

### 5.1 기본 설정 (basic)

| 키 | 타입 | 기본값 | 설명 |
|-----|------|--------|------|
| `base_point` | int | 10 | 기본 출석 포인트 |
| `auto_attendance_enabled` | bool | false | 자동출석 사용 여부 |

### 5.2 시간 설정 (time)

| 키 | 타입 | 기본값 | 설명 |
|-----|------|--------|------|
| `time_restriction_enabled` | bool | false | 출석 시간 제한 사용 여부 |
| `start_hour` | int | 0 | 출석 시작 시간 (0~23) |
| `end_hour` | int | 24 | 출석 종료 시간 (1~24) |

> 기본값은 시간 제한 없음 (하루 종일 가능)

### 5.3 보너스 설정 (bonus)

| 키 | 타입 | 기본값 | 설명 |
|-----|------|--------|------|
| `rank_1st_point` | int | 100 | 1등 보너스 포인트 |
| `rank_2nd_point` | int | 50 | 2등 보너스 포인트 |
| `rank_3rd_point` | int | 30 | 3등 보너스 포인트 |
| `consecutive_weekly_point` | int | 100 | 7일 연속출석 보너스 |
| `consecutive_monthly_point` | int | 500 | 30일 연속출석 보너스 |
| `consecutive_yearly_point` | int | 5000 | 365일 연속출석 보너스 |

### 5.4 랜덤 포인트 설정 (random)

| 키 | 타입 | 기본값 | 설명 |
|-----|------|--------|------|
| `random_point_enabled` | bool | true | 랜덤 포인트 사용 여부 |
| `random_point_min` | int | 1 | 랜덤 포인트 최소값 |
| `random_point_max` | int | 200 | 랜덤 포인트 최대값 |

### 5.5 인삿말 설정 (greetings)

| 키 | 타입 | 기본값 | 설명 |
|-----|------|--------|------|
| `default_greetings` | array | ["좋은 하루 되세요!", ...] | 기본 인삿말 목록 (랜덤 선택용) |

---

## 6. 백엔드에서 설정 사용

### 헬퍼 함수 (코어 제공)

```php
// 단일 설정값 조회
$basePoint = module_setting('lastorder-attendance', 'basic.base_point', 10);

// 전체 설정 조회
$allSettings = module_settings('lastorder-attendance');
```

### 모듈 내부에서 직접 주입

```php
use Modules\Lastorder\Attendance\Services\AttendanceSettingsService;

public function __construct(
    private AttendanceSettingsService $settingsService
) {}

// 모듈 전용 메서드 사용
$basePoint = $this->settingsService->getBasePoint();
$isAutoEnabled = $this->settingsService->isAutoAttendanceEnabled();
```

---

## 다음: [08-HOOK-SYSTEM.md](08-HOOK-SYSTEM.md) →
