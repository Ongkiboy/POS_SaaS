<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('quantity_delta', 18, 4);
            $table->decimal('quantity_before', 18, 4);
            $table->decimal('quantity_after', 18, 4);
            $table->decimal('unit_cost', 18, 4);
            $table->string('reference_type', 20);
            $table->bigInteger('reference_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamp('occurred_at', 0);
            $table->timestamp('created_at', 0)->useCurrent();

            $table->unique(
                ['outlet_id', 'ingredient_id', 'type', 'reference_type', 'reference_id'],
                'stock_movements_idempotency_unique'
            );
        });

        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_type_check CHECK (type IN ('OPENING', 'PURCHASE', 'SALE', 'WASTE', 'ADJUSTMENT', 'STOCK_OPNAME', 'RETURN'))");
        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_delta_check CHECK (quantity_delta != 0)');
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_delta_direction_check CHECK ((type IN ('SALE', 'WASTE') AND quantity_delta < 0) OR (type IN ('OPENING', 'RETURN', 'PURCHASE') AND quantity_delta > 0) OR (type IN ('ADJUSTMENT', 'STOCK_OPNAME')))");

        // ERD §4.1: Indeks hot path dan riwayat movement.
        DB::statement('CREATE INDEX stock_movements_outlet_ingredient_occurred_index ON stock_movements (outlet_id, ingredient_id, occurred_at DESC)');
        DB::statement('CREATE INDEX stock_movements_reference_index ON stock_movements (reference_type, reference_id)');
        DB::statement('CREATE INDEX stock_movements_outlet_type_occurred_index ON stock_movements (outlet_id, type, occurred_at)');

        // ADR-007: Fungsi bersama untuk semua tabel append-only.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION reject_append_only_mutation() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'Tabel % bersifat append-only; % ditolak (ADR-007)', TG_TABLE_NAME, TG_OP;
            END;
            $$;
            SQL);

        DB::statement('CREATE TRIGGER stock_movements_append_only BEFORE UPDATE OR DELETE ON stock_movements FOR EACH ROW EXECUTE FUNCTION reject_append_only_mutation()');
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS stock_movements_append_only ON stock_movements');
        Schema::dropIfExists('stock_movements');
    }
};
