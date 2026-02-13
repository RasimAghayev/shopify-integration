<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // Health check
    Route::get('health', static fn () => response()->json(['status' => 'ok']));
});
