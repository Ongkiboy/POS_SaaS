<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->string('type', 3);
            $table->bigInteger('amount');
            $table->string('reason', 255);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at', 0)->useCurrent();

            // ERD §4.1: ringkasan kas shift.
            $table->index(['shift_id', 'type']);
        });

        DB::statement("ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_type_check CHECK (type IN ('IN', 'OUT'))");
        DB::statement('ALTER TABLE cash_movements ADD CONSTRAINT cash_movements_amount_check CHECK (amount > 0)');

        // ADR-007: fungsi bersama tabel append-only. Isi WAJIB identik dengan DB-AUD-001.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION reject_append_only_mutation() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'Tabel % bersifat append-only; % ditolak (ADR-007)', TG_TABLE_NAME, TG_OP;
            END;
            $$;
            SQL);

        DB::statement('CREATE TRIGGER cash_movements_append_only BEFORE UPDATE OR DELETE ON cash_movements FOR EACH ROW EXECUTE FUNCTION reject_append_only_mutation()');
    }

    public function down(): void
    {
        // Fungsi reject_append_only_mutation() sengaja TIDAK dihapus: dipakai tabel append-only lain.
        Schema::dropIfExists('cash_movements');
    }
};
