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
        Schema::create('users', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('status', 20);
            $table->smallInteger('failed_login_count')->default(0);
            $table->timestamp('locked_until', 0)->nullable();
            $table->timestamp('last_login_at', 0)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();

            $table->index(['business_id', 'status']);
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");
        // Email unik global, case-insensitive (ERD §3.1, §4.1).
        DB::statement('CREATE UNIQUE INDEX users_email_lower_unique ON users (lower(email))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
