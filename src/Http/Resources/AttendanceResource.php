<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class AttendanceResource extends BaseApiResource
{
    /**
     * 출석 기록 상세 리소스
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->when($this->relationLoaded('user'), fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'attendance_date' => $this->formatDateForUser($this->attendance_date),
            'attendance_time' => $this->attendance_time,
            'greeting' => $this->greeting,
            'base_point' => $this->base_point,
            'random_point' => $this->random_point,
            'total_point' => $this->total_point,
            'daily_rank' => $this->daily_rank,
            'consecutive_days' => $this->consecutive_days,
            'total_days' => $this->total_days,
            'is_auto' => $this->is_auto,
            'bonuses' => $this->when($this->relationLoaded('bonuses'), fn () => $this->bonuses->map(fn ($bonus) => [
                'type' => $bonus->bonus_type->value,
                'point' => $bonus->bonus_point,
                'description' => $bonus->description,
            ])),
            ...$this->formatTimestamps(),
        ];
    }
}
