<?php

namespace Modules\Lastorder\Attendance\Services;

use App\Services\ModuleSettingsService;

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

    public function __construct(
        private readonly ModuleSettingsService $moduleSettingsService,
    ) {}

    /**
     * 전체 설정 조회
     *
     * 저장된 설정값을 우선으로 하고, 없는 항목은 config 파일의 기본값을 사용합니다.
     * 동일 요청 내에서는 캐시된 결과를 반환합니다.
     */
    public function getSettings(): array
    {
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        $fileDefaults = config(self::MODULE, []);
        $savedSettings = $this->moduleSettingsService->get(self::MODULE) ?? [];

        $this->cachedSettings = array_merge($fileDefaults, $savedSettings);

        return $this->cachedSettings;
    }

    /**
     * 단일 설정값 조회
     *
     * 캐시된 전체 설정에서 조회합니다.
     * 저장된 설정 → config 파일 → $default 순으로 우선순위를 가집니다.
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
     * 주어진 키-값 배열을 저장하고 캐시를 무효화합니다.
     */
    public function updateSettings(array $data): void
    {
        $current = $this->moduleSettingsService->get(self::MODULE) ?? [];
        $merged = array_merge($current, $data);
        $this->moduleSettingsService->save(self::MODULE, $merged);

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
