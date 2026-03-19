@extends('layouts.doctor')
@section('title', 'Register Walk-in Patient')
@section('page-title', 'Register New Patient')

@push('styles')
<style>
.field-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--txt-lt); display: block; margin-bottom: 5px;
}
.field-inp {
    width: 100%; padding: .6rem .9rem;
    border: 1.5px solid var(--warm-bd); border-radius: 10px;
    font-size: .875rem; color: var(--txt); background: #fff;
    outline: none; font-family: 'Outfit', sans-serif; transition: border-color .15s;
}
.field-inp:focus { border-color: var(--leaf); }
.section-card {
    background: #fff; border: 1.5px solid var(--warm-bd);
    border-radius: 14px; padding: 20px 22px; margin-bottom: 16px;
}
.section-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1rem; font-weight: 500; color: var(--txt);
    margin-bottom: 16px; padding-bottom: 10px;
    border-bottom: 1.5px solid var(--warm-bd);
    display: flex; align-items: center; gap: 8px;
}
.pill-select { display: flex; gap: 6px; flex-wrap: wrap; }
.pill-opt { cursor: pointer; }
.pill-opt input { display: none; }
.pill-lbl {
    display: block; padding: 6px 14px;
    border: 1.5px solid var(--warm-bd); border-radius: 20px;
    font-size: .8rem; font-weight: 500; color: var(--txt-md);
    transition: all .15s; user-select: none;
}
.pill-opt input:checked + .pill-lbl {
    background: var(--leaf); color: #fff; border-color: var(--leaf);
}
</style>
@endpush

