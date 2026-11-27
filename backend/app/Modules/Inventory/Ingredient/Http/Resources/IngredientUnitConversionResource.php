<?php

namespace App\Modules\Inventory\Ingredient\Http\Resources;

use App\Modules\Inventory\Ingredient\Models\IngredientUnitConversion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin IngredientUnitConversion
 */
final class IngredientUnitConversionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'unit_id' => $this->unit_id,
            'unit_code' => $this->unit?->code,
            'unit_name' => $this->unit?->name,
            'factor_to_base' => (string) $this->factor_to_base,
        ];
    }
}
