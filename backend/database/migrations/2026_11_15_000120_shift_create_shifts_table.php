<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('status', 10);
            $table->date('business_date');
            $table->bigInteger('opening_cash');
            $table->bigInteger('expected_cash')->nullable();
            $table->bigInteger('actual_cash')->nullable();
            $table->bigInteger('difference')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('opened_at', 0);
            $table->timestamp('closed_at', 0)->nullable();
            $table->string('note', 255)->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1: laporan harian dan GET /shifts.
            $table->index(['outlet_id', 'business_date']);
        });

        DB::statement("ALTER TABLE shifts ADD CONSTRAINT shifts_status_check CHECK (status IN ('OPEN', 'CLOSED'))");
        DB::statement('ALTER TABLE shifts ADD CONSTRAINT shifts_opening_cash_check CHECK (opening_cash >= 0)');

        // BR-002 (AC1): satu shift OPEN per kasir per outlet. Dijaga DB, aman terhadap request bersamaan.
        DB::statement("CREATE UNIQUE INDEX shifts_one_open_per_cashier_unique ON shifts (outlet_id, cashier_id) WHERE status = 'OPEN'");

        // ERD §4.1: peringatan shift terbuka > 24 jam.
        DB::statement("CREATE INDEX shifts_open_opened_at_index ON shifts (outlet_id, opened_at) WHERE status = 'OPEN'");
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
