<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_modifier_groups', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->restrictOnDelete();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->primary(['product_id', 'modifier_group_id']);
            // ERD §4.1: cari produk dari sebuah group.
            $table->index('modifier_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_modifier_groups');
    }
};
