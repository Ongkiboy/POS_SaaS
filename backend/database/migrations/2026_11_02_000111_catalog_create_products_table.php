<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id()->generatedAs()->always();
            $table->foreignId('business_id')->constrained('businesses')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('sku', 50);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->bigInteger('selling_price');
            $table->string('status', 20);
            $table->string('image_path', 255)->nullable();
            $table->softDeletes(precision: 0);
            $table->timestamp('created_at', 0)->useCurrent();
            $table->timestamp('updated_at', 0)->useCurrent();
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT products_selling_price_check CHECK (selling_price >= 0)');
        DB::statement("ALTER TABLE products ADD CONSTRAINT products_status_check CHECK (status IN ('ACTIVE', 'INACTIVE'))");

        // ERD §4.1: SKU unik per business (baris aktif) dan index katalog POS.
        DB::statement('CREATE UNIQUE INDEX products_business_sku_unique ON products (business_id, sku) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX products_business_category_status_index ON products (business_id, category_id, status) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
