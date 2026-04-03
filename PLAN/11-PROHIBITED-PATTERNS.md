# 11. AGENTS.md 금지 패턴 정리

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 10-TESTING](10-TESTING.md)

---

## 목적

이 문서는 AGENTS.md에 명시된 금지 패턴을 출석부 모듈 관점에서 정리한 것입니다.  
**모든 구현 단계에서 이 문서를 참조하여 금지 패턴 위반이 없는지 확인해야 합니다.**

---

## 1. 백엔드 금지 패턴

### 1.1 아키텍처

| 금지 | 올바른 사용 | 출석부 적용 |
|------|------------|------------|
| Service에 검증 로직 | FormRequest + Custom Rule | CheckInRequest에서 greeting 검증 |
| FormRequest authorize()에 인증/권한 로직 | permission 미들웨어 | 라우트 미들웨어로 인증 처리 |
| Repository 구체 클래스 직접 주입 | RepositoryInterface 주입 | AttendanceRepositoryInterface 사용 |
| Controller에서 Repository 직접 주입 | Service 주입 | AttendanceService 주입 |
| DB CASCADE에만 의존한 삭제 | Service에서 명시적 삭제 (훅/로깅) | 삭제 시 훅 발행 |
| 인증 필요 미들웨어를 append()로 전역 등록 | appendToGroup('api') | 라우트 그룹에서만 미들웨어 적용 |

### 1.2 응답/데이터

| 금지 | 올바른 사용 | 출석부 적용 |
|------|------------|------------|
| `response()->json([...])` | `ResponseHelper::success()` | 모든 컨트롤러에서 $this->success() 사용 |
| 인수 순서 틀림 `success($data, $msg)` | `success($messageKey, $data)` | 메시지가 첫 번째 |
| 로케일 하드코딩 | `config('app.supported_locales')` | 다국어 키 사용 |
| 에러 메시지 하드코딩 | `__()` 함수 필수 | lastorder-attendance::messages.key |
| `successWithResource()` for 컬렉션 | `success()` for 페이지네이션 | 목록 API에 success() 사용 |

### 1.3 composer.json

| 금지 | 올바른 사용 |
|------|------------|
| `illuminate/*` in require | `php: ^8.2`만 require |
| `nesbot/carbon` in require | require-dev에만 포함 |
| 호스트 앱 제공 패키지 in require | 개발/테스트용만 require-dev |

### 1.4 데이터베이스

| 금지 | 올바른 사용 |
|------|------------|
| comment() 누락 | 한국어 comment 필수 |
| down() 미구현 | down() 구현 필수 |
| `App\Models\Setting` 사용 | `ModuleSettingsService` (파일 기반) |

### 1.5 파사드

| 금지 | 올바른 사용 |
|------|------------|
| `\Log::info()` | `use Illuminate\Support\Facades\Log; Log::info()` |
| `auth()->user()` | `Auth::user()` 또는 `$this->getCurrentUser()` |

---

## 2. 프론트엔드/레이아웃 JSON 금지 패턴

### 2.1 API/핸들러 호출

| 금지 | 올바른 사용 |
|------|------------|
| `G7Core.actions.execute` | `G7Core.dispatch` |
| `G7Core.api.call` | `G7Core.dispatch({ handler: 'apiCall' })` |
| `handler: "api"` | `handler: "apiCall"` |
| `handler: "nav"` | `handler: "navigate"` |
| `handler: "setLocalState"` | `handler: "setState"` + `target: "local"` |
| `{{handler()}}` (표현식에서 호출) | `actions: [{ handler: "xxx" }]` |

### 2.2 데이터 바인딩

| 금지 | 올바른 사용 | 출석부 적용 |
|------|------------|------------|
| `{{todayList.data}}` | `{{todayList?.data?.data}}` | 목록 데이터 접근 시 |
| `{{value}}` | `{{value ?? ''}}` (fallback 필수) | 모든 바인딩에 fallback |
| `{{error.data}}` | `{{error.errors}}` | 에러 응답 구조 |
| `$value` (이벤트 값) | `$event.target.value` | 입력 이벤트 |
| `{{props.xxx}}` (Partial) | data_sources ID 직접 참조 | |
| `{{$response.xxx}}` | `{{response.xxx}}` ($ 없음) | onSuccess 핸들러 내 |

### 2.3 iteration/반복 렌더링

