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

    public function test_process_review_ai_generates_response_with_recommendation_for_good_rating(): void
    {
        // Setup
        $user = User::factory()->create();
        $conn = MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => 'fake',
            'is_active' => true,
        ]);

        $sku = 'SKU-GOOD';
        $review = Review::create([
            'marketplace_connection_id' => $conn->id,
            'external_review_id' => 'EXT-GOOD',
            'product_sku' => $sku,
            'rating' => 5,
            'review_text' => 'Супер!',
            'status' => 'new',
        ]);

        RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => $sku,
            'recommendation_sku' => 'REC-123',
        ]);

        // Action
        ProcessReviewAI::dispatchSync($review);

        // Assert
        $review->refresh();
        $this->assertStringContainsString('REC-123', $review->response_text);
        $this->assertEquals('replied', $review->status);
    }

    public function test_process_review_ai_handles_bad_rating_without_recommendation(): void
    {
        // Setup
        $user = User::factory()->create();
        $conn = MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => 'fake',
            'is_active' => true,
        ]);

        $sku = 'SKU-BAD';
        $review = Review::create([
            'marketplace_connection_id' => $conn->id,
            'external_review_id' => 'EXT-BAD',
            'product_sku' => $sku,
            'rating' => 2,
            'review_text' => 'Все сломалось!',
            'status' => 'new',
        ]);

        RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => $sku,
            'recommendation_sku' => 'REC-NO-SHOW',
        ]);

        // Action
        ProcessReviewAI::dispatchSync($review);

        // Assert
        $review->refresh();
        $this->assertStringNotContainsString('REC-NO-SHOW', $review->response_text);
        $this->assertStringContainsString('Нам очень жаль', $review->response_text);
        $this->assertEquals('replied', $review->status);
    }
}
