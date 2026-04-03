# 01. 프로젝트 개요 및 디렉토리 구조

> [← INDEX로 돌아가기](INDEX.md)

---

## 1. 모듈 메타데이터

### module.json

```json
{
    "identifier": "lastorder-attendance",
    "vendor": "lastorder",
    "name": {
        "ko": "출석부",
        "en": "Attendance"
    },
    "version": "1.0.9",
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

- `getName()`, `getVersion()`, `getDescription()`은 module.json에서 자동 파싱 (하드코딩 불필요)
- `getIdentifier()`, `getVendor()`는 디렉토리명에서 자동 추론 (final 메서드)

---

## 2. 네이밍 규칙

| 항목 | 값 |
|------|-----|
| 디렉토리명 | `lastorder-attendance` |
| 네임스페이스 | `Modules\Lastorder\Attendance\` |
| Composer 패키지명 | `modules/lastorder-attendance` |
| URL prefix (자동) | `/api/modules/lastorder-attendance` |
| Name prefix (자동) | `api.modules.lastorder-attendance.` |
| 다국어 백엔드 키 | `lastorder-attendance::messages.key` |
| 다국어 프론트엔드 키 | `$t:lastorder-attendance.section.key` |

---

## 3. 디렉토리 구조

```
lastorder-attendance/
├── CHANGELOG.md                          # Keep a Changelog 형식
├── LICENSE                               # MIT 라이선스
├── README.md
├── composer.json                         # php ^8.2만 require (프레임워크 패키지 금지)
├── module.json                           # 메타데이터 SSoT
├── module.php                            # Module 클래스 (AbstractModule 상속)
├── phpunit.xml                           # PHPUnit 설정
├── vitest.config.ts                      # Vitest 설정 (레이아웃 테스트용)
│
├── config/
│   └── settings/
│       └── defaults.json                 # 모듈 기본 설정값
│
├── database/
│   ├── migrations/
│   │   └── xxxx_xx_xx_create_attendances_table.php
│   └── seeders/
│       └── AttendanceSeeder.php          # 기본 데이터 시더
│
├── src/
│   ├── Contracts/
│   │   └── AttendanceRepositoryInterface.php
│   │
│   ├── Enums/
│   │   ├── AttendanceStatus.php          # 출석 상태 Enum
│   │   └── ConsecutiveType.php           # 연속출석 분류 Enum (주/월/연)
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── AttendanceController.php      # 관리자 출석 현황
│   │   │   │   │   └── SettingsController.php         # 관리자 설정
│   │   │   │   └── Auth/
│   │   │   │       └── AttendanceController.php       # 인증 사용자 출석
│   │   │   └── Public/
│   │   │       └── AttendanceController.php           # 공개 API (오늘 출석 목록)
│   │   ├── Requests/
│   │   │   ├── CheckInRequest.php                     # 출석 체크 검증
│   │   │   └── Admin/
│   │   │       └── UpdateSettingsRequest.php           # 설정 저장 검증
│   │   └── Resources/
│   │       └── AttendanceResource.php                 # API 리소스
│   │
│   ├── Listeners/
│   │   ├── AutoAttendanceListener.php                 # 자동출석 훅 리스너
│   │   └── AttendanceActivityLogListener.php          # 활동 로그 리스너
│   │
│   ├── Models/
│   │   └── Attendance.php                             # Eloquent 모델
│   │
│   ├── Repositories/
│   │   └── AttendanceRepository.php                   # Repository 구현체
│   │
│   ├── Services/
│   │   ├── AttendanceService.php                      # 출석 비즈니스 로직
│   │   ├── AttendanceBonusService.php                 # 보너스 계산 로직
│   │   ├── AttendanceSettingsService.php              # 설정 서비스 (ModuleSettingsInterface)
│   │   └── AttendanceGreetingService.php              # 인삿말 서비스
│   │
│   ├── lang/
│   │   ├── ko/
│   │   │   └── messages.php                           # 백엔드 한국어
│   │   └── en/
│   │       └── messages.php                           # 백엔드 영어
│   │
│   └── routes/
│       └── api.php                                    # API 라우트
│
├── resources/
│   ├── extensions/
│   │   └── user-navigation.json                       # _user_base 네비게이션 확장
│   │
│   ├── lang/
│   │   ├── ko.json                                    # 프론트엔드 한국어 (partial 참조)
│   │   ├── en.json                                    # 프론트엔드 영어 (partial 참조)
│   │   └── partial/
│   │       ├── ko/
│   │       │   ├── admin.json
│   │       │   └── user.json
│   │       └── en/
│   │           ├── admin.json
│   │           └── user.json
│   │
│   ├── layouts/
│   │   ├── admin/
│   │   │   ├── admin_attendance_index.json            # 관리자 출석 현황
│   │   │   └── admin_attendance_settings.json         # 관리자 설정
│   │   └── user/
│   │       └── user_attendance.json                   # 사용자 출석 페이지
│   │
│   └── routes/
│       ├── admin.json                                 # 관리자 프론트엔드 라우트
│       └── user.json                                  # 사용자 프론트엔드 라우트
│
├── tests/
│   ├── bootstrap.php
│   ├── stubs.php                                      # 테스트 스텁 (독립 실행)
│   ├── Feature/
│   │   ├── CheckInTest.php
│   │   ├── BonusTest.php
│   │   ├── AutoAttendanceTest.php
│   │   └── Admin/
│   │       ├── AttendanceAdminTest.php
│   │       └── SettingsTest.php
│   └── Unit/
│       ├── AttendanceServiceTest.php
│       └── AttendanceBonusServiceTest.php
│
└── upgrades/                                          # 업그레이드 스텝 (자동 발견)
    └── (필요시 추가)
