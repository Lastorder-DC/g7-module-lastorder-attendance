<?php

namespace Modules\Lastorder\Attendance\Services;

use App\Models\Setting;

class AttendanceSettingsService
{
    /**
     * 모듈 식별자
     */
    private const MODULE = 'lastorder-attendance';

    /**
     * 전체 설정 조회
     *
     * DB 설정값을 우선으로 하고, 없는 항목은 config 파일의 기본값을 사용합니다.
     */
    public function getSettings(): array
    {
        $fileDefaults = config(self::MODULE, []);

        $dbSettings = Setting::where('module', self::MODULE)
            ->pluck('value', 'key')
            ->mapWithKeys(function ($value, $key) {
                try {
                    return [$key => json_decode($value, true, 512, JSON_THROW_ON_ERROR)];
                } catch (\JsonException) {
                    return [$key => $value];
                }
            })
            ->toArray();

        return array_merge($fileDefaults, $dbSettings);
    }

    /**
     * 단일 설정값 조회
     *
     * DB → config 파일 → $default 순으로 우선순위를 가집니다.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('module', self::MODULE)
            ->where('key', $key)
            ->first();

        if ($setting) {
            try {
                return json_decode($setting->value, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return $setting->value;
            }
        }

        return config(self::MODULE.'.'.$key, $default);
    }

    /**
     * 설정 업데이트
     *
     * 주어진 키-값 배열을 DB에 저장합니다.
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['module' => self::MODULE, 'key' => $key],
                ['value' => json_encode($value)],
            );
        }
    }
}
