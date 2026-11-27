<?php

namespace App\Modules\Inventory\Stock\Http\Resources;

use App\Modules\Inventory\Stock\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockAdjustment
 */
class StockAdjustmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outlet_id' => $this->outlet_id,
            'ingredient_id' => $this->ingredient_id,
            'quantity_before' => (string) $this->quantity_before,
            'quantity_delta' => (string) $this->quantity_delta,
            'quantity_after' => (string) $this->quantity_after,
            'reason' => $this->reason,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toISOString(),
            'ingredient' => $this->whenLoaded('ingredient', fn () => [
                'id' => $this->ingredient->id,
                'name' => $this->ingredient->name,
                'sku' => $this->ingredient->sku,
            ]),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
        ];
    }
}
