<?php

namespace Modules\Lastorder\Attendance\Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Lastorder\Attendance\Models\Attendance;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    /**
     * 기본 팩토리 정의
     */
    public function definition(): array
    {
        $basePoint = config('lastorder-attendance.base_point', 10);
        $randomPoint = 0;

        return [
            'user_id' => User::factory(),
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'attendance_time' => Carbon::now()->format('H:i:s'),
            'greeting' => $this->faker->randomElement(
                config('lastorder-attendance.default_greetings', ['안녕하세요!'])
            ),
            'base_point' => $basePoint,
            'random_point' => $randomPoint,
            'total_point' => $basePoint + $randomPoint,
            'daily_rank' => $this->faker->numberBetween(1, 50),
            'consecutive_days' => 1,
            'total_days' => 1,
            'ip_address' => $this->faker->ipv4(),
            'is_auto' => false,
        ];
    }

    /**
     * 특정 사용자 지정
     */
    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    /**
     * 특정 날짜 지정
     */
    public function onDate(string $date): static
    {
        return $this->state(fn () => ['attendance_date' => $date]);
    }

    /**
     * 자동출석 상태
     */
    public function auto(): static
    {
        return $this->state(fn () => ['is_auto' => true]);
    }

    /**
     * 특정 순위 지정
     */
    public function rank(int $rank): static
    {
        return $this->state(fn () => ['daily_rank' => $rank]);
    }

    /**
     * 연속 출석일수 지정
     */
    public function consecutiveDays(int $days): static
    {
        return $this->state(fn () => ['consecutive_days' => $days]);
    }

    /**
     * 총 출석일수 지정
     */
    public function totalDays(int $days): static
    {
        return $this->state(fn () => ['total_days' => $days]);
    }

    /**
     * 랜덤 포인트 포함
     */
    public function withRandomPoint(int $point): static
    {
        return $this->state(fn (array $attributes) => [
            'random_point' => $point,
            'total_point' => $attributes['base_point'] + $point,
        ]);
    }
}
