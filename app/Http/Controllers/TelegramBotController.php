<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceConnection;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        Log::info("Telegram Message from {$chatId}: {$text}");

        if ($text === '/start') {
            $this->sendMessage($chatId, "Привет! Я ReplyZen AI Bot. \n\nИспользуй /status для проверки отзывов.");
        } elseif ($text === '/status') {
            $count = Review::where('status', 'new')->count();
            $this->sendMessage($chatId, "Новых отзывов в очереди: {$count}");
        } else {
            $this->sendMessage($chatId, "Команда не распознана. Доступные команды: /start, /status");
        }

        return response()->json(['status' => 'ok']);
    }

    private function sendMessage($chatId, $text)
    {
        $token = config('services.telegram.bot_token');
        if (!$token) return;

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }
}
