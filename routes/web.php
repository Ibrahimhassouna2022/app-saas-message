<?php

use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return "Central App";
        });
    });
}

Route::prefix('superadmin')->group(function () {
    Route::get('/companies', [SuperAdminController::class, 'index']);
    Route::post('/companies', [SuperAdminController::class, 'registerCompany']);
    Route::put('/companies/{tenantId}/subscription', [SuperAdminController::class, 'updateSubscription']);
    Route::delete('/companies/{tenantId}', [SuperAdminController::class, 'deleteCompany']);
});
 