<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\SuspensionAppeal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuspensionAppealController extends Controller
{
    /**
     * Handle submission of suspension appeal from user or store.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'applicant_name' => ['required', 'string', 'max:150'],
            'applicant_email' => ['required', 'email', 'max:150'],
            'applicant_phone' => ['nullable', 'string', 'max:30'],
            'reason' => ['required', 'string', 'min:15', 'max:2500'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:3072'],
        ], [
            'identifier.required' => 'Email atau username akun yang diblokir wajib diisi.',
            'applicant_name.required' => 'Nama lengkap pemohon wajib diisi.',
            'applicant_email.required' => 'Alamat email aktif wajib diisi.',
            'applicant_email.email' => 'Format alamat email tidak valid.',
            'reason.required' => 'Penjelasan banding wajib diisi.',
            'reason.min' => 'Penjelasan banding minimal 15 karakter agar admin dapat meninjau dengan jelas.',
            'attachment.max' => 'Ukuran berkas bukti pendukung maksimal 3MB.',
            'attachment.mimes' => 'Berkas harus berupa gambar (JPG, PNG, WebP) atau dokumen PDF.',
        ]);

        $identifier = trim($validated['identifier']);

        // Check user or store
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        $store = null;
        if (! $user) {
            $store = Store::where('name', $identifier)
                ->orWhere('email', $identifier)
                ->first();
            if ($store) {
                $user = $store->user;
            }
        } else {
            $store = $user->store;
        }

        if (! $user && ! $store) {
            return back()->withInput()->with('error', 'Akun atau Toko dengan username/email "'.$identifier.'" tidak ditemukan dalam sistem kami.');
        }

        $isSuspended = ($user && $user->isSuspended()) || ($store && $store->isSuspended());

        if (! $isSuspended) {
            return back()->withInput()->with('info', 'Akun atau Toko ini tidak dalam status pemblokiran/penangguhan saat ini.');
        }

        // Check if there is already a pending appeal
        $existingPending = SuspensionAppeal::query()
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when($store && ! $user, fn ($q) => $q->where('store_id', $store->id))
            ->where('status', 'pending')
            ->first();

        if ($existingPending) {
            return back()->with([
                'suspended' => true,
                'suspension_reason' => $user?->suspension_reason ?? $store?->suspension_reason ?? 'Pelanggaran ketentuan layanan.',
                'suspended_until' => $user?->suspended_until ? $user->suspended_until->translatedFormat('d F Y, H:i') : null,
                'suspended_diff' => $user?->suspended_until ? $user->suspended_until->diffForHumans() : null,
                'is_permanent' => is_null($user?->suspended_until),
                'existing_appeal' => $existingPending,
                'info' => 'Kamu sudah memiliki permohonan banding yang sedang menunggu peninjauan tim admin (Diajukan pada '.$existingPending->created_at->translatedFormat('d M Y, H:i').').',
            ]);
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment') && $request->file('attachment')->isValid()) {
            $attachmentPath = $request->file('attachment')->store('appeals', 'public');
        }

        $type = $request->input('type', $store && ! $user ? 'store' : 'user');

        $appeal = SuspensionAppeal::create([
            'user_id' => $user?->id,
            'store_id' => $store?->id,
            'type' => $type,
            'applicant_name' => $validated['applicant_name'],
            'applicant_email' => $validated['applicant_email'],
            'applicant_phone' => $validated['applicant_phone'] ?? null,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('login')->with([
            'suspended' => true,
            'suspension_reason' => $user?->suspension_reason ?? $store?->suspension_reason ?? 'Pelanggaran ketentuan layanan.',
            'suspended_until' => $user?->suspended_until ? $user->suspended_until->translatedFormat('d F Y, H:i') : null,
            'suspended_diff' => $user?->suspended_until ? $user->suspended_until->diffForHumans() : null,
            'is_permanent' => is_null($user?->suspended_until),
            'appeal_submitted' => true,
            'success' => 'Permohonan banding berhasil dikirim! Tim Admin NusantaraMart akan segera meninjau pembelaan kamu.',
        ]);
    }

    /**
     * Admin: Display list of appeals.
     */
    public function adminIndex(Request $request): View
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = SuspensionAppeal::with(['user', 'store', 'reviewer'])
            ->latest();

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                    ->orWhere('applicant_email', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"))
                    ->orWhereHas('store', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $appeals = $query->paginate(15)->withQueryString();

        $counts = [
            'total' => SuspensionAppeal::count(),
            'pending' => SuspensionAppeal::where('status', 'pending')->count(),
            'approved' => SuspensionAppeal::where('status', 'approved')->count(),
            'rejected' => SuspensionAppeal::where('status', 'rejected')->count(),
        ];

        return view('admin.appeals.index', compact('appeals', 'counts', 'status', 'search'));
    }

    /**
     * Admin: Approve an appeal and automatically lift suspension.
     */
    public function adminApprove(Request $request, SuspensionAppeal $appeal): RedirectResponse
    {
        $notes = $request->input('admin_notes', 'Banding disetujui oleh administrator.');

        $appeal->update([
            'status' => 'approved',
            'admin_notes' => $notes,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        // Lift user suspension
        if ($appeal->user) {
            $appeal->user->update([
                'is_suspended' => false,
                'suspended_at' => null,
                'suspended_until' => null,
                'suspension_reason' => null,
            ]);
        }

        // Lift store suspension
        if ($appeal->store) {
            $appeal->store->update([
                'is_suspended' => false,
                'suspended_at' => null,
                'suspended_until' => null,
                'suspension_reason' => null,
            ]);
        }

        return back()->with('success', 'Banding berhasil disetujui! Pemblokiran akun/toko telah otomatis dicabut.');
    }

    /**
     * Admin: Reject an appeal with explanation notes.
     */
    public function adminReject(Request $request, SuspensionAppeal $appeal): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'admin_notes.required' => 'Catatan alasan penolakan banding wajib diisi.',
            'admin_notes.min' => 'Catatan minimal 5 karakter.',
        ]);

        $appeal->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan banding telah ditolak dengan catatan resmi.');
    }
}
