# Changelog

이 파일은 [Keep a Changelog](https://keepachangelog.com/ko/1.0.0/) 형식을 따르며,
이 프로젝트는 [Semantic Versioning](https://semver.org/lang/ko/) 을 준수합니다.

## [1.0.4] - 2026-04-02

### 수정됨 (Fixed)

- **관리자 설정 저장 버튼 동작 수정**
  - PageHeader의 `actions` prop은 `onClick` 콜백을 기대하지만, 레이아웃 JSON의 핸들러 정의는 자동 변환되지 않아 버튼이 동작하지 않던 문제 수정
  - PageHeader의 `children`으로 Button 컴포넌트를 직접 렌더링하는 방식으로 변경 (g7 모듈 표준 패턴)

- **관리자 설정 인삿말 목록 표시 및 추가 불가 수정**
  - g7 템플릿 엔진에 존재하지 않는 `ForEach` (type: basic) 컴포넌트 사용을 g7 표준 `iteration` 속성으로 변경
  - 기본 인삿말 목록이 정상적으로 표시되고 추가/삭제 가능

- **관리자 출석 현황 테이블 렌더링 오류 수정**
  - g7 관리자 템플릿에 존재하지 않는 `DataTable` 컴포넌트를 `DataGrid` 컴포넌트로 교체
  - 컬럼 정의를 DataGrid 규격에 맞게 변환 (`key` → `field`, `label` → `header`, `render` → `cellChildren`)

- **사용자 출석 페이지 테이블 렌더링 및 "비회원" 표시 오류 수정**
  - 사용자 템플릿(sirsoft-basic)에 존재하지 않는 `DataTable` 컴포넌트를 기본 Table/Thead/Tbody/Tr/Th/Td 컴포넌트와 `iteration`으로 교체
  - 컴포넌트 렌더링 오류로 인해 `_auth` 상태 참조가 실패하여 로그인 상태에서도 "비회원"으로 표시되던 문제 해결

## [1.0.3] - 2026-04-02

### 수정됨 (Fixed)

- **모듈 설치 오류 수정 (Class "App\Models\Setting" not found)**
  - g7 코어에 존재하지 않는 `App\Models\Setting` 모델 사용을 g7 표준 `App\Services\ModuleSettingsService`로 전환
  - `database/seeders/AttendanceSettingsSeeder.php`, `database/seeders/DefaultGreetingsSeeder.php` 수정
  - 모듈 설치 시 시더 실행 오류 해결

- **모든 API 엔드포인트 "Target class does not exist" 오류 수정**
  - `src/Services/AttendanceSettingsService.php`에서 `App\Models\Setting` 의존성을 `ModuleSettingsService`로 교체
  - 설정 저장 방식을 DB 직접 접근에서 g7 표준 파일 기반 설정 시스템으로 변경
  - 관리자 API (`admin/attendance`, `admin/settings`) 및 사용자 API (`today`, `greeting`, `status`, `my`, `calendar`) 정상 동작

- **관리자 설정 저장 버튼 동작 수정**
  - 백엔드 API 오류 해결로 설정 저장 기능 정상 동작

- **사용자 출석 페이지 다국어 깨짐 수정**
  - `resources/lang/partial/ko/user.json`, `en/user.json`에서 중복된 `nav` 키를 하나로 병합
  - `lastorder-attendance.user.nav.this_month` 등의 번역 키가 원문 그대로 노출되던 문제 해결

## [1.0.2] - 2026-04-02

### 수정됨 (Fixed)

- **모듈 설치 및 DB API 서버 오류 수정**
  - `composer.json`에서 호스트 애플리케이션이 이미 제공하는 프레임워크 의존성(`illuminate/database`, `illuminate/support`, `nesbot/carbon`)을 `require`에서 제거
  - 모듈 설치 시 Composer 의존성 충돌로 인한 설치 실패 및 ServiceProvider 미등록 문제 해결
  - 이로 인해 발생하던 모든 DB 관련 API 엔드포인트 서버 오류 해결
  - 프레임워크 패키지들은 개발/테스트용으로 `require-dev`로 이동

### 추가됨 (Added)

- **사용자 템플릿 내비게이션 링크 추가**
  - `resources/extensions/user-navigation.json` 레이아웃 확장 파일 추가
  - 기본 사용자 템플릿(`_user_base`) 모바일 내비게이션 드로어에 출석체크 메뉴 항목 추가
  - 데스크톱 헤더에 출석체크 링크 추가 (extraMenuItems inject_props)
  - 한국어/영어 내비게이션 번역 추가 (`user.nav.section_title`, `user.nav.attendance`)

## [1.0.1] - 2026-04-02

### 수정됨 (Fixed)

- **다국어(i18n) 번역 깨짐 수정**
  - 관리자 출석 현황(`/admin/attendance`), 설정(`/admin/attendance/settings`) 페이지에서 번역 키가 `lastorder-attendance.admin.attendance.title`처럼 원문 그대로 노출되던 문제 수정
  - `resources/lang/en.json`, `ko.json`의 플랫(flat) 키 구조를 g7 모듈 표준인 `$partial` 참조 기반 계층형(nested) 구조로 변환
  - `resources/lang/partial/{en,ko}/admin.json`, `user.json` 파일 추가

- **사용자 출석 페이지 오류 수정**
  - `/attendance` 접속 시 "Layout data field 'components' must be an array" 오류 발생하던 문제 수정
  - `resources/layouts/user/user_attendance.json`에 `"extends": "_user_base"` 추가 (g7에서 `extends` 없는 레이아웃은 `components` 필드가 필수이나, 슬롯 기반 레이아웃이므로 `_user_base`를 상속하도록 수정)

## [1.0.0] - 2026-04-01

### 추가됨 (Added)

- **출석 체크 기능**
  - 매일 1회 출석 체크 (인삿말 입력)
  - 출석 가능 시간 제한 설정 (관리자)
  - 기본 포인트 + 랜덤 보너스 포인트 지급
  - IP 주소 기록
  - 당일 출석 순위 자동 계산

- **보너스 시스템**
  - 순위 보너스: 1~3위 추가 포인트 지급
  - 연속출석 보너스: 7일/30일/365일 배수 달성 시 추가 포인트
  - 랜덤 포인트: 확률 기반 추가 포인트 지급

- **자동출석 기능**
  - 로그인 시 자동출석 처리 (Hook 리스너)
  - 자동출석 인삿말 설정 또는 랜덤 인삿말 사용

- **관리자 기능**
  - 출석 현황 조회 (날짜별 목록, 페이지네이션)
  - 출석 기록 삭제
  - 특정 회원 연속/총 출석 일수 수동 재계산
  - 전체 설정 관리 (기본 포인트, 보너스, 랜덤 포인트, 시간 제한, 인삿말 등)

- **사용자 기능**
  - 출석 체크 API
  - 내 출석 현황 조회 (연속/총 출석일수)
  - 월별 출석 캘린더 조회
  - 출석 가능 상태 확인
  - 오늘 출석 목록 조회
  - 랜덤 인삿말 조회

- **프론트엔드 (JSON 레이아웃)**
  - 사용자 출석 페이지 (캘린더, 출석 목록, 출석 폼)
  - 관리자 출석 현황 페이지 (필터, 통계, 수동 재계산)
  - 관리자 설정 페이지 (모든 설정 항목)

- **다국어 지원**
  - 한국어 (ko)
  - 영어 (en)

- **보안 및 성능**
  - 요청 단위 설정 캐시 (동일 요청 내 DB 중복 조회 방지)
  - DB 트랜잭션 내 중복 출석 체크 (레이스 컨디션 방지)
  - Unique 제약조건을 통한 중복 출석 최종 방지
  - 입력값 sanitization (XSS 방지)
  - N+1 쿼리 방지 (eager loading)
  - 에러 로깅

- **테스트**
  - Feature 테스트: 출석 체크, 보너스, 자동출석, 관리자 설정
  - Unit 테스트: AttendanceService, AttendanceBonusService
