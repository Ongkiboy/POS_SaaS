<?php

/*
 * Scope "Own/Limited" per role (SRS v3.0 §2.1). Tidak disimpan di Spatie;
 * dibaca Policy dan /me (SDD API-AUTH-04). Permission yang tidak tercantum = tanpa scope (null).
 */
return [
    'CASHIER' => [
        'sales.view' => 'OWN_ONLY',
        'sales.cancel' => 'LIMITED',
        'reports.view' => 'LIMITED',
        'dashboard.view' => 'LIMITED',
    ],
    'MANAGER' => [
        'users.view' => 'LIMITED',
        'users.create' => 'LIMITED',
        'users.update' => 'LIMITED',
        'users.delete' => 'LIMITED',
        'settings.view' => 'LIMITED',
        'settings.update' => 'LIMITED',
        'audit.view' => 'LIMITED',
    ],
    'INVENTORY' => [
        'dashboard.view' => 'LIMITED',
    ],
    'KITCHEN' => [
        'inventory.waste' => 'LIMITED',
    ],
];
