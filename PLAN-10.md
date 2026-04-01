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
