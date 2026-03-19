@extends('layouts.admin')
@section('title', 'Register Doctor')
@section('page-title')
    <a href="{{ route('admin.users.doctors') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Doctors</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Register New Doctor
@endsection

@section('content')
<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start">

{{-- ── LEFT: Form ───────────────────────────────────────────────────────────── --}}
<div>
<form method="POST" action="{{ route('admin.doctors.store') }}">
    @csrf

    @if($errors->any())
    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:18px">
        @foreach($errors->all() as $e)<div style="font-size:.8rem;color:#dc2626">• {{ $e }}</div>@endforeach
    </div>
    @endif

    {{-- ── Section 1: Account ──────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px 24px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px">
            <span style="width:26px;height:26px;border-radius:7px;background:var(--accent);color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center">1</span>
            Login Credentials
        </div>

        <div style="display:grid;grid-template-columns:auto 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Country</label>
                <select name="country_code" class="inp" style="min-width:100px">
                    <option value="+91" selected>🇮🇳 +91 India</option>
                    <option value="+1">🇺🇸 +1 USA</option>
                    <option value="+44">🇬🇧 +44 UK</option>
                    <option value="+971">🇦🇪 +971 UAE</option>
                    <option value="+65">🇸🇬 +65 Singapore</option>
                    <option value="+61">🇦🇺 +61 Australia</option>
                </select>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Mobile Number <span style="color:#ef4444">*</span></label>
                <input type="tel" name="mobile_number" value="{{ old('mobile_number') }}"
                       class="inp" style="width:100%" placeholder="10-digit mobile" maxlength="10"
                       pattern="[6-9]\d{9}" required autofocus>
            </div>
        </div>

        <div style="padding:10px 13px;background:#eff6ff;border-radius:9px;border:1px solid #bfdbfe;font-size:.78rem;color:#1e40af">
            💡 The doctor will log in using this mobile number. They'll set their own password on first login.
        </div>
    </div>

    {{-- ── Section 2: Personal ─────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px 24px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px">
            <span style="width:26px;height:26px;border-radius:7px;background:var(--accent);color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center">2</span>
            Personal Details
        </div>

        <div style="margin-bottom:14px">
            <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Full Name <span style="color:#ef4444">*</span></label>
            <input type="text" name="full_name" value="{{ old('full_name') }}"
                   class="inp" style="width:100%" placeholder="Dr. Full Name (as on medical registration)" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}"
                       class="inp" style="width:100%" max="{{ today()->format('Y-m-d') }}">
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Gender</label>
                <select name="gender" class="inp" style="width:100%">
                    <option value="">— Select —</option>
                    <option value="male"   {{ old('gender')==='male'   ? 'selected':'' }}>Male</option>
                    <option value="female" {{ old('gender')==='female' ? 'selected':'' }}>Female</option>
                    <option value="other"  {{ old('gender')==='other'  ? 'selected':'' }}>Other</option>
                </select>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="inp" style="width:100%" placeholder="doctor@hospital.com">
            </div>
        </div>
    </div>

    {{-- ── Section 3: Professional ──────────────────────────────────────────── --}}
    <div class="card" style="padding:20px 24px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px">
            <span style="width:26px;height:26px;border-radius:7px;background:var(--accent);color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center">3</span>
            Professional Credentials <span style="font-size:.7rem;font-weight:400;color:var(--txt-lt);margin-left:4px">(required for verification)</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Specialization <span style="color:#ef4444">*</span></label>
                <select name="specialization" class="inp" style="width:100%" required>
                    <option value="">— Select —</option>
                    @foreach([
                        'General Medicine','Family Medicine','Internal Medicine',
                        'Obstetrics & Gynecology','Pediatrics','Neonatology',
                        'Cardiology','Dermatology','Neurology','Orthopedics',
                        'Ophthalmology','ENT','Psychiatry','Oncology',
                        'Gastroenterology','Nephrology','Pulmonology','Endocrinology',
                        'Urology','Dentistry','Radiology','Anesthesiology',
                        'Emergency Medicine','Reproductive Medicine','Physiotherapy',
                        'Nutrition & Dietetics','Other',
                    ] as $spec)
                    <option value="{{ $spec }}" {{ old('specialization')===$spec ? 'selected':'' }}>{{ $spec }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Sub-Specialization</label>
                <input type="text" name="sub_specialization" value="{{ old('sub_specialization') }}"
                       class="inp" style="width:100%" placeholder="e.g. High Risk Pregnancy">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Registration Number <span style="color:#ef4444">*</span></label>
                <input type="text" name="registration_number" value="{{ old('registration_number') }}"
                       class="inp" style="width:100%;font-family:monospace" placeholder="e.g. MH-12345" required>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Registration Council <span style="color:#ef4444">*</span></label>
                <select name="registration_council" class="inp" style="width:100%" required>
                    <option value="">— Select —</option>
                    <option value="Medical Council of India (MCI)" {{ old('registration_council')==='Medical Council of India (MCI)' ? 'selected':'' }}>Medical Council of India (MCI)</option>
                    <option value="National Medical Commission (NMC)" {{ old('registration_council')==='National Medical Commission (NMC)' ? 'selected':'' }}>National Medical Commission (NMC)</option>
                    @foreach(['Andhra Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'] as $state)
                    @php $val = $state.' Medical Council'; @endphp
                    <option value="{{ $val }}" {{ old('registration_council')===$val ? 'selected':'' }}>{{ $val }}</option>
                    @endforeach
                    <option value="Dental Council of India" {{ old('registration_council')==='Dental Council of India' ? 'selected':'' }}>Dental Council of India</option>
                    <option value="Indian Nursing Council" {{ old('registration_council')==='Indian Nursing Council' ? 'selected':'' }}>Indian Nursing Council</option>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Qualification <span style="color:#ef4444">*</span></label>
                <input type="text" name="qualification" value="{{ old('qualification') }}"
                       class="inp" style="width:100%" placeholder="e.g. MBBS, MD (Obstetrics), DNB" required>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Experience (Years)</label>
                <input type="number" name="experience_years" value="{{ old('experience_years') }}"
                       class="inp" style="width:100%" min="0" max="60" placeholder="e.g. 12">
            </div>
        </div>
    </div>

    {{-- ── Section 4: Clinic ────────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px 24px;margin-bottom:16px">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:8px">
            <span style="width:26px;height:26px;border-radius:7px;background:var(--accent);color:#fff;font-size:.75rem;font-weight:700;display:flex;align-items:center;justify-content:center">4</span>
            Clinic & Practice <span style="font-size:.7rem;font-weight:400;color:var(--txt-lt);margin-left:4px">optional</span>
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Clinic / Hospital Name</label>
            <input type="text" name="clinic_name" value="{{ old('clinic_name') }}"
                   class="inp" style="width:100%" placeholder="e.g. Apollo Clinic, Nagpur">
        </div>

        <div style="margin-bottom:12px">
            <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Clinic Address</label>
            <input type="text" name="clinic_address" value="{{ old('clinic_address') }}"
                   class="inp" style="width:100%" placeholder="Street, Area">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">City</label>
                <input type="text" name="clinic_city" value="{{ old('clinic_city') }}"
                       class="inp" style="width:100%" placeholder="e.g. Nagpur">
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">State</label>
                <select name="clinic_state" class="inp" style="width:100%">
                    <option value="">— State —</option>
                    @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry'] as $state)
                    <option value="{{ $state }}" {{ old('clinic_state')===$state ? 'selected':'' }}>{{ $state }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Consultation Fee (₹)</label>
                <input type="number" name="consultation_fee" value="{{ old('consultation_fee') }}"
                       class="inp" style="width:100%" min="0" placeholder="e.g. 500">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">UPI ID</label>
                <input type="text" name="upi_id" value="{{ old('upi_id') }}"
                       class="inp" style="width:100%" placeholder="e.g. doctor@upi">
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px">Languages Spoken</label>
                <div style="display:flex;flex-wrap:wrap;gap:5px;padding:8px 10px;border:1.5px solid var(--bd);border-radius:9px;background:#fff;min-height:38px">
                    @foreach(['English','Hindi','Marathi','Tamil','Telugu','Kannada','Malayalam','Gujarati','Bengali','Punjabi','Urdu'] as $lang)
                    <label style="display:flex;align-items:center;gap:3px;cursor:pointer;font-size:.75rem;color:var(--txt-md)">
                        <input type="checkbox" name="languages_spoken[]" value="{{ $lang }}"
                               {{ in_array($lang, old('languages_spoken', ['English','Hindi'])) ? 'checked':'' }}
                               style="accent-color:var(--accent)">
                        {{ $lang }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <button type="submit"
            style="width:100%;padding:.85rem;background:var(--accent);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s;display:flex;align-items:center;justify-content:center;gap:9px"
            onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Register Doctor & Proceed to Verification
    </button>
    <div style="text-align:center;font-size:.75rem;color:var(--txt-lt);margin-top:10px">
        Doctor will be unverified by default — you'll verify credentials on the next screen.
    </div>
</form>
</div>

{{-- ── RIGHT: Info ──────────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- What happens --}}
    <div class="card" style="padding:18px 20px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">What Happens Next</div>
        @foreach([
            ['🔐', 'Account created with mobile number login'],
            ['📋', 'Professional profile saved'],
            ['✏️', 'You\'re taken to the verification screen'],
            ['✅', 'Approve to let them accept appointments'],
            ['📱', 'Doctor logs in using their mobile number'],
            ['🔑', 'They set their own password on first login'],
        ] as [$icon, $text])
        <div style="display:flex;gap:9px;padding:6px 0;border-bottom:1px solid var(--bd);font-size:.8rem;color:var(--txt-md)">
            <span style="flex-shrink:0">{{ $icon }}</span>
            <span style="line-height:1.45">{{ $text }}</span>
        </div>
        @endforeach
    </div>

    {{-- Required fields --}}
    <div class="card" style="padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Required Fields</div>
        @foreach(['Mobile Number', 'Full Name', 'Specialization', 'Registration Number', 'Registration Council', 'Qualification'] as $f)
        <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--txt-md);padding:3px 0">
            <span style="color:#ef4444">*</span> {{ $f }}
        </div>
        @endforeach
    </div>

    {{-- Alternative: doctor self-registers --}}
    <div style="padding:14px 16px;background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px">
        <div style="font-size:.75rem;font-weight:600;color:#b45309;margin-bottom:5px">Alternatively</div>
        <div style="font-size:.75rem;color:#92400e;line-height:1.5">
            The doctor can register themselves at
            <code style="background:#fef3c7;padding:1px 5px;border-radius:4px">/login</code>
            by choosing "Doctor" during role setup, then fill their profile at
            <code style="background:#fef3c7;padding:1px 5px;border-radius:4px">/doctor/setup</code>
        </div>
    </div>

    <a href="{{ route('admin.users.doctors') }}"
       style="display:flex;align-items:center;justify-content:center;padding:9px;border:1.5px solid var(--bd);border-radius:10px;font-size:.8rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
       onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
        ← View All Doctors
    </a>
</div>

</div>
@endsection
