<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('version');
            $table->boolean('is_active');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->unique(['product_id', 'version']);
        });

        // BR-013 (AC1): hanya satu versi aktif per produk.
        DB::statement('CREATE UNIQUE INDEX recipes_one_active_per_product_unique ON recipes (product_id) WHERE is_active');
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
