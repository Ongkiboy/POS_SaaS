<?php

namespace App\Modules\Catalog\Category;

use Illuminate\Support\ServiceProvider;

class CategoryModule extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }
}