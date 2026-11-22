<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            // AC2: versi recipe yang dipakai saat order (snapshot). NULL = produk tanpa recipe.
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->restrictOnDelete();
            $table->string('product_name_snapshot', 150);
            $table->string('sku_snapshot', 50);
            $table->bigInteger('unit_price');
            $table->bigInteger('unit_cost_snapshot')->default(0); // HPP saat order (BR-014)
            $table->integer('quantity');
            $table->bigInteger('line_total');
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1
            $table->index('order_id');
            $table->index(['business_id', 'product_id']);
        });

        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_quantity_check CHECK (quantity BETWEEN 1 AND 999)');
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
