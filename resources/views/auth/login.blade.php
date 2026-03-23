@extends('layouts.guest')
@section('title', 'Sign In')

@section('content')
<div x-data="{
    mode: 'otp',
    loading: false,
    cc: '+91',
    mob: '',
    pwd: '',
    showPwd: false,

    get indian(){ return this.cc === '+91' },
    get mobValid(){
        if(this.indian) return /^[6-9]\d{9}$/.test(this.mob);
        return this.mob.length >= 5;
    },
    get canOtp(){ return this.mobValid },
    get canPwd(){ return this.mobValid && this.pwd.length >= 1 },

    goOtp(){
        if(!this.canOtp) return;
        this.loading = true;
        this.$refs.otpForm.submit();
    },
    goPwd(){
        if(!this.canPwd) return;
        this.loading = true;
        this.$refs.pwdForm.submit();
    }
}">

    <!-- Heading -->
    <div style="text-align:center;margin-bottom:1.5rem">
        <h1 class="font-display" style="font-size:1.6rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:4px">Welcome back</h1>
        <p style="font-size:.875rem;color:var(--mt)">Sign in to your Naumah Clinic account</p>
    </div>

    <!-- Mode Tabs -->
    <div class="tab-bar">
        <button @click="mode='otp'" :class="mode==='otp' ? 'tab-btn on' : 'tab-btn'" type="button">
            📱 OTP Login
        </button>
        <button @click="mode='password'" :class="mode==='password' ? 'tab-btn on' : 'tab-btn'" type="button">
            🔑 Password
        </button>
    </div>

    <!-- Mobile field (shared) -->
    <div style="margin-bottom:1rem">
        <label class="lbl">Mobile Number</label>
        <div class="mob-wrap" :class="{'err': {{ $errors->has('mobile_number') ? 'true' : 'false' }} }">
            <select x-model="cc" class="cc-sel">
                <option value="+91">🇮🇳 +91</option>
                <option value="+1">🇺🇸 +1</option>
                <option value="+44">🇬🇧 +44</option>
                <option value="+61">🇦🇺 +61</option>
                <option value="+971">🇦🇪 +971</option>
                <option value="+65">🇸🇬 +65</option>
                <option value="+60">🇲🇾 +60</option>
            </select>
            <input x-model="mob" type="tel" inputmode="numeric" placeholder="Enter mobile number"
                   maxlength="15" class="mob-inp"
                   @input="mob = mob.replace(/\D/g, '')" autofocus>
        </div>
        @error('mobile_number')
            <p class="ferr">{{ $message }}</p>
        @enderror
        <p x-show="mob.length > 3 && !mobValid && indian"
           style="font-size:.75rem;color:#d97706;margin-top:4px">
            Indian numbers must start with 6–9 and be 10 digits.
        </p>
    </div>

    <!-- OTP Login -->
    <div x-show="mode === 'otp'" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <form x-ref="otpForm" method="POST" action="{{ route('auth.login.send-otp') }}">
            @csrf
            <input type="hidden" name="mobile_number" :value="mob">
            <input type="hidden" name="country_code" :value="cc">

            <button type="button" @click="goOtp()" :disabled="!canOtp || loading" class="btn-p" style="margin-top:.5rem">
                <span x-show="loading" class="spinner"></span>
                <span x-text="loading ? 'Sending OTP…' : 'Send OTP'"></span>
                <svg x-show="!loading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>

    <!-- Password Login -->
    <div x-show="mode === 'password'" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">

        <form x-ref="pwdForm" method="POST" action="{{ route('auth.login.password') }}">
            @csrf
            <input type="hidden" name="mobile_number" :value="mob">
            <input type="hidden" name="country_code" :value="cc">

            <div style="margin-bottom:1rem">
                <label class="lbl">Password</label>
                <div style="position:relative">
                    <input x-model="pwd" :type="showPwd ? 'text' : 'password'"
                           name="password" placeholder="Enter your password"
                           class="inp {{ $errors->has('password') ? 'err' : '' }}"
                           style="padding-right:2.75rem">
                    <button type="button" @click="showPwd=!showPwd"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--mt);padding:0">
                        <svg x-show="!showPwd" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPwd" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="ferr">{{ $message }}</p>
                @enderror
            </div>

            <label style="display:flex;align-items:center;gap:8px;font-size:.875rem;color:var(--mt);cursor:pointer;margin-bottom:1rem">
                <input type="checkbox" name="remember" style="accent-color:var(--p);width:15px;height:15px">
                Remember me for 30 days
            </label>

            <button type="button" @click="goPwd()" :disabled="!canPwd || loading" class="btn-p">
                <span x-show="loading" class="spinner"></span>
                <span x-text="loading ? 'Signing in…' : 'Sign In'"></span>
            </button>
        </form>
    </div>

    <!-- Divider + register hint -->
    <div class="divider" style="margin-top:1.5rem">New to Naumah Clinic?</div>

    <p style="text-align:center;font-size:.875rem;color:var(--mt);margin-bottom:.75rem">
        OTP login automatically creates your account if you're new.
    </p>
    <a href="{{ route('auth.register.international') }}" class="btn-g">
        🌍 International number? Register here
    </a>

</div>
@endsection
