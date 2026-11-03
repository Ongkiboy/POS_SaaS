<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredient_unit_conversions', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('factor_to_base', 18, 6);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->unique(['ingredient_id', 'unit_id']);
        });

        DB::statement('ALTER TABLE ingredient_unit_conversions ADD CONSTRAINT ingredient_unit_conversions_factor_check CHECK (factor_to_base > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('ingredient_unit_conversions');
    }
};
