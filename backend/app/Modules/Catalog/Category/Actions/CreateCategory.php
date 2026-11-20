<?php

namespace App\Modules\Catalog\Category\Actions;

use App\Models\Category;

class CreateCategory
{
    public function handle(array $data): Category
    {
        $data['sort_order'] ??= (Category::max('sort_order') ?? 0) + 1;

        return Category::create($data);
    }
}