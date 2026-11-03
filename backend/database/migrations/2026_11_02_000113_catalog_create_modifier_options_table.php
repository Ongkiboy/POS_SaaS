<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->restrictOnDelete();
            $table->string('name', 100);
            $table->bigInteger('price_delta');
            $table->integer('sort_order')->default(0);
            $table->string('status', 20);
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            // ERD §4.1: muat option per group.
            $table->index(['modifier_group_id', 'status']);
        });

        DB::statement('ALTER TABLE modifier_options ADD CONSTRAINT modifier_options_price_delta_check CHECK (price_delta >= 0)');
        DB::statement("ALTER TABLE modifier_options ADD CONSTRAINT modifier_options_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_options');
    }
};
