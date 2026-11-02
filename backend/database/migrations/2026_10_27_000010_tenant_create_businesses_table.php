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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('status', 20);
            $table->string('address', 255)->nullable();
            $table->string('license_plan', 50)->nullable();
            $table->date('license_valid_until')->nullable();
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });

        DB::statement("ALTER TABLE businesses ADD CONSTRAINT businesses_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
