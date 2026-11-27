<?php

namespace App\Modules\Inventory\Stock\Http\Requests;

use App\Modules\Platform\Tenant\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAdjustmentRequest extends FormRequest
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
            'ingredient_id' => [
                'required',
                'integer',
                Rule::exists('ingredients', 'id')
                    ->where('business_id', $businessId)
                    ->whereNull('deleted_at'),
            ],
            'direction' => [
                'required',
                'string',
                Rule::in(['INCREASE', 'DECREASE', 'TAMBAH', 'KURANGI']),
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'regex:/^\d+(\.\d{1,4})?$/',
            ],
            'reason' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ingredient_id.required' => 'Bahan wajib dipilih.',
            'ingredient_id.exists' => 'Bahan tidak ditemukan.',
            'direction.required' => 'Jenis penyesuaian wajib dipilih.',
            'direction.in' => 'Jenis penyesuaian harus INCREASE atau DECREASE.',
            'quantity.required' => 'Jumlah harus lebih dari 0.',
            'quantity.numeric' => 'Jumlah harus lebih dari 0.',
            'quantity.gt' => 'Jumlah harus lebih dari 0.',
            'quantity.regex' => 'Jumlah maksimal 4 desimal.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ];
    }
}
