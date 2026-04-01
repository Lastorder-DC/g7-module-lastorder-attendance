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
