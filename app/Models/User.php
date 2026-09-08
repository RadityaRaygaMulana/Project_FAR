<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'username',
    'email',
    'phone',
    'avatar',
    'address_label',
    'recipient_name',
    'recipient_phone',
    'province',
    'city',
    'district',
    'postal_code',
    'address_detail',
    'map_notes',
    'latitude',
    'longitude',
    'default_address',
    'favorite_spiciness',
    'password',
    'role',
    'otp_code',
    'otp_expires_at',
    'is_suspended',
    'suspension_reason',
    'suspended_at',
    'suspended_until',
])]
#[Hidden(['password', 'remember_token', 'otp_code'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'favorite_spiciness' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'otp_expires_at' => 'datetime',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
            'suspended_until' => 'datetime',
        ];
    }

    /**
     * Check if this user account is currently suspended by an administrator.
     */
    public function isSuspended(): bool
    {
        if (! $this->is_suspended) {
            return false;
        }

        if ($this->suspended_until && now()->greaterThanOrEqualTo($this->suspended_until)) {
            $this->update([
                'is_suspended' => false,
                'suspension_reason' => null,
                'suspended_at' => null,
                'suspended_until' => null,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get user avatar URL with graceful fallback.
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (! $this->avatar) {
                    return null;
                }
                if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
                    return $this->avatar;
                }

                return asset('storage/'.$this->avatar);
            }
        );
    }

    /**
     * Get formatted full delivery address.
     */
    public function formattedAddress(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->address_detail || $this->city || $this->province) {
                    $parts = array_filter([
                        $this->address_detail,
                        $this->district ? 'Kec. '.$this->district : null,
                        $this->city,
                        $this->province,
                        $this->postal_code,
                    ]);

                    return implode(', ', $parts);
                }

                return $this->default_address ?? '';
            }
        );
    }

    /**
     * Determine if the user has completed their delivery address in settings.
     */
    public function hasCompleteAddress(): bool
    {
        $hasRecipient = ! empty($this->recipient_name) && ! empty($this->recipient_phone);
        $hasAddress = ! empty($this->address_detail) || ! empty($this->default_address);

        return $hasRecipient && $hasAddress;
    }

    /**
     * Get the orders placed by the user.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user's registered store.
     *
     * @return HasOne<Store, $this>
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    /**
     * Check if user is an approved seller.
     */
    public function isSeller(): bool
    {
        return $this->store !== null && in_array($this->store->status, ['approved', 'closed']);
    }

    /**
     * Check if user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get conversations where the user is the customer/buyer.
     *
     * @return HasMany<Conversation, $this>
     */
    public function buyerConversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get user voucher claim records.
     *
     * @return HasMany<UserVoucher, $this>
     */
    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    /**
     * Get vouchers claimed by this user.
     *
     * @return BelongsToMany<Voucher, $this>
     */
    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'user_vouchers')
            ->withPivot(['claimed_at', 'used_at', 'order_id'])
            ->withTimestamps();
    }

    /**
     * Get product review likes by this user.
     *
     * @return HasMany<ProductReviewLike, $this>
     */
    public function reviewLikes(): HasMany
    {
        return $this->hasMany(ProductReviewLike::class);
    }

    /**
     * Get comments on product reviews written by this user.
     *
     * @return HasMany<ProductReviewComment, $this>
     */
    public function reviewComments(): HasMany
    {
        return $this->hasMany(ProductReviewComment::class);
    }

    /**
     * Get the user's membership tier ('silver', 'gold', 'platinum').
     */
    public function getMemberTierAttribute(): string
    {
        if ($this->role === 'admin') {
            return 'platinum';
        }

        $totalSpent = (float) $this->orders()->whereIn('status', ['paid', 'processing', 'completed'])->sum('grand_total');
        $totalOrders = $this->orders()->count();

        if ($totalSpent >= 1000000 || $totalOrders >= 10) {
            return 'platinum';
        }

        if ($totalSpent >= 250000 || $totalOrders >= 3) {
            return 'gold';
        }

        return 'silver';
    }

    /**
     * Get formatted member tier title with badge.
     */
    public function getMemberTierLabelAttribute(): string
    {
        return match ($this->member_tier) {
            'platinum' => '👑 Platinum VIP',
            'gold' => '🥇 Gold Member',
            default => '🥈 Silver Member',
        };
    }

    /**
     * Check if this user meets or exceeds a required membership tier.
     */
    public function meetsTierRequirement(?string $requiredTier): bool
    {
        if (empty($requiredTier) || $requiredTier === 'all') {
            return true;
        }

        $hierarchy = [
            'silver' => 1,
            'gold' => 2,
            'platinum' => 3,
        ];

        $userLevel = $hierarchy[$this->member_tier] ?? 1;
        $reqLevel = $hierarchy[strtolower($requiredTier)] ?? 1;

        return $userLevel >= $reqLevel;
    }

    public function suspensionAppeals()
    {
        return $this->hasMany(SuspensionAppeal::class);
    }
}
