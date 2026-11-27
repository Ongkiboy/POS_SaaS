<?php

namespace App\Modules\Inventory\Stock\Http\Requests;

use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOpeningStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $businessId = app(TenantContext::class)->requireId();

        return [
            'note' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('ingredients', 'id')
                    ->where('business_id', $businessId)
                    ->whereNull('deleted_at'),
            ],
            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^\d+(\.\d{1,4})?$/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Daftar bahan wajib diisi.',
            'items.array' => 'Daftar bahan harus berupa array.',
            'items.min' => 'Daftar bahan minimal berisi 1 bahan.',
            'items.*.ingredient_id.required' => 'Bahan wajib dipilih.',
            'items.*.ingredient_id.integer' => 'ID bahan harus berupa bilangan bulat.',
            'items.*.ingredient_id.distinct' => 'Bahan ganda dalam satu batch ditolak.',
            'items.*.ingredient_id.exists' => 'Bahan tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah harus lebih dari 0.',
            'items.*.quantity.numeric' => 'Jumlah harus lebih dari 0.',
            'items.*.quantity.gt' => 'Jumlah harus lebih dari 0.',
            'items.*.quantity.regex' => 'Jumlah maksimal 4 desimal.',
            'note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}
