<?php

namespace App\Console\Commands;

use App\Models\MarketplaceConnection;
use App\Models\Review;
use Illuminate\Console\Command;

class SyncMarketplaceReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync reviews from marketplaces via API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connections = MarketplaceConnection::where('is_active', true)->get();

        if ($connections->isEmpty()) {
            $this->info('No active marketplace connections found.');
            return;
        }

        foreach ($connections as $connection) {
            $this->info("Syncing reviews for {$connection->marketplace_type} (User: {$connection->user_id})...");
            
            // Mock API logic: generating 1 random review for simulation
            $mockReviewId = 'EXT-' . rand(1000, 9999);
            
            Review::updateOrCreate(
                [
                    'marketplace_connection_id' => $connection->id,
                    'external_review_id' => $mockReviewId,
                ],
                [
                    'product_sku' => 'SKU-MOCK-' . rand(1, 5),
                    'rating' => rand(1, 5),
                    'review_text' => 'Это тестовый отзыв для проверки синхронизации.',
                    'status' => 'new',
                ]
            );
            
            $this->info("Synced review {$mockReviewId}.");
        }

        $this->info('Sync completed successfully.');
    }
}
