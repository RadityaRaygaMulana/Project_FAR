<?php

namespace App\Models;

use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'category',
        'type',
        'reward_amount',
        'max_discount',
        'min_spend',
        'description',
        'quota',
        'claimed_count',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'member_tier',
        'is_weekly_recurring',
        'weekly_day_rule',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reward_amount' => 'integer',
            'max_discount' => 'integer',
            'min_spend' => 'integer',
            'quota' => 'integer',
            'claimed_count' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'is_active' => 'boolean',
            'is_weekly_recurring' => 'boolean',
        ];
    }

    /**
     * Get user claims for this voucher.
     *
     * @return HasMany<UserVoucher, $this>
     */
    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    /**
     * Get users who have claimed this voucher.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_vouchers')
            ->withPivot(['claimed_at', 'used_at', 'order_id'])
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active and unexpired vouchers.
     *
     * @param  Builder<Voucher>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope a query to only include shipping vouchers.
     *
     * @param  Builder<Voucher>  $query
     */
    public function scopeShipping(Builder $query): void
    {
        $query->where('category', 'shipping');
    }

    /**
     * Scope a query to only include product discount vouchers.
     *
     * @param  Builder<Voucher>  $query
     */
    public function scopeDiscount(Builder $query): void
    {
        $query->where('category', 'discount');
    }

    /**
     * Check if this voucher is claimed by a specific user (within the current week if weekly recurring).
     */
    public function isClaimedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->is_weekly_recurring) {
            return $this->userVouchers()
                ->where('user_id', $user->id)
                ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->exists();
        }

        return $this->userVouchers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if this voucher has been used by a specific user (within the current week if weekly recurring).
     */
    public function isUsedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->is_weekly_recurring) {
            return $this->userVouchers()
                ->where('user_id', $user->id)
                ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->whereNotNull('used_at')
                ->exists();
        }

        return $this->userVouchers()
            ->where('user_id', $user->id)
            ->whereNotNull('used_at')
            ->exists();
    }

    /**
     * Check if a specific user has an active, unused claim on this voucher (within the current week if weekly recurring).
     */
    public function isAvailableForUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->is_weekly_recurring) {
            return $this->userVouchers()
                ->where('user_id', $user->id)
                ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->whereNull('used_at')
                ->exists();
        }

        return $this->userVouchers()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->exists();
    }

    /**
     * Get claimed count for the current weekly period (or total count if regular).
     */
    public function getCurrentPeriodClaimedCount(): int
    {
        if ($this->is_weekly_recurring) {
            return $this->userVouchers()
                ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
        }

        return (int) $this->claimed_count;
    }

    /**
     * Check if this voucher has remaining quota (enforcing weekly quota cap for weekly recurring).
     */
    public function hasRemainingQuota(): bool
    {
        if ($this->is_weekly_recurring) {
            return $this->quota > $this->getCurrentPeriodClaimedCount();
        }

        return $this->quota > $this->claimed_count;
    }

    /**
     * Check if the voucher is applicable for the given subtotal.
     */
    public function isApplicable(int|float $subtotal): bool
    {
        return $subtotal >= $this->min_spend;
    }

    /**
     * Calculate discount amount given subtotal and current shipping cost.
     */
    public function calculateDeduction(int|float $subtotal, int|float $shippingCost = 15000): int
    {
        if (! $this->isApplicable($subtotal)) {
            return 0;
        }

        if ($this->category === 'shipping') {
            if ($this->type === 'percentage') {
                $discount = (int) round($shippingCost * ($this->reward_amount / 100));
                if ($this->max_discount) {
                    $discount = min($discount, $this->max_discount);
                }

                return min((int) $shippingCost, $discount);
            }

            return min((int) $shippingCost, (int) $this->reward_amount);
        }

        // Discount category
        if ($this->type === 'percentage') {
            $discount = (int) round($subtotal * ($this->reward_amount / 100));
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }

            return min((int) $subtotal, $discount);
        }

        return min((int) $subtotal, (int) $this->reward_amount);
    }

    /**
     * Get human-readable reward label.
     */
    public function getFormattedRewardAttribute(): string
    {
        if ($this->category === 'shipping') {
            if ($this->reward_amount >= 15000 && $this->type === 'fixed') {
                return 'Gratis Ongkir';
            }

            return 'Potongan Ongkir Rp '.number_format($this->reward_amount, 0, ',', '.');
        }

        if ($this->type === 'percentage') {
            $text = 'Diskon '.$this->reward_amount.'%';
            if ($this->max_discount) {
                $text .= ' s/d Rp '.number_format($this->max_discount, 0, ',', '.');
            }

            return $text;
        }

        return 'Diskon Rp '.number_format($this->reward_amount, 0, ',', '.');
    }

    /**
     * Get human-readable minimum spend requirement.
     */
    public function getFormattedMinSpendAttribute(): string
    {
        if ($this->min_spend <= 0) {
            return 'Tanpa Min. Belanja';
        }

        return 'Min. Belanja Rp '.number_format($this->min_spend, 0, ',', '.');
    }

    /**
     * Get category badge label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category === 'shipping' ? 'Gratis Ongkir' : 'Diskon Belanja';
    }

    /**
     * Get member tier formatted label.
     */
    public function getMemberTierLabelAttribute(): string
    {
        return match ($this->member_tier) {
            'platinum' => 'Khusus Platinum VIP 👑',
            'gold' => 'Khusus Gold & Platinum 🥇',
            'silver' => 'Silver Member+ 🥈',
            default => 'Semua Member 🌟',
        };
    }

    /**
     * Get member tier short badge text.
     */
    public function getMemberTierBadgeAttribute(): string
    {
        return match ($this->member_tier) {
            'platinum' => '👑 Platinum VIP',
            'gold' => '🥇 Gold & Up',
            'silver' => '🥈 Silver & Up',
            default => '🌟 Semua Member',
        };
    }

    /**
     * Check if weekly recurring schedule applies today.
     */
    public function isApplicableForToday(): bool
    {
        if (! $this->is_weekly_recurring) {
            return true;
        }

        $rule = strtolower($this->weekly_day_rule ?: 'all');

        return match ($rule) {
            'weekdays' => now()->isWeekday(),
            'weekends' => now()->isWeekend(),
            'monday' => now()->isMonday(),
            'tuesday' => now()->isTuesday(),
            'wednesday' => now()->isWednesday(),
            'thursday' => now()->isThursday(),
            'friday' => now()->isFriday(),
            'saturday' => now()->isSaturday(),
            'sunday' => now()->isSunday(),
            default => true,
        };
    }

    /**
     * Check whether a user is eligible to claim/use this voucher based on member tier and schedule.
     */
    public function isEligibleForUser(?User $user): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->isApplicableForToday()) {
            return false;
        }

        if (empty($this->member_tier) || $this->member_tier === 'all') {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->meetsTierRequirement($this->member_tier);
    }
}
