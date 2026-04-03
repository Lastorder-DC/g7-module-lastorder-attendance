<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * 리소스를 배열로 변환
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name ?? '',
            'user_nick' => $this->user?->nick ?? '',
            'attended_at' => $this->attended_at?->format('Y-m-d'),
            'attended_time' => $this->attended_time,
            'greeting' => $this->greeting,
            'base_point' => $this->base_point,
            'bonus_point' => $this->bonus_point,
            'random_point' => $this->random_point,
            'rank_point' => $this->rank_point,
            'consecutive_point' => $this->consecutive_point,
            'total_point' => $this->total_point,
            'daily_rank' => $this->daily_rank,
            'consecutive_days' => $this->consecutive_days,
            'total_days' => $this->total_days,
            'is_auto' => $this->is_auto,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
