<?php

use App\Modules\Catalog\Modifier\Models\ModifierGroup;
use App\Modules\Catalog\Modifier\Models\ModifierOption;
use App\Modules\Catalog\Product\Models\Product;
use App\Modules\Platform\Outlet\Models\Outlet;
use App\Modules\Sales\Order\Models\Order;
use App\Modules\Shift\Models\Shift;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\OrderType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Insert order langsung ke tabel (menguji constraint DB, bukan aplikasi).
 *
 * @param  array<string, mixed>  $overrides
 */
function ordSchemaOrder(Shift $shift, array $overrides = []): int
{
    static $sequence = 0;
    $sequence++;

    return DB::table('orders')->insertGetId(array_merge([
        'business_id' => $shift->business_id,
        'outlet_id' => $shift->outlet_id,
        'shift_id' => $shift->id,
        'cashier_id' => $shift->cashier_id,
        'business_date' => $shift->business_date->toDateString(),
        'order_number' => 'KP01-261123-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
        'order_type' => 'TAKEAWAY',
        'status' => 'DRAFT',
        'tax_rate_snapshot' => '10.00',
        'service_rate_snapshot' => '5.00',
    ], $overrides));
}

/**
 * @param  array<string, mixed>  $overrides
 */
function ordSchemaItem(int $businessId, int $orderId, int $productId, array $overrides = []): int
{
    return DB::table('order_items')->insertGetId(array_merge([
        'business_id' => $businessId,
        'order_id' => $orderId,
        'product_id' => $productId,
        'product_name_snapshot' => 'Es Kopi Susu',
        'sku_snapshot' => 'KOP-001',
        'unit_price' => 22000,
        'quantity' => 1,
        'line_total' => 22000,
    ], $overrides));
}

beforeEach(function () {
    $this->shift = Shift::factory()->create();
    $this->biz = $this->shift->business_id;
    $this->product = Product::factory()->create(['business_id' => $this->biz])->id;
});

it('AC1: nomor meja hanya untuk DINE_IN', function () {
    expect(ordSchemaOrder($this->shift, ['order_type' => 'DINE_IN', 'table_number' => 'A3']))->toBeInt()
        ->and(ordSchemaOrder($this->shift, ['order_type' => 'TAKEAWAY', 'table_number' => null]))->toBeInt();

    expect(fn () => DB::transaction(fn () => ordSchemaOrder($this->shift, ['order_type' => 'TAKEAWAY', 'table_number' => 'A3'])))
        ->toThrow(QueryException::class);
});

it('AC1: rounding_amount boleh negatif, total tidak boleh negatif', function () {
    expect(ordSchemaOrder($this->shift, ['rounding_amount' => -33, 'total' => 56100]))->toBeInt();

    expect(fn () => DB::transaction(fn () => ordSchemaOrder($this->shift, ['total' => -1])))
        ->toThrow(QueryException::class);
});

it('CHECK enum menolak nilai di luar ERD', function (string $column, string $value) {
    expect(fn () => DB::transaction(fn () => ordSchemaOrder($this->shift, [$column => $value])))
        ->toThrow(QueryException::class);
})->with([
    'status' => ['status', 'OPEN'],
    'order_type' => ['order_type', 'DELIVERY'],
    'kitchen_status' => ['kitchen_status', 'DONE'],
    'discount_type' => ['discount_type', 'FREE'],
]);

it('nomor order unik per outlet, boleh sama di outlet lain (BR-019)', function () {
    ordSchemaOrder($this->shift, ['order_number' => 'KP01-261123-0001']);

    expect(fn () => DB::transaction(fn () => ordSchemaOrder($this->shift, ['order_number' => 'KP01-261123-0001'])))
        ->toThrow(QueryException::class);

    $otherOutlet = Outlet::factory()->create(['business_id' => $this->biz]);
    $otherShift = Shift::factory()->create(['business_id' => $this->biz, 'outlet_id' => $otherOutlet->id]);

    expect(ordSchemaOrder($otherShift, ['order_number' => 'KP01-261123-0001']))->toBeInt();
});

