<?php

namespace App\Modules\Catalog\Category\Actions;

use App\Models\Category;

class UpdateCategory
{
    public function handle(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }
}