| 금지 | 올바른 사용 | 출석부 적용 |
|------|------------|------------|
| `"item"`, `"index"` 키명 | `"item_var"`, `"index_var"` | 캘린더/목록 iteration |
| ForEach 컴포넌트 (존재하지 않음) | `iteration` 속성 | 모든 반복 렌더링 |
| if가 iteration보다 나중 평가 가정 | if가 iteration보다 먼저 평가됨 | 조건부 반복 시 주의 |
| `type: "conditional"` | `if` 속성 | 조건부 렌더링 |

### 2.4 컴포넌트 Props

| 금지 | 올바른 사용 |
|------|------------|
| `Icon className="w-4 h-4"` | `Icon size="sm"` |
| `Select valueKey/labelKey` | computed로 `{ value, label }` 변환 |
| Form 내 `Button` type 없음 | `type="button"` 명시 (submit 방지) |
| `options={{options}}` | `options={{options ?? []}}` (fallback) |
| HTML 태그 직접 사용 `<div>` | 기본 컴포넌트 `Div` |

### 2.5 상태 관리

| 금지 | 올바른 사용 |
|------|------------|
| 스냅샷 기반 setState | 함수형 업데이트 또는 `stateRef.current` |
| closeModal 후 setState | setState 후 closeModal (순서 중요) |
| await 후 캡처된 상태 사용 | await 후 `G7Core.state.getLocal()` 재조회 |
| setState params 키에 `{{}}` 사용 | 키는 정적 경로만 |

### 2.6 사용자 템플릿 특수 제약

| 금지 | 올바른 사용 | 이유 |
|------|------------|------|
| `Tr`, `Td`, `Th`, `Thead`, `Tbody` | `Div` + Flexbox | sirsoft-basic에 미등록 |
| `DataTable` | `Div` 기반 테이블 | 사용자 템플릿에 미존재 |
| `DataGrid` (사용자 템플릿) | `Div` 기반 테이블 | 관리자 전용 composite |
| `_auth.user` 참조 | `_global.currentUser` | g7 표준 인증 상태 |

---

## 3. 확장 시스템 금지 패턴

| 금지 | 올바른 사용 |
|------|------------|
| 활성 디렉토리 직접 수정 | `_bundled` 디렉토리에서만 작업 |
| 버전 업 없이 코드 변경 | 코드 변경 시 manifest 버전 업 필수 |
| 버전 업 시 CHANGELOG 미기록 | CHANGELOG.md 기록 필수 |
| `Storage::disk()` 직접 호출 | `StorageInterface` 사용 |
| 코어 레이아웃에 모듈 UI 하드코딩 | `layout_extensions`만 사용 |
| `layouts/` 루트에 레이아웃 배치 | `admin/` 또는 `user/` 하위 필수 |

---

## 4. 유저 페이지에서 관리자 API 사용 금지 ⚠️

```
❌ 유저 레이아웃(user_attendance.json)에서:
   /api/modules/lastorder-attendance/admin/settings (403 에러 발생)
   
✅ 대신 공개 API 사용:
   /api/modules/lastorder-attendance/status (optional.sanctum)
```

사용자 페이지에서 필요한 설정 정보(보너스 기준, 출석 시간 등)는 `/status` API를 통해 공개 범위로 제공. 관리자 전용 엔드포인트(`/admin/*`)는 절대 사용하지 않음.

---

## 5. 화살표 함수 인코딩 문제 ⚠️

레이아웃 JSON 내 표현식에서 화살표 함수 사용 시 `=>`가 HTML 엔티티 `=&gt;`로 인코딩됨:

```
❌ .filter((v, i) => i !== greetingIndex)
✅ .filter(function(v, i) { return i !== greetingIndex })
```

---

## 6. 체크리스트

구현 시 매 파일마다 확인:

```
□ Repository 인터페이스로 주입했는가?
□ FormRequest로 검증했는가? (Service에 검증 로직 없는가?)
□ ResponseHelper 메서드를 사용했는가?
□ __() 함수로 다국어 처리했는가?
□ 마이그레이션에 comment()와 down()이 있는가?
□ 레이아웃에서 등록된 컴포넌트만 사용했는가?
□ 데이터 바인딩에 fallback이 있는가?
□ iteration에서 item_var/index_var를 사용했는가?
□ 유저 페이지에서 admin API를 호출하지 않는가?
□ 버전을 올리고 CHANGELOG를 업데이트했는가?
```

---

## 다음: [12-IMPLEMENTATION-ORDER.md](12-IMPLEMENTATION-ORDER.md) →
