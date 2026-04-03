# 06. 관리자 페이지 레이아웃

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 05-FRONTEND-USER](05-FRONTEND-USER.md)

---

## 1. 레이아웃 파일

| 파일 | DB 등록명 | 용도 |
|------|-----------|------|
| `resources/layouts/admin/admin_attendance_index.json` | `lastorder-attendance.admin_attendance_index` | 출석 현황 |
| `resources/layouts/admin/admin_attendance_settings.json` | `lastorder-attendance.admin_attendance_settings` | 출석 설정 |

모든 관리자 레이아웃은 `"extends": "_admin_base"` 상속.

---

## 2. 프론트엔드 라우트

### resources/routes/admin.json

```json
{
  "version": "1.0.0",
  "routes": [
    {
      "path": "*/admin/attendance",
      "layout": "admin_attendance_index",
      "auth_required": true,
      "meta": {
        "title": "$t:lastorder-attendance.admin.attendance.title"
      }
    },
    {
      "path": "*/admin/attendance/settings",
      "layout": "admin_attendance_settings",
      "auth_required": true,
      "meta": {
        "title": "$t:lastorder-attendance.admin.settings.title"
      }
    }
  ]
}
```

---

## 3. 출석 현황 페이지 (admin_attendance_index)

### 3.1 페이지 구성

```
┌──────────────────────────────────────────────────────────┐
│ PageHeader: "출석 현황"                                   │
│                                    [설정] 버튼 (navigate) │
├──────────────────────────────────────────────────────────┤
│ 필터 영역:                                                │
│ [날짜 선택: 2026-04-03] [검색어 입력     ] [검색 버튼]    │
├──────────────────────────────────────────────────────────┤
│ 통계 카드:                                                │
│ 오늘 출석: 169명  |  전체 회원: 1,234명  |  출석률: 13.7% │
├──────────────────────────────────────────────────────────┤
│ DataGrid:                                                 │
│ ┌────┬────────┬────────┬──────┬────────┬──────┬──────┐   │
│ │순위│출석시각│ 별명   │포인트│랜덤포인│ 개근 │ 액션 │   │
│ ├────┼────────┼────────┼──────┼────────┼──────┼──────┤   │
│ │  1 │00:00:01│ 홍길동 │ 110  │  100   │16일 │[삭제]│   │
│ │  2 │00:01:23│ 김철수 │  85  │   75   │ 3일 │[삭제]│   │
│ └────┴────────┴────────┴──────┴────────┴──────┴──────┘   │
│                                                           │
│ 페이지네이션: [< 1 2 3 4 5 >]                             │
└──────────────────────────────────────────────────────────┘
```

### 3.2 Data Sources

```json
{
  "data_sources": [
    {
      "id": "attendanceList",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/admin/attendance",
      "method": "GET",
      "auto_fetch": true,
      "auth_required": true,
      "params": {
        "page": "{{_local.currentPage ?? 1}}",
        "per_page": 20,
        "date": "{{_local.filterDate}}",
        "search": "{{_local.searchQuery}}"
      }
    }
  ]
}
```

### 3.3 DataGrid 컬럼 정의

관리자 템플릿(sirsoft-admin_basic)에는 `DataGrid` composite 컴포넌트가 있음.  
컬럼은 `field`/`header` 형식 사용 (key/label 아님).

```json
{
  "name": "DataGrid",
  "type": "composite",
  "props": {
    "columns": [
      { "field": "daily_rank", "header": "$t:lastorder-attendance.admin.attendance.columns.rank", "width": "80px" },
      { "field": "attended_time", "header": "$t:lastorder-attendance.admin.attendance.columns.time", "width": "120px" },
      { "field": "user_nick", "header": "$t:lastorder-attendance.admin.attendance.columns.nickname" },
      { "field": "greeting", "header": "$t:lastorder-attendance.admin.attendance.columns.greeting" },
      { "field": "total_point", "header": "$t:lastorder-attendance.admin.attendance.columns.total_point", "width": "100px" },
      { "field": "random_point", "header": "$t:lastorder-attendance.admin.attendance.columns.random_point", "width": "120px" },
      { "field": "consecutive_days", "header": "$t:lastorder-attendance.admin.attendance.columns.consecutive", "width": "100px" },
      { "field": "total_days", "header": "$t:lastorder-attendance.admin.attendance.columns.total_days", "width": "100px" }
    ],
    "data": "{{attendanceList?.data?.data ?? []}}",
    "rowKey": "id"
  }
}
```

### 3.4 액션 (삭제, 재계산)

**삭제 버튼** (cellChildren으로 커스텀 셀):

