<?php

namespace Modules\Lastorder\Attendance\Http\Resources;

use App\Http\Resources\BaseApiResource;
use Illuminate\Http\Request;

class AttendanceCalendarResource extends BaseApiResource
{
    /**
     * 월별 출석 캘린더 리소스
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $year = $this->resource['year'];
        $month = $this->resource['month'];
        $calendar = $this->resource['calendar'];

        $days = [];
        foreach ($calendar as $date => $info) {
            $day = [
                'date' => $date,
                'status' => $this->resolveStatus($info),
            ];

            if ($info['attended'] && $info['attendance'] !== null) {
                $day['rank'] = $info['attendance']->daily_rank;
                $day['point'] = $info['attendance']->total_point;
            }

            $days[] = $day;
        }

        return [
            'year' => $year,
            'month' => $month,
            'days' => $days,
        ];
    }

    /**
     * 출석 상태 결정
     */
    private function resolveStatus(array $info): string
    {
        if ($info['is_future']) {
            return 'future';
        }

        return $info['attended'] ? 'attended' : 'absent';
    }
}
