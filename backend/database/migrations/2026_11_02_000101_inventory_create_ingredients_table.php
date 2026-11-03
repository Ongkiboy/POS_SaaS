<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('base_unit_id')->constrained('units')->restrictOnDelete();
            $table->string('sku', 50);
            $table->string('name', 150);
            $table->decimal('last_cost', 18, 4)->default(0);
            $table->decimal('min_stock', 18, 4)->default(0);
            $table->string('status', 20);
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });

        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_last_cost_check CHECK (last_cost >= 0)');
        DB::statement('ALTER TABLE ingredients ADD CONSTRAINT ingredients_min_stock_check CHECK (min_stock >= 0)');
        DB::statement("ALTER TABLE ingredients ADD CONSTRAINT ingredients_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");

        // ERD §4.1: SKU unik per business (baris aktif) dan daftar bahan per status.
        DB::statement('CREATE UNIQUE INDEX ingredients_business_sku_unique ON ingredients (business_id, sku) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX ingredients_business_status_index ON ingredients (business_id, status) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
