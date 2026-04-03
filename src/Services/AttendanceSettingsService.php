<?php

namespace Modules\Lastorder\Attendance\Services;

use App\Contracts\Extension\ModuleSettingsInterface;
use Illuminate\Support\Arr;

class AttendanceSettingsService implements ModuleSettingsInterface
{
    private const MODULE_IDENTIFIER = 'lastorder-attendance';
    private ?array $defaults = null;
    private ?array $settings = null;

    /**
     * defaults.json 파일 경로 반환
     */
    public function getSettingsDefaultsPath(): ?string
    {
        $path = $this->getModulePath() . '/config/settings/defaults.json';

        return file_exists($path) ? $path : null;
    }

    /**
     * 단일 설정값 조회
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getAllSettings();

        return Arr::get($settings, $key, $default);
    }

    /**
     * 단일 설정값 저장
     */
    public function setSetting(string $key, mixed $value): bool
    {
        $settings = $this->getAllSettings();
        Arr::set($settings, $key, $value);
        $parts = explode('.', $key);
        $category = $parts[0];

        return $this->saveCategorySettings($category, $settings[$category] ?? []);
    }

    /**
     * 전체 설정 조회
     */
    public function getAllSettings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $defaults = $this->getDefaults();
        $categories = $defaults['_meta']['categories'] ?? [];
        $defaultValues = $defaults['defaults'] ?? [];

        $settings = [];
        foreach ($categories as $category) {
            $categoryDefaults = $defaultValues[$category] ?? [];
            $savedSettings = $this->loadCategorySettings($category);
            $settings[$category] = array_merge($categoryDefaults, $savedSettings);
        }

        $this->settings = $settings;

        return $settings;
    }

    /**
     * 카테고리별 설정 조회
     */
    public function getSettings(string $category): array
    {
        $allSettings = $this->getAllSettings();

        return $allSettings[$category] ?? [];
    }

    /**
     * 설정 저장
     */
    public function saveSettings(array $settings): bool
    {
        $success = true;
        foreach ($settings as $category => $categorySettings) {
            if (str_starts_with($category, '_')) {
                continue;
            }
            if (!$this->saveCategorySettings($category, $categorySettings)) {
                $success = false;
            }
        }
        $this->settings = null;

        return $success;
    }

    /**
     * 프론트엔드 노출용 설정 조회
     */
    public function getFrontendSettings(): array
    {
        $defaults = $this->getDefaults();
        $frontendSchema = $defaults['frontend_schema'] ?? [];
        $allSettings = $this->getAllSettings();

        $frontendSettings = [];
        foreach ($frontendSchema as $category => $schema) {
            if (!($schema['expose'] ?? false)) {
                continue;
            }
            $categorySettings = $allSettings[$category] ?? [];
            $fields = $schema['fields'] ?? [];

            if (empty($fields)) {
                $frontendSettings[$category] = $categorySettings;
                continue;
            }

            $exposedFields = [];
            foreach ($fields as $fieldName => $fieldSchema) {
                if ($fieldSchema['expose'] ?? false) {
                    $exposedFields[$fieldName] = $categorySettings[$fieldName] ?? null;
                }
            }
            if (!empty($exposedFields)) {
                $frontendSettings[$category] = $exposedFields;
            }
        }

        return $frontendSettings;
    }

    /**
     * 기본 출석 포인트 조회
     */
    public function getBasePoint(): int
    {
        return (int) $this->getSetting('basic.base_point', 10);
    }

    /**
     * 자동출석 사용 여부
     */
    public function isAutoAttendanceEnabled(): bool
    {
        return (bool) $this->getSetting('basic.auto_attendance_enabled', false);
    }

    /**
     * 출석 가능 시간 확인
     */
    public function isWithinAttendanceTime(): bool
    {
        if (!$this->getSetting('time.time_restriction_enabled', false)) {
            return true;
        }

        $now = (int) now()->format('H');
        $start = (int) $this->getSetting('time.start_hour', 0);
        $end = (int) $this->getSetting('time.end_hour', 24);

        return $now >= $start && $now < $end;
    }

    /**
     * 순위별 보너스 포인트 조회
     */
    public function getRankBonus(int $rank): int
    {
        return match ($rank) {
            1 => (int) $this->getSetting('bonus.rank_1st_point', 100),
            2 => (int) $this->getSetting('bonus.rank_2nd_point', 50),
            3 => (int) $this->getSetting('bonus.rank_3rd_point', 30),
            default => 0,
        };
    }

    /**
     * 연속출석 보너스 포인트 조회
     */
    public function getConsecutiveBonus(int $days): int
    {
        if ($days > 0 && $days % 365 === 0) {
            return (int) $this->getSetting('bonus.consecutive_yearly_point', 5000);
        }
        if ($days > 0 && $days % 30 === 0) {
            return (int) $this->getSetting('bonus.consecutive_monthly_point', 500);
        }
        if ($days > 0 && $days % 7 === 0) {
            return (int) $this->getSetting('bonus.consecutive_weekly_point', 100);
        }

        return 0;
    }

    /**
     * 랜덤 포인트 계산
     */
    public function calculateRandomPoint(): int
    {
        if (!$this->getSetting('random.random_point_enabled', true)) {
            return 0;
        }
        $min = (int) $this->getSetting('random.random_point_min', 1);
        $max = (int) $this->getSetting('random.random_point_max', 200);

        return random_int($min, $max);
    }

    /**
     * 기본 인삿말 목록 조회
     */
    public function getDefaultGreetings(): array
    {
        $greetings = $this->getSetting('greetings.default_greetings', []);

        return is_array($greetings) ? $greetings : [];
    }

    /**
     * defaults.json 로드
     */
    private function getDefaults(): array
    {
        if ($this->defaults !== null) {
            return $this->defaults;
        }

        $path = $this->getSettingsDefaultsPath();
        if ($path === null) {
            $this->defaults = [];

            return [];
        }

        $content = file_get_contents($path);
        $this->defaults = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $this->defaults;
    }

    /**
     * 카테고리별 저장된 설정 로드
     */
    private function loadCategorySettings(string $category): array
    {
        $path = $this->getStoragePath() . '/' . $category . '.json';

        if (!file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }

    /**
     * 카테고리별 설정 저장
     */
    private function saveCategorySettings(string $category, array $settings): bool
    {
        $storagePath = $this->getStoragePath();

        if (!is_dir($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $path = $storagePath . '/' . $category . '.json';
        $content = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return file_put_contents($path, $content) !== false;
    }

    /**
     * 모듈 경로
     */
    private function getModulePath(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * 설정 저장 경로
     */
    private function getStoragePath(): string
    {
        return storage_path('app/modules/' . self::MODULE_IDENTIFIER . '/settings');
    }
}
