@extends('layouts.guest')
@section('title', 'International Registration')

@section('content')
<div x-data="{
    cc: '+1',
    mob: '',
    name: '',
    role: 'patient',
    loading: false,

    get mobValid() { return this.mob.length >= 5 },
    get canSubmit() { return this.mobValid && this.name.trim().length >= 2 }
}">

    <div style="text-align:center;margin-bottom:1.75rem">
        <h1 class="font-display" style="font-size:1.5rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:6px">
            International Registration
        </h1>
        <p style="font-size:.875rem;color:var(--mt)">For mobile numbers outside India (+91)</p>
    </div>

    <form method="POST" action="{{ route('auth.register.international.submit') }}" x-ref="form">
        @csrf

        <div style="margin-bottom:1rem">
            <label class="lbl">Full Name *</label>
            <input type="text" name="full_name" x-model="name" value="{{ old('full_name') }}"
                   placeholder="Your full name"
                   class="inp {{ $errors->has('full_name') ? 'err' : '' }}" required>
            @error('full_name') <p class="ferr">{{ $message }}</p> @enderror
        </div>

        <div style="margin-bottom:1rem">
            <label class="lbl">Mobile Number *</label>
            <input type="hidden" name="mobile_number" :value="mob">
            <input type="hidden" name="country_code" :value="cc">
            <div class="mob-wrap" :class="{{ $errors->has('mobile_number') ? '\'err\'' : '\'\''}}" >
                <select x-model="cc" class="cc-sel" style="min-width:100px">
                    <option value="+1">🇺🇸 +1</option>
                    <option value="+44">🇬🇧 +44</option>
                    <option value="+61">🇦🇺 +61</option>
                    <option value="+971">🇦🇪 +971</option>
                    <option value="+65">🇸🇬 +65</option>
                    <option value="+60">🇲🇾 +60</option>
                    <option value="+49">🇩🇪 +49</option>
                    <option value="+33">🇫🇷 +33</option>
                    <option value="+81">🇯🇵 +81</option>
                    <option value="+86">🇨🇳 +86</option>
                    <option value="+1-CA">🇨🇦 +1</option>
                    <option value="+27">🇿🇦 +27</option>
                    <option value="+55">🇧🇷 +55</option>
                </select>
                <input x-model="mob" type="tel" inputmode="numeric" placeholder="Mobile number"
                       class="mob-inp" @input="mob = mob.replace(/\D/g, '')">
            </div>
            @error('mobile_number') <p class="ferr">{{ $message }}</p> @enderror
        </div>

        <div style="margin-bottom:1.5rem">
            <label class="lbl">I am a</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
                <label style="cursor:pointer">
                    <input type="radio" name="role" value="patient" x-model="role" style="display:none">
                    <div :class="role==='patient' ? 'role-card sel' : 'role-card'"
                         style="text-align:center;padding:.875rem .5rem">
                        <div style="font-size:1.25rem;margin-bottom:4px">👤</div>
                        <div style="font-size:.875rem;font-weight:600;color:var(--tx)">Patient</div>
                    </div>
                </label>
                <label style="cursor:pointer">
                    <input type="radio" name="role" value="doctor" x-model="role" style="display:none">
                    <div :class="role==='doctor' ? 'role-card sel' : 'role-card'"
                         style="text-align:center;padding:.875rem .5rem">
                        <div style="font-size:1.25rem;margin-bottom:4px">🩺</div>
                        <div style="font-size:.875rem;font-weight:600;color:var(--tx)">Doctor</div>
                    </div>
                </label>
            </div>
        </div>

        <button type="button" @click="if(canSubmit){loading=true;$refs.form.submit()}"
                :disabled="!canSubmit || loading" class="btn-p" style="margin-bottom:.75rem">
            <span x-show="loading" class="spinner"></span>
            <span x-show="!loading">Register &amp; Send OTP</span>
            <span x-show="loading">Registering…</span>
        </button>
    </form>

    <div class="divider">OR</div>

    <a href="{{ route('auth.login') }}" class="btn-o">
        ← Back to Login
    </a>

</div>
@endsection
