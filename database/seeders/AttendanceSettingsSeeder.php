<?php

namespace Modules\Lastorder\Attendance\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AttendanceSettingsSeeder extends Seeder
{
    /**
     * 출석부 기본 설정값을 DB에 시딩
     */
    public function run(): void
    {
        $settings = [
            'base_point' => 10,
            'allowed_start_time' => '01:00',
            'allowed_end_time' => '23:00',
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

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['module' => 'lastorder-attendance', 'key' => $key],
                ['value' => json_encode($value)]
            );
        }
    }
}
