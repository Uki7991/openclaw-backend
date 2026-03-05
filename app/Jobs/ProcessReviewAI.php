<?php

namespace App\Jobs;

use App\Models\Review;
use App\Models\RecommendationProduct;
use App\Services\AIService; // Используем сервис
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

    public function handle(AIService $aiService): void
    {
        $this->review->update(['status' => 'processing']);

        try {
            $recommendation = null;
            if ($this->review->rating >= 4) {
                $recommendation = RecommendationProduct::where('source_sku', $this->review->product_sku)
                    ->where('user_id', $this->review->marketplaceConnection->user_id)
                    ->first();
            }

            // Вызов централизованного сервиса
            $aiResponse = $aiService->generateResponse(
                $this->review->review_text,
                $this->review->rating,
                $recommendation?->recommendation_sku
            );

            $this->review->update([
                'response_text' => $aiResponse,
                'status' => 'replied'
            ]);

            Log::info("AI Response generated via AIService for review {$this->review->id}");

        } catch (\Exception $e) {
            $this->review->update(['status' => 'failed']);
            Log::error("AI Processing failed: " . $e->getMessage());
        }
    }
}
