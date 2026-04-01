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
