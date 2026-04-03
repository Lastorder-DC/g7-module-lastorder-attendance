# 03. 백엔드 아키텍처

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 02-DATABASE](02-DATABASE.md)

---

## 1. 계층 구조

```
Controller → FormRequest → Service → RepositoryInterface → Repository → Model
```

### 규칙 (AGENTS.md 준수)

- Service에 검증 로직 배치 금지 → FormRequest + Custom Rule 사용
- Repository 구체 클래스 직접 타입힌트 금지 → RepositoryInterface 주입 필수
- 컨트롤러에 비즈니스 로직 배치 금지 → Service에 위임
- 모든 API 응답은 ResponseHelper 사용
- `__()` 함수를 사용한 다국어 처리 필수
- 상태/타입/분류는 Enum으로 정의 필수

---

## 2. Contracts (인터페이스)

### AttendanceRepositoryInterface

```php
<?php

namespace Modules\Lastorder\Attendance\Contracts;

interface AttendanceRepositoryInterface
{
    /**
     * 오늘 특정 사용자의 출석 기록 조회
     */
    public function findTodayByUserId(int $userId): ?object;

    /**
     * 출석 기록 생성
     */
    public function create(array $data): object;

    /**
     * 오늘 출석 순위 (현재 출석 인원수 + 1)
     */
    public function getTodayRank(): int;

    /**
     * 연속출석 일수 계산 (어제까지)
     */
    public function getConsecutiveDays(int $userId): int;

    /**
     * 총 출석 일수
     */
    public function getTotalDays(int $userId): int;

    /**
     * 오늘 출석 목록 (페이지네이션)
     */
    public function getTodayAttendances(int $perPage = 20, int $page = 1): object;

    /**
     * 특정 날짜 출석 목록 (관리자, 페이지네이션)
     */
    public function getAttendancesByDate(string $date, int $perPage = 20, int $page = 1): object;

    /**
     * 월별 출석 캘린더 데이터
     */
    public function getMonthlyCalendar(int $userId, int $year, int $month): array;

    /**
     * 내 출석 현황 (연속/총 일수)
     */
    public function getUserStatus(int $userId): array;

    /**
     * 출석 기록 삭제 (관리자)
     */
    public function delete(int $id): bool;

    /**
     * 연속출석 일수 재계산 (관리자)
     */
    public function recalculateConsecutiveDays(int $userId): int;
}
```

---

## 3. Repository 구현

### AttendanceRepository

- `AttendanceRepositoryInterface` 구현
- Eloquent 모델을 통한 DB 접근
- N+1 쿼리 방지를 위한 eager loading 적용
- 날짜 기반 쿼리에 인덱스 활용

핵심 메서드 구현 포인트:

```
findTodayByUserId()  → where('user_id', $userId)->where('attended_at', today())
getTodayRank()       → where('attended_at', today())->count() + 1
getConsecutiveDays() → 어제부터 역순으로 연속 날짜 카운트
getTotalDays()       → where('user_id', $userId)->count()
getMonthlyCalendar() → whereBetween('attended_at', [월초, 월말])->pluck('attended_at')
```

---

## 4. Enums

### ConsecutiveType (연속출석 분류)

```php
<?php

namespace Modules\Lastorder\Attendance\Enums;

enum ConsecutiveType: string
{
    case Weekly = 'weekly';     // 7일 단위
    case Monthly = 'monthly';   // 30일 단위
    case Yearly = 'yearly';     // 365일 단위

    /**
     * 표시 라벨
     */
    public function label(): string
    {
        return match($this) {
            self::Weekly => __('lastorder-attendance::messages.consecutive.weekly'),
            self::Monthly => __('lastorder-attendance::messages.consecutive.monthly'),
            self::Yearly => __('lastorder-attendance::messages.consecutive.yearly'),
        };
    }

    /**
     * 해당 타입의 일수 기준
     */
    public function days(): int
    {
        return match($this) {
            self::Weekly => 7,
            self::Monthly => 30,
            self::Yearly => 365,
        };
    }
}
```

---

## 5. Services

### 5.1 AttendanceService (출석 핵심 로직)

책임:
- 출석 가능 여부 확인 (`canCheckIn`)
- 출석 처리 (`checkIn`)
- 오늘 출석 목록 조회
- 월별 캘린더 조회
- 사용자 출석 현황 조회

