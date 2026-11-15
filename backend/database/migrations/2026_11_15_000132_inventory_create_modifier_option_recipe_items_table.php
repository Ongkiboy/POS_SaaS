<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_option_recipe_items', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('modifier_option_id')->constrained('modifier_options')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // Nama eksplisit: nama otomatis (66 karakter) melebihi batas 63 karakter PostgreSQL.
            $table->unique(['modifier_option_id', 'ingredient_id'], 'mori_option_ingredient_unique');
            $table->index('ingredient_id');
        });

        DB::statement('ALTER TABLE modifier_option_recipe_items ADD CONSTRAINT modifier_option_recipe_items_quantity_check CHECK (quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_option_recipe_items');
    }
};
