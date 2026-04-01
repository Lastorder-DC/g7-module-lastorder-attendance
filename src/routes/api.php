<?php

use Illuminate\Support\Facades\Route;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Admin\AttendanceAdminController;
use Modules\Lastorder\Attendance\Http\Controllers\Api\Admin\AttendanceSettingsController;
use Modules\Lastorder\Attendance\Http\Controllers\Api\User\AttendanceController;

/*
|--------------------------------------------------------------------------
| 출석부 모듈 API 라우트
|--------------------------------------------------------------------------
|
| 자동 적용 접두사: /api/modules/lastorder-attendance
| 자동 적용 네임: api.modules.lastorder-attendance.
|
*/

// ─── 사용자 API (인증 필수) ────────────────────────────────

Route::middleware(['auth:sanctum'])->group(function () {
    // 출석 체크
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])
        ->name('check-in');

    // 내 출석 현황
    Route::get('/my', [AttendanceController::class, 'my'])
        ->name('my');

    // 월별 출석 캘린더
    Route::get('/calendar/{year}/{month}', [AttendanceController::class, 'calendar'])
        ->name('calendar')
        ->where(['year' => '[0-9]{4}', 'month' => '[0-9]{1,2}']);

    // 출석 가능 상태 조회
    Route::get('/status', [AttendanceController::class, 'status'])
        ->name('status');
});

// ─── 사용자 API (인증 선택) ────────────────────────────────

Route::middleware(['optional.sanctum'])->group(function () {
    // 오늘 출석 목록
    Route::get('/today', [AttendanceController::class, 'today'])
        ->name('today');

    // 랜덤 인삿말 조회
    Route::get('/greeting', [AttendanceController::class, 'greeting'])
        ->name('greeting');
});

// ─── 관리자 API ────────────────────────────────────────────

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // 출석 현황 관리
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceAdminController::class, 'index'])
            ->name('admin.attendance.index')
            ->middleware('permission:admin,lastorder-attendance.attendance.read');

        Route::delete('/{id}', [AttendanceAdminController::class, 'destroy'])
            ->name('admin.attendance.destroy')
            ->where('id', '[0-9]+')
            ->middleware('permission:admin,lastorder-attendance.attendance.delete');

        // 관리자 수동 연속출석/총출석 재계산
        Route::post('/recalculate-consecutive/{userId}', [AttendanceAdminController::class, 'recalculateConsecutiveDays'])
            ->name('admin.attendance.recalculate-consecutive')
            ->where('userId', '[0-9]+')
            ->middleware('permission:admin,lastorder-attendance.attendance.read');

        Route::post('/recalculate-total/{userId}', [AttendanceAdminController::class, 'recalculateTotalDays'])
            ->name('admin.attendance.recalculate-total')
            ->where('userId', '[0-9]+')
            ->middleware('permission:admin,lastorder-attendance.attendance.read');
    });

    // 설정 관리
    Route::prefix('settings')->group(function () {
        Route::get('/', [AttendanceSettingsController::class, 'index'])
            ->name('admin.settings.index')
            ->middleware('permission:admin,lastorder-attendance.settings.read');

        Route::put('/', [AttendanceSettingsController::class, 'update'])
            ->name('admin.settings.update')
            ->middleware('permission:admin,lastorder-attendance.settings.update');
    });
});
