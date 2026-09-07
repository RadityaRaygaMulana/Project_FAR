<?php

namespace App\Http\Controllers;

use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VoucherController extends Controller
{
    /**
     * Display the Voucher Discovery & Claims showcase portal.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $category = $request->input('category'); // 'shipping', 'discount', or null (all)

        $query = Voucher::active();

        if ($category === 'shipping') {
            $query->shipping();
        } elseif ($category === 'discount') {
            $query->discount();
        }

        $vouchers = $query->orderBy('min_spend', 'asc')->get();

        // Categorized counts for UI badges
        $totalShippingCount = Voucher::active()->shipping()->count();
        $totalDiscountCount = Voucher::active()->discount()->count();
        $totalVouchersCount = $totalShippingCount + $totalDiscountCount;

        // User claim status mapping
        $claimedVoucherIds = [];
        $usedVoucherIds = [];

        if ($user) {
            $userVouchers = UserVoucher::where('user_id', $user->id)->get();
            $claimedVoucherIds = [];
            $usedVoucherIds = [];

            foreach ($vouchers as $v) {
                if ($v->is_weekly_recurring) {
                    $claim = $userVouchers->first(function ($uv) use ($v) {
                        return $uv->voucher_id === $v->id
                            && $uv->claimed_at >= now()->startOfWeek()
                            && $uv->claimed_at <= now()->endOfWeek();
                    });
                    if ($claim) {
                        $claimedVoucherIds[] = $v->id;
                        if ($claim->used_at) {
                            $usedVoucherIds[] = $v->id;
                        }
                    }
                } else {
                    $claim = $userVouchers->firstWhere('voucher_id', $v->id);
                    if ($claim) {
                        $claimedVoucherIds[] = $v->id;
                        if ($claim->used_at) {
                            $usedVoucherIds[] = $v->id;
                        }
                    }
                }
            }
        }

        $userTier = $user?->member_tier ?? 'silver';

        return view('voucher', compact(
            'vouchers',
            'category',
            'totalVouchersCount',
            'totalShippingCount',
            'totalDiscountCount',
            'claimedVoucherIds',
            'usedVoucherIds',
            'userTier'
        ));
    }

    /**
     * Claim a voucher by the authenticated user.
     */
    public function claim(Request $request, Voucher $voucher): JsonResponse|RedirectResponse
    {
        if (! Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan masuk (login) terlebih dahulu untuk mengklaim voucher.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu untuk klaim voucher.');
        }

        $user = Auth::user();

        if (! $voucher->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher ini sudah tidak aktif atau masa berlakunya telah berakhir.',
            ], 422);
        }

        if (! $voucher->isApplicableForToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher mingguan ini hanya aktif pada periode hari yang telah ditentukan.',
            ], 422);
        }

        if (! empty($voucher->member_tier) && $voucher->member_tier !== 'all') {
            if (! $user->meetsTierRequirement($voucher->member_tier)) {
                return response()->json([
                    'success' => false,
                    'message' => "Voucher ini khusus untuk {$voucher->member_tier_label}. Tingkat member kamu saat ini adalah {$user->member_tier_label}.",
                ], 422);
            }
        }

        if (! $voucher->hasRemainingQuota()) {
            return response()->json([
                'success' => false,
                'message' => $voucher->is_weekly_recurring
                    ? 'Maaf, kuota maksimal klaim untuk voucher mingguan periode ini sudah habis ('.number_format($voucher->quota).' user).'
                    : 'Maaf, kuota klaim untuk voucher ini sudah habis.',
            ], 422);
        }

        if ($voucher->is_weekly_recurring) {
            $existingWeeklyClaim = UserVoucher::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->first();

            if ($existingWeeklyClaim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamu sudah mengklaim voucher mingguan ini untuk periode minggu ini (maksimal 1x per user setiap minggu).',
                    'already_claimed' => true,
                ], 200);
            }
        } else {
            $existingClaim = UserVoucher::where('user_id', $user->id)
                ->where('voucher_id', $voucher->id)
                ->first();

            if ($existingClaim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamu sudah mengklaim voucher ini sebelumnya.',
                    'already_claimed' => true,
                ], 200);
            }
        }

        UserVoucher::create([
            'user_id' => $user->id,
            'voucher_id' => $voucher->id,
            'claimed_at' => now(),
        ]);

        $voucher->increment('claimed_count');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Voucher {$voucher->name} berhasil diklaim!",
                'voucher' => [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'name' => $voucher->name,
                    'category' => $voucher->category,
                    'reward_label' => $voucher->formatted_reward,
                    'min_spend_label' => $voucher->formatted_min_spend,
                    'member_tier' => $voucher->member_tier,
                    'member_tier_badge' => $voucher->member_tier_badge,
                ],
            ]);
        }

        return back()->with('success', "Voucher {$voucher->name} berhasil diklaim!");
    }

    /**
     * Fetch vouchers for the checkout modal.
     */
    public function getAvailableForCheckout(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['shipping' => [], 'discount' => []]);
        }

        $allActive = Voucher::active()->get();
        $userClaims = UserVoucher::where('user_id', $user->id)->get()->keyBy('voucher_id');

        $shippingList = [];
        $discountList = [];

        foreach ($allActive as $v) {
            if ($v->is_weekly_recurring) {
                $claim = UserVoucher::where('user_id', $user->id)
                    ->where('voucher_id', $v->id)
                    ->whereBetween('claimed_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->first();
            } else {
                $claim = $userClaims[$v->id] ?? null;
            }

            $isClaimed = $claim !== null;
            $isUsed = $isClaimed && $claim->used_at !== null;
            $isTierEligible = $user->meetsTierRequirement($v->member_tier);
            $isDayEligible = $v->isApplicableForToday();
            $isEligible = $isClaimed && ! $isUsed && $isTierEligible && $isDayEligible;

            $item = [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'category' => $v->category,
                'type' => $v->type,
                'reward_amount' => $v->reward_amount,
                'max_discount' => $v->max_discount,
                'min_spend' => $v->min_spend,
                'description' => $v->description,
                'formatted_reward' => $v->formatted_reward,
                'formatted_min_spend' => $v->formatted_min_spend,
                'member_tier' => $v->member_tier,
                'member_tier_badge' => $v->member_tier_badge,
                'is_weekly_recurring' => (bool) $v->is_weekly_recurring,
                'is_claimed' => $isClaimed,
                'is_used' => $isUsed,
                'is_tier_eligible' => $isTierEligible,
                'is_eligible' => $isEligible,
            ];

            if ($v->category === 'shipping') {
                $shippingList[] = $item;
            } else {
                $discountList[] = $item;
            }
        }

        return response()->json([
            'shipping' => $shippingList,
            'discount' => $discountList,
        ]);
    }
}
