<?php

namespace App\Models;

use App\Services\AuditLogger;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_notes',
        'payment_method',
        'coupon_code',
        'redeem_code',
        'redeem_discount_amount',
        'shipping_voucher_code',
        'discount_voucher_code',
        'discount_amount',
        'total_amount',
        'shipping_cost',
        'shipping_discount_amount',
        'shipping_courier',
        'tracking_number',
        'grand_total',
        'status',
        'delivered_at',
        'completed_at',
        'payment_status',
        'cancellation_status',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_responded_at',
        'cancellation_response_note',
        'return_status',
        'return_reason',
        'return_description',
        'return_proof_image',
        'return_requested_at',
        'return_responded_at',
        'return_response_note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'shipping_cost' => 'integer',
            'shipping_discount_amount' => 'integer',
            'grand_total' => 'integer',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancellation_requested_at' => 'datetime',
            'cancellation_responded_at' => 'datetime',
            'return_requested_at' => 'datetime',
            'return_responded_at' => 'datetime',
        ];
    }

    /**
     * Get the user that placed the order.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get user voucher usages for this order.
     *
     * @return HasMany<UserVoucher, $this>
     */
    public function userVouchers(): HasMany
    {
        return $this->hasMany(UserVoucher::class);
    }

    /**
     * Get the product reviews associated with this order.
     *
     * @return HasMany<ProductReview, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get order items that haven't been reviewed yet.
     *
     * @return Collection<int, OrderItem>
     */
    public function unreviewedItems(): Collection
    {
        if (strtolower((string) $this->status) !== 'completed') {
            return collect();
        }

        $reviewedProductIds = $this->reviews->pluck('product_id')->all();

        return $this->items->filter(function (OrderItem $item) use ($reviewedProductIds) {
            return ! in_array($item->product_id, $reviewedProductIds);
        });
    }

    /**
     * Check if this completed order has items waiting to be rated.
     */
    public function hasUnreviewedItems(): bool
    {
        return strtolower((string) $this->status) === 'completed' && $this->unreviewedItems()->isNotEmpty();
    }

    /**
     * Check if all items in this completed order have been rated.
     */
    public function isFullyReviewed(): bool
    {
        return strtolower((string) $this->status) === 'completed' && $this->items->isNotEmpty() && $this->unreviewedItems()->isEmpty();
    }

    /**
     * Generate a unique order code for new orders.
     */
    public static function generateOrderCode(): string
    {
        do {
            $code = 'SNK-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('order_code', $code)->exists());

        return $code;
    }

    /**
     * Get formatted grand total in Indonesian Rupiah.
     */
    public function formattedGrandTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Rp '.number_format($this->grand_total, 0, ',', '.'),
        );
    }

    /**
     * Check if cancellation is currently requested and awaiting response.
     */
    public function isCancellationPending(): bool
    {
        return $this->cancellation_status === 'requested';
    }

    /**
     * Determine if order can be cancelled by the buyer.
     */
    public function canBeCancelledByBuyer(): bool
    {
        return in_array(strtolower($this->status), ['pending', 'processing'])
            && $this->cancellation_status !== 'requested'
            && $this->cancellation_status !== 'approved';
    }

    /**
     * Automatically cancel orders where cancellation request has exceeded 3 days (72 hours).
     */
    public static function autoCancelExpiredRequests(): int
    {
        $cutoff = now()->subDays(3);
        $expiredOrders = static::whereIn('status', ['pending', 'processing'])
            ->where('cancellation_status', 'requested')
            ->where('cancellation_requested_at', '<=', $cutoff)
            ->with(['items.product.variants'])
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            DB::transaction(function () use ($order) {
                // Restore stock for products & variants
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                        $item->product->decrement('sold_count', min($item->quantity, $item->product->sold_count));

                        // If variant name exists, restore variant stock too
                        if ($item->product->relationLoaded('variants')) {
                            foreach ($item->product->variants as $variant) {
                                if (str_contains($item->product_name, $variant->name)) {
                                    $variant->increment('stock', $item->quantity);
                                    break;
                                }
                            }
                        }
                    }
                }

                $order->update([
                    'status' => 'cancelled',
                    'cancellation_status' => 'approved',
                    'cancellation_responded_at' => now(),
                    'cancellation_response_note' => 'Dibatalkan otomatis oleh sistem (penjual tidak merespons pengajuan pembatalan dalam waktu 3 hari).',
                ]);
            });

            AuditLogger::order('Pesanan Dibatalkan Otomatis Sistem (Melewati 3 Hari)', $order);
            $count++;
        }

        return $count;
    }

    /**
     * Check if order can be marked as completed by the buyer.
     */
    public function canBeConfirmedCompletedByBuyer(): bool
    {
        return strtolower($this->status) === 'delivered'
            && $this->return_status !== 'requested';
    }

    /**
     * Determine if order can be returned by the buyer.
     * Allowed only when delivered and not completed or already returned.
     */
    public function canBeReturnedByBuyer(): bool
    {
        return strtolower($this->status) === 'delivered'
            && ! in_array(strtolower($this->status), ['completed', 'cancelled', 'returned'])
            && ! in_array($this->return_status, ['requested', 'approved']);
    }

    /**
     * Automatically complete orders that have been delivered for more than 7 days (168 hours).
     */
    public static function autoCompleteDeliveredOrders(): int
    {
        $cutoff = now()->subDays(7);
        $expiredOrders = static::where('status', 'delivered')
            ->where(function ($q) {
                $q->whereNull('return_status')
                    ->orWhere('return_status', '!=', 'requested');
            })
            ->where('delivered_at', '<=', $cutoff)
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            $order->update([
                'status' => 'completed',
                'payment_status' => 'paid',
                'completed_at' => now(),
            ]);

            AuditLogger::order('Pesanan Diselesaikan Otomatis oleh Sistem (Melewati Batas Waktu 7 Hari)', $order);
            $count++;
        }

        return $count;
    }
}
