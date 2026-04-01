<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class AttendanceListResource extends BaseApiResource
{
    /**
     * 출석 목록용 경량 리소스
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
        ];
    }
}
