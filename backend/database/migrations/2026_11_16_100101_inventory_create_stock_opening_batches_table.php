<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opening_batches', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opening_batches');
    }
};
