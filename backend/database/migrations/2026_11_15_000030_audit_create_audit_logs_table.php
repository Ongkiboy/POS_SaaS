<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('action', 50);
            $table->string('resource_type', 50);
            $table->bigInteger('resource_id')->nullable();
            $table->jsonb('before_data')->nullable();
            $table->jsonb('after_data')->nullable();
            $table->string('reason', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
        });

        // ERD §4.1 (urutan DESC tidak bisa lewat Blueprint).
        DB::statement('CREATE INDEX audit_logs_business_created_at_index ON audit_logs (business_id, created_at DESC)');
        DB::statement('CREATE INDEX audit_logs_business_action_created_at_index ON audit_logs (business_id, action, created_at DESC)');
        DB::statement('CREATE INDEX audit_logs_resource_index ON audit_logs (resource_type, resource_id)');
        DB::statement('CREATE INDEX audit_logs_user_created_at_index ON audit_logs (user_id, created_at DESC)');

        // ADR-007: fungsi bersama untuk semua tabel append-only.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION reject_append_only_mutation() RETURNS trigger
            LANGUAGE plpgsql AS $$
            BEGIN
                RAISE EXCEPTION 'Tabel % bersifat append-only; % ditolak (ADR-007)', TG_TABLE_NAME, TG_OP;
            END;
            $$;
            SQL);

        DB::statement('CREATE TRIGGER audit_logs_append_only BEFORE UPDATE OR DELETE ON audit_logs FOR EACH ROW EXECUTE FUNCTION reject_append_only_mutation()');
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        DB::statement('DROP FUNCTION IF EXISTS reject_append_only_mutation()');
    }
};
