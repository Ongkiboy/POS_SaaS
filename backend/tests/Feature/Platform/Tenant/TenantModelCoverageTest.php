<?php

use App\Modules\Platform\Tenant\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\Finder;

it('semua model dengan kolom business_id memakai BelongsToBusiness', function () {
    $missing = [];

    foreach ((new Finder)->files()->in(app_path('Modules'))->path('Models')->name('*.php') as $file) {
        $relative = str_replace(['\\', '.php'], ['/', ''], $file->getRelativePathname());
        $class = 'App\\Modules\\'.str_replace('/', '\\', $relative);

        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            continue;
        }

        $table = (new $class)->getTable();

        if (Schema::hasColumn($table, 'business_id') && ! in_array(BelongsToBusiness::class, class_uses_recursive($class), true)) {
            $missing[] = $class;
        }
    }

    expect($missing)->toBeEmpty();
});
