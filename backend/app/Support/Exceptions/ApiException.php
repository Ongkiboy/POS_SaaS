<?php

namespace App\Support\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Basis exception domain yang dipetakan ke envelope error SDD §4.2.
 * Sengaja tidak memakai nama properti $code (bentrok dengan Exception::$code).
 */
abstract class ApiException extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $details
     */
    public function __construct(
        string $message,
        public readonly string $errorCode,
        public readonly int $httpStatus,
        public readonly ?array $details = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
