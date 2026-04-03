# 12. 구현 순서 및 체크리스트

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 11-PROHIBITED-PATTERNS](11-PROHIBITED-PATTERNS.md)

---

## 1. 구현 순서 (Phase)

### Phase 1: 기반 구조 (Foundation)

기존 코드를 모두 제거하고 새로 작성.

- [ ] `module.json` 버전 업데이트 (`1.0.9`)
- [ ] `composer.json` 정리 (require에 `php: ^8.2`만)
- [ ] `module.php` 재작성 (AbstractModule 상속, 메뉴/권한/훅리스너)
- [ ] `config/settings/defaults.json` 생성

### Phase 2: 데이터베이스 (Database)

- [ ] 마이그레이션 작성 (`create_attendances_table`)
  - [ ] 한국어 comment 추가
  - [ ] down() 구현
  - [ ] 인덱스 및 UNIQUE 제약조건
- [ ] Eloquent 모델 (`Attendance`) 작성
- [ ] Enum 작성 (`ConsecutiveType`)

### Phase 3: 백엔드 핵심 (Backend Core)

- [ ] `Contracts/AttendanceRepositoryInterface.php` 인터페이스
- [ ] `Repositories/AttendanceRepository.php` 구현체
- [ ] `Services/AttendanceSettingsService.php` (ModuleSettingsInterface 구현)
- [ ] `Services/AttendanceGreetingService.php`
- [ ] `Services/AttendanceBonusService.php`
- [ ] `Services/AttendanceService.php`
- [ ] `Http/Requests/CheckInRequest.php`
- [ ] `Http/Requests/Admin/UpdateSettingsRequest.php`
- [ ] `Http/Resources/AttendanceResource.php`

### Phase 4: 컨트롤러 & 라우트 (Controllers & Routes)

- [ ] `Http/Controllers/Api/Admin/AttendanceController.php` (AdminBaseController)
- [ ] `Http/Controllers/Api/Admin/SettingsController.php` (AdminBaseController)
- [ ] `Http/Controllers/Api/Auth/AttendanceController.php` (AuthBaseController)
- [ ] `Http/Controllers/Api/Public/AttendanceController.php` (PublicBaseController)
- [ ] `routes/api.php` 라우트 파일

### Phase 5: 훅 시스템 (Hooks)

- [ ] `Listeners/AutoAttendanceListener.php` (자동출석)
- [ ] `Listeners/AttendanceActivityLogListener.php` (활동 로그)

### Phase 6: 다국어 (i18n)

- [ ] `src/lang/ko/messages.php` (백엔드 한국어)
- [ ] `src/lang/en/messages.php` (백엔드 영어)
- [ ] `resources/lang/ko.json` (프론트엔드 한국어 메인)
- [ ] `resources/lang/en.json` (프론트엔드 영어 메인)
- [ ] `resources/lang/partial/ko/admin.json`
- [ ] `resources/lang/partial/ko/user.json`
- [ ] `resources/lang/partial/en/admin.json`
- [ ] `resources/lang/partial/en/user.json`

### Phase 7: 프론트엔드 라우트 (Frontend Routes)

- [ ] `resources/routes/admin.json` (관리자 라우트)
- [ ] `resources/routes/user.json` (사용자 라우트)

### Phase 8: 관리자 레이아웃 (Admin Layouts)

- [ ] `resources/layouts/admin/admin_attendance_index.json`
  - [ ] PageHeader + 설정 링크 버튼
  - [ ] 날짜/검색 필터
  - [ ] 통계 카드 (오늘 출석, 전체 회원, 출석률)
  - [ ] DataGrid (출석 목록)
  - [ ] 삭제/재계산 액션
  - [ ] 페이지네이션
- [ ] `resources/layouts/admin/admin_attendance_settings.json`
  - [ ] PageHeader + 저장 버튼 (children)
  - [ ] 기본 설정 섹션
  - [ ] 시간 설정 섹션
  - [ ] 순위 보너스 섹션
  - [ ] 연속출석 보너스 섹션
  - [ ] 랜덤 포인트 섹션
  - [ ] 인삿말 목록 관리 (iteration + 추가/삭제)

### Phase 9: 사용자 레이아웃 (User Layout)

