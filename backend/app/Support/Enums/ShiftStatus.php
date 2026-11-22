<?php

namespace App\Support\Enums;

/**
 * Nilai = CHECK `shifts.status` di ERD v3.0. Ubah keduanya bersamaan.
 */
enum ShiftStatus: string
{
    case Open = 'OPEN';
    case Closed = 'CLOSED';
}
