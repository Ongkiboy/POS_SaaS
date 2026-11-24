<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ERD §3.4: hanya pembayaran BERHASIL. Append-only (ADR-007, BR-020).
        Schema::create('payments', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->string('method', 10);
            // Kolom gateway [P2], nullable sejak Phase 1 karena ALTER tabel ber-trigger lebih mahal (ERD §7.3).
            $table->string('provider', 20)->nullable();
            $table->bigInteger('gateway_transaction_id')->nullable(); // FK ke payment_gateway_transactions ditambah di Phase 2
            $table->string('gateway_payment_type', 30)->nullable();
            $table->string('status', 10);
            $table->bigInteger('amount');
            $table->bigInteger('tendered_amount')->nullable(); // Cash saja
            $table->bigInteger('change_amount')->nullable();   // Cash saja
            $table->string('reference_no', 50)->nullable();    // QRIS manual
            $table->string('idempotency_key', 64);
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete(); // NULL = webhook [P2]
            $table->timestamp('paid_at', 0);
            $table->timestamp('created_at', 0)->useCurrent();

            // ERD §4.1
            $table->unique(['business_id', 'idempotency_key']); // BR-008
            $table->index(['shift_id', 'method']);
            $table->index(['outlet_id', 'paid_at']);
        });

        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_method_check CHECK (method IN ('CASH', 'QRIS', 'CARD', 'GATEWAY'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_provider_check CHECK (provider IN ('MIDTRANS'))");
        DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status IN ('SUCCESS', 'FAILED', 'VOID'))");
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_check CHECK (amount >= 0)');

        // BR-007 (AC1): tanpa split payment, maks 1 payment SUCCESS per order.
        DB::statement("CREATE UNIQUE INDEX payments_one_success_per_order_unique ON payments (order_id) WHERE status = 'SUCCESS'");
        // ERD §4.1 [P2]: 1 payment per transaksi gateway.
        DB::statement('CREATE UNIQUE INDEX payments_gateway_transaction_unique ON payments (gateway_transaction_id) WHERE gateway_transaction_id IS NOT NULL');

        // ADR-007: fungsi reject_append_only_mutation() sudah dibuat DB-AUD-001; di sini hanya dipasang.
        DB::statement('CREATE TRIGGER payments_append_only BEFORE UPDATE OR DELETE ON payments FOR EACH ROW EXECUTE FUNCTION reject_append_only_mutation()');
    }

    public function down(): void
    {
        // Trigger ikut terhapus bersama tabel. Fungsi bersama TIDAK dihapus.
        Schema::dropIfExists('payments');
    }
};
