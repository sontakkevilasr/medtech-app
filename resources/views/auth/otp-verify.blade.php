@extends('layouts.guest')
@section('title', 'Verify OTP')

@section('content')
<div x-data="otpApp()" x-init="init()">

    <!-- Header -->
    <div style="text-align:center;margin-bottom:1.75rem">
        <div style="width:52px;height:52px;background:var(--p-bg);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--p)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
            </svg>
        </div>
        <h1 class="font-display" style="font-size:1.5rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:6px">Verify your mobile</h1>
        <p style="font-size:.875rem;color:var(--mt);line-height:1.5">
            We sent a 6-digit OTP to<br>
            <strong style="color:var(--p)">{{ $maskedMobile }}</strong>
        </p>
    </div>

    <!-- OTP Boxes -->
    <form method="POST" action="{{ route('auth.otp.verify.submit') }}" x-ref="otpForm" style="margin-bottom:1.25rem">
        @csrf
        <input type="hidden" name="otp" :value="digits.join('')">

        <div style="display:flex;justify-content:center;gap:10px;margin-bottom:1.5rem">
            <template x-for="(d,i) in digits" :key="i">
                <input
                    type="tel" inputmode="numeric" maxlength="1"
                    :value="d"
                    :class="'otp-box' + (d ? ' filled' : '') + (hasError ? ' oerr' : '')"
                    @input="onInput($event, i)"
                    @keydown="onKey($event, i)"
                    @paste.prevent="onPaste($event)"
                    @focus="$event.target.select()"
                    x-ref="'box'+i">
            </template>
        </div>

        @error('otp')
        <div class="alert a-er" style="margin-bottom:1rem">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </div>
        @enderror

        <div x-show="hasError && !{{ $errors->has('otp') ? 'true' : 'false' }}" class="alert a-er" style="margin-bottom:1rem">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            Please enter all 6 digits.
        </div>

        <button type="button" @click="submit()" :disabled="submitting" class="btn-p">
            <span x-show="submitting" class="spinner"></span>
            <span x-text="submitting ? 'Verifying…' : 'Verify OTP'"></span>
        </button>
    </form>

    <!-- Countdown + Resend -->
    <div style="text-align:center">
        <!-- Countdown timer -->
        <div x-show="countdown > 0" style="margin-bottom:.75rem">
            <div style="display:flex;align-items:center;justify-content:center;gap:8px;font-size:.875rem;color:var(--mt)">
                <!-- Mini ring timer -->
                <svg width="28" height="28" viewBox="0 0 28 28" style="transform:rotate(-90deg)">
                    <circle cx="14" cy="14" r="11" fill="none" stroke="#e2e8f0" stroke-width="2.5"/>
                    <circle cx="14" cy="14" r="11" fill="none" stroke="var(--p)" stroke-width="2.5"
                            stroke-dasharray="69.1" :stroke-dashoffset="69.1 * (1 - countdown / {{ $expiresIn * 60 }})"
                            style="transition:stroke-dashoffset 1s linear;stroke-linecap:round"/>
                </svg>
                <span>OTP expires in <strong x-text="fmtTime(countdown)" style="color:var(--p)"></strong></span>
            </div>
        </div>

        <!-- Expired notice -->
        <div x-show="countdown <= 0" class="alert a-er" style="margin-bottom:.75rem;text-align:left">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
            OTP expired. Please request a new one.
        </div>

        <!-- Resend cooldown -->
        <div x-show="resendCooldown > 0" style="font-size:.8125rem;color:var(--mt)">
            Resend available in <strong x-text="resendCooldown + 's'" style="color:var(--p)"></strong>
        </div>

        <!-- Resend button -->
        <div x-show="resendCooldown <= 0">
            <button @click="resend()" :disabled="resending" type="button"
                    style="background:none;border:none;cursor:pointer;font-size:.875rem;color:var(--p);font-weight:600;font-family:'DM Sans',sans-serif;padding:4px 8px;border-radius:6px;transition:background .2s"
                    onmouseover="this.style.background='var(--p-bg)'" onmouseout="this.style.background='none'">
                <span x-show="resending">Sending…</span>
                <span x-show="!resending">↺ Resend OTP</span>
            </button>
        </div>
    </div>

    <!-- Back link -->
    <div style="text-align:center;margin-top:1.5rem">
        <a href="{{ route('auth.login') }}"
           style="font-size:.8125rem;color:var(--mt);text-decoration:none;display:inline-flex;align-items:center;gap:5px"
           onmouseover="this.style.color='var(--p)'" onmouseout="this.style.color='var(--mt)'">
            ← Use a different number
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
function otpApp() {
    return {
        digits:         ['','','','','',''],
        hasError:       false,
        submitting:     false,
        resending:      false,
        countdown:      {{ $expiresIn * 60 }},
        resendCooldown: {{ $resendCooldown ?? 60 }},
        timer:          null,
        cdTimer:        null,

        init() {
            // Main countdown
            this.timer = setInterval(() => {
                if (this.countdown > 0) this.countdown--;
                else clearInterval(this.timer);
            }, 1000);

            // Resend cooldown (from session sent_at)
            const sentAt   = {{ session('otp_sent_at', 'null') }};
            const cooldown = {{ $resendCooldown ?? 60 }};
            if (sentAt) {
                const elapsed = Math.floor(Date.now() / 1000) - sentAt;
                this.resendCooldown = Math.max(0, cooldown - elapsed);
            }
            if (this.resendCooldown > 0) {
                this.cdTimer = setInterval(() => {
                    if (this.resendCooldown > 0) this.resendCooldown--;
                    else clearInterval(this.cdTimer);
                }, 1000);
            }

            // Auto-focus first empty box
            this.$nextTick(() => {
                const first = this.$el.querySelector('.otp-box');
                if (first) first.focus();
            });
        },

        onInput(e, i) {
            const val = e.target.value.replace(/\D/g,'').slice(-1);
            this.digits[i] = val;
            this.hasError   = false;
            if (val && i < 5) {
                this.$nextTick(() => {
                    const next = this.$el.querySelectorAll('.otp-box')[i+1];
                    if (next) next.focus();
                });
            }
            if (this.digits.every(d => d !== '')) this.submit();
        },

        onKey(e, i) {
            if (e.key === 'Backspace') {
                if (this.digits[i]) {
                    this.digits[i] = '';
                } else if (i > 0) {
                    this.digits[i-1] = '';
                    this.$nextTick(() => {
                        const prev = this.$el.querySelectorAll('.otp-box')[i-1];
                        if (prev) prev.focus();
                    });
                }
                e.preventDefault();
            }
            if (e.key === 'ArrowLeft' && i > 0) {
                const prev = this.$el.querySelectorAll('.otp-box')[i-1];
                if (prev) prev.focus();
            }
            if (e.key === 'ArrowRight' && i < 5) {
                const next = this.$el.querySelectorAll('.otp-box')[i+1];
                if (next) next.focus();
            }
        },

        onPaste(e) {
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
            if (text.length >= 6) {
                for (let i = 0; i < 6; i++) this.digits[i] = text[i] || '';
                this.$nextTick(() => {
                    const boxes = this.$el.querySelectorAll('.otp-box');
                    boxes[5]?.focus();
                    if (this.digits.every(d => d !== '')) this.submit();
                });
            }
        },

        submit() {
            if (!this.digits.every(d => d !== '')) {
                this.hasError = true;
                return;
            }
            if (this.submitting) return;
            this.submitting = true;
            this.$refs.otpForm.submit();
        },

        async resend() {
            this.resending = true;
            try {
                const res = await fetch('{{ route("auth.otp.resend") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.digits = ['','','','','',''];
                    this.countdown = {{ $expiresIn * 60 }};
                    this.resendCooldown = {{ $resendCooldown ?? 60 }};
                    this.hasError = false;
                    // Restart timers
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        if(this.countdown > 0) this.countdown--;
                        else clearInterval(this.timer);
                    }, 1000);
                    clearInterval(this.cdTimer);
                    this.cdTimer = setInterval(() => {
                        if(this.resendCooldown > 0) this.resendCooldown--;
                        else clearInterval(this.cdTimer);
                    }, 1000);
                    this.$nextTick(() => {
                        const first = this.$el.querySelector('.otp-box');
                        if (first) first.focus();
                    });
                } else {
                    alert(data.message || 'Could not resend OTP.');
                }
            } catch(err) {
                alert('Network error. Please try again.');
            } finally {
                this.resending = false;
            }
        },

        fmtTime(sec) {
            const m = Math.floor(sec / 60);
            const s = sec % 60;
            return m + ':' + String(s).padStart(2, '0');
        }
    }
}
</script>
@endpush
