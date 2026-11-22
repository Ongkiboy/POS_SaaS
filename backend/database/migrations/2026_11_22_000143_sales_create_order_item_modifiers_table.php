<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_item_modifiers', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->restrictOnDelete();
            $table->foreignId('modifier_option_id')->constrained('modifier_options')->restrictOnDelete();
            $table->string('name_snapshot', 100);
            $table->bigInteger('price_delta');
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1
            $table->index('order_item_id');
            $table->index('modifier_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_modifiers');
    }
};
