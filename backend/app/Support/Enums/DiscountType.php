<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `orders.discount_type` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum DiscountType: string
{
    case Percent = 'PERCENT';
    case Amount = 'AMOUNT';
}
