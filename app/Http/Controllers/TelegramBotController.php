<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Update:', $update);

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'] ?? '';

        $response = match($text) {
            '/start' => "Привет! Я бот ReplyZen AI. 🚀\nЗдесь ты сможешь управлять ответами на маркетплейсах.",
            '/status' => "Система работает. Все воркеры активны.",
            default => "Команда не распознана. Используй /start"
        };

        $this->sendMessage($chatId, $response);

        return response()->json(['status' => 'ok']);
    }

    private function sendMessage($chatId, $text)
    {
        $token = config('services.telegram.bot_token');
        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        @file_get_contents($url . "?" . http_build_query([
            'chat_id' => $chatId,
            'text' => $text,
        ]));
    }
}
