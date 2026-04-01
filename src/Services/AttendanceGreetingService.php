<?php

namespace Modules\Lastorder\Attendance\Services;

class AttendanceGreetingService
{
    public function __construct(
        protected AttendanceSettingsService $settingsService,
    ) {}

    /**
     * 기본 인삿말 목록 조회
     */
    public function getDefaultGreetings(): array
    {
        $greetings = $this->settingsService->getSetting('default_greetings');

        if (is_array($greetings) && count($greetings) > 0) {
            return $greetings;
        }

        return config('lastorder-attendance.default_greetings', []);
    }

    /**
     * 기본 인삿말 랜덤 반환
     */
    public function getRandomGreeting(): string
    {
        $greetings = $this->getDefaultGreetings();

        if (empty($greetings)) {
            return '';
        }

        return $greetings[array_rand($greetings)];
    }
}
