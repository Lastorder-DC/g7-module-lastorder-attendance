<?php

namespace Modules\Lastorder\Attendance\Enums;

enum ConsecutiveType: string
{
    /**
     * 주간 (7일 단위)
     */
    case Weekly = 'weekly';

    /**
     * 월간 (30일 단위)
     */
    case Monthly = 'monthly';

    /**
     * 연간 (365일 단위)
     */
    case Yearly = 'yearly';

    /**
     * 표시 라벨
     */
    public function label(): string
    {
        return match ($this) {
            self::Weekly => __('lastorder-attendance::messages.consecutive.weekly'),
            self::Monthly => __('lastorder-attendance::messages.consecutive.monthly'),
            self::Yearly => __('lastorder-attendance::messages.consecutive.yearly'),
        };
    }

    /**
     * 해당 타입의 일수 기준
     */
    public function days(): int
    {
        return match ($this) {
            self::Weekly => 7,
            self::Monthly => 30,
            self::Yearly => 365,
        };
    }

    /**
     * 모든 상태 값을 문자열 배열로 반환
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * 유효한 값인지 확인
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }
}