- [ ] `resources/layouts/user/user_attendance.json`
  - [ ] 상단 바 (현재 시간 실시간, 관리자 버튼, 월 네비게이션)
  - [ ] 정보 패널 (출석/개근/랭킹 점수, 자세히 보기 드롭다운)
  - [ ] 캘린더 (출석/결석/미출석 표시)
  - [ ] 출석 폼 (인삿말 + 출석 버튼 / 완료 메시지)
  - [ ] 오늘 출석 목록 (Div 기반 테이블 + iteration)
  - [ ] 페이지네이션
  - [ ] scripts (실시간 시계)
- [ ] `resources/extensions/user-navigation.json` (네비게이션 확장)

### Phase 10: 테스트 (Tests)

- [ ] `tests/bootstrap.php`
- [ ] `tests/stubs.php` (코어 스텁)
- [ ] `tests/Feature/CheckInTest.php`
- [ ] `tests/Feature/BonusTest.php`
- [ ] `tests/Feature/AutoAttendanceTest.php`
- [ ] `tests/Feature/Admin/AttendanceAdminTest.php`
- [ ] `tests/Feature/Admin/SettingsTest.php`
- [ ] `tests/Unit/AttendanceServiceTest.php`
- [ ] `tests/Unit/AttendanceBonusServiceTest.php`
- [ ] `phpunit.xml`
- [ ] `.github/workflows/tests.yml`

### Phase 11: 레이아웃 렌더링 테스트

- [ ] `vitest.config.ts`
- [ ] `resources/js/__tests__/layouts/user_attendance.test.tsx`
- [ ] `resources/js/__tests__/layouts/admin_attendance_index.test.tsx`
- [ ] `resources/js/__tests__/layouts/admin_attendance_settings.test.tsx`

### Phase 12: 마무리 (Finalization)

- [ ] `CHANGELOG.md` 업데이트 (1.0.9 항목)
- [ ] `README.md` 업데이트
- [ ] 다크 모드 클래스 확인 (light/dark variant)
- [ ] 모든 테스트 통과 확인
- [ ] PHP 구문 검사 통과 확인
- [ ] 금지 패턴 체크리스트 최종 확인 ([11-PROHIBITED-PATTERNS](11-PROHIBITED-PATTERNS.md))

---

## 2. 기존 코드 처리

기존 코드(v1.0.0~v1.0.8)는 **전면 재작성**합니다.

기존 코드의 알려진 문제점:
- `App\Models\Setting` 사용 (g7에 존재하지 않음)
- `DataTable` 컴포넌트 사용 (존재하지 않음)
- `ForEach` 컴포넌트 사용 (존재하지 않음)
- `_auth.user` 참조 (g7 표준은 `_global.currentUser`)
- `Tr/Td/Th/Thead/Tbody` 미등록 컴포넌트 사용
- `successWithResource()` 페이지네이션 메타데이터 소실
- 화살표 함수 HTML 인코딩 문제
- 유저 페이지에서 관리자 API 호출

이러한 문제를 근본적으로 해결하기 위해 처음부터 AGENTS.md 규칙을 준수하여 새로 작성합니다.

---

## 3. 버전 관리

| 버전 | 상태 | 설명 |
|------|------|------|
| 1.0.0 ~ 1.0.8 | 레거시 | 기존 구현 (폐기) |
| **1.0.9** | **신규** | 전면 재작성 첫 버전 |
| 1.1.0+ | 향후 | 기능 추가 (필요시) |

### CHANGELOG.md 형식

```markdown
## [1.0.9] - YYYY-MM-DD

### 변경됨 (Changed)

- **전면 재작성**: AGENTS.md 규칙을 준수하여 모듈 전체를 처음부터 재설계 및 구현
  - Service-Repository 패턴 적용 (RepositoryInterface 주입)
  - ModuleSettingsInterface 기반 설정 시스템
  - FormRequest 검증
  - ResponseHelper 표준 응답
  - 사용자 템플릿 호환 컴포넌트 (Div 기반 테이블)
  - 유저 페이지에서 관리자 API 분리 (/status 공개 API)
  - 다크 모드 지원
  - 전 계층 테스트 (PHPUnit + Vitest)
```

---

## 4. 파일 삭제 대상

기존 코드 중 삭제 후 재작성할 파일 목록:

```
src/ 하위 전체 파일
database/ 하위 전체 파일
resources/ 하위 전체 파일
tests/ 하위 전체 파일
config/ 하위 전체 파일
upgrades/ 하위 전체 파일
```

유지할 파일:
```
.github/workflows/tests.yml (수정 후 유지)
.gitignore
LICENSE
```
