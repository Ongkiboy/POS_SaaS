<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete(); // BR-035: 1 refund per order
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->restrictOnDelete(); // shift OPEN pelaku
            $table->bigInteger('amount');
            $table->string('method', 10); // mengikuti pembayaran asli
            $table->string('reason', 255);
            $table->boolean('restock')->default(false);
            $table->string('gateway_refund_key', 100)->nullable(); // [P2]
            $table->string('gateway_status', 20)->nullable();      // [P2] NULL = refund manual
            $table->foreignId('refunded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('refunded_at', 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1
            $table->index('payment_id');
        });

        // BR-012 (AC2): refund tunai wajib dicatat di shift OPEN pelaku.
        DB::statement("ALTER TABLE refunds ADD CONSTRAINT refunds_cash_requires_shift_check CHECK (method <> 'CASH' OR shift_id IS NOT NULL)");
        DB::statement("ALTER TABLE refunds ADD CONSTRAINT refunds_gateway_status_check CHECK (gateway_status IN ('PENDING', 'SUCCESS', 'FAILED'))");

        // ERD §4.1
        DB::statement('CREATE INDEX refunds_shift_id_index ON refunds (shift_id) WHERE shift_id IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX refunds_gateway_refund_key_unique ON refunds (business_id, gateway_refund_key) WHERE gateway_refund_key IS NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
