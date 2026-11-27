<?php

namespace App\Modules\Inventory\Ingredient\Http\Resources;

use App\Modules\Inventory\Ingredient\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ingredient
 */
final class IngredientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'base_unit_id' => $this->base_unit_id,
            'base_unit' => new UnitResource($this->whenLoaded('baseUnit')),
            'last_cost' => (string) $this->last_cost,
            'min_stock' => (string) $this->min_stock,
            'status' => $this->status,
            'conversions' => IngredientUnitConversionResource::collection($this->whenLoaded('conversions')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
