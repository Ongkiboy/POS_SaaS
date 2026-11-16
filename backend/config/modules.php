<?php

use App\Modules\Platform\Tenant\TenantModule;

return [
    /*
     * Modul aktif. Registrasi eksplisit (bukan scan folder), aman untuk
     * config:cache dan route:cache. Payments\Gateway (Phase 2) sengaja tidak didaftarkan.
     */
    'enabled' => [
        TenantModule::class,
    ],
];
