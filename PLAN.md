# 그누보드7 출석부 모듈 개발 계획서

> **모듈 식별자**: `lastorder-attendance`
> **네임스페이스**: `Modules\Lastorder\Attendance\`
> **Composer 패키지명**: `modules/lastorder-attendance`
> **대상 플랫폼**: Gnuboard7 (Laravel 12 + React 19)

---

## 목차

1. [모듈 개요](#1-모듈-개요)
2. [디렉토리 구조](#2-디렉토리-구조)
3. [데이터베이스 설계](#3-데이터베이스-설계)
4. [백엔드 아키텍처](#4-백엔드-아키텍처)
5. [API 설계](#5-api-설계)
6. [프론트엔드 (JSON 레이아웃)](#6-프론트엔드-json-레이아웃)
7. [설정 시스템](#7-설정-시스템)
8. [포인트 및 보너스 시스템](#8-포인트-및-보너스-시스템)
9. [자동출석 시스템](#9-자동출석-시스템)
10. [다국어 지원](#10-다국어-지원)
11. [권한 및 메뉴](#11-권한-및-메뉴)
12. [구현 단계별 계획](#12-구현-단계별-계획)

---

## 1. 모듈 개요

### 1.1 목적

커뮤니티 활성화를 위한 출석부 모듈로, 회원이 매일 출석 체크를 하고 포인트를 획득하며, 연속 출석 및 순위 기반 보너스를 받을 수 있는 기능을 제공한다.

### 1.2 핵심 기능 요약

| 기능 | 설명 |
|------|------|
| **출석 체크** | 하루 1회 출석 체크, 인삿말 입력 |
| **연속출석 보너스** | 주/월/년 연속 출석 시 보너스 포인트 |
| **순위 보너스** | 매일 1위~3위 출석자에게 보너스 포인트 |
| **인삿말 시스템** | 기본 인삿말 랜덤 제공 + 직접 입력 가능 |
| **출석 캘린더** | 월별 출석/결석/미출석 날짜 시각화 |
| **자동출석** | 로그인/자동로그인 시 자동 출석 체크 옵션 |
| **출석 가능 시간** | 관리자가 출석 가능 시간대를 설정 |
| **랜덤 포인트** | 출석 시 설정 범위 내 랜덤 추가 포인트 |
| **관리자 설정** | 출석 관련 모든 설정을 관리자 페이지에서 관리 |

---

## 2. 디렉토리 구조

```
modules/_bundled/lastorder-attendance/     (또는 modules/lastorder-attendance/)
├── module.json                            # 모듈 메타데이터 (SSoT)
├── module.php                             # Module 클래스 (AbstractModule 상속)
├── composer.json                          # Composer 오토로딩
├── LICENSE                                # MIT 라이선스
├── CHANGELOG.md                           # 변경 이력
├── config/
│   ├── attendance.php                     # 모듈 설정 파일
│   └── settings/
│       └── attendance.php                 # DB 저장 설정 기본값 정의
├── database/
│   ├── factories/
│   │   └── AttendanceFactory.php          # 테스트용 Factory
│   ├── migrations/
│   │   ├── 2026_04_01_000001_create_attendances_table.php
│   │   ├── 2026_04_01_000002_create_attendance_settings_table.php
│   │   └── 2026_04_01_000003_create_attendance_bonuses_table.php
│   └── seeders/
│       ├── AttendanceSettingsSeeder.php    # 기본 설정값 시더
│       └── DefaultGreetingsSeeder.php     # 기본 인삿말 시더
├── resources/
│   ├── lang/
│   │   ├── ko.json                        # 프론트엔드 한국어
│   │   └── en.json                        # 프론트엔드 영어
│   ├── layouts/
│   │   ├── admin/
│   │   │   ├── admin_attendance_settings.json    # 관리자 설정 페이지
│   │   │   └── admin_attendance_index.json       # 관리자 출석 현황 페이지
│   │   └── user/
│   │       └── user_attendance.json               # 사용자 출석부 페이지
│   ├── routes/
│   │   ├── admin.json                     # 관리자 프론트엔드 라우트
│   │   └── user.json                      # 사용자 프론트엔드 라우트
│   └── extensions/
│       └── user-auto-attendance.json      # 자동출석 훅 (로그인 후 자동 출석)
├── src/
│   ├── Enums/
│   │   └── BonusType.php                  # 보너스 유형 Enum (weekly, monthly, yearly, rank)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AttendanceController.php        # 사용자 출석 API
│   │   │       └── Admin/
│   │   │           ├── AttendanceAdminController.php    # 관리자 출석 현황 API
│   │   │           └── AttendanceSettingsController.php # 관리자 설정 API
│   │   ├── Requests/
│   │   │   ├── CheckInRequest.php                  # 출석 요청 Validation
│   │   │   └── Admin/
│   │   │       └── UpdateSettingsRequest.php        # 설정 수정 Validation
│   │   └── Resources/
│   │       ├── AttendanceResource.php               # 출석 기록 리소스
│   │       ├── AttendanceListResource.php           # 출석 목록 리소스
│   │       ├── AttendanceCalendarResource.php       # 캘린더 리소스
│   │       └── AttendanceSettingsResource.php       # 설정 리소스
│   ├── Listeners/
│   │   ├── AutoAttendanceListener.php               # 자동 출석 훅 리스너
│   │   └── AttendanceActivityLogListener.php        # 활동 로그 리스너
│   ├── Models/
│   │   ├── Attendance.php                           # 출석 기록 모델
│   │   └── AttendanceBonus.php                      # 보너스 기록 모델
│   ├── Providers/
│   │   └── AttendanceServiceProvider.php            # 서비스 프로바이더
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── AttendanceRepositoryInterface.php
│   │   │   └── AttendanceBonusRepositoryInterface.php
│   │   ├── AttendanceRepository.php
│   │   └── AttendanceBonusRepository.php
│   ├── Services/
│   │   ├── AttendanceService.php                    # 출석 비즈니스 로직
│   │   ├── AttendanceBonusService.php               # 보너스 계산 로직
│   │   ├── AttendanceSettingsService.php            # 설정 관리 로직
│   │   └── AttendanceGreetingService.php            # 인삿말 관리 로직
│   ├── lang/
│   │   ├── en/
│   │   │   └── attendance.php                       # 백엔드 영어
│   │   └── ko/
│   │       └── attendance.php                       # 백엔드 한국어
│   └── routes/
│       └── api.php                                  # API 라우트
├── tests/
│   ├── Feature/
│   │   ├── CheckInTest.php
│   │   ├── BonusTest.php
│   │   ├── AutoAttendanceTest.php
│   │   └── AdminSettingsTest.php
│   └── Unit/
│       ├── AttendanceServiceTest.php
│       └── AttendanceBonusServiceTest.php
└── upgrades/                                        # 향후 버전 업그레이드용
```

---

## 3. 데이터베이스 설계

### 3.1 `attendances` 테이블 (출석 기록)

매일 출석 체크 기록을 저장하는 핵심 테이블.

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | `bigint unsigned` PK | 자동 증가 ID |
| `user_id` | `bigint unsigned` FK | 회원 ID (users 테이블 참조) |
| `attendance_date` | `date` | 출석 날짜 |
| `attendance_time` | `time` | 출석 시각 |
| `greeting` | `varchar(200)` | 인삿말 |
| `base_point` | `int` | 기본 출석 포인트 |
| `random_point` | `int` default 0 | 랜덤 추가 포인트 |
| `total_point` | `int` | 총 획득 포인트 (base + random) |
| `daily_rank` | `smallint unsigned` NULL | 당일 출석 순위 |
| `consecutive_days` | `int unsigned` default 1 | 연속 출석 일수 |
| `total_days` | `int unsigned` default 1 | 총 출석 일수 |
| `ip_address` | `varchar(45)` | 출석 IP 주소 |
| `is_auto` | `boolean` default false | 자동출석 여부 |
| `created_at` | `timestamp` | 생성일시 |
| `updated_at` | `timestamp` | 수정일시 |

**인덱스:**

```
- UNIQUE INDEX: (user_id, attendance_date) — 하루 1회 출석 보장
- INDEX: (attendance_date, daily_rank) — 일별 순위 조회
- INDEX: (attendance_date, created_at) — 일별 출석 시각순 조회
- INDEX: (user_id, attendance_date DESC) — 사용자별 최근 출석 조회
- INDEX: (user_id, consecutive_days) — 연속 출석 조회
```

### 3.2 `attendance_bonuses` 테이블 (보너스 기록)

연속출석 보너스 및 순위 보너스 지급 기록.

| 컬럼 | 타입 | 설명 |
|------|------|------|
| `id` | `bigint unsigned` PK | 자동 증가 ID |
| `user_id` | `bigint unsigned` FK | 회원 ID |
| `attendance_id` | `bigint unsigned` FK NULL | 출석 기록 ID |
| `bonus_type` | `varchar(20)` | 보너스 유형 (`rank_1`, `rank_2`, `rank_3`, `weekly`, `monthly`, `yearly`) |
| `bonus_point` | `int` | 보너스 포인트 |
| `bonus_date` | `date` | 보너스 적용 날짜 |
| `description` | `varchar(200)` | 보너스 설명 |
| `created_at` | `timestamp` | 생성일시 |

**인덱스:**

```
- INDEX: (user_id, bonus_date) — 사용자별 보너스 조회
- INDEX: (bonus_date, bonus_type) — 일별 보너스 현황
- UNIQUE INDEX: (user_id, bonus_date, bonus_type) — 중복 보너스 방지
```

---

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

## 5. API 설계

### 5.1 사용자 API

| 메서드 | 경로 | 설명 | 인증 |
|--------|------|------|------|
| `POST` | `/api/attendance/check-in` | 출석 체크 | 필수 |
| `GET` | `/api/attendance/today` | 오늘 출석 목록 (페이지네이션) | 선택 |
| `GET` | `/api/attendance/my` | 내 출석 현황 (오늘 출석 여부, 연속일수, 총일수) | 필수 |
| `GET` | `/api/attendance/calendar/{year}/{month}` | 월별 출석 캘린더 | 필수 |
| `GET` | `/api/attendance/greeting` | 랜덤 인삿말 조회 | 선택 |
| `GET` | `/api/attendance/status` | 출석 가능 상태 (시간 체크, 이미 출석 여부) | 필수 |

### 5.2 관리자 API

| 메서드 | 경로 | 설명 | 권한 |
|--------|------|------|------|
| `GET` | `/api/admin/attendance` | 출석 현황 목록 (필터, 페이지네이션) | `lastorder-attendance.attendance.read` |
| `GET` | `/api/admin/attendance/settings` | 설정 조회 | `lastorder-attendance.settings.read` |
| `PUT` | `/api/admin/attendance/settings` | 설정 수정 | `lastorder-attendance.settings.update` |
| `DELETE` | `/api/admin/attendance/{id}` | 출석 기록 삭제 (관리자) | `lastorder-attendance.attendance.delete` |

### 5.3 API 응답 예시

#### `POST /api/attendance/check-in`

**요청:**

```json
{
    "greeting": "좋은 아침이에요~"
}
```

**응답 (성공):**

```json
{
    "data": {
        "id": 1234,
        "user": {
            "id": 42,
            "name": "홍길동"
        },
        "attendance_date": "2026-04-01",
        "attendance_time": "09:15:23",
        "greeting": "좋은 아침이에요~",
        "base_point": 10,
        "random_point": 5,
        "total_point": 15,
        "daily_rank": 3,
        "consecutive_days": 7,
        "total_days": 45,
        "bonuses": [
            {
                "type": "rank_3",
                "point": 30,
                "description": "3위 보너스"
            },
            {
                "type": "weekly",
                "point": 100,
                "description": "7일 연속출석 보너스"
            }
        ]
    },
    "message": "출석이 완료되었습니다."
}
```

#### `GET /api/attendance/calendar/2026/4`

```json
{
    "data": {
        "year": 2026,
        "month": 4,
        "user_joined_date": "2025-01-15",
        "days": [
            { "date": "2026-04-01", "status": "attended", "rank": 3, "point": 15 },
            { "date": "2026-04-02", "status": "absent" },
            { "date": "2026-04-03", "status": "attended", "rank": 12, "point": 10 },
            { "date": "2026-04-04", "status": "attended", "rank": 1, "point": 60 },
            { "date": "2026-04-05", "status": "attended", "rank": 45, "point": 10 },
            { "date": "2026-04-06", "status": "future" },
            ...
        ],
        "summary": {
            "attended_count": 4,
            "absent_count": 1,
            "consecutive_days": 3,
            "total_days": 45
        }
    }
}
```

#### `GET /api/attendance/today`

```json
{
    "data": [
        {
            "rank": 1,
            "attendance_time": "00:01:05",
            "greeting": "좋은 하루~",
            "user": { "id": 10, "name": "열혈회원" },
            "base_point": 10,
            "random_point": 0,
            "total_point": 60,
            "consecutive_days": 30,
            "total_days": 563
        },
        ...
    ],
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 169
    }
}
```

---

## 6. 프론트엔드 (JSON 레이아웃)

그누보드7의 JSON 레이아웃 엔진을 활용하여 프론트엔드 빌드 없이 UI를 구성한다.

### 6.1 사용자 출석부 페이지 (`user_attendance.json`)

참고 이미지의 UI를 기반으로 다음 섹션들을 구성한다:

#### 상단 정보 바

- 현재 날짜 및 시간 표시
- 관리자용: 설정, 스킨관리, 관리 버튼
- 이전달/이번달/다음달 네비게이션

#### 출석 현황 요약

| 항목 | 설명 |
|------|------|
| 출석점수 | 오늘 획득한 총 포인트 |
| 출석시간 | 출석 시각 or "하루 종일" |
| 개근점수 | 연속출석 보너스 포인트 |
| 진행상태 | 출석가능/출석마감 |
| 랭킹점수 | 순위 보너스 포인트 |
| 출석여부 | 출석완료/미출석 |
| 출석권한 | 로그인 사용자/비회원 |
| 개근분류 | 현재 연속출석 일수 |

#### 출석 캘린더 (월별)

- 1~30(31)일 숫자 표시
- **출석한 날**: 파란색 강조 (●)
- **결석한 날**: 빨간색 강조 (●)
- **미출석 (미래/가입전)**: 회색
- 하단 범례: 결석 ● 출석 ● 미출석

#### 출석 체크 폼

- "출석이 완료되었습니다" / "출석하기" 메시지 영역
- 인삿말 입력 필드 (기본값: 랜덤 인삿말)
- 출석 버튼

#### 오늘 출석 목록 테이블

| 컬럼 | 설명 |
|------|------|
| 순위 | 당일 출석 순위 |
| 출석시각 | HH:MM:SS |
| 인삿말 | 입력한 인삿말 (마스킹 처리 옵션) |
| 별명 | 회원 닉네임/이름 |
| 포인트 | 기본 포인트 |
| 랜덤 포인트 | 랜덤 추가 포인트 (꽝/숫자) |
| 개근 | N일째 연속 출석 |
| 총 출석일 | 누적 출석 일수 |

- 페이지네이션 지원

### 6.2 관리자 설정 페이지 (`admin_attendance_settings.json`)

관리자 페이지에서 출석부 모듈의 모든 설정을 관리하는 화면.

#### 기본 설정 섹션

- 출석 기본 포인트 (숫자 입력)
- 출석 가능 시작 시간 (시간 선택)
- 출석 가능 종료 시간 (시간 선택)

#### 자동출석 설정 섹션

- 자동출석 사용여부 (토글)
- 자동출석 시 인삿말 (기본 인삿말 사용/빈값)

#### 순위 보너스 설정 섹션

- 1위 보너스 포인트
- 2위 보너스 포인트
- 3위 보너스 포인트

#### 연속출석 보너스 설정 섹션

- 주간(7일) 연속출석 보너스 포인트
- 월간(30일) 연속출석 보너스 포인트
- 연간(365일) 연속출석 보너스 포인트

#### 랜덤 포인트 설정 섹션

- 랜덤 포인트 사용여부 (토글)
- 최소 랜덤 포인트
- 최대 랜덤 포인트
- 랜덤 포인트 당첨 확률 (%)

#### 인삿말 설정 섹션

- 기본 인삿말 목록 (추가/삭제 가능)

### 6.3 관리자 출석 현황 페이지 (`admin_attendance_index.json`)

- 날짜별 출석 현황 조회
- 회원 검색 필터
- 출석 기록 삭제 기능
- 통계 요약 (일별 출석자 수, 보너스 지급 현황)

### 6.4 프론트엔드 라우트

#### `resources/routes/admin.json`

```json
{
    "version": "1.0.0",
    "routes": [
        {
            "path": "*/admin/attendance/settings",
            "layout": "admin_attendance_settings",
            "auth_required": true,
            "meta": {
                "title": "$t:lastorder-attendance.admin.settings.title",
                "permission": "lastorder-attendance.settings.read"
            }
        },
        {
            "path": "*/admin/attendance",
            "layout": "admin_attendance_index",
            "auth_required": true,
            "meta": {
                "title": "$t:lastorder-attendance.admin.attendance.title",
                "permission": "lastorder-attendance.attendance.read"
            }
        }
    ]
}
```

#### `resources/routes/user.json`

```json
{
    "version": "1.0.0",
    "routes": [
        {
            "path": "*/attendance",
            "layout": "user_attendance",
            "auth_required": false,
            "meta": {
                "title": "$t:lastorder-attendance.user.title"
            }
        }
    ]
}
```

---

## 7. 설정 시스템

그누보드7의 설정 시스템을 활용하여 관리자가 변경 가능한 설정을 DB에 저장한다.

### 7.1 설정 항목

```php
// config/attendance.php
return [
    // 기본 출석 포인트
    'base_point' => 10,

    // 출석 가능 시간 (24시간 형식)
    'allowed_start_time' => '01:00',    // 오전 1시
    'allowed_end_time' => '23:00',      // 오후 11시

    // 자동출석 설정
    'auto_attendance_enabled' => false,
    'auto_attendance_greeting' => '',   // 빈 문자열이면 랜덤 인삿말 사용

    // 순위 보너스 포인트
    'rank_1_bonus' => 50,
    'rank_2_bonus' => 30,
    'rank_3_bonus' => 20,

    // 연속출석 보너스 포인트
    'weekly_bonus' => 100,           // 7일 연속
    'monthly_bonus' => 500,          // 30일 연속
    'yearly_bonus' => 5000,          // 365일 연속

    // 랜덤 포인트 설정
    'random_point_enabled' => false,
    'random_point_min' => 1,
    'random_point_max' => 100,
    'random_point_chance' => 30,     // 당첨 확률 (%)

    // 기본 인삿말 목록
    'default_greetings' => [
        '좋은 아침이에요~',
        '오늘도 화이팅!',
        '반갑습니다~',
        '좋은 하루 보내세요!',
        '안녕하세요!',
        '오늘도 좋은 하루~',
        '즐거운 하루 되세요!',
        '행복한 하루~',
        '꾸준함이 힘이에요!',
        '오늘도 출석 완료!',
    ],

    // 페이지네이션
    'per_page' => 20,

    // 캐시
    'cache' => [
        'enabled' => true,
        'ttl' => 60,   // 초 단위
    ],
];
```

### 7.2 설정 저장 방식

그누보드7의 코어 설정 시스템(`settings` 테이블)을 통해 DB에 저장한다.
`config/settings/attendance.php`에 DB 저장 설정의 기본값과 유효성 검증 규칙을 정의한다.

---

## 8. 포인트 및 보너스 시스템

### 8.1 기본 포인트

- 출석 시 **기본 포인트** 지급 (관리자 설정, 기본값: 10)
- 그누보드7의 **포인트 시스템**과 연동하여 포인트 적립

### 8.2 랜덤 포인트

```
활성화 시:
1. 랜덤 확률(%) 판정 → 당첨/꽝 결정
2. 당첨 시: 설정된 최소~최대 범위 내 랜덤 포인트 생성
3. 꽝일 경우: random_point = 0
4. 출석 목록에 "꽝" 또는 포인트 숫자 표시
```

### 8.3 순위 보너스

```
출석 순서대로 순위 부여:
- 1위: rank_1_bonus 포인트 (기본 50)
- 2위: rank_2_bonus 포인트 (기본 30)
- 3위: rank_3_bonus 포인트 (기본 20)
- 4위 이후: 순위 보너스 없음
```

### 8.4 연속출석 보너스

```
연속 출석 일수 기준:
- 7일 연속: weekly_bonus 포인트 (기본 100)
- 30일 연속: monthly_bonus 포인트 (기본 500)
- 365일 연속: yearly_bonus 포인트 (기본 5000)

