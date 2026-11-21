<?php

namespace App\Modules\Platform\Auth\Exceptions;

use App\Support\Exceptions\ApiException;

final class AccountInactiveException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Akun nonaktif. Hubungi pemilik.', 'ACCOUNT_INACTIVE', 403);
    }
}
