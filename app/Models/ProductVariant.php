<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'name',
        'price',
        'stock',
        'image_url',
        'image_path',
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
            'stock' => 'integer',
        ];
    }

    /**
     * Get the product that owns this variant.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the resolved image URL: uploaded file takes priority over image_url.
     */
    public function variantImageUrl(): Attribute
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
}