연속출석 판정 로직:
1. 어제 출석했는지 확인
2. 어제 출석 O → 연속일수 = 어제의 연속일수 + 1
3. 어제 출석 X → 연속일수 = 1 (리셋)
4. 연속일수가 7/30/365의 배수이면 해당 보너스 지급
```

### 8.5 포인트 적립 연동

그누보드7 코어의 포인트 시스템과 연동한다. 코어에서 제공하는 포인트 적립 API/Service를 활용:

```
포인트 적립 시:
- 카테고리: 'attendance'
- 설명: '출석 포인트', '1위 보너스', '7일 연속출석 보너스' 등
- 금액: 계산된 포인트
```

---

## 9. 자동출석 시스템

### 9.1 동작 방식

그누보드7의 Hook 시스템을 활용하여, 로그인 성공 이벤트에 자동출석 리스너를 등록한다.

```
AutoAttendanceListener:
1. 자동출석 설정 확인 (auto_attendance_enabled)
2. 비활성화 시: 아무것도 하지 않음
3. 활성화 시:
   a. 출석 가능 시간 확인
   b. 이미 출석했는지 확인
   c. 미출석 시: 자동 출석 처리
      - 인삿말: 설정된 값 또는 랜덤 인삿말
      - is_auto: true로 기록
```

### 9.2 Hook 리스너 등록

```php
// module.php
public function getHookListeners(): array
{
    return [
        AutoAttendanceListener::class,
        AttendanceActivityLogListener::class,
    ];
}
```

`AutoAttendanceListener`는 코어의 로그인 성공 훅(`auth.login.success` 등)을 구독하여
자동출석을 수행한다.

---

## 10. 다국어 지원

### 10.1 프론트엔드 다국어 (`resources/lang/`)

#### `ko.json` (한국어)

```json
{
    "lastorder-attendance.user.title": "출석부",
    "lastorder-attendance.user.check_in": "출석하기",
    "lastorder-attendance.user.checked_in": "출석이 완료되었습니다.",
    "lastorder-attendance.user.checked_in_notice": "출석은 하루 1회만 참여하실 수 있습니다. 내일 다시 출석해 주세요.^^",
    "lastorder-attendance.user.greeting_placeholder": "인삿말을 입력하세요",
    "lastorder-attendance.user.calendar.attended": "출석",
    "lastorder-attendance.user.calendar.absent": "결석",
    "lastorder-attendance.user.calendar.future": "미출석",
    "lastorder-attendance.user.calendar.notice": "* 이전달은 가입일까지 열람이 가능합니다.",
    "lastorder-attendance.user.table.rank": "순위",
    "lastorder-attendance.user.table.time": "출석시각",
    "lastorder-attendance.user.table.greeting": "인삿말",
    "lastorder-attendance.user.table.nickname": "별명",
    "lastorder-attendance.user.table.point": "포인트",
    "lastorder-attendance.user.table.random_point": "랜덤 포인트",
    "lastorder-attendance.user.table.consecutive": "개근",
    "lastorder-attendance.user.table.total_days": "총 출석일",
    "lastorder-attendance.user.table.miss": "꽝",
    "lastorder-attendance.user.summary.attendance_point": "출석점수",
    "lastorder-attendance.user.summary.attendance_time": "출석시간",
    "lastorder-attendance.user.summary.consecutive_point": "개근점수",
    "lastorder-attendance.user.summary.progress_status": "진행상태",
    "lastorder-attendance.user.summary.ranking_point": "랭킹점수",
    "lastorder-attendance.user.summary.attendance_status": "출석여부",
    "lastorder-attendance.user.summary.attendance_auth": "출석권한",
    "lastorder-attendance.user.summary.consecutive_category": "개근분류",
    "lastorder-attendance.user.summary.status_available": "출석가능",
    "lastorder-attendance.user.summary.status_closed": "출석마감",
    "lastorder-attendance.user.summary.completed": "출석완료",
    "lastorder-attendance.user.summary.not_completed": "미출석",
    "lastorder-attendance.user.summary.logged_in": "로그인 사용자",
    "lastorder-attendance.user.summary.all_day": "하루 종일",
    "lastorder-attendance.user.summary.view_detail": "자세히 보기",
    "lastorder-attendance.user.nav.prev_month": "이전달",
    "lastorder-attendance.user.nav.this_month": "이번달",
    "lastorder-attendance.user.nav.next_month": "다음달",
    "lastorder-attendance.user.consecutive_days": "{days}일째",
    "lastorder-attendance.user.total_days_suffix": "{days}일",
    "lastorder-attendance.admin.settings.title": "출석부 설정",
    "lastorder-attendance.admin.attendance.title": "출석 현황",
    "lastorder-attendance.admin.menu.title": "출석부 관리",
    "lastorder-attendance.admin.menu.settings": "환경설정",
    "lastorder-attendance.admin.menu.list": "출석 현황"
}
```

#### `en.json` (영어)

```json
{
    "lastorder-attendance.user.title": "Attendance",
    "lastorder-attendance.user.check_in": "Check In",
    "lastorder-attendance.user.checked_in": "Attendance completed.",
    "lastorder-attendance.user.checked_in_notice": "You can only check in once a day. Please come back tomorrow!",
    "lastorder-attendance.user.greeting_placeholder": "Enter your greeting",
    "lastorder-attendance.user.calendar.attended": "Attended",
    "lastorder-attendance.user.calendar.absent": "Absent",
    "lastorder-attendance.user.calendar.future": "Not yet",
    "lastorder-attendance.user.table.rank": "Rank",
    "lastorder-attendance.user.table.time": "Time",
    "lastorder-attendance.user.table.greeting": "Greeting",
    "lastorder-attendance.user.table.nickname": "Nickname",
    "lastorder-attendance.user.table.point": "Point",
    "lastorder-attendance.user.table.random_point": "Random Point",
    "lastorder-attendance.user.table.consecutive": "Streak",
    "lastorder-attendance.user.table.total_days": "Total Days",
    "lastorder-attendance.user.table.miss": "Miss",
    "lastorder-attendance.admin.settings.title": "Attendance Settings",
    "lastorder-attendance.admin.attendance.title": "Attendance Status",
    "lastorder-attendance.admin.menu.title": "Attendance Management",
    "lastorder-attendance.admin.menu.settings": "Settings",
    "lastorder-attendance.admin.menu.list": "Attendance List"
}
```

### 10.2 백엔드 다국어 (`src/lang/`)

#### `ko/attendance.php`

```php
return [
    'check_in_success' => '출석이 완료되었습니다.',
    'already_checked_in' => '이미 오늘 출석하셨습니다.',
    'not_allowed_time' => '출석 가능 시간이 아닙니다. (:start ~ :end)',
    'login_required' => '로그인 후 출석이 가능합니다.',
    'rank_bonus' => ':rank위 보너스',
    'weekly_bonus' => ':days일 연속출석 보너스',
    'monthly_bonus' => ':days일 연속출석 보너스',
    'yearly_bonus' => ':days일 연속출석 보너스',
    'point_description' => '출석 포인트 (:date)',
    'settings_updated' => '출석부 설정이 저장되었습니다.',
];
```

---

## 11. 권한 및 메뉴

### 11.1 권한 구조

```php
// module.php → getPermissions()
public function getPermissions(): array
{
    return [
        'name' => [
            'ko' => '출석부',
            'en' => 'Attendance',
        ],
        'description' => [
            'ko' => '출석부 모듈 권한',
            'en' => 'Attendance module permissions',
        ],
        'categories' => [
            // 출석 관리 권한
            [
                'identifier' => 'attendance',
                'name' => [
                    'ko' => '출석 관리',
                    'en' => 'Attendance Management',
                ],
                'description' => [
                    'ko' => '출석 기록 관리 권한',
                    'en' => 'Attendance record management permissions',
                ],
                'permissions' => [
                    [
                        'action' => 'read',
                        'name' => ['ko' => '출석 현황 조회', 'en' => 'View Attendance'],
                        'description' => ['ko' => '출석 현황 목록 조회', 'en' => 'View attendance list'],
                        'type' => 'admin',
                        'roles' => ['admin', 'manager'],
                    ],
                    [
                        'action' => 'delete',
                        'name' => ['ko' => '출석 기록 삭제', 'en' => 'Delete Attendance'],
                        'description' => ['ko' => '출석 기록 삭제', 'en' => 'Delete attendance record'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                ],
            ],
            // 환경설정 권한
            [
                'identifier' => 'settings',
                'name' => [
                    'ko' => '환경설정',
                    'en' => 'Settings',
                ],
                'description' => [
                    'ko' => '출석부 환경설정 권한',
                    'en' => 'Attendance settings permissions',
                ],
                'permissions' => [
                    [
                        'action' => 'read',
                        'name' => ['ko' => '환경설정 조회', 'en' => 'View Settings'],
                        'description' => ['ko' => '출석부 환경설정 조회', 'en' => 'View attendance settings'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                    [
                        'action' => 'update',
                        'name' => ['ko' => '환경설정 수정', 'en' => 'Update Settings'],
                        'description' => ['ko' => '출석부 환경설정 수정', 'en' => 'Update attendance settings'],
                        'type' => 'admin',
                        'roles' => ['admin'],
                    ],
                ],
            ],
        ],
    ];
}
```

### 11.2 관리자 메뉴

```php
// module.php → getAdminMenus()
public function getAdminMenus(): array
{
    return [
        [
            'name' => [
                'ko' => '출석부 관리',
                'en' => 'Attendance Management',
            ],
            'slug' => 'lastorder-attendance',
            'url' => null,
            'icon' => 'fas fa-calendar-check',
            'order' => 35,
            'children' => [
                [
                    'name' => [
                        'ko' => '환경설정',
                        'en' => 'Settings',
                    ],
                    'slug' => 'lastorder-attendance-settings',
                    'url' => '/admin/attendance/settings',
                    'icon' => 'fas fa-cog',
                    'order' => 1,
                    'permission' => 'lastorder-attendance.settings.read',
                ],
                [
                    'name' => [
                        'ko' => '출석 현황',
                        'en' => 'Attendance List',
                    ],
                    'slug' => 'lastorder-attendance-list',
                    'url' => '/admin/attendance',
                    'icon' => 'fas fa-list',
                    'order' => 2,
                    'permission' => 'lastorder-attendance.attendance.read',
                ],
            ],
        ],
    ];
}
```

---

## 12. 구현 단계별 계획

### Phase 1: 프로젝트 기본 구조 설정

- [ ] `module.json` 작성
- [ ] `module.php` (Module 클래스) 작성
- [ ] `composer.json` 작성
- [ ] 기본 디렉토리 구조 생성
- [ ] `config/attendance.php` 설정 파일 작성
- [ ] `LICENSE` 파일 추가

### Phase 2: 데이터베이스

- [ ] `attendances` 테이블 마이그레이션 작성
- [ ] `attendance_bonuses` 테이블 마이그레이션 작성
- [ ] `Attendance` 모델 작성
- [ ] `AttendanceBonus` 모델 작성
- [ ] `AttendanceFactory` 작성 (테스트용)
- [ ] `AttendanceSettingsSeeder` 작성 (기본 설정값)
- [ ] `DefaultGreetingsSeeder` 작성 (기본 인삿말)

### Phase 3: Repository 계층

- [ ] `AttendanceRepositoryInterface` 작성
- [ ] `AttendanceRepository` 구현
- [ ] `AttendanceBonusRepositoryInterface` 작성
- [ ] `AttendanceBonusRepository` 구현

### Phase 4: Service 계층

- [ ] `AttendanceService` 작성 (출석 체크 핵심 로직)
- [ ] `AttendanceBonusService` 작성 (보너스 계산/지급)
- [ ] `AttendanceSettingsService` 작성 (설정 관리)
- [ ] `AttendanceGreetingService` 작성 (인삿말 관리)

### Phase 5: HTTP 계층

- [ ] `CheckInRequest` FormRequest 작성
- [ ] `UpdateSettingsRequest` FormRequest 작성
- [ ] `AttendanceController` 작성 (사용자 API)
- [ ] `AttendanceAdminController` 작성 (관리자 출석 현황 API)
- [ ] `AttendanceSettingsController` 작성 (관리자 설정 API)
- [ ] `AttendanceResource` / `AttendanceListResource` 작성
- [ ] `AttendanceCalendarResource` 작성
- [ ] `AttendanceSettingsResource` 작성
- [ ] `api.php` 라우트 정의

### Phase 6: Service Provider

- [ ] `AttendanceServiceProvider` 작성 (Repository 바인딩)

### Phase 7: Hook 리스너

- [ ] `AutoAttendanceListener` 작성 (로그인 시 자동출석)
- [ ] `AttendanceActivityLogListener` 작성 (활동 로그)

### Phase 8: 프론트엔드 (JSON 레이아웃)

- [ ] `resources/routes/admin.json` 작성
- [ ] `resources/routes/user.json` 작성
- [ ] `resources/layouts/user/user_attendance.json` 작성
  - [ ] 상단 정보 바 + 네비게이션
  - [ ] 출석 현황 요약 섹션
  - [ ] 월별 캘린더 (출석/결석/미출석 표시)
  - [ ] 출석 체크 폼
  - [ ] 오늘 출석 목록 테이블 (페이지네이션)
- [ ] `resources/layouts/admin/admin_attendance_settings.json` 작성
  - [ ] 기본 설정 폼
  - [ ] 순위 보너스 설정
  - [ ] 연속출석 보너스 설정
  - [ ] 랜덤 포인트 설정
  - [ ] 인삿말 설정
- [ ] `resources/layouts/admin/admin_attendance_index.json` 작성
  - [ ] 날짜별 출석 목록
  - [ ] 필터/검색
  - [ ] 통계 요약

### Phase 9: 다국어

- [ ] `resources/lang/ko.json` 작성
- [ ] `resources/lang/en.json` 작성
- [ ] `src/lang/ko/attendance.php` 작성
- [ ] `src/lang/en/attendance.php` 작성

### Phase 10: 테스트

- [ ] `Feature/CheckInTest.php` — 출석 체크 기능 테스트
  - 정상 출석
  - 중복 출석 방지
  - 출석 가능 시간 외 출석 시도
  - 비로그인 출석 시도
- [ ] `Feature/BonusTest.php` — 보너스 지급 테스트
  - 순위 보너스 (1~3위)
  - 연속출석 보너스 (7일, 30일, 365일)
  - 랜덤 포인트
- [ ] `Feature/AutoAttendanceTest.php` — 자동출석 테스트
  - 자동출석 활성화/비활성화
  - 로그인 시 자동출석 동작
- [ ] `Feature/AdminSettingsTest.php` — 관리자 설정 테스트
  - 설정 조회/수정
  - 유효하지 않은 설정값 검증
- [ ] `Unit/AttendanceServiceTest.php` — 서비스 단위 테스트
- [ ] `Unit/AttendanceBonusServiceTest.php` — 보너스 서비스 단위 테스트

### Phase 11: 마무리

- [ ] `CHANGELOG.md` 작성
- [ ] `README.md` 작성 (설치 가이드, 기능 설명)
- [ ] 코드 스타일 검증 (Laravel Pint, PSR-12)
- [ ] 전체 테스트 실행 및 검증

---

## 부록: 참고 자료

### A. 참고한 기존 모듈

| 모듈 | 참고 포인트 |
|------|-------------|
| `sirsoft-board` | Module 클래스 구조, 권한/메뉴 정의, Service-Repository 패턴, JSON 레이아웃, 라우트 정의 |
| `sirsoft-ecommerce` | module.json assets 설정, Hook 리스너, ServiceProvider 패턴 |
| `sirsoft-page` | 간결한 모듈 구조 참고, 간단한 CRUD 패턴 |

### B. 기술 스택

- **백엔드**: PHP 8.2+, Laravel 12.x
- **프론트엔드**: React 19 (JSON 레이아웃 엔진을 통한 선언적 UI)
- **데이터베이스**: MySQL 8.0+ (utf8mb4)
- **테스트**: PHPUnit 11.x, Vitest
- **코드 스타일**: Laravel Pint (PSR-12)
