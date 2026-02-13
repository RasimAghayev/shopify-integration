<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');
            $table->string('sku', 50);
            $table->integer('price')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->integer('inventory_quantity')->default(0);
            $table->string('shopify_variant_id', 50)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('weight_unit', 10)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'sku']);
            $table->index('shopify_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
