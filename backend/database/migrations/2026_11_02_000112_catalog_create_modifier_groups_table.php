<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('selection_type', 10);
            $table->boolean('is_required');
            $table->smallInteger('min_select')->default(0);
            $table->smallInteger('max_select');
            $table->integer('sort_order')->default(0);
            $table->string('status', 20);
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });

        DB::statement("ALTER TABLE modifier_groups ADD CONSTRAINT modifier_groups_selection_type_check CHECK (selection_type IN ('SINGLE', 'MULTI'))");
        DB::statement('ALTER TABLE modifier_groups ADD CONSTRAINT modifier_groups_min_max_check CHECK (min_select <= max_select)');
        DB::statement("ALTER TABLE modifier_groups ADD CONSTRAINT modifier_groups_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};
