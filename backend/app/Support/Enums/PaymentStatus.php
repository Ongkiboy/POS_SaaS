<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `payments.status` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum PaymentStatus: string
{
    case Success = 'SUCCESS';
    case Failed = 'FAILED';
    case Void = 'VOID';
}
