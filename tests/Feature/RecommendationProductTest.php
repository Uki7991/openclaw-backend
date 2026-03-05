<?php

namespace Tests\Feature;

use App\Models\RecommendationProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test creating and retrieving recommendation products for a specific source SKU.
     */
    public function test_can_create_and_retrieve_recommendations_for_sku(): void
    {
        // 1. Setup
        $user = User::factory()->create();
        $sourceSku = 'SKU-123-MAIN';
        
        // 2. Action: Create recommendations
        $rec1 = RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => $sourceSku,
            'recommendation_sku' => 'SKU-REC-001',
            'recommendation_url' => 'https://example.com/product/001',
        ]);

        $rec2 = RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => $sourceSku,
            'recommendation_sku' => 'SKU-REC-002',
            // url is optional
        ]);

        // Create a distraction (another SKU)
        RecommendationProduct::create([
            'user_id' => $user->id,
            'source_sku' => 'OTHER-SKU',
            'recommendation_sku' => 'SKU-REC-999',
        ]);

        // 3. Assertions
        $this->assertDatabaseCount('recommendation_products', 3);

        // Retrieve via Eloquent
        $recommendations = RecommendationProduct::where('source_sku', $sourceSku)->get();

        $this->assertCount(2, $recommendations);
        $this->assertEquals('SKU-REC-001', $recommendations->first()->recommendation_sku);
        $this->assertEquals('SKU-REC-002', $recommendations->last()->recommendation_sku);
    }
}
