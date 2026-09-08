@extends('layouts.admin')

@section('title', 'Kelola Pengguna — NusantaraMart Admin')

@section('content')
<div class="space-y-6" 
     x-data="{
        search: '',
        selectedRole: 'all',
        perPage: 20,
        currentPage: 1,
        editModalOpen: false,
        deleteModalOpen: false,
        deleteTarget: {
            id: null,
            name: '',
            username: '',
            delete_url: ''
        },
        editingUser: {
            id: null,
            name: '',
            username: '',
            email: '',
            phone: '',
            role: 'customer',
            avatar_url: null,
            is_verified: false,
            update_url: ''
        },
        openEdit(u) {
            this.editingUser = { ...u };
            this.editModalOpen = true;
        },
        confirmDelete(u) {
            this.deleteTarget = { ...u };
            this.deleteModalOpen = true;
        },
        userList: [
            @foreach($users as $user)
            {
                id: {{ $user->id }},
                name: {{ Js::from($user->name) }},
                username: {{ Js::from($user->username ?? '') }},
                email: {{ Js::from($user->email) }},
                phone: {{ Js::from($user->phone ?? '') }},
                role: {{ Js::from($user->role) }}
            },
            @endforeach
        ],
        matches(u) {
            let roleMatch = (this.selectedRole === 'all' || u.role === this.selectedRole);
            if (!roleMatch) return false;
            if (!this.search.trim()) return true;
            let q = this.search.toLowerCase().trim();
            return (u.name && u.name.toLowerCase().includes(q)) ||
                   (u.username && u.username.toLowerCase().includes(q)) ||
                   (u.email && u.email.toLowerCase().includes(q)) ||
                   (u.phone && u.phone.toLowerCase().includes(q));
        },
        get filteredUsers() {
            return this.userList.filter(u => this.matches(u));
        },
        get totalPages() {
            return Math.ceil(this.filteredUsers.length / this.perPage) || 1;
        },
        isUserVisible(userId) {
            let index = this.filteredUsers.findIndex(u => u.id === userId);
            if (index === -1) return false;
            let start = (this.currentPage - 1) * this.perPage;
            let end = start + this.perPage;
            return index >= start && index < end;
        },
        get startEntry() {
            if (this.filteredUsers.length === 0) return 0;
            return (this.currentPage - 1) * this.perPage + 1;
        },
        get endEntry() {
            return Math.min(this.currentPage * this.perPage, this.filteredUsers.length);
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        goToPage(p) {
            this.currentPage = p;
        },
        init() {
            this.$watch('search', () => { this.currentPage = 1; });
            this.$watch('selectedRole', () => { this.currentPage = 1; });
        }
     }">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                Manajemen Akun Pengguna & Hak Akses
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Kelola profil pengguna, tetapkan peran administrator, dan kelola akun sistem.
            </p>
        </div>
        <div class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl self-start sm:self-auto font-mono">
            Total: <span class="font-bold text-slate-900" x-text="filteredUsers.length"></span> / <span class="font-bold text-slate-900">{{ $users->count() }}</span> Akun
        </div>
    </div>

    <!-- REAL-TIME FILTER & SEARCH BAR -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-xs">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            
            <!-- REAL-TIME ROLE SELECTOR -->
            <div class="w-full sm:w-56 shrink-0">
                <select x-model="selectedRole" 
                        class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226] cursor-pointer">
                    <option value="all">Semua Peran ({{ $users->count() }})</option>
                    <option value="customer">Pelanggan ({{ $users->where('role', 'customer')->count() }})</option>
                    <option value="admin">Administrator ({{ $users->where('role', 'admin')->count() }})</option>
                </select>
            </div>

            <!-- REAL-TIME SEARCH INPUT -->
            <div class="relative w-full">
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari nama, username, email, atau telepon..." 
                       class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]/30 focus:border-[#6B4226] transition font-mono">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <button type="button" 
                        x-show="search.length > 0" 
                        x-cloak 
                        @click="search = ''" 
                        class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center transition cursor-pointer">
                    ✕
                </button>
            </div>

        </div>
    </div>

    <!-- USERS DATA TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4 pl-6">Pengguna</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Peran Akun</th>
                        <th class="py-3.5 px-4">Status Akun</th>
                        <th class="py-3.5 px-4">Terdaftar</th>
                        <th class="py-3.5 px-4 pr-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                        <tr x-show="isUserVisible({{ $user->id }})" 
                            class="hover:bg-slate-50/60 transition group">
                            
                            <!-- USER PROFILE WITH REAL AVATAR PHOTO -->
                            <td class="py-4 px-4 pl-6">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" 
                                             alt="{{ $user->name }}" 
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#6B4226]/10 to-[#6B4226]/25 border border-[#6B4226]/30 flex items-center justify-center text-[#6B4226] font-black text-sm shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-extrabold text-slate-900 text-xs tracking-tight">
                                            {{ $user->name }}
                                        </p>
                                        <p class="text-slate-400 font-mono text-[10px] mt-0.5">
                                            {{ '@' . ($user->username ?? 'user' . $user->id) }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- CONTACT INFO -->
                            <td class="py-4 px-4">
                                <div class="space-y-0.5">
                                    <p class="text-slate-700 font-mono text-[11px]">{{ $user->email }}</p>
                                    <p class="text-slate-400 font-mono text-[10px]">{{ $user->phone ?? '-' }}</p>
                                </div>
                            </td>

                            <!-- ROLE BADGE -->
                            <td class="py-4 px-4">
                                @if($user->role === 'admin')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-300/80">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Administrator</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Pelanggan</span>
                                    </span>
                                @endif
                            </td>

                            <!-- VERIFICATION STATUS -->
                            <td class="py-4 px-4">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Verified</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        Pending OTP
                                    </span>
                                @endif
                            </td>

                            <!-- REGISTERED DATE -->
                            <td class="py-4 px-4 text-slate-500 font-mono text-[11px]">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                            </td>

                            <!-- ACTION BUTTONS: ONLY EDIT & HAPUS -->
                            <td class="py-4 px-4 pr-6 text-right">
                                @if($user->id !== Auth::id())
                                    <div class="inline-flex items-center gap-2">
                                        
                                        <!-- Edit Button (Opens Edit & Jadikan Admin Modal) -->
                                        <button type="button" 
                                                @click="openEdit({
                                                    id: {{ $user->id }},
                                                    name: {{ Js::from($user->name) }},
                                                    username: {{ Js::from($user->username ?? '') }},
                                                    email: {{ Js::from($user->email) }},
                                                    phone: {{ Js::from($user->phone ?? '') }},
                                                    role: {{ Js::from($user->role) }},
                                                    avatar_url: {{ Js::from($user->avatar_url) }},
                                                    is_verified: {{ $user->email_verified_at ? 'true' : 'false' }},
                                                    update_url: '{{ route('admin.users.update', $user) }}'
                                                })"
                                                class="px-3 py-1.5 text-[11px] font-bold bg-white hover:bg-slate-100 text-slate-700 rounded-lg border border-slate-300 transition cursor-pointer flex items-center gap-1.5 shadow-2xs active:scale-95">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            <span>Edit</span>
                                        </button>

                                        <!-- Hapus Button (Opens Beautiful Modal, NO browser alert) -->
                                        <button type="button" 
                                                @click="confirmDelete({
                                                    id: {{ $user->id }},
                                                    name: {{ Js::from($user->name) }},
                                                    username: {{ Js::from($user->username ?? '') }},
                                                    delete_url: '{{ route('admin.users.destroy', $user) }}'
                                                })"
                                                class="px-3 py-1.5 text-[11px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200 transition cursor-pointer flex items-center gap-1.5 active:scale-95">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>

                                    </div>
                                @else
                                    <span class="px-2.5 py-1 rounded-md bg-slate-100 text-[11px] font-bold text-slate-400 border border-slate-200">
                                        Akun Anda
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <!-- REAL-TIME EMPTY RESULT NOTICE -->
                    <tr x-show="filteredUsers.length === 0" x-cloak>
                        <td colspan="6" class="py-12 px-4 text-center">
                            <p class="text-sm font-bold text-slate-700">Tidak ada data pengguna yang cocok</p>
                            <p class="text-xs text-slate-400 mt-1">
                                Hasil pencarian <span class="font-bold text-slate-600" x-text="'\'' + search + '\''"></span> tidak ditemukan.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTROLS (20 PER PAGE) -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs" 
             x-show="filteredUsers.length > 0">
            
            <div class="text-slate-500 font-medium">
                Menampilkan <span class="font-bold text-slate-900" x-text="startEntry"></span> - <span class="font-bold text-slate-900" x-text="endEntry"></span> dari <span class="font-bold text-slate-900" x-text="filteredUsers.length"></span> pengguna
            </div>

            <div class="flex items-center gap-1.5" x-show="totalPages > 1">
                <!-- Prev Button -->
                <button type="button" 
                        @click="prevPage()" 
                        :disabled="currentPage === 1"
                        :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 shadow-2xs cursor-pointer'"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold transition">
                    ← Sebelumnya
                </button>

                <!-- Page Number Buttons -->
                <template x-for="p in totalPages" :key="p">
                    <button type="button" 
                            @click="goToPage(p)" 
                            :class="currentPage === p ? 'bg-[#6B4226] text-white font-black shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-700 font-bold border border-slate-200'"
                            class="w-8 h-8 rounded-lg flex items-center justify-center transition cursor-pointer text-xs"
                            x-text="p">
                    </button>
                </template>

                <!-- Next Button -->
                <button type="button" 
                        @click="nextPage()" 
                        :disabled="currentPage === totalPages"
                        :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-white hover:bg-slate-100 text-slate-700 shadow-2xs cursor-pointer'"
                        class="px-3 py-1.5 rounded-lg border border-slate-200 font-bold transition">
                    Selanjutnya →
                </button>
            </div>
        </div>
    </div>

    <!-- 1. COMPACT EDIT PENGGUNA & JADIKAN ADMIN MODAL -->
    <div x-show="editModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-md w-full p-5 border border-slate-200 shadow-2xl space-y-4"
             @click.away="editModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header with User Badge (Read-Only Avatar) -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <template x-if="editingUser.avatar_url">
                        <img :src="editingUser.avatar_url" class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-2xs shrink-0">
                    </template>
                    <template x-if="!editingUser.avatar_url">
                        <div class="w-9 h-9 rounded-full bg-[#6B4226]/10 border border-[#6B4226]/30 flex items-center justify-center text-[#6B4226] font-bold text-xs shrink-0 shadow-2xs">
                            <span x-text="editingUser.name ? editingUser.name.charAt(0).toUpperCase() : 'U'"></span>
                        </div>
                    </template>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 tracking-tight">Edit Data & Peran Pengguna</h2>
                        <p class="text-[11px] text-slate-500" x-text="'@' + (editingUser.username || 'user')"></p>
                    </div>
                </div>
                <button type="button" 
                        @click="editModalOpen = false" 
                        class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs cursor-pointer transition">
                    ✕
                </button>
            </div>

            <form :action="editingUser.update_url" method="POST" class="space-y-3">
                @csrf
                @method('PUT')

                <!-- NAMA & USERNAME -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lengkap</label>
                        <input type="text" 
                               name="name" 
                               x-model="editingUser.name" 
                               required 
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Username</label>
                        <input type="text" 
                               name="username" 
                               x-model="editingUser.username" 
                               required 
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]">
                    </div>
                </div>

                <!-- EMAIL & PHONE -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Email</label>
                        <input type="email" 
                               name="email" 
                               x-model="editingUser.email" 
                               required 
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Nomor Telepon</label>
                        <input type="text" 
                               name="phone" 
                               x-model="editingUser.phone" 
                               placeholder="08123456789" 
                               class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]">
                    </div>
                </div>

                <!-- ROLE SELECTION (JADIKAN ADMIN / PELANGGAN) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Peran Akun (*Role*)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="p-2.5 border rounded-xl cursor-pointer flex items-center gap-2 transition text-left"
                               :class="editingUser.role === 'customer' ? 'border-[#6B4226] bg-[#6B4226]/5 ring-1 ring-[#6B4226]' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="role" value="customer" x-model="editingUser.role" class="text-[#6B4226] focus:ring-[#6B4226]">
                            <div>
                                <p class="text-xs font-bold text-slate-900">Pelanggan</p>
                                <p class="text-[10px] text-slate-500">Akses biasa</p>
                            </div>
                        </label>

                        <label class="p-2.5 border rounded-xl cursor-pointer flex items-center gap-2 transition text-left"
                               :class="editingUser.role === 'admin' ? 'border-amber-500 bg-amber-50/80 ring-1 ring-amber-500' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="role" value="admin" x-model="editingUser.role" class="text-amber-600 focus:ring-amber-500">
                            <div>
                                <p class="text-xs font-extrabold text-amber-900">🛡️ Administrator</p>
                                <p class="text-[10px] text-amber-700 font-bold">Jadikan Admin</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- PASSWORD UPDATE (OPTIONAL) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Kata Sandi Baru <span class="text-[10px] font-normal text-slate-400">(Kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           placeholder="Minimal 6 karakter" 
                           class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#6B4226]">
                </div>

                <!-- VERIFY EMAIL CHECKBOX (ONLY IF UNVERIFIED) -->
                <template x-if="!editingUser.is_verified">
                    <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="verify_email" value="1" class="rounded text-[#6B4226] focus:ring-[#6B4226]">
                            <span class="text-xs font-bold text-amber-900">Verifikasi email akun ini sekarang</span>
                        </label>
                    </div>
                </template>

                <!-- SUBMIT BUTTONS -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" 
                            @click="editModalOpen = false" 
                            class="px-3.5 py-2 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-xs font-bold bg-[#6B4226] hover:bg-[#54321B] text-white rounded-xl shadow-xs transition cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. BEAUTIFUL CONFIRMATION POPUP MODAL (REPLACES BROWSER 'THIS PAGE SAYS') -->
    <div x-show="deleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 border border-slate-200 shadow-2xl text-center space-y-4"
             @click.away="deleteModalOpen = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Soft Rose Icon -->
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center mx-auto shadow-xs">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>

            <div>
                <h3 class="text-base font-black text-slate-900 tracking-tight">Hapus Akun Pengguna?</h3>
                <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                    Apakah Anda yakin ingin menghapus akun <span class="font-extrabold text-slate-800" x-text="deleteTarget.name"></span> (<span class="font-mono text-slate-600" x-text="'@' + deleteTarget.username"></span>) secara permanen?
                </p>
                <p class="text-[11px] text-rose-600 font-semibold mt-1">Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <form :action="deleteTarget.delete_url" method="POST" class="pt-2 flex items-center justify-center gap-2">
                @csrf
                @method('DELETE')
                
                <button type="button" 
                        @click="deleteModalOpen = false" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition cursor-pointer">
                    Batal
                </button>

                <button type="submit" 
                        class="w-1/2 py-2.5 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-xs transition cursor-pointer">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
