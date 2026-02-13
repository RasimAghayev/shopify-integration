<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Presentation\Http\Controllers\Api\V1\{InventoryController,ProductController,SyncController};

Route::prefix('v1')->group(function (): void {

    // Products
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{sku}', [ProductController::class, 'show']);
    Route::delete('products/{sku}', [ProductController::class, 'destroy']);

    // Sync
    Route::prefix('sync')->group(function (): void {
        Route::post('product', [SyncController::class, 'syncProduct']);
        Route::post('bulk', [SyncController::class, 'bulkSync']);
        Route::post('bulk/immediate', [SyncController::class, 'bulkSyncImmediate']);
    });

    // Inventory
    Route::put('inventory', [InventoryController::class, 'update']);

    // Health check
    Route::get('health', static fn () => response()->json(['status' => 'ok']));
});
