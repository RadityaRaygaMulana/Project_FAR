<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductReview extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'review',
        'variant_name',
        'photos',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'photos' => 'array',
        ];
    }

    /**
     * Get the product being reviewed.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user author of this review.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated order.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the likes for this review.
     *
     * @return HasMany<ProductReviewLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(ProductReviewLike::class);
    }

    /**
     * Get the comments/replies for this review.
     *
     * @return HasMany<ProductReviewComment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ProductReviewComment::class)->oldest();
    }

    /**
     * Check if a given user has liked this review.
     */
    public function isLikedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('likes')) {
            return $this->likes->contains('user_id', $user->id);
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * Get accessible URLs for the review photos.
     *
     * @return list<string>
     */
    public function getPhotoUrlsAttribute(): array
    {
        if (empty($this->photos) || ! is_array($this->photos)) {
            return [];
        }

        return array_values(array_map(function ($photo) {
            if (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://')) {
                return $photo;
            }

            return asset('storage/'.ltrim($photo, '/'));
        }, $this->photos));
    }
}
