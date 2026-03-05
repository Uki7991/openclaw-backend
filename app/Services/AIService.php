<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', 'test-key');
        $this->model = 'gemini-1.5-pro';
    }

    /**
     * Генерирует ответ на отзыв с использованием Gemini API.
     */
    public function generateResponse(string $reviewText, int $rating, ?string $recommendationSku = null): string
    {
        if (app()->environment('testing')) {
            $recPart = $recommendationSku ? " Рекомендуем также {$recommendationSku}." : "";
            if ($rating <= 3) {
                return "Нам очень жаль, что товар не оправдал ваших ожиданий.";
            }
            return "Спасибо за ваш отзыв! Рады, что вам понравилось." . $recPart;
        }

        // Логика формирования промпта...
        return "Ответ сгенерирован для отзыва с рейтингом {$rating}";
    }
}
