<?php

use App\Support\Enums\DiscountType;
use App\Support\Enums\KitchenStatus;
use App\Support\Enums\MovementType;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentProvider;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\ShiftStatus;
use Illuminate\Support\Facades\DB;

/**
 * Nilai di CHECK constraint PostgreSQL, atau null bila constraint belum ada (tabelnya dibuat tiket lain).
 *
 * @return list<string>|null
 */
function enumCheckValues(string $constraint): ?array
{
    $row = DB::selectOne('SELECT pg_get_constraintdef(oid) AS def FROM pg_constraint WHERE conname = ?', [$constraint]);

    if ($row === null) {
        return null;
    }

    preg_match_all("/'([^']+)'/", (string) $row->def, $matches);
    $values = $matches[1];
    sort($values);

    return $values;
}

it('nilai enum sama dengan CHECK di database (AC2)', function (string $constraint, string $enum) {
    $values = enumCheckValues($constraint);

    if ($values === null) {
        $this->markTestSkipped("Constraint {$constraint} belum ada (tabelnya belum dibuat di branch ini).");
    }

    /** @var class-string<BackedEnum> $enum */
    $cases = array_map(fn (BackedEnum $case) => (string) $case->value, $enum::cases());
    sort($cases);

    expect($cases)->toBe($values);
})->with([
    'ShiftStatus' => ['shifts_status_check', ShiftStatus::class],
    'OrderStatus' => ['orders_status_check', OrderStatus::class],
    'PaymentMethod' => ['payments_method_check', PaymentMethod::class],
    'MovementType' => ['stock_movements_type_check', MovementType::class],
    'OrderType' => ['orders_order_type_check', OrderType::class],
    'KitchenStatus' => ['orders_kitchen_status_check', KitchenStatus::class],
    'DiscountType' => ['orders_discount_type_check', DiscountType::class],
    'PaymentStatus' => ['payments_status_check', PaymentStatus::class],
    'PaymentProvider' => ['payments_provider_check', PaymentProvider::class],
]);
