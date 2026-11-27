<?php

namespace App\Modules\Inventory\Stock;

use App\Support\Module\BaseModule;

class StockModule extends BaseModule
{
    public function routes(): ?string
    {
        return __DIR__.'/routes.php';
    }
}
