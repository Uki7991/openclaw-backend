<?php

namespace Tests\Feature;

use App\Jobs\ProcessReviewAI;
use App\Models\MarketplaceConnection;
use App\Models\RecommendationProduct;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessReviewAITest extends TestCase
{
    use RefreshDatabase;

    public function test_process_review_ai_generates_response_with_recommendation(): void
    {
        // 1. Setup
        $user = User::factory()->create();
        $conn = MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => 'fake',
            'is_active' => true,
        ]);

        $sku = 'SKU-123';
        $review = Review::create([
            'marketplace_connection_id' => $conn->id,
            'external_review_id' => 'EXT-1',
            'product_sku' => $sku,
            'rating' => 5,
            'review_text' => 'Отличный товар!',
            'status' => 'new',
        ]);

        RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => $sku,
            'recommendation_sku' => 'REC-456',
        ]);

        // 2. Action
        ProcessReviewAI::dispatchSync($review);

        // 3. Assertions
        $review->refresh();
        $this->assertEquals('replied', $review->status);
        $this->assertStringContainsString('REC-456', $review->response_text);
        $this->assertStringContainsString('5 звезд', $review->response_text);
    }
}
