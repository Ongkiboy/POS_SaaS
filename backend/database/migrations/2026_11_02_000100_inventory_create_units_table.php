<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Global (ERD §1: units tidak punya business_id).
        Schema::create('units', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
