<?php

namespace Tests\Feature;

use App\Models\MarketplaceConnection;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SyncMarketplaceReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_reviews_command_creates_reviews(): void
    {
        // 1. Setup
        $user = User::factory()->create();
        MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => 'fake-key',
            'is_active' => true,
        ]);

        // 2. Action
        Artisan::call('sync:reviews');

        // 3. Assertions
        $this->assertDatabaseCount('reviews', 1);
        $review = Review::first();
        $this->assertEquals('new', $review->status);
        $this->assertStringStartsWith('SKU-MOCK-', $review->product_sku);
    }
}
