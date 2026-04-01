<?php

namespace Modules\Lastorder\Attendance\Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class DefaultGreetingsSeeder extends Seeder
{
    /**
     * 기본 인삿말 목록을 DB에 시딩
     */
    public function run(): void
    {
        $greetings = [
            '좋은 아침이에요~',
            '오늘도 화이팅!',
            '반갑습니다~',
            '좋은 하루 보내세요!',
            '안녕하세요!',
            '오늘도 좋은 하루~',
            '즐거운 하루 되세요!',
            '행복한 하루~',
            '꾸준함이 힘이에요!',
            '오늘도 출석 완료!',
        ];

        Setting::updateOrCreate(
            ['module' => 'lastorder-attendance', 'key' => 'default_greetings'],
            ['value' => json_encode($greetings)]
        );
    }
}