```json
{
  "handler": "apiCall",
  "params": {
    "method": "DELETE",
    "url": "/api/modules/lastorder-attendance/admin/attendance/{{row.id}}",
    "onSuccess": [
      { "handler": "refetchDataSource", "params": { "id": "attendanceList" } },
      { "handler": "showToast", "params": { "message": "$t:lastorder-attendance.admin.messages.deleted", "type": "success" } }
    ]
  }
}
```

---

## 4. 설정 페이지 (admin_attendance_settings)

### 4.1 페이지 구성

```
┌──────────────────────────────────────────────────────────┐
│ PageHeader: "출석부 설정"                                 │
│                                              [저장] 버튼  │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ ■ 기본 설정                                               │
│   기본 출석 포인트: [10          ]                         │
│   자동출석 사용:    [✓] 사용                               │
│                                                          │
│ ■ 출석 시간 설정                                          │
│   출석 가능 시간 제한: [✓] 사용                            │
│   시작 시간: [01] 시                                      │
│   종료 시간: [23] 시                                      │
│                                                          │
│ ■ 순위 보너스                                             │
│   1등 보너스: [100         ]                               │
│   2등 보너스: [50          ]                               │
│   3등 보너스: [30          ]                               │
│                                                          │
│ ■ 연속출석 보너스                                          │
│   7일 연속: [100         ]                                │
│   30일 연속: [500         ]                               │
│   365일 연속: [5000        ]                              │
│                                                          │
│ ■ 랜덤 포인트                                             │
│   랜덤 포인트 사용: [✓] 사용                               │
│   최소값: [1            ]                                 │
│   최대값: [200          ]                                 │
│                                                          │
│ ■ 기본 인삿말 목록                                        │
│   [ㅋㅋㅋㅋ    ] [삭제]                                    │
│   [좋은하루    ] [삭제]                                    │
│   [화이팅      ] [삭제]                                    │
│               [+ 인삿말 추가]                              │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### 4.2 Data Sources

```json
{
  "data_sources": [
    {
      "id": "settings",
      "type": "api",
      "endpoint": "/api/modules/lastorder-attendance/admin/settings",
      "method": "GET",
      "auto_fetch": true,
      "auth_required": true,
      "initLocal": "form",
      "refetchOnMount": true
    }
  ]
}
```

### 4.3 저장 액션

PageHeader의 `children`으로 Button 직접 렌더링 (g7 표준 패턴):

```json
{
  "id": "save-button",
  "type": "basic",
  "name": "Button",
  "props": {
    "text": "$t:lastorder-attendance.admin.settings.save",
    "variant": "primary"
  },
  "actions": [
    {
      "type": "click",
      "handler": "apiCall",
      "params": {
        "method": "PUT",
        "url": "/api/modules/lastorder-attendance/admin/settings",
        "body": "{{_local.form}}",
        "onSuccess": [
          { "handler": "showToast", "params": { "message": "$t:lastorder-attendance.admin.messages.settings_saved", "type": "success" } }
        ]
      }
    }
  ]
}
```

> **주의**: PageHeader의 `actions` prop에 핸들러 정의는 onClick으로 자동 변환되지 않음. `children`으로 Button을 직접 넣어야 함.

### 4.4 인삿말 목록 관리

`iteration` 속성으로 인삿말 목록 반복 렌더링:

```json
{
  "type": "basic",
  "name": "Div",
  "iteration": {
    "source": "{{_local.form?.greetings ?? []}}",
    "item_var": "greetingItem",
    "index_var": "greetingIndex"
  },
  "children": [
    {
      "type": "basic",
      "name": "Input",
      "props": {
        "value": "{{greetingItem}}"
      }
    },
    {
      "type": "basic",
      "name": "Button",
      "props": { "text": "$t:lastorder-attendance.admin.settings.delete" },
      "actions": [
        {
          "type": "click",
          "handler": "setState",
          "params": {
            "target": "local",
            "key": "form.greetings",
            "value": "{{_local.form.greetings.filter(function(v, i) { return i !== greetingIndex })}}"
          }
        }
      ]
    }
  ]
}
```

> **주의**: 화살표 함수(`=>`)는 HTML 엔티티 인코딩 문제 발생. 일반 함수 표현식(`function(){}`) 사용.

---

## 5. 관리자 템플릿 컴포넌트 참고

관리자 템플릿(sirsoft-admin_basic) 사용 가능 컴포넌트:

- **Basic**: Div, Button, Input, Select, Form, A, H1-H4, Span, P, Label, etc. (37개)
- **Composite**: DataGrid, PageHeader, Card, Modal, Alert, Badge, etc.

> DataGrid는 `DataTable`이 아님. `field`/`header` 사용 (`key`/`label` 아님).

---

## 다음: [07-SETTINGS-SYSTEM.md](07-SETTINGS-SYSTEM.md) →
