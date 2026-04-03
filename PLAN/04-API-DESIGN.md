# 04. API 엔드포인트 설계

> [← INDEX로 돌아가기](INDEX.md) | [← 이전: 03-BACKEND-ARCHITECTURE](03-BACKEND-ARCHITECTURE.md)

---

## 1. 라우트 prefix (자동 적용)

모든 라우트에 자동으로 적용되는 prefix:

| 항목 | 값 |
|------|-----|
| URL prefix | `/api/modules/lastorder-attendance` |
| Name prefix | `api.modules.lastorder-attendance.` |

> 모듈 개발자는 내부 구조만 정의. prefix 중복 입력 금지.

---

## 2. API 엔드포인트 목록

### 2.1 관리자 API (Admin)

미들웨어: `auth:sanctum`, `admin`

| Method | 경로 | 설명 | Controller |
|--------|------|------|------------|
| GET | `/admin/attendance` | 출석 현황 목록 (날짜별, 페이지네이션) | `Admin\AttendanceController@index` |
| DELETE | `/admin/attendance/{id}` | 출석 기록 삭제 | `Admin\AttendanceController@destroy` |
| POST | `/admin/attendance/recalculate/{userId}` | 특정 회원 연속출석 재계산 | `Admin\AttendanceController@recalculate` |
| GET | `/admin/settings` | 전체 설정 조회 | `Admin\SettingsController@index` |
| PUT | `/admin/settings` | 설정 저장 | `Admin\SettingsController@store` |

### 2.2 인증 사용자 API (Auth)

미들웨어: `auth:sanctum`

| Method | 경로 | 설명 | Controller |
|--------|------|------|------------|
| POST | `/check-in` | 출석 체크 | `Auth\AttendanceController@checkIn` |
| GET | `/my/status` | 내 출석 현황 (연속/총 일수) | `Auth\AttendanceController@myStatus` |
| GET | `/my/calendar` | 내 월별 캘린더 | `Auth\AttendanceController@myCalendar` |

### 2.3 공개/선택적 인증 API

미들웨어: `optional.sanctum` (로그인 시 사용자 정보 포함, 비로그인 시에도 접근 가능)

| Method | 경로 | 설명 | Controller |
|--------|------|------|------------|
| GET | `/today` | 오늘 출석 목록 (페이지네이션) | `Public\AttendanceController@today` |
| GET | `/greeting/random` | 랜덤 인삿말 | `Public\AttendanceController@randomGreeting` |
| GET | `/status` | 출석 가능 상태 + 공개 설정 | `Public\AttendanceController@status` |

---

## 3. 라우트 파일

### src/routes/api.php

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Admin\AttendanceController as AdminAttendanceController;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Admin\SettingsController;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Auth\AttendanceController as AuthAttendanceController;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Public\AttendanceController as PublicAttendanceController;

/*
|--------------------------------------------------------------------------
| Attendance Module API Routes
|--------------------------------------------------------------------------
|
| 주의: ModuleRouteServiceProvider가 자동으로 prefix를 적용합니다.
| - URL prefix: 'api/modules/lastorder-attendance'
| - Name prefix: 'api.modules.lastorder-attendance.'
|
*/

// 관리자 전용 API
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // 출석 현황
    Route::get('attendance', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.index');
    Route::delete('attendance/{id}', [AdminAttendanceController::class, 'destroy'])
        ->name('admin.attendance.destroy');
    Route::post('attendance/recalculate/{userId}', [AdminAttendanceController::class, 'recalculate'])
        ->name('admin.attendance.recalculate');

    // 설정
    Route::get('settings', [SettingsController::class, 'index'])
        ->name('admin.settings.index');
    Route::put('settings', [SettingsController::class, 'store'])
        ->name('admin.settings.store');
});

// 인증 사용자 API
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('check-in', [AuthAttendanceController::class, 'checkIn'])
        ->name('check-in');
    Route::get('my/status', [AuthAttendanceController::class, 'myStatus'])
        ->name('my.status');
    Route::get('my/calendar', [AuthAttendanceController::class, 'myCalendar'])
        ->name('my.calendar');
});

