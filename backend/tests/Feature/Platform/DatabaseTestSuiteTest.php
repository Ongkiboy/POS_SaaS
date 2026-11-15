<?php

use Illuminate\Support\Facades\DB;

it('suite test memakai PostgreSQL database *_test, bukan SQLite', function () {
    expect(DB::connection()->getDriverName())->toBe('pgsql')
        ->and(DB::connection()->getDatabaseName())->toEndWith('_test');
});

it('SELECT ... FOR UPDATE bekerja dalam transaksi di database test', function () {
    $rows = DB::transaction(function () {
        DB::statement('CREATE TEMP TABLE _uji_lock (id int)');
        DB::statement('INSERT INTO _uji_lock (id) VALUES (1)');

        return DB::select('select * from _uji_lock for update');
    });

    expect($rows)->toHaveCount(1);
});