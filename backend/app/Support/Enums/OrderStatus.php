<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `orders.status` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum OrderStatus: string
{
    case Draft = 'DRAFT';
    case PendingPayment = 'PENDING_PAYMENT';
    case Paid = 'PAID';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';
    case Refunded = 'REFUNDED';
}
