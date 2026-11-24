<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `payments.provider` di ERD v3.0 (kolom nullable; NULL untuk pembayaran non-gateway).
 */
enum PaymentProvider: string
{
    case Midtrans = 'MIDTRANS';
}
