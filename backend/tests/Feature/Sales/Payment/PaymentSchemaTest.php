<?php

use App\Modules\Platform\Business\Models\Business;
use App\Modules\Sales\Order\Models\Order;
use App\Modules\Sales\Payment\Models\Payment;
use App\Support\Enums\PaymentMethod;
use App\Support\Enums\PaymentStatus;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @param  array<string, mixed>  $overrides
 */
function paySchemaPayment(Order $order, array $overrides = []): int
{
    return DB::table('payments')->insertGetId(array_merge([
        'business_id' => $order->business_id,
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'shift_id' => $order->shift_id,
        'method' => 'CASH',
        'status' => 'SUCCESS',
        'amount' => 62400,
        'tendered_amount' => 100000,
        'change_amount' => 37600,
        'idempotency_key' => (string) str()->uuid(),
        'created_by' => $order->cashier_id,
        'paid_at' => now(),
    ], $overrides));
}

/**
 * @param  array<string, mixed>  $overrides
 */
function paySchemaRefund(Order $order, int $paymentId, array $overrides = []): int
{
    return DB::table('refunds')->insertGetId(array_merge([
        'business_id' => $order->business_id,
        'outlet_id' => $order->outlet_id,
        'order_id' => $order->id,
        'payment_id' => $paymentId,
        'shift_id' => $order->shift_id,
        'amount' => 62400,
        'method' => 'CASH',
        'reason' => 'Salah pesan',
        'refunded_by' => $order->cashier_id,
        'refunded_at' => now(),
    ], $overrides));
}

beforeEach(function () {
    $this->order = Order::factory()->create();
});

it('AC1: Idempotency-Key unik per business, boleh sama di business lain (BR-008)', function () {
    paySchemaPayment($this->order, ['idempotency_key' => 'key-1']);

    $sameBusinessOrder = Order::factory()->create(['business_id' => $this->order->business_id]);
    expect(fn () => DB::transaction(fn () => paySchemaPayment($sameBusinessOrder, ['idempotency_key' => 'key-1'])))
        ->toThrow(QueryException::class);

    $otherBusinessOrder = Order::factory()->create(['business_id' => Business::factory()]);
    expect(paySchemaPayment($otherBusinessOrder, ['idempotency_key' => 'key-1']))->toBeInt();
});

it('AC1: maks satu payment SUCCESS per order (BR-007)', function () {
    paySchemaPayment($this->order);

    expect(fn () => DB::transaction(fn () => paySchemaPayment($this->order)))
        ->toThrow(QueryException::class);
});

it('payments append-only: UPDATE dan DELETE ditolak trigger (ADR-007)', function () {
    $id = paySchemaPayment($this->order);

    expect(fn () => DB::transaction(fn () => DB::table('payments')->where('id', $id)->update(['amount' => 1])))
        ->toThrow(QueryException::class, 'append-only');
    expect(fn () => DB::transaction(fn () => DB::table('payments')->where('id', $id)->delete()))
        ->toThrow(QueryException::class, 'append-only');
});

it('AC2: created_by payments boleh NULL (webhook P2), kolom gateway nullable', function () {
    expect(paySchemaPayment($this->order, [
        'created_by' => null, 'provider' => null, 'gateway_transaction_id' => null, 'gateway_payment_type' => null,
    ]))->toBeInt();
});

it('CHECK payments menolak nilai di luar ERD', function (string $column, mixed $value) {
    expect(fn () => DB::transaction(fn () => paySchemaPayment($this->order, [$column => $value])))
        ->toThrow(QueryException::class);
})->with([
    'method' => ['method', 'DEBIT'],
    'status' => ['status', 'PENDING'],
    'provider' => ['provider', 'XENDIT'],
    'amount negatif' => ['amount', -1],
]);

it('AC2: refunds UNIQUE(order_id) — refund kedua ditolak (BR-035)', function () {
    $payment = paySchemaPayment($this->order);
    paySchemaRefund($this->order, $payment);

    expect(fn () => DB::transaction(fn () => paySchemaRefund($this->order, $payment)))
        ->toThrow(QueryException::class);
});

it('AC2: refund CASH wajib shift_id, non-tunai boleh NULL (BR-012)', function () {
    $payment = paySchemaPayment($this->order, ['method' => 'QRIS', 'tendered_amount' => null, 'change_amount' => null]);

    expect(fn () => DB::transaction(fn () => paySchemaRefund($this->order, $payment, ['method' => 'CASH', 'shift_id' => null])))
        ->toThrow(QueryException::class);

    expect(paySchemaRefund($this->order, $payment, ['method' => 'QRIS', 'shift_id' => null]))->toBeInt();
});

it('refunds.gateway_status: hanya PENDING/SUCCESS/FAILED, NULL = refund manual', function () {
    $payment = paySchemaPayment($this->order);

    expect(fn () => DB::transaction(fn () => paySchemaRefund($this->order, $payment, ['gateway_status' => 'DONE'])))
        ->toThrow(QueryException::class);

    expect(paySchemaRefund($this->order, $payment, ['gateway_status' => 'PENDING']))->toBeInt();
});

it('refunds.gateway_refund_key unik per business, boleh sama di business lain (BR-043)', function () {
    paySchemaRefund($this->order, paySchemaPayment($this->order), ['gateway_refund_key' => 'RF-1']);

    $sameBusinessOrder = Order::factory()->create(['business_id' => $this->order->business_id]);
    $samePayment = paySchemaPayment($sameBusinessOrder);
    expect(fn () => DB::transaction(fn () => paySchemaRefund($sameBusinessOrder, $samePayment, ['gateway_refund_key' => 'RF-1'])))
        ->toThrow(QueryException::class);

    $otherBusinessOrder = Order::factory()->create(['business_id' => Business::factory()]);
    $otherPayment = paySchemaPayment($otherBusinessOrder);
    expect(paySchemaRefund($otherBusinessOrder, $otherPayment, ['gateway_refund_key' => 'RF-1']))->toBeInt();
});

it('refunds tidak append-only: UPDATE diizinkan (ERD §3.4, dipakai gateway_status di P2)', function () {
    $id = paySchemaRefund($this->order, paySchemaPayment($this->order));

    expect(DB::table('refunds')->where('id', $id)->update(['reason' => 'Diubah']))->toBe(1);
});

it('model Payment: cast enum dan tanpa updated_at', function () {
    $id = paySchemaPayment($this->order);
    $payment = Payment::query()->withoutGlobalScopes()->findOrFail($id);

    expect($payment->method)->toBe(PaymentMethod::Cash)
        ->and($payment->status)->toBe(PaymentStatus::Success)
        ->and($payment->amount)->toBe(62400)
        ->and($this->order->fresh()?->payment?->id)->toBe($id);
});

it('payments tanpa updated_at; index parsial sesuai ERD §4.1', function () {
    $indexes = collect(DB::select("select indexname, indexdef from pg_indexes where tablename in ('payments', 'refunds')"))
        ->pluck('indexdef', 'indexname');

    expect(Schema::hasColumn('payments', 'updated_at'))->toBeFalse()
        ->and($indexes['payments_one_success_per_order_unique'])->toContain("WHERE ((status)::text = 'SUCCESS'::text)")
        ->and($indexes['payments_gateway_transaction_unique'])->toContain('WHERE (gateway_transaction_id IS NOT NULL)')
        ->and($indexes['refunds_shift_id_index'])->toContain('WHERE (shift_id IS NOT NULL)')
        ->and($indexes)->toHaveKey('refunds_order_id_unique');
});
