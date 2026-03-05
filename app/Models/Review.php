<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'marketplace_connection_id',
        'external_review_id',
        'product_sku',
        'rating',
        'review_text',
        'status',
        'response_text',
    ];

    public function marketplaceConnection(): BelongsTo
    {
        return $this->belongsTo(MarketplaceConnection::class);
    }
}
