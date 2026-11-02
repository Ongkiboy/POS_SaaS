<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->string('code', 6);
            $table->string('name', 100);
            $table->string('address', 255)->nullable();
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->boolean('allow_negative_stock')->default(true);
            $table->decimal('cashier_max_discount_percent', 5, 2)->default(10);
            $table->integer('rounding_increment')->default(100);
            $table->string('receipt_header', 255)->nullable();
            $table->string('receipt_footer', 255)->nullable();
            $table->string('status', 20);
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->unique(['business_id', 'code']);
        });

        DB::statement("ALTER TABLE outlets ADD CONSTRAINT outlets_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");
        DB::statement('ALTER TABLE outlets ADD CONSTRAINT outlets_tax_rate_check CHECK (tax_rate BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE outlets ADD CONSTRAINT outlets_service_charge_rate_check CHECK (service_charge_rate BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE outlets ADD CONSTRAINT outlets_cashier_max_discount_percent_check CHECK (cashier_max_discount_percent BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE outlets ADD CONSTRAINT outlets_rounding_increment_check CHECK (rounding_increment IN (0, 100, 500, 1000))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
