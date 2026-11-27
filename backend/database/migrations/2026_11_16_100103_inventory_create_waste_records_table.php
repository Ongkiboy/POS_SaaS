<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('outlet_id')->constrained('outlets')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('quantity', 18, 4);
            $table->string('reason', 20);
            $table->string('note', 255)->nullable();
            $table->decimal('unit_cost', 18, 4);
            $table->bigInteger('total_cost');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('wasted_at', 0);
            $table->timestamp('created_at', 0)->useCurrent();
        });

        DB::statement('ALTER TABLE waste_records ADD CONSTRAINT waste_records_quantity_check CHECK (quantity > 0)');
        DB::statement("ALTER TABLE waste_records ADD CONSTRAINT waste_records_reason_check CHECK (reason IN ('EXPIRED', 'SPOILED', 'SPILLED', 'PRODUCTION_ERROR', 'OTHER'))");
        DB::statement("ALTER TABLE waste_records ADD CONSTRAINT waste_records_note_other_check CHECK (reason != 'OTHER' OR note IS NOT NULL)");

        // ERD §4.1: Waste report.
        DB::statement('CREATE INDEX waste_records_outlet_wasted_at_index ON waste_records (outlet_id, wasted_at DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
    }
};
