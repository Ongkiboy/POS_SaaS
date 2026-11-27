<?php

/*
 * Aturan dependensi modul (TD §2.3). Panah = "boleh bergantung pada".
 * Aturan didaftarkan hanya bila folder grupnya ada (menghindari namespace kosong).
 */

$groups = ['Platform', 'Catalog', 'Inventory', 'Shift', 'Engine', 'Sales', 'Purchasing', 'Reporting'];

$allowed = [
    'Platform' => [],
    'Catalog' => ['Platform'],
    'Inventory' => ['Platform', 'Catalog'],
    'Shift' => ['Platform'],
    'Engine' => ['Platform', 'Inventory'],
    'Sales' => ['Platform', 'Catalog', 'Inventory', 'Shift'], // Engine hanya lewat App\Support\Contracts
    'Purchasing' => ['Platform', 'Inventory'],
    'Reporting' => ['Platform', 'Catalog', 'Inventory', 'Shift', 'Engine', 'Sales', 'Purchasing'],
];

$modulesPath = dirname(__DIR__, 2).'/app/Modules';

foreach ($groups as $group) {
    if (! is_dir("{$modulesPath}/{$group}")) {
        continue;
    }

    $forbidden = array_values(array_diff($groups, [$group], $allowed[$group]));

    if ($forbidden === []) {
        continue;
    }

    arch("{$group} tidak boleh bergantung pada ".implode(', ', $forbidden))
        ->expect("App\\Modules\\{$group}")
        ->not->toUse(array_map(fn (string $other) => "App\\Modules\\{$other}", $forbidden));
}

// [KEPUTUSAN OI-19] Recipe berada di grup Inventory; ia tidak boleh memanggil Stock.
if (is_dir("{$modulesPath}/Inventory/Recipe")) {
    arch('Inventory\\Recipe tidak boleh memakai Inventory\\Stock')
        ->expect('App\\Modules\\Inventory\\Recipe')
        ->not->toUse('App\\Modules\\Inventory\\Stock');
}

// Lapisan lintas modul tidak boleh bergantung ke modul (TD §2.1: Support tanpa logika bisnis).
arch('Support tidak bergantung pada Modules')
    ->expect('App\\Support')
    ->not->toUse('App\\Modules');

// Kontrak lintas modul hanya berisi interface.
arch('Support\\Contracts hanya berisi interface')
    ->expect('App\\Support\\Contracts')
    ->toBeInterfaces();
