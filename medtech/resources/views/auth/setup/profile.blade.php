@extends('layouts.guest')
@section('title', 'Complete Your Profile')

@section('content')
<div x-data="{ loading: false }">

    <!-- Progress -->
    <div style="display:flex;justify-content:center;gap:6px;margin-bottom:1.75rem">
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p-lt)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p-lt)"></div>
        <div style="width:8px;height:8px;border-radius:50%;background:var(--p);transform:scale(1.2)"></div>
    </div>

    <!-- Heading -->
    <div style="text-align:center;margin-bottom:1.75rem">
        <div style="width:52px;height:52px;background:var(--p-bg);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
            @if($role === 'doctor')
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--p)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            @else
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--p)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            @endif
        </div>
        <h1 class="font-display" style="font-size:1.5rem;color:var(--tx);letter-spacing:-.02em;margin-bottom:6px">
            {{ $role === 'doctor' ? 'Your Professional Profile' : 'Tell us about yourself' }}
        </h1>
        <p style="font-size:.875rem;color:var(--mt)">
            {{ $role === 'doctor' ? 'Patients will see this information' : 'Helps your doctor serve you better' }}
        </p>
    </div>

    <form method="POST" action="{{ route('auth.setup.profile.save') }}" x-ref="form">
        @csrf

        <!-- ── Common Fields ──────────────────────────────────────── -->
        <div style="margin-bottom:1rem">
            <label class="lbl">Full Name *</label>
            <input type="text" name="full_name" value="{{ old('full_name') }}"
                   placeholder="{{ $role === 'doctor' ? 'Dr. Firstname Lastname' : 'Your full name' }}"
                   class="inp {{ $errors->has('full_name') ? 'err' : '' }}" required>
            @error('full_name') <p class="ferr">{{ $message }}</p> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
            <div>
                <label class="lbl">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="inp {{ $errors->has('dob') ? 'err' : '' }}"
                       max="{{ now()->subYears(1)->format('Y-m-d') }}">
                @error('dob') <p class="ferr">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lbl">Gender</label>
                <select name="gender" class="inp {{ $errors->has('gender') ? 'err' : '' }}">
                    <option value="">Select</option>
                    <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                </select>
                @error('gender') <p class="ferr">{{ $message }}</p> @enderror
            </div>
        </div>

        @if($role === 'patient')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
            <div>
                <label class="lbl">Blood Group</label>
                <select name="blood_group" class="inp">
                    <option value="">Not known</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lbl">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       placeholder="Mumbai, Delhi…" class="inp">
            </div>
        </div>
        <div style="margin-bottom:1rem">
            <label class="lbl">State</label>
            <select name="state" class="inp">
                <option value="">Select State</option>
                @foreach(['Andhra Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'] as $st)
                <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if($role === 'doctor')
        <!-- ── Doctor Specific ─────────────────────────────────── -->
        <div style="margin:.5rem 0 1rem;padding:.75rem 1rem;background:var(--p-bg);border:1px solid var(--p-bd);border-radius:10px">
            <p style="font-size:.75rem;font-weight:600;color:var(--p);letter-spacing:.06em;text-transform:uppercase;margin-bottom:.25rem">Professional Details</p>
            <p style="font-size:.8125rem;color:var(--mt)">Required for patient verification and trust</p>
        </div>

        <div style="margin-bottom:1rem">
            <label class="lbl">Specialization *</label>
            <input type="text" name="specialization" value="{{ old('specialization') }}"
                   placeholder="Obstetrics &amp; Gynaecology, Paediatrics…"
                   class="inp {{ $errors->has('specialization') ? 'err' : '' }}" required>
            @error('specialization') <p class="ferr">{{ $message }}</p> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
            <div>
                <label class="lbl">MCI / State Reg. No. *</label>
                <input type="text" name="registration_number" value="{{ old('registration_number') }}"
                       placeholder="MH-12345"
                       class="inp {{ $errors->has('registration_number') ? 'err' : '' }}" required>
                @error('registration_number') <p class="ferr">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lbl">Experience (years)</label>
                <input type="number" name="experience_years" value="{{ old('experience_years', 0) }}"
                       min="0" max="70" placeholder="5" class="inp">
            </div>
        </div>

        <div style="margin-bottom:1rem">
            <label class="lbl">Qualification *</label>
            <input type="text" name="qualification" value="{{ old('qualification') }}"
                   placeholder="MBBS, MD — AIIMS Delhi"
                   class="inp {{ $errors->has('qualification') ? 'err' : '' }}" required>
            @error('qualification') <p class="ferr">{{ $message }}</p> @enderror
        </div>

        <div style="margin-bottom:1rem">
            <label class="lbl">Clinic / Hospital Name</label>
            <input type="text" name="clinic_name" value="{{ old('clinic_name') }}"
                   placeholder="City Care Clinic" class="inp">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
            <div>
                <label class="lbl">City</label>
                <input type="text" name="city" value="{{ old('city') }}" placeholder="Mumbai" class="inp">
            </div>
            <div>
                <label class="lbl">Consultation Fee (₹)</label>
                <input type="number" name="consultation_fee" value="{{ old('consultation_fee', '') }}"
                       min="0" step="50" placeholder="500" class="inp">
            </div>
        </div>

        <div style="margin-bottom:1.5rem">
            <label class="lbl">State</label>
            <select name="state" class="inp">
                <option value="">Select State</option>
                @foreach(['Andhra Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'] as $st)
                <option value="{{ $st }}" {{ old('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <button type="button" @click="loading=true;$refs.form.submit()"
                :disabled="loading" class="btn-p">
            <span x-show="loading" class="spinner"></span>
            <span x-show="!loading">Complete Setup →</span>
            <span x-show="loading">Saving…</span>
        </button>

    </form>
</div>
@endsection