it('quantity item hanya 1–999 (TC-ORD-009)', function (int $qty, bool $valid) {
    $order = ordSchemaOrder($this->shift);
    $insert = fn () => DB::transaction(fn () => ordSchemaItem($this->biz, $order, $this->product, ['quantity' => $qty]));

    if ($valid) {
        expect($insert())->toBeInt();
    } else {
        expect($insert)->toThrow(QueryException::class);
    }
})->with([
    'nol' => [0, false],
    'satu' => [1, true],
    '999' => [999, true],
    '1000' => [1000, false],
]);

it('AC2: recipe_id boleh NULL dan harus menunjuk recipes bila diisi', function () {
    $order = ordSchemaOrder($this->shift);

    expect(ordSchemaItem($this->biz, $order, $this->product, ['recipe_id' => null]))->toBeInt();

    $recipe = DB::table('recipes')->insertGetId([
        'business_id' => $this->biz, 'product_id' => $this->product, 'version' => 1,
        'is_active' => true, 'created_by' => $this->shift->cashier_id,
    ]);
    expect(ordSchemaItem($this->biz, $order, $this->product, ['recipe_id' => $recipe]))->toBeInt();

    expect(fn () => DB::transaction(fn () => ordSchemaItem($this->biz, $order, $this->product, ['recipe_id' => 999999])))
        ->toThrow(QueryException::class);
});

it('order_item_modifiers menyimpan snapshot nama dan price_delta', function () {
    $group = ModifierGroup::query()->create([
        'business_id' => $this->biz, 'name' => 'Extra', 'selection_type' => 'MULTI',
        'is_required' => false, 'min_select' => 0, 'max_select' => 3, 'status' => 'ACTIVE',
    ]);
    $option = ModifierOption::query()->create([
        'business_id' => $this->biz, 'modifier_group_id' => $group->id, 'name' => 'Extra Shot',
        'price_delta' => 5000, 'status' => 'ACTIVE',
    ]);
    $item = ordSchemaItem($this->biz, ordSchemaOrder($this->shift), $this->product);

    DB::table('order_item_modifiers')->insert([
        'business_id' => $this->biz, 'order_item_id' => $item, 'modifier_option_id' => $option->id,
        'name_snapshot' => 'Extra Shot', 'price_delta' => 5000,
    ]);

    expect(DB::table('order_item_modifiers')->where('order_item_id', $item)->value('price_delta'))->toEqual(5000);
});

it('order_sequences: satu baris per outlet per business date', function () {
    $row = [
        'outlet_id' => $this->shift->outlet_id, 'business_date' => '2026-11-23',
        'business_id' => $this->biz, 'last_number' => 1,
    ];
    DB::table('order_sequences')->insert($row);
    DB::table('order_sequences')->insert(array_merge($row, ['business_date' => '2026-11-24']));

    expect(fn () => DB::transaction(fn () => DB::table('order_sequences')->insert($row)))
        ->toThrow(QueryException::class);
});

it('model Order: cast enum, uang int, relasi item', function () {
    $order = Order::factory()->create(['business_id' => $this->biz, 'shift_id' => $this->shift->id]);
    ordSchemaItem($this->biz, $order->id, $this->product);

    $order = Order::query()->withoutGlobalScopes()->with('items')->findOrFail($order->id);

    expect($order->status)->toBe(OrderStatus::Draft)
        ->and($order->order_type)->toBe(OrderType::Takeaway)
        ->and($order->total)->toBe(0)
        ->and($order->tax_rate_snapshot)->toBe('10.00')
        ->and($order->outlet_id)->toBe($this->shift->outlet_id)
        ->and($order->items)->toHaveCount(1);
});

it('index sesuai ERD §4.1', function () {
    $indexes = collect(DB::select("select indexname, indexdef from pg_indexes where tablename = 'orders'"))
        ->pluck('indexdef', 'indexname');

    expect($indexes['orders_cashier_created_at_index'])->toContain('(cashier_id, created_at DESC)')
        ->and($indexes['orders_kitchen_queue_index'])->toContain('WHERE')
        ->and($indexes)->toHaveKey('orders_outlet_id_order_number_unique')
        ->and($indexes)->toHaveKey('orders_outlet_id_business_date_status_index')
        ->and($indexes)->toHaveKey('orders_shift_id_status_index');
});
