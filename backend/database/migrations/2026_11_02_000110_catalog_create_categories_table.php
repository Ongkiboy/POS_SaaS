<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->string('status', 20);
            $table->integer('sort_order')->default(0);
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });

        DB::statement("ALTER TABLE categories ADD CONSTRAINT categories_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");

        // ERD §3.2 / §4.1: nama kategori unik per business, hanya untuk baris yang belum dihapus.
        DB::statement('CREATE UNIQUE INDEX categories_business_name_unique ON categories (business_id, name) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
