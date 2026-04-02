<?php

/**
 * Stubs for host-application classes used by this module.
 *
 * These classes belong to the Gnuboard7 core and are not shipped with this
 * module. Minimal stubs are provided so the module's own classes can be loaded
 * and tested without the full host application.
 */

namespace App\Contracts\Extension {
    if (! interface_exists(HookListenerInterface::class)) {
        interface HookListenerInterface
        {
            public static function getSubscribedHooks(): array;

            public function handle(...$args): void;
        }
    }
}

namespace App\Services {
    if (! class_exists(ModuleSettingsService::class)) {
        class ModuleSettingsService
        {
            private array $store = [];

            public function get(string $identifier, ?string $key = null, mixed $default = null): mixed
            {
                $settings = $this->store[$identifier] ?? null;

                if ($settings === null) {
                    return $key !== null ? $default : null;
                }

                if ($key !== null) {
                    return $settings[$key] ?? $default;
                }

                return $settings;
            }

            public function save(string $identifier, array $settings): void
            {
                $this->store[$identifier] = $settings;
            }

            public function reset(string $identifier): void
            {
                unset($this->store[$identifier]);
            }
        }
    }
}

namespace App\Models {
    if (! class_exists(User::class)) {
        class User extends \Illuminate\Database\Eloquent\Model
        {
            protected $fillable = ['id', 'name', 'email'];
        }
    }
}

namespace App\Http\Controllers\Api\Base {
    if (! class_exists(BaseApiController::class)) {
        class BaseApiController
        {
            protected function success(string $message, array $data = [], int $statusCode = 200): \Illuminate\Http\JsonResponse
            {
                return new \Illuminate\Http\JsonResponse(['message' => $message, 'data' => $data], $statusCode);
            }

            protected function error(string $message, int $statusCode = 400): \Illuminate\Http\JsonResponse
            {
                return new \Illuminate\Http\JsonResponse(['message' => $message], $statusCode);
            }

            protected function successWithResource(string $message, mixed $resource, int $statusCode = 200): \Illuminate\Http\JsonResponse
            {
                return new \Illuminate\Http\JsonResponse(['message' => $message, 'data' => $resource], $statusCode);
            }

            protected function notFound(): \Illuminate\Http\JsonResponse
            {
                return new \Illuminate\Http\JsonResponse(['message' => 'Not Found'], 404);
            }
        }
    }

    if (! class_exists(AdminBaseController::class)) {
        class AdminBaseController extends BaseApiController
        {
        }
    }
}

namespace App\Http\Resources {
    if (! class_exists(BaseApiResource::class)) {
        class BaseApiResource extends \Illuminate\Http\Resources\Json\JsonResource
        {
            protected function formatDateForUser(mixed $date): ?string
            {
                if ($date === null) {
                    return null;
                }

                return $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;
            }

            protected function formatTimestamps(): array
            {
                return [
                    'created_at' => $this->created_at?->toISOString(),
                    'updated_at' => $this->updated_at?->toISOString(),
                ];
            }
        }
    }
}

namespace App\Extension {
    if (! class_exists(AbstractModule::class)) {
        abstract class AbstractModule
        {
            public function getModulePath(): string
            {
                return dirname(__DIR__);
            }
        }
    }

    if (! class_exists(BaseModuleServiceProvider::class)) {
        class BaseModuleServiceProvider extends \Illuminate\Support\ServiceProvider
        {
            protected string $moduleIdentifier = '';
            protected array $repositories = [];

            public function register(): void
            {
                foreach ($this->repositories as $interface => $implementation) {
                    $this->app->bind($interface, $implementation);
                }
            }
        }
    }
}

namespace App\ActivityLog\Traits {
    if (! trait_exists(ResolvesActivityLogType::class)) {
        trait ResolvesActivityLogType
        {
            protected function logActivity(string $type, array $data = []): void
            {
                // Stub: no-op in tests
            }
        }
    }
}

namespace Illuminate\Foundation\Http {
    if (! class_exists(FormRequest::class)) {
        class FormRequest extends \Illuminate\Http\Request
        {
            public function authorize(): bool
            {
                return true;
            }

            public function rules(): array
            {
                return [];
            }

            public function messages(): array
            {
                return [];
            }

            public function validated(mixed $key = null, mixed $default = null): mixed
            {
                $rules = $this->rules();
                if ($key !== null) {
                    return $this->input($key, $default);
                }

                return array_intersect_key(
                    $this->all(),
                    $rules,
                );
            }
        }
    }
}

// Global helper stubs
namespace {
    if (! function_exists('request')) {
        function request(?string $key = null, mixed $default = null)
        {
            static $request;
            if ($request === null) {
                $request = new \Illuminate\Http\Request();
            }
            if ($key !== null) {
                return $request->input($key, $default);
            }

            return $request;
        }
    }

    if (! function_exists('__')) {
        function __(string $key, array $replace = [], ?string $locale = null): string
        {
            $result = $key;
            foreach ($replace as $search => $value) {
                $result = str_replace(':'.$search, (string) $value, $result);
            }

            return $result;
        }
    }

    if (! function_exists('config')) {
        function config($key = null, $default = null)
        {
            if ($key === null) {
                return [];
            }
            // For testing, return empty array or default
            return $default;
        }
    }

    if (! function_exists('now')) {
        function now()
        {
            return \Carbon\Carbon::now();
        }
    }

    if (! function_exists('auth')) {
        function auth()
        {
            return new class {
                public function id(): ?int
                {
                    return 1;
                }
            };
        }
    }
}
