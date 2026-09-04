<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * Get the order that owns the item.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product referenced by the item.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted subtotal in Indonesian Rupiah.
     */
    public function formattedSubtotal(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Rp '.number_format($this->subtotal, 0, ',', '.'),
        );
    }

    /**
     * Get the resolved image URL for this item, prioritizing variant photo if applicable.
     */
    public function itemImageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->product) {
                    return null;
                }

                $variants = $this->product->relationLoaded('variants')
                    ? $this->product->variants
                    : $this->product->variants()->get();

                foreach ($variants as $variant) {
                    if ($variant->variant_image_url && str_contains($this->product_name, $variant->name)) {
                        return $variant->variant_image_url;
                    }
                }

                return $this->product->product_image_url;
            },
        );
    }

    /**
     * Get the review associated with this item in this order.
     */
    public function getReviewAttribute(): ?ProductReview
    {
        if (! $this->relationLoaded('order') || ! $this->order) {
            return ProductReview::where('order_id', $this->order_id)
                ->where('product_id', $this->product_id)
                ->first();
        }

        if ($this->order->relationLoaded('reviews')) {
            return $this->order->reviews->firstWhere('product_id', $this->product_id);
        }

        return $this->order->reviews()->where('product_id', $this->product_id)->first();
    }

    /**
     * Check if this item has already been reviewed.
     */
    public function isReviewed(): bool
    {
        return $this->review !== null;
    }
}