```
checkIn(int $userId, ?string $greeting, string $ip) → Attendance
├── 1. 출석 가능 여부 확인 (canCheckIn)
│   ├── 이미 출석했는지 확인
│   └── 출석 가능 시간인지 확인 (설정 참조)
├── 2. 순위 계산 (getTodayRank)
├── 3. 연속출석 일수 계산
├── 4. 총 출석일수 계산
├── 5. 인삿말 처리 (빈 값이면 랜덤 인삿말)
├── 6. 포인트 계산 (BonusService 위임)
│   ├── 기본 포인트
│   ├── 랜덤 포인트
│   ├── 순위 보너스 (1~3위)
│   └── 연속출석 보너스 (7/30/365일 배수)
├── 7. DB 트랜잭션 내 출석 기록 생성
├── 8. g7 포인트 시스템에 포인트 지급 (훅)
└── 9. 출석 완료 훅 실행
```

### 5.2 AttendanceBonusService (보너스 계산)

책임:
- 순위 보너스 계산 (1~3위)
- 연속출석 보너스 계산 (7/30/365일 배수)
- 랜덤 포인트 계산

```
calculateAllBonuses(int $rank, int $consecutiveDays, settings) → array
├── rank_point: calculateRankBonus(rank)
│   └── 1위=설정값, 2위=설정값, 3위=설정값, 그 외=0
├── consecutive_point: calculateConsecutiveBonus(consecutiveDays)
│   └── days % 365 == 0 → 연간보너스
│   └── days % 30 == 0 → 월간보너스
│   └── days % 7 == 0 → 주간보너스
│   └── 그 외 → 0
└── random_point: calculateRandomBonus(settings)
    └── 랜덤 사용 시 → random(min, max) 확률 적용
    └── 미사용 시 → 0
```

### 5.3 AttendanceSettingsService (설정)

→ [07-SETTINGS-SYSTEM.md](07-SETTINGS-SYSTEM.md) 참조

### 5.4 AttendanceGreetingService (인삿말)

책임:
- 기본 인삿말 목록에서 랜덤 선택
- 설정에서 기본 인삿말 목록 관리

```
getRandomGreeting() → string
├── 설정에서 기본 인삿말 목록 조회
└── 랜덤 선택하여 반환
```

---

## 6. FormRequest (검증)

### CheckInRequest

```php
<?php

namespace Modules\Lastorder\Attendance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 인증은 미들웨어에서 처리
    }

    public function rules(): array
    {
        return [
            'greeting' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'greeting.max' => __('lastorder-attendance::messages.validation.greeting_max'),
        ];
    }
}
```

> **규칙**: FormRequest `authorize()`에서 인증/권한 로직 금지 → permission 미들웨어 사용

### Admin\UpdateSettingsRequest

설정 저장 시 검증. 각 설정값의 타입과 범위를 검증.

---

## 7. API Resources

### AttendanceResource

```php
<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? '',
            'user_nick' => $this->user?->nick ?? '',
            'attended_at' => $this->attended_at?->format('Y-m-d'),
            'attended_time' => $this->attended_time,
            'greeting' => $this->greeting,
            'base_point' => $this->base_point,
            'bonus_point' => $this->bonus_point,
            'random_point' => $this->random_point,
            'rank_point' => $this->rank_point,
            'consecutive_point' => $this->consecutive_point,
            'total_point' => $this->total_point,
            'daily_rank' => $this->daily_rank,
            'consecutive_days' => $this->consecutive_days,
            'total_days' => $this->total_days,
            'is_auto' => $this->is_auto,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

---

## 8. 포인트 지급 연동

g7 코어의 포인트 시스템과 연동:

```
출석 시 포인트 지급 흐름:
1. AttendanceService::checkIn()
2. → 포인트 계산 완료
3. → g7 코어 PointService (또는 훅)를 통해 포인트 지급
4. → 포인트 지급 내역: "[출석부] YYYY-MM-DD 출석 포인트"
```

> 포인트 지급은 g7 코어의 포인트 시스템 훅 또는 서비스를 통해 수행. 코어에 직접 DB 접근하지 않음.

---

## 다음: [04-API-DESIGN.md](04-API-DESIGN.md) →
