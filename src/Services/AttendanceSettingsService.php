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
     * 요청 내 설정 캐시 (동일 요청에서 반복 DB 조회 방지)
     */
    private ?array $cachedSettings = null;

    /**
     * 전체 설정 조회
     *
     * DB 설정값을 우선으로 하고, 없는 항목은 config 파일의 기본값을 사용합니다.
     * 동일 요청 내에서는 캐시된 결과를 반환합니다.
     */
    public function getSettings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

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

        $this->cachedSettings = array_merge($fileDefaults, $dbSettings);

        return $this->cachedSettings;
    }

    /**
     * 단일 설정값 조회
     *
     * 캐시된 전체 설정에서 조회합니다.
     * DB → config 파일 → $default 순으로 우선순위를 가집니다.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }

    /**
     * 설정 업데이트
     *
     * 주어진 키-값 배열을 DB에 저장하고 캐시를 무효화합니다.
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['module' => self::MODULE, 'key' => $key],
                ['value' => json_encode($value)],
            );
        }

        $this->clearCache();
    }

    /**
     * 설정 캐시 무효화
     */
    public function clearCache(): void
    {
        $this->cachedSettings = null;
    }
}
