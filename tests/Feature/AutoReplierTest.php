<?php

namespace Tests\Feature;

use App\Jobs\PostMarketplaceResponse;
use App\Models\MarketplaceConnection;
use App\Models\Review; // Добавлено!
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AutoReplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_replier_job_logs_correct_info(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(fn($message) => str_contains($message, 'Posting response to wb'));

        // Setup
        $user = User::factory()->create();
        $conn = MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => 'secret-key-123',
            'is_active' => true,
        ]);

        $review = Review::create([
            'marketplace_connection_id' => $conn->id,
            'external_review_id' => 'EXT-777',
            'product_sku' => 'SKU-1',
            'rating' => 5,
            'review_text' => 'Good!',
            'response_text' => 'Thank you!',
            'status' => 'replied',
        ]);

        // Action
        PostMarketplaceResponse::dispatchSync($review);

        // Assert
        $this->assertEquals('replied', $review->status);
    }
}
