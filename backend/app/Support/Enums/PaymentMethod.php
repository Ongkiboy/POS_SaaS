<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `payments.method` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum PaymentMethod: string
{
    case Cash = 'CASH';
    case Qris = 'QRIS';
    case Card = 'CARD';
    case Gateway = 'GATEWAY';
}
