# 05. 사용자 출석 페이지 레이아웃

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 04-API-DESIGN](04-API-DESIGN.md)

---

## 1. 레이아웃 파일

**위치**: `resources/layouts/user/user_attendance.json`  
**DB 등록명**: `lastorder-attendance.user_attendance`  
**상속**: `extends: "_user_base"`

---

## 2. 프론트엔드 라우트

### resources/routes/user.json

```json
{
  "version": "1.0.0",
  "routes": [
    {
      "path": "*/attendance",
      "layout": "user_attendance",
      "meta": {
        "title": "$t:lastorder-attendance.user.page.title"
      }
    }
  ]
}
```

---

## 3. 페이지 구성 (참고 이미지 기반)

상단부터 하단까지 순서대로:

### 3.1 상단 바 (Header Bar)

```
┌─────────────────────────────────────────────────────────┐
│ 📅 2026-04-03 17:30:22  [⚙설정] [🎨스킨관리] [🔵관리]  │
│                                      [<이전달] [이번달] [다음달>] │
└─────────────────────────────────────────────────────────┘
```

- **현재 시간**: 1초마다 갱신 (scripts의 setInterval로 DOM 업데이트)
- **설정/스킨관리/관리 버튼**: 관리자(`_global.currentUser?.is_admin`)인 경우에만 표시
- **월 네비게이션**: 이전달/이번달/다음달 버튼 → setState로 `selectedYear`, `selectedMonth` 변경 후 캘린더 data_source refetch

### 3.2 정보 패널 (Stats Panel)

```
┌──────────────────────────────────────────────────────────────┐
│ 출석점수  개근점수  자세히보기▼  │ 랭킹점수  자세히보기▼  │ 출석권한     │
│   10       7일째    [드롭다운]  │   169위    [드롭다운]  │ 로그인 사용자 │
│ 출석시간  진행상태     출석여부  │           개근분류     │ 자세히보기▼   │
│ 하루종일  출석가능     출석완료  │           [드롭다운]  │               │
└──────────────────────────────────────────────────────────────┘
```

- **출석점수**: 오늘 받은 총 포인트 (`myStatus.data.today_point`)
- **개근점수**: 현재 연속출석 일수 (`myStatus.data.consecutive_days`)
- **랭킹점수**: 오늘 출석 순위 (`myStatus.data.today_rank`)
- **출석시간**: 설정 기반 (제한없음 = "하루종일", 제한있음 = "01시~23시")
- **진행상태**: 출석 가능 시간 내이면 "출석가능", 아니면 "마감"
- **출석여부**: 오늘 출석했으면 "출석완료", 아니면 "미출석"
- **출석권한**: 로그인 여부 표시
- **자세히 보기**: 클릭 시 드롭다운으로 상세 정보 표시 (setState toggle)

#### 자세히 보기 드롭다운 내용

**개근점수 자세히 보기:**
- 주간 보너스: 7일마다 +100P
- 월간 보너스: 30일마다 +500P
- 연간 보너스: 365일마다 +5000P

**랭킹점수 자세히 보기:**
- 1등: +100P
- 2등: +50P
- 3등: +30P

**개근분류 자세히 보기:**
- 주간 (7일): 🔵 파란색
- 월간 (30일): 🟢 초록색
- 연간 (365일): 🔴 빨간색

### 3.3 캘린더 (Calendar)

```
┌──────────────────────────────────────────────────────────┐
│ 1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 ...  30   │
│ ●     ●  ●  ●     ●                                     │
│                                                          │
│ ● 결석  ● 출석  ○ 미출석   * 이전달은 가입일까지 열람 가능  │
└──────────────────────────────────────────────────────────┘
```

- **출석한 날**: 초록색 (●)
- **결석한 날** (오늘 이전 + 미출석): 빨간색/회색 (●)
- **미출석** (오늘 이후): 회색 (○)
- iteration으로 일별 Div 렌더링
- 각 날짜의 상태는 `myCalendar.data.attended_dates` 배열과 비교하여 computed로 계산

### 3.4 출석 폼 (비로그인 시 숨김)

```
┌──────────────────────────────────────────────────────────┐
│ ✅ 출석이 완료되었습니다.                                 │
│ 출석은 하루 1회만 참여하실 수 있습니다. 내일 다시 출석해 주세요.^^ │
└──────────────────────────────────────────────────────────┘
```

또는 (미출석 시):

```
┌──────────────────────────────────────────────────────────┐
│ 인삿말: [ㅋㅋㅋㅋ                           ]  [출석하기] │
└──────────────────────────────────────────────────────────┘
```

- 로그인 필수 (비로그인 시 "로그인 후 이용해주세요" 표시)
- 이미 출석한 경우 완료 메시지
- 미출석 시 인삿말 입력 + 출석 버튼
- 인삿말 기본값: `/greeting/random` API에서 가져온 랜덤 인삿말

### 3.5 오늘 출석 목록 (테이블)

