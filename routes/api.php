<?php

use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\LegalController;
use App\Http\Controllers\Api\V1\NotifyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 - CuponesHub
|--------------------------------------------------------------------------
| Autenticación: Header X-Client-Id + X-Client-Secret
| O Bearer token en Authorization header
|
| Rate Limits:
|   - validate: 30 req/min
|   - redeem:   10 req/min
|   - general:  60 req/min
*/

Route::prefix('v1')->name('api.v1.')->middleware(['api.auth'])->group(function () {

    // ── Cupones ──────────────────────────────────────────────────
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::post('validate', [CouponController::class, 'validate'])
            ->name('validate')
            ->middleware('throttle:30,1');

        Route::post('redeem', [CouponController::class, 'redeem'])
            ->name('redeem')
            ->middleware('throttle:10,1');

        Route::get('{code}', [CouponController::class, 'show'])
            ->name('show');
    });

    // ── Clientes ─────────────────────────────────────────────────
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::post('register', [CustomerController::class, 'register'])
            ->name('register');

        Route::post('accept-terms', [CustomerController::class, 'acceptTerms'])
            ->name('accept-terms');

        Route::get('{document}', [CustomerController::class, 'show'])
            ->name('show');
    });

    // ── Documentos Legales ───────────────────────────────────────
    Route::prefix('legal')->name('legal.')->withoutMiddleware(['api.auth'])->group(function () {
        Route::get('{type}',                 [LegalController::class, 'show'])    ->name('show');
        Route::get('{type}/versions',        [LegalController::class, 'history']) ->name('history');
        Route::get('{type}/versions/{version}', [LegalController::class, 'version'])->name('version');
    });

    // ── Notificaciones (SMS + Email simultáneo) ──────────────────
    Route::prefix('notify')->name('notify.')->group(function () {
        Route::post('send', [NotifyController::class, 'send'])
            ->name('send')
            ->middleware('throttle:20,1');
    });
});

// Health check (sin auth)
Route::get('health', fn() => response()->json([
    'status' => 'ok',
    'app' => 'CuponesHub',
    'version' => '1.0',
    'timestamp' => now()->toIso8601String(),
]))->name('api.health');
