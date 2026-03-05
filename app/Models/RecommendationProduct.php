<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendationProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'source_sku',
        'recommendation_sku',
        'recommendation_url',
    ];

    /**
     * Get the user that owns the recommendation product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
