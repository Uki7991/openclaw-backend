<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Роут для вебхука телеграм (LARA-13)
Route::post('/api/telegram/webhook', [TelegramBotController::class, 'handle'])->name('telegram.webhook');
