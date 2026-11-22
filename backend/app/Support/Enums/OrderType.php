<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `orders.order_type` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum OrderType: string
{
    case DineIn = 'DINE_IN';
    case Takeaway = 'TAKEAWAY';
}
