<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'store_id',
        'name',
        'brand',
        'badge',
        'slug',
        'description',
        'price',
        'discount_price',
        'weight_grams',
        'spiciness_level',
        'stock',
        'rating',
        'sold_count',
        'image_url',
        'image_path',
        'gallery_images',
        'is_featured',
        'is_available',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'discount_price' => 'integer',
            'weight_grams' => 'integer',
            'spiciness_level' => 'integer',
            'stock' => 'integer',
            'rating' => 'float',
            'sold_count' => 'integer',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'gallery_images' => 'array',
        ];
    }

    /**
     * Get the category that owns the product.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the store that owns the product.
     *
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the order items for the product.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the variants for this product.
     *
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the reviews for this product.
     *
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the resolved image URL: uploaded file takes priority over image_url.
     */
    public function productImageUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
                    return Storage::url($this->image_path);
                }

                return $this->image_url ?: null;
            },
        );
    }

    /**
     * Get all resolved image URLs for the product (primary image + gallery images).
     *
     * @return list<string>
     */
    public function allImageUrls(): array
    {
        $urls = [];

        if ($this->product_image_url) {
            $urls[] = $this->product_image_url;
        }

        if (is_array($this->gallery_images)) {
            foreach ($this->gallery_images as $path) {
                if ($path && Storage::disk('public')->exists($path)) {
                    $url = Storage::url($path);
                    if (! in_array($url, $urls, true)) {
                        $urls[] = $url;
                    }
                } elseif ($path && filter_var($path, FILTER_VALIDATE_URL)) {
                    if (! in_array($path, $urls, true)) {
                        $urls[] = $path;
                    }
                }
            }
        }

        return $urls;
    }

    /**
     * Determine if the product has a valid discount.
     */
    public function hasDiscount(): bool
    {
        return ! is_null($this->discount_price) && $this->discount_price < $this->price;
    }

    /**
     * Get the effective price considering discount.
     */
    public function effectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn (): int => $this->hasDiscount() ? (int) $this->discount_price : (int) $this->price,
        );
    }

    /**
     * Get discount percentage.
     */
    public function discountPercentage(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                if (! $this->hasDiscount() || $this->price <= 0) {
                    return 0;
                }

                return (int) round((($this->price - $this->discount_price) / $this->price) * 100);
            },
        );
    }

    /**
     * Get formatted regular price in Indonesian Rupiah.
     */
    public function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Rp '.number_format($this->price, 0, ',', '.'),
        );
    }

    /**
     * Get formatted discount price in Indonesian Rupiah.
     */
    public function formattedDiscountPrice(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->discount_price !== null ? 'Rp '.number_format($this->discount_price, 0, ',', '.') : null,
        );
    }

    /**
     * Get formatted effective price in Indonesian Rupiah.
     */
    public function formattedEffectivePrice(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Rp '.number_format($this->hasDiscount() ? $this->discount_price : $this->price, 0, ',', '.'),
        );
    }

    /**
     * Get seller origin city for marketplace card.
     */
    public function originCity(): Attribute
    {
        $cities = ['Kota Bandung', 'Jakarta Selatan', 'Kota Surabaya', 'Kab. Tangerang', 'Kota Semarang', 'Kota Medan', 'Kota Yogyakarta'];

        return Attribute::make(
            get: fn (): string => $cities[$this->id % count($cities)],
        );
    }

    /**
     * Scope a query to only include available products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true)->where('stock', '>', 0);
    }

    /**
     * Scope a query to only include featured products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
