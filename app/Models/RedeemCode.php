<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $discount_type
 * @property int $discount_amount
 * @property int|null $max_discount
 * @property int $shipping_discount
 * @property int $min_spend
 * @property int|null $quota
 * @property int $used_count
 * @property bool $is_active
 */
class RedeemCode extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_amount',
        'max_discount',
        'shipping_discount',
        'min_spend',
        'quota',
        'used_count',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'discount_amount' => 'integer',
        'max_discount' => 'integer',
        'shipping_discount' => 'integer',
        'min_spend' => 'integer',
        'quota' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope: only active redeem codes.
     *
     * @param  Builder<RedeemCode>  $query
     * @return Builder<RedeemCode>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this code still has remaining quota.
     */
    public function hasRemainingQuota(): bool
    {
        if ($this->quota === null) {
            return true;
        }

        return $this->used_count < $this->quota;
    }

    /**
     * Calculate the product discount based on subtotal.
     */
    public function calculateProductDiscount(int|float $subtotal): int
    {
        if ($subtotal < $this->min_spend) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            $disc = (int) round($subtotal * ($this->discount_amount / 100));
            if ($this->max_discount !== null) {
                $disc = min($disc, $this->max_discount);
            }

            return min($disc, (int) $subtotal);
        }

        return min($this->discount_amount, (int) $subtotal);
    }

    /**
     * Returns a human-readable label for the discount.
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            $label = "Diskon {$this->discount_amount}%";
            if ($this->max_discount) {
                $label .= ' (maks Rp '.number_format($this->max_discount, 0, ',', '.').')';
            }

            return $label;
        }

        return 'Rp '.number_format($this->discount_amount, 0, ',', '.');
    }
}
