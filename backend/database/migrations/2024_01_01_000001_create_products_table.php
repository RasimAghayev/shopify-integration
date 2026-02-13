<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('price')->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 20)->default('draft');
            $table->integer('inventory_quantity')->default(0);
            $table->string('shopify_id', 50)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'inventory_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
