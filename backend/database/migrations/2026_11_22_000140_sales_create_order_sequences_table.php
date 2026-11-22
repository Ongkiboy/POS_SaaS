<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ERD §3.4: penomoran order per outlet per business date (BR-019). Dikunci FOR UPDATE oleh OrderNumberGenerator.
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->date('business_date');
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->integer('last_number');
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // PK komposit (ERD §3.4 + §4.1). Tidak ada kolom id.
            $table->primary(['outlet_id', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};
