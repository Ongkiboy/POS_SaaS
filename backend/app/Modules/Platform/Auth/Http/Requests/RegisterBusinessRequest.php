<?php

namespace App\Modules\Platform\Auth\Http\Requests;

use App\Modules\Platform\User\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterBusinessRequest extends FormRequest
{
    // Pesan SRS FR-AUTH-001 dan SDD API-AUTH-01.
    public const EMAIL_TAKEN = 'Email sudah digunakan, silakan gunakan email lain.';

    public const PASSWORD_POLICY = 'Password minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.';

    public const PASSWORD_CONFIRMATION = 'Konfirmasi password tidak cocok.';

    public const OUTLET_CODE = 'Kode outlet 2–6 karakter huruf atau angka.';

    public const TIMEZONE = 'Timezone tidak valid.';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:100'],
            'outlet_name' => ['required', 'string', 'max:100'],
            'outlet_code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{2,6}$/'],
            'timezone' => ['required', 'string', 'timezone'],
            'owner_name' => ['required', 'string', 'max:100'],
            'email' => ['bail', 'required', 'string', 'email', 'max:255', $this->emailNotTaken()],
            'password' => [
                'bail', 'required', 'string', 'min:8',
                'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', // OI-14: [a-z] dari SDD "mixed case"
                'confirmed',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.min' => self::PASSWORD_POLICY,
            'password.regex' => self::PASSWORD_POLICY,
            'password.confirmed' => self::PASSWORD_CONFIRMATION,
            'outlet_code.regex' => self::OUTLET_CODE,
            'timezone.timezone' => self::TIMEZONE,
            'email.email' => 'Format email tidak valid.',
            'required' => ':attribute wajib diisi.',
            'max' => ':attribute maksimal :max karakter.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'business_name' => 'Nama business',
            'outlet_name' => 'Nama outlet',
            'outlet_code' => 'Kode outlet',
            'timezone' => 'Timezone',
            'owner_name' => 'Nama owner',
            'email' => 'Email',
            'password' => 'Password',
        ];
    }

    /** Unik global, case-insensitive, termasuk user yang di-soft delete (index lower(email)). */
    private function emailNotTaken(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $taken = User::query()
                ->withoutGlobalScopes()
                ->whereRaw('lower(email) = ?', [mb_strtolower((string) $value)])
                ->exists();

            if ($taken) {
                $fail(self::EMAIL_TAKEN);
            }
        };
    }
}
