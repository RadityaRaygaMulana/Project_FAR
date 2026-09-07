<?php

namespace App\Http\Controllers;

use App\Models\RedeemCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRedeemCodeController extends Controller
{
    /**
     * Display the admin panel for managing redeem codes.
     */
    public function index(): View
    {
        $codes = RedeemCode::orderBy('is_active', 'desc')
            ->orderByDesc('created_at')
            ->get();

        $totalCodes = $codes->count();
        $activeCodes = $codes->where('is_active', true)->count();
        $totalUsed = $codes->sum('used_count');
        $noQuotaCodes = $codes->whereNull('quota')->count();

        return view('admin.redeem-codes.index', compact(
            'codes',
            'totalCodes',
            'activeCodes',
            'totalUsed',
            'noQuotaCodes',
        ));
    }

    /**
     * Store a newly created redeem code.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', Rule::unique('redeem_codes', 'code')],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_amount' => ['required', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'shipping_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['discount_amount'] = (int) ($data['discount_amount'] ?? 0);
        $data['shipping_discount'] = (int) ($data['shipping_discount'] ?? 0);
        $data['min_spend'] = (int) ($data['min_spend'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        RedeemCode::create($data);

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "Kode redeem {$data['code']} berhasil dibuat!");
    }

    /**
     * Update the specified redeem code.
     */
    public function update(Request $request, RedeemCode $redeemCode): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', Rule::unique('redeem_codes', 'code')->ignore($redeemCode->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'discount_amount' => ['required', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'shipping_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['code'] = strtoupper($data['code']);
        $data['discount_amount'] = (int) ($data['discount_amount'] ?? 0);
        $data['shipping_discount'] = (int) ($data['shipping_discount'] ?? 0);
        $data['min_spend'] = (int) ($data['min_spend'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        $redeemCode->update($data);

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "Kode redeem {$redeemCode->code} berhasil diperbarui!");
    }

    /**
     * Toggle the active status of a redeem code.
     */
    public function toggle(RedeemCode $redeemCode): RedirectResponse
    {
        $redeemCode->update(['is_active' => ! $redeemCode->is_active]);

        $status = $redeemCode->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "Kode {$redeemCode->code} berhasil {$status}.");
    }

    /**
     * Reset the used_count of a redeem code.
     */
    public function resetUsed(RedeemCode $redeemCode): RedirectResponse
    {
        $redeemCode->update(['used_count' => 0]);

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "Hitungan pemakaian kode {$redeemCode->code} berhasil direset ke 0.");
    }

    /**
     * Public API: validate a redeem code and return its discount details.
     */
    public function checkCode(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->string('code', '')));
        if (! $code) {
            return response()->json(['success' => false, 'message' => 'Silakan masukkan kode redeem.'], 422);
        }

        $redeemCode = RedeemCode::active()->where('code', $code)->first();

        if (! $redeemCode) {
            return response()->json(['success' => false, 'message' => "Kode redeem \"{$code}\" tidak valid atau tidak aktif."], 422);
        }

        if (! $redeemCode->hasRemainingQuota()) {
            return response()->json(['success' => false, 'message' => "Kode {$code} sudah mencapai batas pemakaian."], 422);
        }

        return response()->json([
            'success' => true,
            'code' => $redeemCode->code,
            'name' => $redeemCode->name,
            'discount_type' => $redeemCode->discount_type,
            'discount_amount' => $redeemCode->discount_amount,
            'max_discount' => $redeemCode->max_discount,
            'shipping_discount' => $redeemCode->shipping_discount,
            'min_spend' => $redeemCode->min_spend,
            'formatted_discount' => $redeemCode->formatted_discount,
        ]);
    }

    /**
     * Remove the specified redeem code from storage.
     */
    public function destroy(RedeemCode $redeemCode): RedirectResponse
    {
        $code = $redeemCode->code;
        $redeemCode->delete();

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "Kode redeem {$code} berhasil dihapus.");
    }
}
