@extends('layouts.guest')
@section('title', 'Set a Password')

@section('content')
<div x-data="{
    pwd: '',
    cpwd: '',
    showP: false,
    showC: false,
    loading: false,
    skipping: false,

    get len()   { return this.pwd.length >= 8 },
    get upper() { return /[A-Z]/.test(this.pwd) },
    get digit() { return /[0-9]/.test(this.pwd) },
    get match() { return this.pwd && this.pwd === this.cpwd },
    get strong() { return this.len && this.upper && this.digit },
    get canSave(){ return this.strong && this.match },

    strength() {
        let s = 0;
        if(this.len)   s++;
        if(this.upper) s++;
        if(this.digit) s++;
        if(/[^a-zA-Z0-9]/.test(this.pwd)) s++;
        return s; // 0-4
    },
    strengthLabel() {
        return ['','Weak','Fair','Good','Strong'][this.strength()];
    },
    strengthColor() {
        return ['','#ef4444','#f59e0b','#3b82f6','#10b981'][this.strength()];
    }
}">

    <!-- Progress -->
    <div style="display:flex;justify-content:center;gap:6px;margin-bottom:1.75rem">
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p-lt)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p);transform:scale(1.2)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--bd)"></div>
    </div>

    <!-- Heading -->
    <div style="text-align:center;margin-bottom:1.75rem">
        <div style="width:52px;height:52px;background:var(--p-bg);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--p)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <h1 class="font-display" style="font-size:1.5rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:6px">Set a password</h1>
        <p style="font-size:.875rem;color:var(--mt)">Optional but recommended for quick login</p>
    </div>

    <form method="POST" action="{{ route('auth.setup.password.save') }}" x-ref="form">
        @csrf

        <!-- Password field -->
        <div style="margin-bottom:1rem">
            <label class="lbl">Password</label>
            <div style="position:relative">
                <input x-model="pwd" :type="showP ? 'text' : 'password'" name="password"
                       placeholder="Min. 8 characters"
                       class="inp {{ $errors->has('password') ? 'err' : '' }}"
                       style="padding-right:2.75rem" autocomplete="new-password">
                <button type="button" @click="showP=!showP"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--mt)">
                    <svg x-show="!showP" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showP" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @error('password')
                <p class="ferr">{{ $message }}</p>
            @enderror
        </div>

        <!-- Strength bar -->
        <div x-show="pwd.length > 0" style="margin-bottom:1rem">
            <div style="display:flex;gap:4px;margin-bottom:5px">
                <template x-for="i in 4" :key="i">
                    <div style="flex:1;height:3px;border-radius:2px;transition:background .3s"
                         :style="i <= strength() ? 'background:'+strengthColor() : 'background:#e2e8f0'"></div>
                </template>
            </div>
            <div style="font-size:.75rem;display:flex;justify-content:space-between;align-items:center">
                <span :style="'color:'+strengthColor()" x-text="strengthLabel()"></span>
                <div style="display:flex;gap:12px">
                    <span :style="len ? 'color:var(--ok)' : 'color:#94a3b8'" style="font-size:.72rem">✓ 8+ chars</span>
                    <span :style="upper ? 'color:var(--ok)' : 'color:#94a3b8'" style="font-size:.72rem">✓ Uppercase</span>
                    <span :style="digit ? 'color:var(--ok)' : 'color:#94a3b8'" style="font-size:.72rem">✓ Number</span>
                </div>
            </div>
        </div>

        <!-- Confirm password -->
        <div style="margin-bottom:1.5rem">
            <label class="lbl">Confirm Password</label>
            <div style="position:relative">
                <input x-model="cpwd" :type="showC ? 'text' : 'password'" name="password_confirmation"
                       placeholder="Repeat password"
                       class="inp" :class="cpwd && !match ? 'err' : ''"
                       style="padding-right:2.75rem" autocomplete="new-password">
                <button type="button" @click="showC=!showC"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--mt)">
                    <svg x-show="!showC" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showC" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <p x-show="cpwd && !match" class="ferr">Passwords do not match.</p>
            <p x-show="cpwd && match" style="font-size:.75rem;color:var(--ok);margin-top:4px">✓ Passwords match</p>
        </div>

        <!-- Save button -->
        <button type="button" @click="if(canSave){loading=true;$refs.form.submit()}"
                :disabled="!canSave || loading" class="btn-p" style="margin-bottom:.75rem">
            <span x-show="loading" class="spinner"></span>
            <span x-show="!loading">Save Password &amp; Continue</span>
            <span x-show="loading">Saving…</span>
        </button>
    </form>

    <!-- Skip -->
    <form method="POST" action="{{ route('auth.setup.password.skip') }}">
        @csrf
        <button type="submit" @click="skipping=true" class="btn-g" :disabled="skipping">
            <span x-show="skipping">Skipping…</span>
            <span x-show="!skipping">Skip for now — use OTP login</span>
        </button>
    </form>

    <p style="text-align:center;font-size:.75rem;color:#94a3b8;margin-top:.75rem">
        You can always add or change your password later in settings.
    </p>

</div>
@endsection
