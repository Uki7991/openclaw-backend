<?php

namespace App\Jobs;

use App\Models\Review;
use App\Models\RecommendationProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessReviewAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Review $review)
    {}

    public function handle(): void
    {
        $this->review->update(['status' => 'processing']);

        try {
            // 1. Находим рекомендации для товара (из LARA-8)
            $recommendation = RecommendationProduct::where('source_sku', $this->review->product_sku)
                ->where('user_id', $this->review->marketplaceConnection->user_id)
                ->first();

            $recText = $recommendation 
                ? "Посоветуй также наш товар: {$recommendation->recommendation_sku}." 
                : "";

            // 2. Mock ИИ-генерации (LARA-11)
            // В реальной задаче здесь будет вызов Gemini API
            $aiResponse = "Спасибо за ваш отзыв на {$this->review->product_sku}! Нам очень приятно. Мы рады, что вы оценили нас на {$this->review->rating} звезд. {$recText}";

            // 3. Сохраняем результат
            $this->review->update([
                'response_text' => $aiResponse,
                'status' => 'replied'
            ]);

            Log::info("AI Response generated for review {$this->review->id}");

        } catch (\Exception $e) {
            $this->review->update(['status' => 'failed']);
            Log::error("AI Processing failed: " . $e->getMessage());
        }
    }
}