@section('content')
<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ── LEFT: Form ───────────────────────────────────────────────────────────── --}}
<div>
<form method="POST" action="{{ route('doctor.quick-register.store') }}" x-data="{ mobile: '', exists: false, checking: false }">
    @csrf

    @if($errors->any())
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:16px">
        @foreach($errors->all() as $e)<div style="font-size:.8rem;color:#dc2626">• {{ $e }}</div>@endforeach
    </div>
    @endif

    @if(session('info'))
    <div style="padding:12px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:16px;font-size:.875rem;color:#1e40af">
        ℹ️ {{ session('info') }}
    </div>
    @endif

    {{-- ── Section 1: Identity ─────────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span style="width:28px;height:28px;border-radius:8px;background:var(--leaf);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0">1</span>
            Patient Identity
        </div>

        {{-- Full name --}}
        <div style="margin-bottom:14px">
            <label class="field-label">Full Name <span style="color:var(--coral)">*</span></label>
            <input type="text" name="full_name" value="{{ old('full_name') }}"
                   class="field-inp" placeholder="As on Aadhaar / ID proof" autofocus required>
        </div>

        {{-- Mobile --}}
        <div style="margin-bottom:14px">
            <label class="field-label">Mobile Number <span style="color:var(--coral)">*</span></label>
            <div style="display:flex;gap:8px">
                <select name="country_code"
                        style="width:90px;padding:.6rem .7rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:'Outfit',sans-serif">
                    <option value="+91" selected>🇮🇳 +91</option>
                    <option value="+1">🇺🇸 +1</option>
                    <option value="+44">🇬🇧 +44</option>
                    <option value="+971">🇦🇪 +971</option>
                    <option value="+65">🇸🇬 +65</option>
                    <option value="+61">🇦🇺 +61</option>
                </select>
                <input type="tel" name="mobile_number" value="{{ old('mobile_number') }}"
                       x-model="mobile"
                       class="field-inp" placeholder="10-digit mobile number"
                       pattern="[6-9]\d{9}" maxlength="10" required>
            </div>
            <div style="font-size:.72rem;color:var(--txt-lt);margin-top:5px">
                💡 If the patient already has an account, they will be linked automatically.
            </div>
        </div>
    </div>

    {{-- ── Section 2: Basic Details ────────────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span style="width:28px;height:28px;border-radius:8px;background:var(--leaf);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;flex-shrink:0">2</span>
            Basic Details
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
            {{-- DOB --}}
            <div>
                <label class="field-label">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="field-inp" max="{{ today()->format('Y-m-d') }}">
            </div>

            {{-- Blood Group --}}
            <div>
                <label class="field-label">Blood Group</label>
                <select name="blood_group" class="field-inp">
                    <option value="">— Unknown —</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    <option value="{{ $bg }}" {{ old('blood_group')===$bg ? 'selected':'' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Gender --}}
        <div>
            <label class="field-label">Gender</label>
            <div class="pill-select">
                @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $lbl)
                <label class="pill-opt">
                    <input type="radio" name="gender" value="{{ $val }}" {{ old('gender')===$val ? 'checked':'' }}>
                    <span class="pill-lbl">{{ $lbl }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Section 3: Address (optional) ──────────────────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span style="width:28px;height:28px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;color:#0369a1;font-size:.75rem;font-weight:700;flex-shrink:0">3</span>
            Address
            <span style="font-size:.7rem;font-weight:400;color:var(--txt-lt);margin-left:4px">optional</span>
        </div>

        <div style="margin-bottom:12px">
            <label class="field-label">Address</label>
            <input type="text" name="address" value="{{ old('address') }}"
                   class="field-inp" placeholder="House / Flat, Street, Area">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <label class="field-label">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       class="field-inp" placeholder="e.g. Nagpur">
            </div>
            <div>
                <label class="field-label">State</label>
                <select name="state" class="field-inp">
                    <option value="">— Select State —</option>
                    @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry','Chandigarh'] as $state)
                    <option value="{{ $state }}" {{ old('state')===$state ? 'selected':'' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Section 4: Emergency Contact (optional) ─────────────────────────── --}}
    <div class="section-card">
        <div class="section-title">
            <span style="width:28px;height:28px;border-radius:8px;background:#fef2f2;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:.75rem;font-weight:700;flex-shrink:0">4</span>
            Emergency Contact
            <span style="font-size:.7rem;font-weight:400;color:var(--txt-lt);margin-left:4px">optional</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <label class="field-label">Contact Name</label>
                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}"
                       class="field-inp" placeholder="e.g. Spouse / Parent name">
            </div>
            <div>
                <label class="field-label">Contact Mobile</label>
                <input type="tel" name="emergency_contact_number" value="{{ old('emergency_contact_number') }}"
                       class="field-inp" placeholder="10-digit number" maxlength="10">
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <button type="submit"
            style="width:100%;padding:.85rem;background:var(--leaf);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s;display:flex;align-items:center;justify-content:center;gap:9px"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
        </svg>
        Register Patient & Grant Access
    </button>

    <div style="text-align:center;font-size:.75rem;color:var(--txt-lt);margin-top:10px">
        The patient will be registered instantly and you'll get immediate access to their records.
    </div>
</form>
</div>

{{-- ── RIGHT: Info panel ────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- What happens --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:18px 20px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">What Happens Next</div>
        @foreach([
            ['🧑', 'Patient account created with their mobile number'],
            ['🏷️', 'Unique Sub-ID generated (e.g. MED-00042-A)'],
            ['✅', 'Your access is granted immediately — no OTP needed'],
            ['📋', 'You can start adding medical records right away'],
            ['📱', 'Patient can log in later using their mobile number'],
            ['🔑', 'They set their own password on first login'],
        ] as [$icon, $text])
        <div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--warm-bd);font-size:.8rem;color:var(--txt-md)">
            <span style="flex-shrink:0;font-size:1rem">{{ $icon }}</span>
            <span style="line-height:1.45">{{ $text }}</span>
        </div>
        @endforeach
    </div>

    {{-- Already registered --}}
    <div style="background:#fef9ec;border:1.5px solid #fde68a;border-radius:12px;padding:14px 16px">
        <div style="font-size:.75rem;font-weight:600;color:#b45309;margin-bottom:5px">⚠ Patient Already Registered?</div>
        <div style="font-size:.75rem;color:#92400e;line-height:1.5">
            If the mobile number is already in the system, the existing account will be linked and access granted — no duplicate created.
        </div>
    </div>

    {{-- Minimum required --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:12px;padding:14px 16px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Minimum Required</div>
        <div style="display:flex;flex-direction:column;gap:4px">
            <div style="font-size:.8rem;color:var(--txt-md);display:flex;align-items:center;gap:6px">
                <span style="color:var(--coral)">*</span> Full Name
            </div>
            <div style="font-size:.8rem;color:var(--txt-md);display:flex;align-items:center;gap:6px">
                <span style="color:var(--coral)">*</span> Mobile Number
            </div>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:4px">
                All other fields can be filled in later by the patient.
            </div>
        </div>
    </div>

    {{-- Search existing patient --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:12px;padding:14px 16px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Or Search Existing</div>
        <a href="{{ route('doctor.patients.index') }}"
           style="display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt-md);text-decoration:none;font-size:.8125rem;font-weight:500;transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>
            Search Patient List
        </a>
    </div>
</div>

</div>
@endsection
