<?php

namespace App\Modules\Catalog\Category\Actions;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCategories
{
    public function handle(?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage);
    }
}