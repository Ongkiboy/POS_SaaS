<?php

namespace App\Modules\Catalog\Category\Actions;

use App\Models\Category;

class DeleteCategory
{
    public function handle(Category $category): void
    {
        $category->delete();
    }
}