<?php

namespace App\Http\Controllers;

use App\Models\TelegramLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $update = $request->all();

        if (!isset($update['message'])) {
            return response()->json(['status' => 'ok']);
        }

        $chatId = $update['message']['chat']['id'];
        $text = $update['message']['text'] ?? '';
        $userName = $update['message']['from']['username'] ?? 'unknown';

        $response = match($text) {
            '/start' => "Привет! Я бот ReplyZen AI. 🚀\nЗдесь ты сможешь управлять ответами на маркетплейсах.",
            '/status' => "Система работает. Все воркеры активны.",
            default => "Команда не распознана. Используй /start"
        };

        // Сохраняем логи (LARA-13)
        TelegramLog::create([
            'chat_id' => (string)$chatId,
            'user_name' => $userName,
            'message' => $text,
            'bot_response' => $response,
            'action' => str_starts_with($text, '/') ? 'command' : 'message',
            'payload' => $update,
        ]);

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
