<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Генерирует ответ на отзыв с использованием реального Gemini API.
     */
    public function generateResponse(string $reviewText, int $rating, ?string $recommendationSku = null): string
    {
        if (!$this->apiKey) {
            Log::error("AIService: Gemini API Key is missing.");
            return $this->getFallbackResponse($rating);
        }

        $prompt = $this->buildPrompt($reviewText, $rating, $recommendationSku);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                ]
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($aiText) {
                    Log::info("AIService: AI response generated successfully.");
                    return trim($aiText);
                }
            }

            Log::error("AIService: API Request failed", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error("AIService: Exception during API call", ['message' => $e->getMessage()]);
        }

        return $this->getFallbackResponse($rating);
    }

    protected function buildPrompt(string $reviewText, int $rating, ?string $recommendationSku): string
    {
        $prompt = "Ты — профессиональный менеджер по работе с клиентами на маркетплейсе. 
        Твоя задача: вежливо и человечно ответить на отзыв покупателя.
        
        Текст отзыва: \"{$reviewText}\"
        Рейтинг: {$rating} звезд.
        ";

        if ($rating <= 3) {
            $prompt .= "\nИнструкция: Это негативный отзыв. Извинись, прояви эмпатию, пообещай разобраться в ситуации. Не предлагай другие товары.";
        } else {
            $prompt .= "\nИнструкция: Это хороший отзыв. Поблагодари клиента.";
            if ($recommendationSku) {
                $prompt .= " Нативно порекомендуй наш другой товар с артикулом (SKU): {$recommendationSku}.";
            }
        }

        $prompt .= "\n\nОтвет должен быть на русском языке, без лишних вступлений, только текст ответа.";

        return $prompt;
    }

    protected function getFallbackResponse(int $rating): string
    {
        return $rating <= 3 
            ? "Спасибо за обратную связь. Нам жаль, что товар не подошел, мы работаем над улучшением качества."
            : "Спасибо за ваш заказ и высокую оценку! Будем рады видеть вас снова.";
    }
}
