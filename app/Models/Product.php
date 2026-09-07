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
        'allowed_payment_methods',
        'is_free_shipping',
        'free_shipping_min_spend',
        'allow_vouchers',
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
            'allowed_payment_methods' => 'array',
            'is_free_shipping' => 'boolean',
            'free_shipping_min_spend' => 'integer',
            'allow_vouchers' => 'boolean',
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
        return Attribute::make(
            get: function (): string {
                // Real store city from seller profile
                if ($this->store && ! empty($this->store->city)) {
                    $city = trim($this->store->city);
                    if (! str_starts_with(strtolower($city), 'kota ') && ! str_starts_with(strtolower($city), 'kab. ') && ! str_starts_with(strtolower($city), 'kabupaten ')) {
                        return 'Kota '.$city;
                    }

                    return $city;
                }

                // Real owner user city if store city is empty
                if ($this->store && $this->store->user && ! empty($this->store->user->city)) {
                    $userCity = trim($this->store->user->city);
                    if (! str_starts_with(strtolower($userCity), 'kota ') && ! str_starts_with(strtolower($userCity), 'kab. ') && ! str_starts_with(strtolower($userCity), 'kabupaten ')) {
                        return 'Kota '.$userCity;
                    }

                    return $userCity;
                }

                return 'Kota Bandung';
            },
        );
    }

    /**
     * Get seller full origin address for marketplace card / shipping tooltip.
     */
    public function originAddress(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $parts = [];
                if ($this->store && ! empty($this->store->address_detail)) {
                    $parts[] = trim($this->store->address_detail);
                }
                $parts[] = $this->origin_city;
                if ($this->store && ! empty($this->store->province)) {
                    $parts[] = trim($this->store->province);
                }

                return implode(', ', array_filter($parts));
            },
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

    /**
     * Get the effective allowed payment methods for this product.
     *
     * @return array<int, string>
     */
    public function getAllowedPaymentMethodsAttribute(): array
    {
        $methods = $this->attributes['allowed_payment_methods'] ?? null;
        if ($methods) {
            $decoded = is_string($methods) ? json_decode($methods, true) : $methods;
            if (is_array($decoded) && ! empty($decoded)) {
                return array_values($decoded);
            }
        }

        return ['qris', 'cod'];
    }

    /**
     * Check whether a specific payment method is supported by this product.
     */
    public function allowsPaymentMethod(string $method): bool
    {
        return in_array($method, $this->allowed_payment_methods, true);
    }

    /**
     * Check whether this product belongs to the given user's store.
     */
    public function isOwnedBy(User|int|null $user): bool
    {
        if (! $user) {
            return false;
        }

        $userId = $user instanceof User ? $user->id : (int) $user;

        return (int) ($this->store?->user_id) === $userId;
    }

    /**
     * Check whether this product qualifies for free shipping (optionally against subtotal).
     */
    public function hasFreeShipping(?int $subtotal = null): bool
    {
        if (! $this->is_free_shipping) {
            return false;
        }

        if ($this->free_shipping_min_spend > 0 && $subtotal !== null) {
            return $subtotal >= $this->free_shipping_min_spend;
        }

        return true;
    }

    /**
     * Check whether this product allows vouchers.
     */
    public function allowsVouchers(): bool
    {
        return (bool) $this->allow_vouchers;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve the model for a bound value (supports both slug and id).
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (is_numeric($value)) {
            return $this->where('id', (int) $value)->first() ?? $this->where('slug', $value)->first();
        }

        return $this->where('slug', $value)->first() ?? $this->where('id', $value)->first();
    }
}