```
┌─────┬────────┬────────┬────────┬──────┬──────────┬──────┬─────────┐
│순위 │출석시각│ 인사말 │  별명  │포인트│랜덤포인트│ 개근 │ 총출석일│
├─────┼────────┼────────┼────────┼──────┼──────────┼──────┼─────────┤
│ 169 │17:23:00│ ㅋㅋㅋ │매일아침│  10  │    꽝    │16일째│  563일  │
│ 168 │17:05:03│ 쪽쪽아웅│booster│  85  │    75    │ 3일째│   44일  │
└─────┴────────┴────────┴────────┴──────┴──────────┴──────┴─────────┘
```

- **Div 기반 테이블** 사용 (sirsoft-basic에 Tr/Td/Th/Thead/Tbody 미등록)
- Flexbox CSS로 테이블 형태 구현
- `iteration` 속성으로 행 반복 렌더링
- 페이지네이션: 하단에 페이지 번호 표시

#### 컬럼 설명

| 컬럼 | 데이터 바인딩 | 비고 |
|------|-------------|------|
| 순위 | `{{item.daily_rank}}` | |
| 출석시각 | `{{item.attended_time}}` | HH:MM:SS |
| 인사말 | `{{item.greeting}}` | |
| 별명 | `{{item.user_nick}}` | |
| 포인트 | `{{item.base_point}}` | 기본 포인트 |
| 랜덤포인트 | `{{item.random_point}}` | 0이면 "꽝" 표시 |
| 개근 | `{{item.consecutive_days}}일째` | |
| 총출석일 | `{{item.total_days}}일` | |

---

## 4. Data Sources

```json
{
  "data_sources": [
    {
      "id": "todayList",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/today",
      "method": "GET",
      "auto_fetch": true,
      "params": {
        "page": "{{_local.currentPage ?? 1}}",
        "per_page": 20
      }
    },
    {
      "id": "myStatus",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/my/status",
      "method": "GET",
      "auto_fetch": true,
      "auth_required": true
    },
    {
      "id": "myCalendar",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/my/calendar",
      "method": "GET",
      "auto_fetch": true,
      "auth_required": true,
      "params": {
        "year": "{{_local.selectedYear}}",
        "month": "{{_local.selectedMonth}}"
      }
    },
    {
      "id": "attendanceStatus",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/status",
      "method": "GET",
      "auto_fetch": true
    },
    {
      "id": "randomGreeting",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/greeting/random",
      "method": "GET",
      "auto_fetch": true
    }
  ]
}
```

---

## 5. State 초기값

```json
{
  "state": {
    "currentPage": 1,
    "selectedYear": null,
    "selectedMonth": null,
    "greeting": "",
    "showConsecutiveDetail": false,
    "showRankDetail": false,
    "showClassDetail": false
  }
}
```

- `selectedYear`/`selectedMonth`: null이면 서버에서 현재 연월 기준으로 응답
- `showXxxDetail`: 자세히 보기 드롭다운 토글

---

## 6. 현재 시간 실시간 갱신

`scripts` 섹션에서 1초마다 DOM 갱신:

```json
{
  "scripts": [
    {
      "id": "realtime_clock",
      "type": "init",
      "code": "setInterval(function() { var el = document.getElementById('current-time'); if (el) { var now = new Date(); el.textContent = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0') + ' ' + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0') + ':' + String(now.getSeconds()).padStart(2,'0'); } }, 1000);"
    }
  ]
}
```

---

## 7. 주요 액션 (Handlers)

### 출석 체크

```json
{
  "type": "click",
  "handler": "apiCall",
  "params": {
    "method": "POST",
    "url": "/api/modules/lastorder-attendance/check-in",
    "body": {
      "greeting": "{{_local.greeting}}"
    },
    "onSuccess": [
      { "handler": "refetchDataSource", "params": { "id": "todayList" } },
      { "handler": "refetchDataSource", "params": { "id": "myStatus" } },
      { "handler": "refetchDataSource", "params": { "id": "myCalendar" } },
      { "handler": "showToast", "params": { "message": "$t:lastorder-attendance.user.messages.check_in_success", "type": "success" } }
    ]
  }
}
```

### 월 네비게이션

```json
{
  "type": "click",
  "handler": "setState",
  "params": {
    "target": "local",
    "key": "selectedMonth",
    "value": "{{(_local.selectedMonth || new Date().getMonth() + 1) - 1 || 12}}"
  }
}
```

> 월 변경 시 `myCalendar` data_source가 params 변경을 감지하여 자동 refetch

### 자세히 보기 토글

```json
{
  "type": "click",
  "handler": "setState",
  "params": {
    "target": "local",
    "key": "showConsecutiveDetail",
    "value": "{{!_local.showConsecutiveDetail}}"
  }
}
```

---

## 8. 컴포넌트 제약 (sirsoft-basic)

사용자 템플릿(sirsoft-basic)에서 사용 가능한 컴포넌트:

```
Basic 26개: A, Button, Checkbox, Div, Footer, Form, H1-H4, Header, Hr, Icon, 
Img, Input, Label, Li, Nav, Option, P, PasswordInput, Select, Span, Table, 
Textarea, Ul
```

> **주의**: `Tr`, `Td`, `Th`, `Thead`, `Tbody`는 **미등록**. 테이블 형태는 `Div` + Flexbox로 구현.

---

## 다음: [06-FRONTEND-ADMIN.md](06-FRONTEND-ADMIN.md) →
