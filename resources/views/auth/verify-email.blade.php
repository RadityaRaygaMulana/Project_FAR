@extends('layouts.app')

@section('title', 'Verifikasi Email OTP — NusantaraMart')

@section('content')
<div class="min-h-[85vh] bg-[#FAF8F5] py-4 sm:py-6 flex items-center justify-center px-4 sm:px-6">
    <div class="max-w-md w-full space-y-2">
        
        <!-- Top Back to Home Button (Premium Circular Pill) -->
        <div>
            <a href="{{ route('home') }}" 
               class="w-11 h-11 rounded-full bg-white hover:bg-[#FAF4ED] active:bg-[#F5EBE1] text-[#6B4226] hover:text-[#54321B] border border-[#EAE1D7] hover:border-[#6B4226]/40 shadow-[0_2px_8px_rgba(107,66,38,0.08)] hover:shadow-[0_4px_14px_rgba(107,66,38,0.16)] flex items-center justify-center transition-all duration-200 group active:scale-95 cursor-pointer shrink-0"
               title="Kembali ke Beranda">
                <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-[#EAE1D7] p-8 shadow-xl space-y-6"
             x-data="otpVerification()">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#6B4226] to-[#54321B] text-white flex items-center justify-center text-3xl mx-auto shadow-md">
                📬
            </div>
            <h1 class="text-2xl font-black text-[#2D241E] tracking-tight">Verifikasi Email Kamu</h1>
            <p class="text-xs text-[#6D6D72] leading-relaxed">
                Kami telah mengirimkan 6 digit kode verifikasi (OTP) ke alamat email:
                <br>
                <span class="font-bold text-[#6B4226] text-sm">{{ $user->email }}</span>
            </p>
        </div>

        <!-- Notification Alerts -->
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100 max-h-20"
                 x-transition:leave-end="opacity-0 scale-95 max-h-0 py-0"
                 class="overflow-hidden p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-400 hover:text-emerald-700 font-bold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->has('otp'))
            <div class="p-3.5 bg-red-50 border border-red-200 text-red-700 text-xs font-semibold rounded-2xl flex items-center gap-2">
                <span>✕</span>
                <span>{{ $errors->first('otp') }}</span>
            </div>
        @endif

        <!-- OTP Input Form -->
        <form action="{{ route('verification.verify') }}" method="POST" class="space-y-6" @submit="combineOtp()">
            @csrf
            
            <input type="hidden" name="otp" x-model="combinedOtp">

            <!-- 6-Boxes OTP Input Grid -->
            <div>
                <label class="block text-center text-xs font-bold text-[#8A7C70] mb-3 uppercase tracking-wider">
                    Masukkan 6 Digit Kode OTP
                </label>
                <div class="flex items-center justify-center gap-2 sm:gap-2.5" @paste="handlePaste($event)">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input type="text" 
                               inputmode="numeric" 
                               maxlength="1"
                               x-ref="otpInputs"
                               x-model="digits[index]"
                               @input="handleInput(index, $event)"
                               @keydown.backspace="handleBackspace(index, $event)"
                               class="w-11 h-13 sm:w-12 sm:h-14 text-center text-2xl font-black rounded-2xl border-2 border-[#EAE1D7] focus:border-[#6B4226] focus:ring-4 focus:ring-[#6B4226]/10 text-[#2D241E] bg-[#FAF8F5] transition-all outline-none font-mono">
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    :disabled="combinedOtp.length !== 6"
                    :class="combinedOtp.length === 6 ? 'bg-[#6B4226] hover:bg-[#54321B] text-white shadow-md' : 'bg-[#EAE1D7] text-[#8A7C70] cursor-not-allowed'"
                    class="w-full py-3.5 font-bold text-sm rounded-2xl transition duration-200 cursor-pointer">
                Verifikasi & Aktifkan Akun 🚀
            </button>
        </form>

        <!-- Resend OTP & Logout Options -->
        <div class="pt-2 border-t border-[#F2EAE0] text-center space-y-3">
            <div class="text-xs text-[#8A7C70]">
                <span>Tidak menerima kode email? </span>
                <form action="{{ route('verification.resend') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            :disabled="countdown > 0"
                            :class="countdown > 0 ? 'text-[#8E8E93] cursor-not-allowed' : 'text-[#007AFF] hover:underline font-bold cursor-pointer'">
                        <span x-text="countdown > 0 ? 'Kirim ulang (' + countdown + 's)' : 'Kirim Ulang Kode OTP'"></span>
                    </button>
                </form>
            </div>

            <div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-[11px] text-[#8E8E93] hover:text-red-600 transition cursor-pointer">
                        Ganti Akun / Keluar
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    function otpVerification() {
        return {
            digits: ['', '', '', '', '', ''],
            countdown: 60,

            init() {
                this.$nextTick(() => {
                    if (this.$refs.otpInputs && this.$refs.otpInputs[0]) {
                        this.$refs.otpInputs[0].focus();
                    }
                });

                const timer = setInterval(() => {
                    if (this.countdown > 0) {
                        this.countdown--;
                    } else {
                        clearInterval(timer);
                    }
                }, 1000);
            },

            get combinedOtp() {
                return this.digits.join('');
            },

            combineOtp() {
                // updates hidden input
            },

            handleInput(index, event) {
                const val = event.target.value;
                if (!/^[0-9]$/.test(val)) {
                    this.digits[index] = '';
                    return;
                }

                // Move focus to next box
                if (index < 5) {
                    const inputs = document.querySelectorAll('input[x-ref="otpInputs"]');
                    if (inputs[index + 1]) {
                        inputs[index + 1].focus();
                    }
                }
            },

            handleBackspace(index, event) {
                if (this.digits[index] === '' && index > 0) {
                    const inputs = document.querySelectorAll('input[x-ref="otpInputs"]');
                    if (inputs[index - 1]) {
                        inputs[index - 1].focus();
                    }
                }
            },

            handlePaste(event) {
                event.preventDefault();
                const pasteData = (event.clipboardData || window.clipboardData).getData('text').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    for (let i = 0; i < 6; i++) {
                        this.digits[i] = pasteData[i];
                    }
                    const inputs = document.querySelectorAll('input[x-ref="otpInputs"]');
                    if (inputs[5]) {
                        inputs[5].focus();
                    }
                }
            }
        };
    }
</script>
@endsection
