@extends('layouts.doctor')
@section('title', 'Complete Your Profile')
@section('page-title', 'Complete Your Profile')

@section('content')
<div class="fade-in" style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start">

{{-- ── Form ─────────────────────────────────────────────────────────────────── --}}
<div>

{{-- Welcome banner --}}
@if(!$profile->exists)
<div style="padding:16px 20px;background:linear-gradient(135deg,#3d7a6e 0%,#2a5e54 100%);border-radius:14px;color:#fff;margin-bottom:20px;display:flex;align-items:center;gap:14px">
    <span style="font-size:2rem">👋</span>
    <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:500">Welcome to MedTech!</div>
        <div style="font-size:.8rem;opacity:.85;margin-top:2px">Complete your professional profile so patients and admin can verify you.</div>
    </div>
</div>
@endif

<form method="POST" action="{{ route('doctor.setup.save') }}">
    @csrf

    @if($errors->any())
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:16px">
        @foreach($errors->all() as $e)<div style="font-size:.8rem;color:#dc2626">• {{ $e }}</div>@endforeach
    </div>
    @endif

    @php
    $fLabel = fn($l, $req=false) => '<label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">'.$l.($req ? ' <span style=\'color:var(--coral)\'>*</span>' : '').'</label>';
    $fInp   = 'width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:\'Outfit\',sans-serif;';
    @endphp

    {{-- Professional Credentials --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
            Professional Credentials
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                {!! $fLabel('Specialization', true) !!}
                <select name="specialization" style="{{ $fInp }}" required>
                    <option value="">— Select —</option>
                    @foreach(['General Medicine','Family Medicine','Internal Medicine','Obstetrics & Gynecology','Pediatrics','Neonatology','Cardiology','Dermatology','Neurology','Orthopedics','Ophthalmology','ENT','Psychiatry','Oncology','Gastroenterology','Nephrology','Pulmonology','Endocrinology','Urology','Dentistry','Radiology','Reproductive Medicine','Physiotherapy','Other'] as $spec)
                    <option value="{{ $spec }}" {{ old('specialization', $profile->specialization)===$spec ? 'selected':'' }}>{{ $spec }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                {!! $fLabel('Sub-Specialization') !!}
                <input type="text" name="sub_specialization" style="{{ $fInp }}"
                       value="{{ old('sub_specialization', $profile->sub_specialization) }}"
                       placeholder="e.g. High Risk Pregnancy">
            </div>
        </div>

        <div style="margin-bottom:12px">
            {!! $fLabel('Qualification', true) !!}
            <input type="text" name="qualification" style="{{ $fInp }}"
                   value="{{ old('qualification', $profile->qualification) }}"
                   placeholder="e.g. MBBS, MD (OBG), DNB" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
            <div>
                {!! $fLabel('Registration Number', true) !!}
                <input type="text" name="registration_number" style="{{ $fInp }}font-family:monospace;"
                       value="{{ old('registration_number', $profile->registration_number) }}"
                       placeholder="e.g. MH-12345" required>
            </div>
            <div>
                {!! $fLabel('Registration Council', true) !!}
                <select name="registration_council" style="{{ $fInp }}" required>
                    <option value="">— Select —</option>
                    <option value="National Medical Commission (NMC)" {{ old('registration_council', $profile->registration_council)==='National Medical Commission (NMC)' ? 'selected':'' }}>NMC (National)</option>
                    @foreach(['Andhra Pradesh','Assam','Bihar','Delhi','Goa','Gujarat','Haryana','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Odisha','Punjab','Rajasthan','Tamil Nadu','Telangana','Uttar Pradesh','West Bengal'] as $s)
                    @php $v = $s.' Medical Council'; @endphp
                    <option value="{{ $v }}" {{ old('registration_council', $profile->registration_council)===$v ? 'selected':'' }}>{{ $s }}</option>
                    @endforeach
                    <option value="Dental Council of India">Dental Council</option>
                </select>
            </div>
            <div>
                {!! $fLabel('Experience (Years)') !!}
                <input type="number" name="experience_years" style="{{ $fInp }}"
                       value="{{ old('experience_years', $profile->experience_years) }}"
                       min="0" max="60" placeholder="e.g. 10">
            </div>
        </div>
    </div>

    {{-- Clinic --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
            Clinic &amp; Practice
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                {!! $fLabel('Clinic / Hospital Name') !!}
                <input type="text" name="clinic_name" style="{{ $fInp }}"
                       value="{{ old('clinic_name', $profile->clinic_name) }}"
                       placeholder="e.g. Apollo Clinic">
            </div>
            <div>
                {!! $fLabel('Consultation Fee (₹)') !!}
                <input type="number" name="consultation_fee" style="{{ $fInp }}"
                       value="{{ old('consultation_fee', $profile->consultation_fee) }}"
                       min="0" placeholder="e.g. 500">
            </div>
        </div>

        <div style="margin-bottom:12px">
            {!! $fLabel('Clinic Address') !!}
            <input type="text" name="clinic_address" style="{{ $fInp }}"
                   value="{{ old('clinic_address', $profile->clinic_address) }}"
                   placeholder="Street, Area, Landmark">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                {!! $fLabel('City') !!}
                <input type="text" name="clinic_city" style="{{ $fInp }}"
                       value="{{ old('clinic_city', $profile->clinic_city) }}"
                       placeholder="e.g. Nagpur">
            </div>
            <div>
                {!! $fLabel('State') !!}
                <select name="clinic_state" style="{{ $fInp }}">
                    <option value="">— State —</option>
                    @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry'] as $st)
                    <option value="{{ $st }}" {{ old('clinic_state', $profile->clinic_state)===$st ? 'selected':'' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                {!! $fLabel('UPI ID') !!}
                <input type="text" name="upi_id" style="{{ $fInp }}"
                       value="{{ old('upi_id', $profile->upi_id) }}"
                       placeholder="e.g. doctor@upi">
            </div>
            <div>
                {!! $fLabel('Languages Spoken') !!}
                <div style="display:flex;flex-wrap:wrap;gap:5px;padding:8px 10px;border:1.5px solid var(--warm-bd);border-radius:10px;background:#fff;min-height:38px">
                    @foreach(['English','Hindi','Marathi','Tamil','Telugu','Kannada','Malayalam','Gujarati','Bengali','Punjabi'] as $lang)
                    <label style="display:flex;align-items:center;gap:3px;cursor:pointer;font-size:.75rem;color:var(--txt-md)">
                        <input type="checkbox" name="languages_spoken[]" value="{{ $lang }}"
                               {{ in_array($lang, old('languages_spoken', $profile->languages_spoken ?? ['English','Hindi'])) ? 'checked':'' }}
                               style="accent-color:var(--leaf)">
                        {{ $lang }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bio --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 22px;margin-bottom:16px">
        {!! $fLabel('Professional Bio') !!}
        <textarea name="bio" rows="4"
                  style="{{ $fInp }}resize:vertical;line-height:1.6"
                  placeholder="A brief description of your practice, expertise, and approach…">{{ old('bio', $profile->bio) }}</textarea>
    </div>

    <button type="submit"
            style="width:100%;padding:.85rem;background:var(--leaf);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        Save Profile &amp; Go to Dashboard
    </button>
</form>
</div>

{{-- ── Right: status panel ──────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px">
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Verification Status</div>
        <div style="display:flex;align-items:center;gap:9px;padding:10px 12px;border-radius:9px;background:{{ $profile->is_verified ? '#eef5f3' : '#fef9ec' }};border:1px solid {{ $profile->is_verified ? '#b5ddd5' : '#fde68a' }}">
            <span style="font-size:1.2rem">{{ $profile->is_verified ? '✅' : '⏳' }}</span>
            <div>
                <div style="font-size:.8125rem;font-weight:600;color:{{ $profile->is_verified ? '#2a7a6a' : '#b45309' }}">
                    {{ $profile->is_verified ? 'Verified by Admin' : 'Pending Verification' }}
                </div>
                <div style="font-size:.72rem;color:{{ $profile->is_verified ? '#3a8a7a' : '#92400e' }};margin-top:2px">
                    {{ $profile->is_verified ? 'You can accept appointments.' : 'Complete your profile so admin can verify you.' }}
                </div>
            </div>
        </div>

        <div style="margin-top:14px;font-size:.72rem;color:var(--txt-lt);line-height:1.6;padding:10px 12px;background:var(--parch);border-radius:8px">
            After saving, an admin will verify your registration details. Once verified, you can start accepting appointments from patients.
        </div>
    </div>
</div>

</div>
@endsection