// 공개 API (선택적 인증)
Route::middleware(['optional.sanctum'])->group(function () {
    Route::get('today', [PublicAttendanceController::class, 'today'])
        ->name('today');
    Route::get('greeting/random', [PublicAttendanceController::class, 'randomGreeting'])
        ->name('greeting.random');
    Route::get('status', [PublicAttendanceController::class, 'status'])
        ->name('status');
});
```

---

## 4. API 응답 형식

### 4.1 출석 체크 성공 응답

```json
{
  "success": true,
  "message": "출석이 완료되었습니다.",
  "data": {
    "id": 1234,
    "user_id": 42,
    "attended_at": "2026-04-03",
    "attended_time": "14:30:22",
    "greeting": "좋은 하루 되세요!",
    "base_point": 10,
    "bonus_point": 75,
    "random_point": 75,
    "rank_point": 0,
    "consecutive_point": 0,
    "total_point": 85,
    "daily_rank": 169,
    "consecutive_days": 3,
    "total_days": 44
  }
}
```

### 4.2 오늘 출석 목록 (페이지네이션)

```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1234,
        "daily_rank": 1,
        "attended_time": "00:00:01",
        "greeting": "ㅋㅋㅋㅋ",
        "user_nick": "매일아침사람",
        "base_point": 10,
        "random_point": 0,
        "rank_point": 100,
        "total_point": 110,
        "consecutive_days": 16,
        "total_days": 563
      }
    ],
    "pagination": {
      "total": 169,
      "from": 1,
      "to": 20,
      "per_page": 20,
      "current_page": 1,
      "last_page": 9
    }
  }
}
```

### 4.3 출석 상태 + 공개 설정 (/status)

> **핵심**: 이 API는 유저 페이지에서 설정 정보를 가져오는 데 사용. 관리자 API(`/admin/settings`)를 유저 페이지에서 호출하면 안 됨.

```json
{
  "success": true,
  "data": {
    "can_check_in": true,
    "already_checked_in": false,
    "time_restricted": false,
    "start_hour": null,
    "end_hour": null,
    "base_point": 10,
    "random_point_enabled": true,
    "random_point_min": 1,
    "random_point_max": 200,
    "rank_bonus": {
      "1": 100,
      "2": 50,
      "3": 30
    },
    "consecutive_bonus": {
      "weekly": { "days": 7, "point": 100 },
      "monthly": { "days": 30, "point": 500 },
      "yearly": { "days": 365, "point": 5000 }
    }
  }
}
```

### 4.4 내 월별 캘린더

```json
{
  "success": true,
  "data": {
    "year": 2026,
    "month": 4,
    "attended_dates": ["2026-04-01", "2026-04-02", "2026-04-03"],
    "consecutive_days": 3,
    "total_days": 44,
    "month_total": 3
  }
}
```

### 4.5 내 출석 현황

```json
{
  "success": true,
  "data": {
    "consecutive_days": 3,
    "total_days": 44,
    "today_checked_in": true,
    "today_rank": 169,
    "today_point": 85
  }
}
```

---

## 5. 중요 설계 원칙

### 유저 페이지에서 관리자 API 사용 금지

```
❌ 금지: 유저 레이아웃에서 /admin/settings API 호출
✅ 올바름: /status API에서 프론트엔드에 필요한 설정 정보를 공개 범위로 제공
```

`/status` API는 `optional.sanctum` 미들웨어를 사용하여:
- 비로그인 사용자도 출석 가능 시간, 보너스 기준 등 공개 정보 조회 가능
- 로그인 사용자는 추가로 `can_check_in`, `already_checked_in` 정보 포함

### ResponseHelper 사용 필수

```
✅ return $this->success('messages.check_in_success', $data);
✅ return $this->error('messages.already_checked_in', 409);
❌ return response()->json([...]);
```

### 페이지네이션 표준 구조

```
✅ 'data' => [...items], 'pagination' => { total, from, to, per_page, current_page, last_page }
❌ LengthAwarePaginator를 직접 반환하지 않음
```

---

## 다음: [05-FRONTEND-USER.md](05-FRONTEND-USER.md) →
