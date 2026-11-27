<?php

use App\Support\Contracts\Module;
use Symfony\Component\Finder\Finder;

/**
 * Semua class XxxModule di app/Modules.
 *
 * @return array<int, string>
 */
function archDiscoverModuleClasses(): array
{
    $path = dirname(__DIR__, 2).'/app/Modules';

    if (! is_dir($path)) {
        return [];
    }

    $classes = [];

    foreach ((new Finder)->files()->in($path)->name('*Module.php') as $file) {
        $relative = str_replace('\\', '/', $file->getRelativePathname());
        $classes[] = 'App\\Modules\\'.str_replace('/', '\\', substr($relative, 0, -4));
    }

    sort($classes);

    return $classes;
}

/** Pola nama migrasi: TD §2.4. */
function archMigrationPattern(): string
{
    return '/^\d{4}_\d{2}_\d{2}_\d{6}_[a-z]+_(create|alter|add|drop)_[a-z_]+\.php$/';
}

it('semua class XxxModule terdaftar di config/modules.php (kecuali Payments\\Gateway)', function () {
    $enabled = (require dirname(__DIR__, 2).'/config/modules.php')['enabled'];

    $expected = array_values(array_filter(
        archDiscoverModuleClasses(),
        fn (string $class) => ! str_starts_with($class, 'App\\Modules\\Payments\\Gateway\\'),
    ));

    expect(array_values(array_diff($expected, $enabled)))->toBeEmpty();
});

it('config/modules.php tidak berisi class yang tidak ada atau bukan Module', function () {
    $enabled = (require dirname(__DIR__, 2).'/config/modules.php')['enabled'];

    foreach ($enabled as $class) {
        expect(class_exists($class))->toBeTrue("{$class} tidak ditemukan")
            ->and(is_subclass_of($class, Module::class))->toBeTrue("{$class} harus mengimplementasikan Module");
    }
});

it('config/modules.php tidak memuat entri ganda', function () {
    $enabled = (require dirname(__DIR__, 2).'/config/modules.php')['enabled'];

    expect($enabled)->toBe(array_values(array_unique($enabled)));
});

it('nama migrasi mengikuti tanggal_urutGlobal_modul_aksi_tabel', function () {
    $files = glob(dirname(__DIR__, 2).'/database/migrations/*.php') ?: [];
    $names = array_map('basename', $files);

    $invalid = array_values(array_filter(
        $names,
        fn (string $name) => preg_match(archMigrationPattern(), $name) !== 1,
    ));

    expect($invalid)->toBeEmpty();
});

it('pola nama migrasi menerima nama sah dan menolak nama salah', function (string $name, bool $valid) {
    expect(preg_match(archMigrationPattern(), $name) === 1)->toBe($valid);
})->with([
    'sah: tenant' => ['2026_10_27_000010_tenant_create_businesses_table.php', true],
    'sah: rbac' => ['2026_10_27_000020_rbac_create_permission_tables.php', true],
    'sah: alter' => ['2026_12_01_000999_inventory_alter_stock_balances_table.php', true],
    'salah: bawaan Laravel' => ['0001_01_01_000000_create_users_table.php', false],
    'salah: tanpa modul' => ['2026_10_27_000010_create_businesses_table.php', false],
    'salah: aksi tidak dikenal' => ['2026_10_27_000010_tenant_make_businesses_table.php', false],
    'salah: urut bukan 6 digit' => ['2026_10_27_10_tenant_create_businesses_table.php', false],
    'salah: huruf besar' => ['2026_10_27_000010_Tenant_create_businesses_table.php', false],
]);
