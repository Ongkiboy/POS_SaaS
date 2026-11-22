<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete(); // BR-001
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->date('business_date'); // salinan dari shift
            $table->string('order_number', 30);
            $table->string('order_type', 10);
            $table->string('table_number', 20)->nullable();
            $table->string('status', 20);
            $table->string('kitchen_status', 10)->nullable(); // NULL sebelum dibayar
            $table->bigInteger('subtotal')->default(0);
            $table->string('discount_type', 10)->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->bigInteger('discount_amount')->default(0);
            $table->bigInteger('service_charge_amount')->default(0);
            $table->bigInteger('tax_amount')->default(0);
            $table->bigInteger('rounding_amount')->default(0); // boleh negatif (BR-036)
            $table->bigInteger('total')->default(0);
            $table->bigInteger('paid_amount')->default(0);
            $table->decimal('tax_rate_snapshot', 5, 2);
            $table->decimal('service_rate_snapshot', 5, 2);
            $table->string('note', 255)->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('paid_at', 0)->nullable();
            $table->timestamp('completed_at', 0)->nullable();
            $table->timestamp('cancelled_at', 0)->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1
            $table->unique(['outlet_id', 'order_number']);
            $table->index(['outlet_id', 'business_date', 'status']);
            $table->index(['shift_id', 'status']);
        });

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_order_type_check CHECK (order_type IN ('DINE_IN', 'TAKEAWAY'))");
        // AC1: nomor meja hanya untuk Dine In.
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_table_number_dine_in_check CHECK (table_number IS NULL OR order_type = 'DINE_IN')");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('DRAFT', 'PENDING_PAYMENT', 'PAID', 'COMPLETED', 'CANCELLED', 'REFUNDED'))");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_kitchen_status_check CHECK (kitchen_status IN ('NEW', 'READY'))");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_discount_type_check CHECK (discount_type IN ('PERCENT', 'AMOUNT'))");
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_total_check CHECK (total >= 0)');

        // ERD §4.1: riwayat order Cashier (Own), terbaru dulu. Laravel tidak punya index DESC di Blueprint.
        DB::statement('CREATE INDEX orders_cashier_created_at_index ON orders (cashier_id, created_at DESC)');
        // ERD §4.1: antrian dapur (R1.1, polling 10 detik). Dibuat sekarang supaya tidak perlu ALTER nanti.
        DB::statement("CREATE INDEX orders_kitchen_queue_index ON orders (outlet_id, completed_at) WHERE kitchen_status = 'NEW' AND status = 'COMPLETED'");
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
