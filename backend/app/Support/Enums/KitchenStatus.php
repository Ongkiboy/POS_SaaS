<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `orders.kitchen_status` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum KitchenStatus: string
{
    case New = 'NEW';
    case Ready = 'READY';
}
