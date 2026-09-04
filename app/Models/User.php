<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
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
        return $this->store !== null && $this->store->isApproved();
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
}
