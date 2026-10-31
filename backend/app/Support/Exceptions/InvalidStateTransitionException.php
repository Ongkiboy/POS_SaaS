<?php

namespace App\Support\Exceptions;

final class InvalidStateTransitionException extends ApiException
{
    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(string $message = 'Transisi status tidak diizinkan', ?array $details = null)
    {
        parent::__construct($message, 'INVALID_STATE_TRANSITION', 409, $details);
    }
}
