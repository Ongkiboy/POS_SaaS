<?php

use Symfony\Component\Finder\Finder;

it('DB::table() di app/ hanya dipakai lewat TenantQuery', function () {
    $offenders = [];
    $app = dirname(__DIR__, 2).'/app';

    foreach ((new Finder)->files()->in($app)->name('*.php') as $file) {
        if (str_ends_with(str_replace('\\', '/', $file->getPathname()), 'Platform/Tenant/TenantQuery.php')) {
            continue;
        }

        if (preg_match('/DB::table\s*\(/', $file->getContents()) === 1) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBeEmpty();
});
