<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVoucher extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'voucher_id',
        'claimed_at',
        'used_at',
        'order_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Get the user who claimed this voucher.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the associated voucher definition.
     *
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Get the order where this voucher was used.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include unused vouchers.
     *
     * @param  Builder<UserVoucher>  $query
     */
    public function scopeUnused(Builder $query): void
    {
        $query->whereNull('used_at');
    }

    /**
     * Scope a query to only include used vouchers.
     *
     * @param  Builder<UserVoucher>  $query
     */
    public function scopeUsed(Builder $query): void
    {
        $query->whereNotNull('used_at');
    }
}
