<?php

namespace App\Modules\Inventory\Ingredient\Actions;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use Illuminate\Support\Facades\DB;

final class DeleteIngredient
{
    public function handle(Ingredient $ingredient): void
    {
        DB::transaction(function () use ($ingredient): void {
            $ingredient->delete();
        });
    }
}
