<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminVoucherController extends Controller
{
    /**
     * Display the Voucher Management Center in the Admin Panel.
     */
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $tier = $request->input('tier');
        $status = $request->input('status');
        $search = trim((string) $request->input('q', ''));

        $query = Voucher::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($category) && in_array($category, ['shipping', 'discount'], true)) {
            $query->where('category', $category);
        }

        if (! empty($tier) && in_array($tier, ['all', 'silver', 'gold', 'platinum'], true)) {
            $query->where('member_tier', $tier);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'weekly') {
            $query->where('is_weekly_recurring', true);
        }

        $vouchers = $query->orderBy('is_weekly_recurring', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // High-level statistics
        $totalVouchers = Voucher::count();
        $weeklyRecurringCount = Voucher::where('is_weekly_recurring', true)->count();
        $activeCount = Voucher::where('is_active', true)->count();
        $totalClaimed = (int) Voucher::sum('claimed_count');

        return view('admin.vouchers.index', compact(
            'vouchers',
            'totalVouchers',
            'weeklyRecurringCount',
            'activeCount',
            'totalClaimed',
            'category',
            'tier',
            'status',
            'search'
        ));
    }

    /**
     * Store a new voucher created by the administrator.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:vouchers,code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:shipping,discount'],
            'type' => ['required', 'string', 'in:fixed,percentage'],
            'reward_amount' => ['required', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'member_tier' => ['required', 'string', 'in:all,silver,gold,platinum'],
            'is_weekly_recurring' => ['nullable', 'boolean'],
            'weekly_day_rule' => ['nullable', 'string', 'in:all,weekdays,weekends,monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $voucher = Voucher::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => $validated['type'],
            'reward_amount' => (int) $validated['reward_amount'],
            'max_discount' => ! empty($validated['max_discount']) ? (int) $validated['max_discount'] : null,
            'min_spend' => ! empty($validated['min_spend']) ? (int) $validated['min_spend'] : 0,
            'quota' => ! empty($validated['quota']) ? (int) $validated['quota'] : 1000,
            'claimed_count' => 0,
            'used_count' => 0,
            'member_tier' => $validated['member_tier'] ?? 'all',
            'is_weekly_recurring' => $request->boolean('is_weekly_recurring'),
            'weekly_day_rule' => $validated['weekly_day_rule'] ?? 'all',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', "Voucher '{$voucher->code}' berhasil ditambahkan!");
    }

    /**
     * Update an existing voucher.
     */
    public function update(Request $request, Voucher $voucher): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('vouchers', 'code')->ignore($voucher->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:shipping,discount'],
            'type' => ['required', 'string', 'in:fixed,percentage'],
            'reward_amount' => ['required', 'integer', 'min:1'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'quota' => ['nullable', 'integer', 'min:1'],
            'member_tier' => ['required', 'string', 'in:all,silver,gold,platinum'],
            'is_weekly_recurring' => ['nullable', 'boolean'],
            'weekly_day_rule' => ['nullable', 'string', 'in:all,weekdays,weekends,monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $voucher->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'type' => $validated['type'],
            'reward_amount' => (int) $validated['reward_amount'],
            'max_discount' => ! empty($validated['max_discount']) ? (int) $validated['max_discount'] : null,
            'min_spend' => ! empty($validated['min_spend']) ? (int) $validated['min_spend'] : 0,
            'quota' => ! empty($validated['quota']) ? (int) $validated['quota'] : 1000,
            'member_tier' => $validated['member_tier'] ?? 'all',
            'is_weekly_recurring' => $request->boolean('is_weekly_recurring'),
            'weekly_day_rule' => $validated['weekly_day_rule'] ?? 'all',
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.vouchers.index')->with('success', "Voucher '{$voucher->code}' berhasil diperbarui!");
    }

    /**
     * Remove a voucher from the platform.
     */
    public function destroy(Voucher $voucher): RedirectResponse
    {
        $code = $voucher->code;
        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', "Voucher '{$code}' berhasil dihapus.");
    }

    /**
     * Toggle active status of a voucher.
     */
    public function toggle(Voucher $voucher): JsonResponse|RedirectResponse
    {
        $voucher->update(['is_active' => ! $voucher->is_active]);

        $statusText = $voucher->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $voucher->is_active,
                'message' => "Voucher '{$voucher->code}' berhasil {$statusText}.",
            ]);
        }

        return back()->with('success', "Voucher '{$voucher->code}' berhasil {$statusText}.");
    }

    /**
     * Reset claimed count for weekly recurring voucher refresh.
     */
    public function resetQuota(Voucher $voucher): RedirectResponse
    {
        $voucher->update(['claimed_count' => 0]);

        return back()->with('success', "Kuota klaim voucher mingguan '{$voucher->code}' berhasil di-reset menjadi 0!");
    }
}
