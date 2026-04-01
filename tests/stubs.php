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

namespace App\Models {
    if (! class_exists(Setting::class)) {
        class Setting extends \Illuminate\Database\Eloquent\Model
        {
            protected $fillable = ['module', 'key', 'value'];
        }
    }
}