```

---

## 4. Module 클래스 (module.php)

```php
<?php

namespace Modules\Lastorder\Attendance;

use App\Extension\AbstractModule;

class Module extends AbstractModule
{
    // getName(), getVersion(), getDescription()은 module.json에서 자동 파싱
    // getIdentifier(), getVendor()는 디렉토리명에서 자동 추론

    /**
     * 관리자 메뉴 정의
     */
    public function getAdminMenus(): array
    {
        return [
            [
                'name' => [
                    'ko' => '출석부',
                    'en' => 'Attendance',
                ],
                'slug' => 'lastorder-attendance',
                'url' => '/admin/attendance',
                'icon' => 'fa-calendar-check',
                'order' => 50,
                'children' => [
                    [
                        'name' => [
                            'ko' => '출석 현황',
                            'en' => 'Attendance Status',
                        ],
                        'slug' => 'lastorder-attendance-index',
                        'url' => '/admin/attendance',
                        'icon' => 'fa-list',
                        'order' => 1,
                        'permission' => 'lastorder-attendance.admin.view',
                    ],
                    [
                        'name' => [
                            'ko' => '출석부 설정',
                            'en' => 'Attendance Settings',
                        ],
                        'slug' => 'lastorder-attendance-settings',
                        'url' => '/admin/attendance/settings',
                        'icon' => 'fa-cog',
                        'order' => 2,
                        'permission' => 'lastorder-attendance.admin.settings',
                    ],
                ],
            ],
        ];
    }

    /**
     * 권한 목록
     */
    public function getPermissions(): array
    {
        return [
            [
                'identifier' => 'lastorder-attendance.admin.view',
                'name' => ['ko' => '출석 현황 조회', 'en' => 'View Attendance'],
                'description' => ['ko' => '출석 현황을 조회할 수 있습니다', 'en' => 'Can view attendance status'],
                'roles' => ['admin'],
            ],
            [
                'identifier' => 'lastorder-attendance.admin.manage',
                'name' => ['ko' => '출석 기록 관리', 'en' => 'Manage Attendance'],
                'description' => ['ko' => '출석 기록을 삭제/재계산할 수 있습니다', 'en' => 'Can delete/recalculate attendance'],
                'roles' => ['admin'],
            ],
            [
                'identifier' => 'lastorder-attendance.admin.settings',
                'name' => ['ko' => '출석부 설정', 'en' => 'Attendance Settings'],
                'description' => ['ko' => '출석부 설정을 변경할 수 있습니다', 'en' => 'Can change attendance settings'],
                'roles' => ['admin'],
            ],
        ];
    }

    /**
     * 훅 리스너 목록
     */
    public function getHookListeners(): array
    {
        return [
            \Modules\Lastorder\Attendance\Listeners\AutoAttendanceListener::class,
            \Modules\Lastorder\Attendance\Listeners\AttendanceActivityLogListener::class,
        ];
    }
}
```

---

## 5. composer.json

```json
{
    "name": "modules/lastorder-attendance",
    "description": "Daily attendance check-in and point reward module for Gnuboard7",
    "type": "g7-module",
    "license": "MIT",
    "require": {
        "php": "^8.2"
    },
    "require-dev": {
        "phpunit/phpunit": "^11.0",
        "mockery/mockery": "^1.6"
    },
    "autoload": {
        "psr-4": {
            "Modules\\Lastorder\\Attendance\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Modules\\Lastorder\\Attendance\\Tests\\": "tests/"
        }
    }
}
```

> **중요**: `illuminate/*`, `nesbot/carbon` 등 호스트 g7 앱이 제공하는 패키지는 `require`에 절대 포함하지 않음. 필요 시 `require-dev`에만 포함.

---

## 다음: [02-DATABASE.md](02-DATABASE.md) →
