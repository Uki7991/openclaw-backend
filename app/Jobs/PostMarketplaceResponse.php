<?php

namespace App\Jobs;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostMarketplaceResponse implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Review $review)
    {}

    public function handle(): void
    {
        // Только если статус 'replied' (AI подготовил ответ)
        if ($this->review->status !== 'replied' || empty($this->review->response_text)) {
            return;
        }

        try {
            $connection = $this->review->marketplaceConnection;
            
            // Здесь должна быть логика работы с API (WB/Ozon)
            // Используем $connection->api_key (который автоматически расшифруется)
            
            Log::info("Posting response to {$connection->marketplace_type} for review {$this->review->external_review_id}");

            // Имитация успешной отправки
            $this->review->update(['status' => 'replied']); // Статус уже такой, но можно добавить 'posted'
            // Для MVP оставим логику фиксации в БД
            
        } catch (\Exception $e) {
            Log::error("Failed to post response: " . $e->getMessage());
            $this->review->update(['status' => 'failed']);
        }
    }
}
