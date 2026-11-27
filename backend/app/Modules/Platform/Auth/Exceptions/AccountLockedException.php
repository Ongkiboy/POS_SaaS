<?php

namespace App\Modules\Platform\Auth\Exceptions;

use App\Support\Exceptions\ApiException;

final class AccountLockedException extends ApiException
{
    public function __construct()
    {
        parent::__construct('Akun terkunci sementara. Coba lagi dalam 15 menit.', 'ACCOUNT_LOCKED', 423);
    }
}
