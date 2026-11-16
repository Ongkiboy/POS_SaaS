<?php

namespace App\Modules\Inventory\Ingredient\Http\Requests;

use App\Modules\Inventory\Ingredient\Models\Unit;
use App\Modules\Platform\Tenant\TenantContext;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreIngredientRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessId = app(TenantContext::class)->requireId();
        /** @var list<string> $allowedBaseUnits */
        $allowedBaseUnits = config('units.base_units', ['g', 'ml', 'pcs']);

        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('ingredients', 'sku')
                    ->where('business_id', $businessId)
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'base_unit_id' => [
                'required',
                'integer',
                'exists:units,id',
                function (string $attribute, mixed $value, Closure $fail) use ($allowedBaseUnits): void {
                    $unit = Unit::query()->find($value);
                    if (! $unit || ! in_array($unit->code, $allowedBaseUnits, true)) {
                        $fail('Satuan dasar harus berupa '.implode(', ', $allowedBaseUnits).'.');
                    }
                },
            ],
            'last_cost' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'min_stock' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', Rule::in(['ACTIVE', 'INACTIVE'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sku.unique' => 'SKU bahan sudah digunakan.',
            'last_cost.min' => 'Biaya per satuan tidak boleh negatif.',
        ];
    }
}
