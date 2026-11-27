<?php

namespace App\Modules\Inventory\Stock\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array{
 *     batch_id: int,
 *     items: list<array{ingredient_id: int, quantity_after: string}>
 * } $resource
 */
class StockOpeningBatchResource extends JsonResource
{
    /**
     * @return array{
     *     batch_id: int,
     *     items: list<array{ingredient_id: int, quantity_after: string}>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'batch_id' => $this->resource['batch_id'],
            'items' => $this->resource['items'],
        ];
    }
}
