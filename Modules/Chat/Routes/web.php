<?php

use Illuminate\Support\Facades\Route;
use Modules\Chat\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Chat Module Routes (مسارات المراسلات الخاصة بكل شركة)
|--------------------------------------------------------------------------
*/

Route::prefix('chat')->group(function () {
    Route::get('/', [ChatController::class, 'index']);
    Route::get('/conversations', [ChatController::class, 'getConversations']);
    Route::post('/messages', [ChatController::class, 'sendMessage']);
    Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages']);
});
