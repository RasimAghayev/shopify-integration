<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Presentation\Http\Controllers\Api\V1\ProductController;

Route::prefix('v1')->group(function (): void {

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{sku}', [ProductController::class, 'show']);
    Route::delete('products/{sku}', [ProductController::class, 'destroy']);

    // Health check
    Route::get('health', static fn () => response()->json(['status' => 'ok']));
});
