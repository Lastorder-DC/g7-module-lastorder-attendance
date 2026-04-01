<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSettingsResource extends JsonResource
{
    /**
     * 출석부 설정 리소스
     */
    public function toArray(Request $request): array
    {
        $settings = $this->resource;

        return [
            'base_point' => $settings['base_point'] ?? 10,
            'allowed_start_time' => $settings['allowed_start_time'] ?? '00:00',
            'allowed_end_time' => $settings['allowed_end_time'] ?? '23:59',
            'auto_attendance_enabled' => (bool) ($settings['auto_attendance_enabled'] ?? false),
            'auto_attendance_greeting' => $settings['auto_attendance_greeting'] ?? '',
            'rank_1_bonus' => $settings['rank_1_bonus'] ?? 50,
            'rank_2_bonus' => $settings['rank_2_bonus'] ?? 30,
            'rank_3_bonus' => $settings['rank_3_bonus'] ?? 20,
            'weekly_bonus' => $settings['weekly_bonus'] ?? 100,
            'monthly_bonus' => $settings['monthly_bonus'] ?? 500,
            'yearly_bonus' => $settings['yearly_bonus'] ?? 5000,
            'random_point_enabled' => (bool) ($settings['random_point_enabled'] ?? false),
            'random_point_min' => $settings['random_point_min'] ?? 1,
            'random_point_max' => $settings['random_point_max'] ?? 100,
            'random_point_chance' => $settings['random_point_chance'] ?? 30,
            'default_greetings' => $settings['default_greetings'] ?? [],
            'per_page' => $settings['per_page'] ?? 20,
        ];
    }
}
