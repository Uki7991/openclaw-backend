<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url}';
    protected $description = 'Set the telegram webhook URL';

    public function handle()
    {
        $url = $this->argument('url');
        $token = config('services.telegram.bot_token');

        if (!$token) {
            $this->error('Telegram bot token not found in config/services.php');
            return;
        }

        $response = Http::post("https://api.telegram.org/bot{$token}/setWebhook", [
            'url' => "{$url}/api/telegram/webhook",
        ]);

        if ($response->successful()) {
            $this->info('Webhook set successfully.');
        } else {
            $this->error('Failed to set webhook: ' . $response->body());
        }
    }
}
