<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Modules\Chat\Http\Controllers\ChatController;
use App\Http\Middleware\SubscriptionMiddleware;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    SubscriptionMiddleware::class,
])->group(function () {
    
     Route::prefix('chat')->group(function () {
        Route::get('/', [ChatController::class, 'index']);
        Route::get('/conversations', [ChatController::class, 'getConversations']);
        Route::post('/conversations', [ChatController::class, 'store']);
        Route::post('/messages', [ChatController::class, 'sendMessage']);
        Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages']);
    });
    
    // Dashboard الشركة
    Route::get('/dashboard', function () {
        return view('tenant-dashboard', [
            'tenant' => tenant(),
            'plan' => tenant('plan'),
        ]);
    });
});
