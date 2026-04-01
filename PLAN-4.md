## 4. 백엔드 아키텍처

그누보드7의 **Controller → FormRequest → Service → Repository → Model** 패턴을 따른다.

### 4.1 Module 클래스 (`module.php`)

```php
namespace Modules\Lastorder\Attendance;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    // module.json에서 name, version, description 자동 파싱

    public function getPermissions(): array { /* 권한 정의 */ }
    public function getConfig(): array { /* 설정 파일 반환 */ }
    public function getSeeders(): array { /* 설치 시더 반환 */ }
    public function getHookListeners(): array { /* 훅 리스너 반환 */ }
    public function getAdminMenus(): array { /* 관리자 메뉴 정의 */ }
}
```

### 4.2 모듈 메타데이터 (`module.json`)

```json
{
    "identifier": "lastorder-attendance",
    "vendor": "lastorder",
    "name": {
        "ko": "출석부",
        "en": "Attendance"
    },
    "version": "1.0.0",
    "license": "MIT",
    "description": {
        "ko": "매일 출석 체크 및 포인트 보상 모듈",
        "en": "Daily attendance check-in and point reward module"
    },
    "g7_version": ">=7.0.0-beta.1",
    "dependencies": {
        "modules": {},
        "plugins": {}
    },
    "github_url": "https://github.com/Lastorder-DC/g7-module-lastorder-attendance",
    "github_changelog_url": "https://github.com/Lastorder-DC/g7-module-lastorder-attendance/blob/main/CHANGELOG.md"
}
```

### 4.3 Service 계층 설계

#### `AttendanceService` (출석 핵심 비즈니스 로직)

| 메서드 | 설명 |
|--------|------|
| `checkIn(int $userId, string $greeting, ?string $ip): Attendance` | 출석 체크 (핵심 메서드) |
| `canCheckIn(int $userId): bool` | 출석 가능 여부 확인 |
| `isWithinAllowedTime(): bool` | 현재 시간이 출석 가능 시간대인지 확인 |
| `hasCheckedInToday(int $userId): bool` | 오늘 출석했는지 확인 |
| `getConsecutiveDays(int $userId): int` | 연속 출석 일수 계산 |
| `getTotalDays(int $userId): int` | 총 출석 일수 계산 |
| `getDailyRank(string $date): int` | 오늘 몇 번째 출석인지 반환 |
| `getTodayAttendances(int $page, int $perPage): LengthAwarePaginator` | 오늘 출석 목록 (페이지네이션) |
| `getMonthlyCalendar(int $userId, int $year, int $month): array` | 월별 출석 캘린더 데이터 |

**`checkIn()` 상세 처리 흐름:**

```
1. 인증 확인 (로그인 여부)
2. 출석 가능 시간 확인 (isWithinAllowedTime)
3. 중복 출석 확인 (hasCheckedInToday)
4. 연속 출석 일수 계산 (getConsecutiveDays)
5. 총 출석 일수 계산 (getTotalDays)
6. 당일 순위 계산 (getDailyRank)
7. 기본 포인트 계산
8. 랜덤 포인트 계산 (설정에 따라)
9. DB 트랜잭션 시작
   a. 출석 기록 저장
   b. 포인트 지급 (그누보드7 포인트 시스템 연동)
   c. 순위 보너스 확인 및 지급 (1~3위)
   d. 연속출석 보너스 확인 및 지급 (7일/30일/365일)
10. DB 트랜잭션 커밋
11. 출석 결과 반환
```

#### `AttendanceBonusService` (보너스 계산 로직)

| 메서드 | 설명 |
|--------|------|
| `checkAndGrantRankBonus(Attendance $attendance): ?AttendanceBonus` | 순위 보너스 확인/지급 |
| `checkAndGrantConsecutiveBonus(Attendance $attendance): array` | 연속출석 보너스 확인/지급 |
| `getRankBonusPoint(int $rank): int` | 순위별 보너스 포인트 조회 |
| `getConsecutiveBonusPoint(string $type, int $days): int` | 연속출석 보너스 포인트 조회 |

#### `AttendanceSettingsService` (설정 관리)

| 메서드 | 설명 |
|--------|------|
| `getSettings(): array` | 전체 설정 조회 |
| `updateSettings(array $data): void` | 설정 업데이트 |
| `getSetting(string $key, mixed $default): mixed` | 단일 설정값 조회 |

#### `AttendanceGreetingService` (인삿말 관리)

| 메서드 | 설명 |
|--------|------|
| `getRandomGreeting(): string` | 기본 인삿말 랜덤 반환 |
| `getDefaultGreetings(): array` | 기본 인삿말 목록 |

### 4.4 Repository 계층 설계

#### `AttendanceRepository`

| 메서드 | 설명 |
|--------|------|
| `findByUserAndDate(int $userId, string $date): ?Attendance` | 특정 날짜 출석 조회 |
| `getByDate(string $date, int $page, int $perPage): LengthAwarePaginator` | 날짜별 출석 목록 |
| `getByUserAndMonth(int $userId, int $year, int $month): Collection` | 사용자 월별 출석 |
| `getConsecutiveDays(int $userId, string $fromDate): int` | 연속 출석 일수 |
| `getTotalDays(int $userId): int` | 총 출석 일수 |
| `getDailyRank(string $date): int` | 당일 출석 순번 (COUNT+1) |
| `getCountByDate(string $date): int` | 날짜별 총 출석 수 |
| `create(array $data): Attendance` | 출석 기록 생성 |

#### `AttendanceBonusRepository`

| 메서드 | 설명 |
|--------|------|
| `findByUserDateType(int $userId, string $date, string $type): ?AttendanceBonus` | 보너스 중복 확인 |
| `create(array $data): AttendanceBonus` | 보너스 기록 생성 |
| `getByUser(int $userId, int $page, int $perPage): LengthAwarePaginator` | 사용자별 보너스 이력 |

### 4.5 Model 설계

#### `Attendance` 모델

```php
class Attendance extends Model
{
    protected $table = 'attendances';

    protected $fillable = [
        'user_id', 'attendance_date', 'attendance_time',
        'greeting', 'base_point', 'random_point', 'total_point',
        'daily_rank', 'consecutive_days', 'total_days',
        'ip_address', 'is_auto',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_auto' => 'boolean',
    ];

    // Relations
    public function user(): BelongsTo { /* users 테이블 */ }
    public function bonuses(): HasMany { /* attendance_bonuses 테이블 */ }
}
```

#### `AttendanceBonus` 모델

```php
class AttendanceBonus extends Model
{
    protected $table = 'attendance_bonuses';
    public $timestamps = false; // created_at만 사용

    protected $fillable = [
        'user_id', 'attendance_id', 'bonus_type',
        'bonus_point', 'bonus_date', 'description',
    ];

    // Relations
    public function user(): BelongsTo { /* users */ }
    public function attendance(): BelongsTo { /* attendances */ }
}
```

---
