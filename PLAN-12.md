## 12. 구현 단계별 계획

### Phase 1: 프로젝트 기본 구조 설정

- [x] `module.json` 작성
- [x] `module.php` (Module 클래스) 작성
- [x] `composer.json` 작성
- [x] 기본 디렉토리 구조 생성
- [x] `config/attendance.php` 설정 파일 작성
- [x] `LICENSE` 파일 추가

### Phase 2: 데이터베이스

- [x] `attendances` 테이블 마이그레이션 작성
- [x] `attendance_bonuses` 테이블 마이그레이션 작성
- [x] `Attendance` 모델 작성
- [x] `AttendanceBonus` 모델 작성
- [x] `AttendanceFactory` 작성 (테스트용)
- [x] `AttendanceSettingsSeeder` 작성 (기본 설정값)
- [x] `DefaultGreetingsSeeder` 작성 (기본 인삿말)

### Phase 3: Repository 계층

- [x] `AttendanceRepositoryInterface` 작성
- [x] `AttendanceRepository` 구현
- [x] `AttendanceBonusRepositoryInterface` 작성
- [x] `AttendanceBonusRepository` 구현

### Phase 4: Service 계층

- [x] `AttendanceService` 작성 (출석 체크 핵심 로직)
- [x] `AttendanceBonusService` 작성 (보너스 계산/지급)
- [x] `AttendanceSettingsService` 작성 (설정 관리)
- [x] `AttendanceGreetingService` 작성 (인삿말 관리)

### Phase 5: HTTP 계층

- [x] `CheckInRequest` FormRequest 작성
- [x] `UpdateSettingsRequest` FormRequest 작성
- [x] `AttendanceController` 작성 (사용자 API)
- [x] `AttendanceAdminController` 작성 (관리자 출석 현황 API, 관리자 수동 연속 출첵 수정 API recalculateConsecutiveDays / recalculateTotalDays 활용)
- [x] `AttendanceSettingsController` 작성 (관리자 설정 API)
- [x] `AttendanceResource` / `AttendanceListResource` 작성
- [x] `AttendanceCalendarResource` 작성
- [x] `AttendanceSettingsResource` 작성
- [x] `api.php` 라우트 정의

### Phase 6: Service Provider

- [x] `AttendanceServiceProvider` 작성 (Repository 바인딩)

### Phase 7: Hook 리스너

- [x] `AutoAttendanceListener` 작성 (로그인 시 자동출석)
- [x] `AttendanceActivityLogListener` 작성 (활동 로그)

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
  - [ ] 관리자 수동 연속 출첵 수정(특정회원 대상 수정 진행할수 있게)

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
