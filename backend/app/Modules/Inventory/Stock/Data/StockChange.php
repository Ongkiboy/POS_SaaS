<?php

namespace App\Modules\Inventory\Stock\Data;

use DateTimeInterface;

final readonly class StockChange
{
    /**
     * @param  string  $type  Tipe movement: OPENING, PURCHASE, SALE, WASTE, ADJUSTMENT, STOCK_OPNAME, RETURN
     * @param  string  $quantityDelta  Delta bertanda 4 desimal (string)
     * @param  string  $referenceType  Polimorfik referensi: OPENING, ORDER, REFUND, WASTE, ADJUSTMENT, OPNAME, PURCHASE
     * @param  string|null  $unitCost  Snapshot unit cost 4 desimal (default snapshot dari ingredients.last_cost)
     * @param  bool  $forceNegative  Hanya untuk settlement gateway (ADR-012, BR-041)
     */
    public function __construct(
        public string $type,
        public int $outletId,
        public int $ingredientId,
        public string $quantityDelta,
        public string $referenceType,
        public int $referenceId,
        public ?string $unitCost = null,
        public ?int $userId = null,
        public ?string $note = null,
        public bool $forceNegative = false,
        public ?DateTimeInterface $occurredAt = null,
    ) {}
}
