<?php

namespace App\Modules\Platform\Auth\Data;

use SensitiveParameter;

final readonly class LoginInput
{
    public function __construct(
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
            email: (string) $data['email'],
            password: (string) $data['password'],
        );
    }
}
