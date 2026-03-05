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
            // 1. Находим рекомендации для товара (только если отзыв хороший)
            $recommendation = null;
            if ($this->review->rating >= 4) {
                $recommendation = RecommendationProduct::where('source_sku', $this->review->product_sku)
                    ->where('user_id', $this->review->marketplaceConnection->user_id)
                    ->first();
            }

            $recText = $recommendation 
                ? "Посоветуй также наш товар: {$recommendation->recommendation_sku}." 
                : "";

            // 2. Логика обработки в зависимости от рейтинга (LARA-11)
            if ($this->review->rating <= 3) {
                // Промпт для негативного отзыва (извинение, работа с возражениями)
                $aiResponse = "Нам очень жаль, что товар {$this->review->product_sku} не оправдал ваших ожиданий. Мы обязательно разберемся в ситуации. Спасибо за обратную связь, она помогает нам стать лучше.";
            } else {
                // Промпт для позитивного отзыва
                $aiResponse = "Спасибо за ваш отзыв на {$this->review->product_sku}! Нам очень приятно. Мы рады, что вы оценили нас на {$this->review->rating} звезд. {$recText}";
            }

            // 3. Сохраняем результат
            $this->review->update([
                'response_text' => $aiResponse,
                'status' => 'replied'
            ]);

            Log::info("AI Response generated for review {$this->review->id} (Rating: {$this->review->rating})");

        } catch (\Exception $e) {
            $this->review->update(['status' => 'failed']);
            Log::error("AI Processing failed: " . $e->getMessage());
        }
    }
}
