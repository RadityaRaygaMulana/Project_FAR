<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'logo',
        'slug',
        'description',
        'phone',
        'ktp_nik',
        'ktp_name',
        'ktp_photo_path',
        'city',
        'province',
        'address_detail',
        'advantages',
        'badge',
        'rating',
        'status',
        'rejection_reason',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'float',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Get the user who owns this store.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products listed by this store.
     *
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the store followers.
     *
     * @return HasMany<StoreFollower, $this>
     */
    public function followers(): HasMany
    {
        return $this->hasMany(StoreFollower::class);
    }

    /**
     * Check if a specific user follows this store.
     */
    public function isFollowedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->followers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if store application is pending admin approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if store is approved and officially active.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if store application was rejected by admin.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get masked NIK for safe display (e.g. 320145**********).
     */
    public function getMaskedNikAttribute(): string
    {
        if (empty($this->ktp_nik)) {
            return '-';
        }

        return substr($this->ktp_nik, 0, 6).str_repeat('*', max(0, strlen($this->ktp_nik) - 6));
    }

    /**
     * Get the full URL to the uploaded KTP photo.
     */
    public function getKtpPhotoUrlAttribute(): ?string
    {
        if (empty($this->ktp_photo_path)) {
            return null;
        }

        return Storage::url($this->ktp_photo_path);
    }

    /**
     * Get the full URL to the store profile photo / logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo)) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return Storage::url($this->logo);
    }

    /**
     * Get store initials for avatar fallback.
     */
    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= mb_strtoupper(mb_substr($w, 0, 1));
        }

        return $initials ?: 'NM';
    }

    /**
     * Get conversations for this store.
     *
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get real total sold items across all products of this store.
     */
    public function getTotalSoldAttribute(): int
    {
        $productSold = (int) $this->products()->sum('sold_count');
        $productIds = $this->products()->pluck('id');
        $orderItemSold = (int) OrderItem::whereIn('product_id', $productIds)->sum('quantity');

        return max($productSold, $orderItemSold);
    }

    /**
     * Get real rating count (e.g. completed order ratings or orders count).
     */
    public function getRatingCountAttribute(): int
    {
        $productIds = $this->products()->pluck('id');

        return OrderItem::whereIn('product_id', $productIds)->count();
    }

    /**
     * Get real formatted location string.
     */
    public function getLocationAttribute(): string
    {
        $city = $this->city ?: 'Kota Bandung';

        return $this->province ? "{$city}, {$this->province}" : $city;
    }

    /**
     * Get real chat performance statistics from database messages.
     *
     * @return array{rate: string, speed: string, full_label: string}
     */
    public function getChatPerformanceAttribute(): array
    {
        $conversations = $this->conversations()->with(['messages' => fn ($q) => $q->orderBy('created_at')])->get();

        $totalBuyerMessages = 0;
        $repliedBuyerMessages = 0;
        $responseTimesInSeconds = [];

        foreach ($conversations as $conv) {
            $lastBuyerMsgTime = null;

            foreach ($conv->messages as $msg) {
                if ($msg->sender_id !== $this->user_id) {
                    $lastBuyerMsgTime = $msg->created_at;
                    $totalBuyerMessages++;
                } elseif ($lastBuyerMsgTime !== null) {
                    $repliedBuyerMessages++;
                    $diff = abs($msg->created_at->diffInSeconds($lastBuyerMsgTime));
                    $responseTimesInSeconds[] = $diff;
                    $lastBuyerMsgTime = null;
                }
            }
        }

        if ($totalBuyerMessages === 0) {
            return [
                'rate' => '100%',
                'speed' => 'Hitungan Menit',
                'full_label' => '100% (Hitungan Menit)',
            ];
        }

        $ratePercent = (int) round(($repliedBuyerMessages / $totalBuyerMessages) * 100);
        $rate = "{$ratePercent}%";

        if (empty($responseTimesInSeconds)) {
            $speed = 'Hitungan Jam';
        } else {
            $avgSeconds = array_sum($responseTimesInSeconds) / count($responseTimesInSeconds);
            if ($avgSeconds < 180) {
                $speed = '± 1 Menit';
            } elseif ($avgSeconds < 3600) {
                $minutes = max(1, (int) round($avgSeconds / 60));
                $speed = "± {$minutes} Menit";
            } elseif ($avgSeconds < 86400) {
                $hours = max(1, (int) round($avgSeconds / 3600));
                $speed = "± {$hours} Jam";
            } else {
                $speed = 'Hitungan Hari';
            }
        }

        return [
            'rate' => $rate,
            'speed' => $speed,
            'full_label' => "{$rate} ({$speed})",
        ];
    }

    /**
     * Get list of store advantages or default guarantee highlights.
     *
     * @return list<string>
     */
    public function getAdvantagesListAttribute(): array
    {
        if (! empty($this->advantages)) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $this->advantages);
            $filtered = array_values(array_filter(array_map('trim', $lines)));
            if (! empty($filtered)) {
                return $filtered;
            }
        }

        return [
            'Produk 100% Original langsung dari distributor & produsen terverifikasi.',
            'Pengemasan aman menggunakan kardus tebal & lapisan bubble wrap tanpa biaya tambahan.',
            'Pengiriman cepat setiap hari kerja ke seluruh pelosok wilayah Indonesia.',
            'Layanan pelanggan aktif dan tanggap siap membantu jika ada kendala pesanan.',
        ];
    }
}
