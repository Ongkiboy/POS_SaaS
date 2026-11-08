<?php

namespace Database\Seeders;

use App\Modules\Inventory\Ingredient\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, string> $units */
        $units = config('units.units');

        $rows = [];
        foreach ($units as $code => $name) {
            $rows[] = ['code' => $code, 'name' => $name];
        }

        Unit::query()->upsert($rows, ['code'], ['name']);
    }
}
