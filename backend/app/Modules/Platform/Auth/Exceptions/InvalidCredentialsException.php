<?php

namespace App\Modules\Platform\Auth\Exceptions;

use App\Support\Exceptions\ApiException;

final class InvalidCredentialsException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Email atau password salah.', 'INVALID_CREDENTIALS', 422);
    }
}
