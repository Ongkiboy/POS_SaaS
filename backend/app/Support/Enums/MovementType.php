<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `stock_movements.type` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum MovementType: string
{
    case Opening = 'OPENING';
    case Purchase = 'PURCHASE';
    case Sale = 'SALE';
    case Waste = 'WASTE';
    case Adjustment = 'ADJUSTMENT';
    case StockOpname = 'STOCK_OPNAME';
    case Return = 'RETURN';
}
