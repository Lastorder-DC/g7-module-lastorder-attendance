<?php

namespace Modules\Lastorder\Attendance\Services;

class AttendanceGreetingService
{
    public function __construct(
        private AttendanceSettingsService $settingsService
    ) {}

    /**
     * 랜덤 인삿말 반환
     */
    public function getRandomGreeting(): string
    {
        $greetings = $this->settingsService->getDefaultGreetings();

        if (empty($greetings)) {
            return __('lastorder-attendance::messages.default_greeting');
        }

        return $greetings[array_rand($greetings)];
    }
}
