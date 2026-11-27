<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('min_stock', 18, 4)->nullable();
            $table->bigInteger('last_movement_id')->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->unique(['outlet_id', 'ingredient_id']);
        });

        DB::statement('ALTER TABLE stock_balances ADD CONSTRAINT stock_balances_min_stock_check CHECK (min_stock IS NULL OR min_stock >= 0)');

        // ERD §4.1: Laporan stok minus (partial index).
        DB::statement('CREATE INDEX stock_balances_outlet_negative_index ON stock_balances (outlet_id) WHERE quantity < 0');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
