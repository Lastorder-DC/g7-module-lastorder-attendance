<?php

namespace Modules\Lastorder\Attendance\Database\Seeders;

use App\Services\ModuleSettingsService;
use Illuminate\Database\Seeder;

class AttendanceSettingsSeeder extends Seeder
{
    /**
     * 출석부 기본 설정값을 시딩
     */
    public function run(): void
    {
        $settingsService = app(ModuleSettingsService::class);

        $defaults = [
            'base_point' => 10,
            'allowed_start_time' => '00:00',
            'allowed_end_time' => '23:59',
            'auto_attendance_enabled' => false,
            'auto_attendance_greeting' => '',
            'rank_1_bonus' => 50,
            'rank_2_bonus' => 30,
            'rank_3_bonus' => 20,
            'weekly_bonus' => 100,
            'monthly_bonus' => 500,
            'yearly_bonus' => 5000,
            'random_point_enabled' => false,
            'random_point_min' => 1,
            'random_point_max' => 100,
            'random_point_chance' => 30,
            'per_page' => 20,
            'cache_enabled' => true,
            'cache_ttl' => 60,
        ];

        // 기존 설정값이 있으면 유지하고, 없는 항목만 기본값 적용
        $current = $settingsService->get('lastorder-attendance') ?? [];
        $merged = array_merge($defaults, $current);
        $settingsService->save('lastorder-attendance', $merged);
    }
}
