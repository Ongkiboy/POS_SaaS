<?php

namespace App\Modules\Inventory\Ingredient;

use App\Support\Module\BaseModule;

class IngredientModule extends BaseModule
{
    public function routes(): ?string
    {
        return __DIR__.'/routes.php';
    }
}
