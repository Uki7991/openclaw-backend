<?php

namespace Tests\Feature;

use App\Models\MarketplaceConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketplaceConnectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that API key is encrypted in the database and decrypted on access.
     */
    public function test_api_key_is_stored_securely(): void
    {
        // 1. Setup
        $user = User::factory()->create();
        $rawKey = 'wb-secret-api-key-12345';

        // 2. Action
        $connection = MarketplaceConnection::create([
            'user_id' => $user->id,
            'marketplace_type' => 'wb',
            'api_key' => $rawKey,
            'is_active' => true,
        ]);

        // 3. Assertions
        
        // Verify model decryption
        $this->assertEquals($rawKey, $connection->api_key);

        // Verify database encryption (the raw key should NOT be in the DB column)
        $dbRecord = DB::table('marketplace_connections')->where('id', $connection->id)->first();
        $this->assertNotEquals($rawKey, $dbRecord->api_key);
        $this->assertStringContainsString('eyJpdiI6', $dbRecord->api_key); // Standard Laravel encryption prefix
    }
}
