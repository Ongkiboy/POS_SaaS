<?php

namespace App\Modules\Platform\Auth\Data;

use SensitiveParameter;

final readonly class RegisterBusinessInput
{
    public function __construct(
        public string $businessName,
        public string $outletName,
        public string $outletCode,
        public string $timezone,
        public string $ownerName,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}

    /**
     * @param  array<string, mixed>  $data  Hasil validated() Form Request.
     */
    public static function fromValidated(#[SensitiveParameter] array $data): self
    {
        return new self(
            businessName: (string) $data['business_name'],
            outletName: (string) $data['outlet_name'],
            outletCode: (string) $data['outlet_code'],
            timezone: (string) $data['timezone'],
            ownerName: (string) $data['owner_name'],
            email: (string) $data['email'],
            password: (string) $data['password'],
        );
    }
}
