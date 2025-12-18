<?php



use App\Http\Controllers\BetterUserController;
use App\Http\Controllers\UserController;

// Phase 1: Exact Match
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// Phase 2: Proper Version
Route::group(['prefix' => 'v2'], function () {
    Route::get('/users', [BetterUserController::class, 'index']);
    Route::post('/users', [BetterUserController::class, 'store']);
});

