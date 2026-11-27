<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->bigInteger('opname_item_id')->nullable();
            $table->decimal('quantity_before', 18, 4);
            $table->decimal('quantity_delta', 18, 4);
            $table->decimal('quantity_after', 18, 4);
            $table->string('reason', 255);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at', 0)->useCurrent();
        });

        // ERD §4.1: Daftar adjustment dan cek opname_item_id.
        DB::statement('CREATE INDEX stock_adjustments_outlet_created_at_index ON stock_adjustments (outlet_id, created_at DESC)');
        DB::statement('CREATE INDEX stock_adjustments_opname_item_id_index ON stock_adjustments (opname_item_id) WHERE opname_item_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
