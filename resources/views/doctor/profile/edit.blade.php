@php $role = auth()->user()->role; @endphp
@extends($role === 'doctor' ? 'layouts.doctor' : 'layouts.patient')
@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
    .pf-card  { background:var(--cream);border:1.5px solid var(--warm-bd);border-radius:14px;margin-bottom:16px;overflow:hidden; }
    .pf-head  { padding:13px 20px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;gap:8px;font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:var(--txt); }
    .pf-body  { padding:20px 22px; }
    .pf-grid  { display:grid;gap:14px; }
    .pf-2     { grid-template-columns:1fr 1fr; }
    .pf-3     { grid-template-columns:1fr 1fr 1fr; }
    .pf-21    { grid-template-columns:2fr 1fr; }
    .pf-12    { grid-template-columns:1fr 2fr; }
    .pf-label { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);display:block;margin-bottom:5px; }
    .pf-inp   { width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--parch);outline:none;font-family:'Outfit',sans-serif;transition:border-color .15s;box-sizing:border-box; }
    .pf-inp:focus { border-color:var(--leaf); }
    select.pf-inp { cursor:pointer; }
    textarea.pf-inp { resize:vertical;line-height:1.6; }
    .pf-err   { font-size:.72rem;color:#dc2626;margin-top:4px; }
    .pf-btn   { padding:.7rem 24px;background:var(--leaf);color:#fff;border:none;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s; }
    .pf-btn:hover { opacity:.88; }
    .tab-btn  { padding:9px 18px;font-size:.85rem;font-weight:500;border:none;background:transparent;cursor:pointer;border-bottom:2.5px solid transparent;color:var(--txt-lt);font-family:'Outfit',sans-serif;transition:all .15s; }
    .tab-btn.active { color:var(--leaf);border-bottom-color:var(--leaf);font-weight:600; }
    .lang-wrap { display:flex;flex-wrap:wrap;gap:6px;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:10px;background:var(--parch); }
    .pf-outer-grid { grid-template-columns:1fr 300px; }
    @media (max-width:900px) {
        .pf-outer-grid { grid-template-columns:1fr !important; }
        .pf-sidebar-sticky { position:static !important; }
    }
    @media (max-width:640px) {
        .pf-2, .pf-3, .pf-21, .pf-12 { grid-template-columns:1fr !important; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pincode = document.getElementById('personal-pincode');
    const city = document.getElementById('personal-city');
    const state = document.getElementById('personal-state');
    const status = document.getElementById('pincode-status');

    if (!pincode || !city || !state || !status) return;

    let lookupId = 0;
    const stateCities = {
        'Andhra Pradesh':['Amaravati','Visakhapatnam','Vijayawada','Tirupati'],
        'Arunachal Pradesh':['Itanagar','Tawang','Naharlagun'],
        'Assam':['Guwahati','Dibrugarh','Jorhat','Silchar'],
        'Bihar':['Patna','Gaya','Muzaffarpur','Bhagalpur'],
        'Chhattisgarh':['Raipur','Bhilai','Bilaspur','Durg'],
        'Goa':['Panaji','Margao','Vasco da Gama','Mapusa'],
        'Gujarat':['Ahmedabad','Surat','Vadodara','Rajkot','Gandhinagar'],
        'Haryana':['Gurugram','Faridabad','Panipat','Hisar'],
        'Himachal Pradesh':['Shimla','Dharamshala','Solan','Mandi'],
        'Jharkhand':['Ranchi','Jamshedpur','Dhanbad','Bokaro'],
        'Karnataka':['Bengaluru','Mysuru','Mangaluru','Hubballi'],
        'Kerala':['Thiruvananthapuram','Kochi','Kozhikode','Thrissur'],
        'Madhya Pradesh':['Bhopal','Indore','Jabalpur','Gwalior'],
        'Maharashtra':['Mumbai','Pune','Nagpur','Nashik','Thane'],
        'Manipur':['Imphal','Thoubal','Bishnupur'],
        'Meghalaya':['Shillong','Tura','Jowai'],
        'Mizoram':['Aizawl','Lunglei','Champhai'],
        'Nagaland':['Kohima','Dimapur','Mokokchung'],
        'Odisha':['Bhubaneswar','Cuttack','Rourkela','Puri'],
        'Punjab':['Chandigarh','Ludhiana','Amritsar','Jalandhar'],
        'Rajasthan':['Jaipur','Jodhpur','Udaipur','Kota','Ajmer'],
        'Sikkim':['Gangtok','Namchi','Gyalshing'],
        'Tamil Nadu':['Chennai','Coimbatore','Madurai','Salem'],
        'Telangana':['Hyderabad','Warangal','Nizamabad','Karimnagar'],
        'Tripura':['Agartala','Udaipur','Dharmanagar'],
        'Uttar Pradesh':['Lucknow','Kanpur','Agra','Varanasi','Noida','Ghaziabad'],
        'Uttarakhand':['Dehradun','Haridwar','Nainital','Haldwani'],
        'West Bengal':['Kolkata','Siliguri','Asansol','Durgapur'],
        'Delhi':['New Delhi','Delhi'],
        'Jammu & Kashmir':['Srinagar','Jammu','Anantnag'],
        'Ladakh':['Leh','Kargil'],
        'Puducherry':['Puducherry','Karaikal'],
        'Chandigarh':['Chandigarh']
    };

    function populateCities(selected = '') {
        const cities = stateCities[state.value] || [];
        city.innerHTML = '<option value="">— City —</option>';
        cities.forEach(name => city.add(new Option(name, name, false, name === selected)));
        if (selected && !cities.includes(selected)) {
            city.add(new Option(selected, selected, true, true));
        }
        city.disabled = cities.length === 0 && !selected;
    }

    async function lookupPincode() {
        const value = pincode.value.replace(/\D/g, '').slice(0, 6);
        pincode.value = value;
        status.textContent = '';

        if (value.length !== 6) return;

        const currentLookup = ++lookupId;
        status.textContent = 'Finding location...';
        status.style.color = 'var(--txt-lt)';

        try {
            const response = await fetch(`https://api.postalpincode.in/pincode/${value}`);
            const result = await response.json();
            const office = result[0]?.Status === 'Success' ? result[0].PostOffice?.[0] : null;

            if (currentLookup !== lookupId) return;

            if (!office) {
                status.textContent = 'Pincode not found. Enter the location manually.';
                status.style.color = '#dc2626';
                return;
            }

            const stateOption = [...state.options].find(option => option.value === office.State);
            if (stateOption) state.value = stateOption.value;
            populateCities(office.District || office.Block || office.Name || '');
            status.textContent = 'City and state updated from pincode.';
            status.style.color = 'var(--leaf)';
        } catch (error) {
            if (currentLookup !== lookupId) return;
            status.textContent = 'Lookup unavailable. Enter the location manually.';
            status.style.color = '#dc2626';
        }
    }

    state.addEventListener('change', () => {
        populateCities();
        status.textContent = pincode.value ? 'Pincode is optional.' : '';
    });
    pincode.addEventListener('input', lookupPincode);
    populateCities(city.dataset.selectedCity || '');
    if (pincode.value.length === 6) lookupPincode();
});
</script>
@endpush

@section('content')
<div class="fade-in" x-data="{ tab: 'personal' }">

@if(session('success'))
<div style="padding:11px 16px;background:#eef5f3;border:1px solid #b5ddd5;border-radius:10px;margin-bottom:18px;font-size:.85rem;color:#2a7a6a;display:flex;align-items:center;gap:8px">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="pf-outer-grid" style="display:grid;gap:22px;align-items:start">

{{-- ── LEFT ────────────────────────────────────────────────────────────────── --}}
<div>

    {{-- Tab bar --}}
    <div style="border-bottom:2px solid var(--warm-bd);margin-bottom:20px;display:flex;gap:0">
        <button class="tab-btn" :class="tab==='personal' ? 'active' : ''" @click="tab='personal'">Personal</button>
        @if($role === 'doctor')
        <button class="tab-btn" :class="tab==='professional' ? 'active' : ''" @click="tab='professional'">Professional</button>
        @endif
        <button class="tab-btn" :class="tab==='security' ? 'active' : ''" @click="tab='security'">Security</button>
    </div>

    {{-- ── TAB: Personal ──────────────────────────────────────────────────── --}}
    <div x-show="tab === 'personal'" x-transition>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')

            @if($errors->any())
            <div style="padding:11px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:14px">
                @foreach($errors->all() as $e)<div style="font-size:.8rem;color:#dc2626;margin-bottom:2px">• {{ $e }}</div>@endforeach
            </div>
            @endif

            {{-- Personal Details --}}
            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Personal Details
                </div>
                <div class="pf-body pf-grid">
                    <div>
                        <label class="pf-label">Full Name *</label>
                        <input type="text" name="full_name" class="pf-inp"
                               value="{{ old('full_name', $profile?->full_name) }}" required>
                        @error('full_name')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="pf-grid pf-3">
                        <div>
                            <label class="pf-label">Date of Birth</label>
                            <input type="date" name="dob" class="pf-inp"
                                   value="{{ old('dob', $profile?->dob?->format('Y-m-d')) }}"
                                   max="{{ today()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="pf-label">Gender</label>
                            <select name="gender" class="pf-inp">
                                <option value="">— Select —</option>
                                @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v => $l)
                                <option value="{{ $v }}" @selected(old('gender',$profile?->gender)===$v)>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pf-label">Blood Group</label>
                            <select name="blood_group" class="pf-inp">
                                <option value="">— Select —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                <option value="{{ $bg }}" @selected(old('blood_group',$profile?->blood_group)===$bg)>{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Address --}}
            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Address
                </div>
                <div class="pf-body pf-grid">
                    <div>
                        <label class="pf-label">Address</label>
                        <input type="text" name="address" class="pf-inp"
                               value="{{ old('address', $profile?->address) }}" placeholder="Street, Area, Landmark">
                        @error('address')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="pf-grid pf-3">
                        <div>
                            <label class="pf-label">City</label>
                                <select name="city" id="personal-city" class="pf-inp"
                                    data-selected-city="{{ old('city', $profile?->city) }}">
                                <option value="">— City —</option>
                            </select>
                            @error('city')<div class="pf-err">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="pf-label">State</label>
                            <select name="state" id="personal-state" class="pf-inp">
                                <option value="">— State —</option>
                                @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry','Chandigarh'] as $st)
                                <option value="{{ $st }}" @selected(old('state',$profile?->state)===$st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pf-label">Pincode</label>
                            <input type="text" name="pincode" id="personal-pincode" class="pf-inp"
                                   value="{{ old('pincode', $profile?->pincode) }}" maxlength="6" placeholder="6 digits">
                            <div id="pincode-status" class="pf-err" role="status" aria-live="polite"></div>
                            @error('pincode')<div class="pf-err">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Emergency Contact --}}
            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Emergency Contact
                </div>
                <div class="pf-body pf-grid pf-2">
                    <div>
                        <label class="pf-label">Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="pf-inp"
                               value="{{ old('emergency_contact_name', $profile?->emergency_contact_name) }}"
                               placeholder="Spouse / Parent name">
                        @error('emergency_contact_name')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="pf-label">Contact Mobile</label>
                        <input type="tel" name="emergency_contact_number" class="pf-inp"
                               value="{{ old('emergency_contact_number', $profile?->emergency_contact_number) }}"
                               maxlength="10" placeholder="10-digit number">
                        @error('emergency_contact_number')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div style="margin-bottom:6px">
                <button type="submit" class="pf-btn">Save Changes</button>
            </div>
        </form>
    </div>

    {{-- ── TAB: Professional (doctor only) ───────────────────────────────── --}}
    @if($role === 'doctor')
    <div x-show="tab === 'professional'" x-transition>

        {{-- Clinic Logo (separate form — must stay outside the form below; HTML forbids nested forms) --}}
        <div class="pf-card">
            <div class="pf-head">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16l5-5 4 4 5-6 4 5M4 19h16"/></svg>
                Clinic Logo
            </div>
            <div class="pf-body" style="display:flex;align-items:center;gap:16px">
                @php $logoUrl = $dp?->clinic_logo ? Storage::disk('public')->url($dp->clinic_logo) : null; @endphp
                <div style="width:64px;height:64px;border-radius:10px;border:1.5px solid var(--warm-bd);background:var(--parch);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                    @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Clinic logo" style="width:100%;height:100%;object-fit:contain">
                    @else
                    <span style="font-size:.7rem;color:var(--txt-lt)">No logo</span>
                    @endif
                </div>
                <div>
                    <form method="POST" action="{{ route('doctor.profile.logo') }}" enctype="multipart/form-data" id="logo-form">
                        @csrf
                        <label style="cursor:pointer;font-size:.8rem;padding:7px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt-md);background:var(--parch);transition:background .12s;display:inline-block"
                               onmouseover="this.style.background='var(--warm-bd)'" onmouseout="this.style.background='var(--parch)'">
                            🖼️ {{ $logoUrl ? 'Change Logo' : 'Upload Logo' }}
                            <input type="file" name="clinic_logo" accept="image/*" style="display:none"
                                   onchange="document.getElementById('logo-form').submit()">
                        </label>
                    </form>
                    <div style="font-size:.72rem;color:var(--txt-lt);margin-top:6px">PNG/JPG/WEBP, max 1MB. Shown on prescription PDFs.</div>
                    @error('clinic_logo')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')

            {{-- Clinic & Practice --}}
            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Clinic & Practice
                </div>
                <div class="pf-body pf-grid">
                    <div class="pf-grid pf-21">
                        <div>
                            <label class="pf-label">Clinic / Hospital Name</label>
                            <input type="text" name="clinic_name" class="pf-inp"
                                   value="{{ old('clinic_name', $dp?->clinic_name) }}" placeholder="e.g. Apollo Clinic">
                        </div>
                        <div>
                            <label class="pf-label">Consultation Fee (₹)</label>
                            <input type="number" name="consultation_fee" class="pf-inp" min="0"
                                   value="{{ old('consultation_fee', $dp?->consultation_fee) }}" placeholder="500">
                        </div>
                    </div>
                    <div>
                        <label class="pf-label">Clinic Address</label>
                        <input type="text" name="clinic_address" class="pf-inp"
                               value="{{ old('clinic_address', $dp?->clinic_address) }}" placeholder="Street, Area">
                    </div>
                    <div class="pf-grid pf-2">
                        <div>
                            <label class="pf-label">City</label>
                            <input type="text" name="clinic_city" class="pf-inp"
                                   value="{{ old('clinic_city', $dp?->clinic_city) }}">
                        </div>
                        <div>
                            <label class="pf-label">UPI ID</label>
                            <input type="text" name="upi_id" class="pf-inp"
                                   value="{{ old('upi_id', $dp?->upi_id) }}" placeholder="doctor@upi">
                        </div>
                    </div>
                    <div>
                        <label class="pf-label">Professional Bio</label>
                        <textarea name="bio" rows="4" class="pf-inp"
                                  placeholder="Brief description of your practice and expertise…">{{ old('bio', $dp?->bio) }}</textarea>
                    </div>
                    <div>
                        <label class="pf-label">Languages Spoken</label>
                        <div class="lang-wrap">
                            @foreach(['English','Hindi','Marathi','Tamil','Telugu','Kannada','Malayalam','Gujarati','Bengali','Punjabi','Urdu'] as $lang)
                            <label style="display:flex;align-items:center;gap:4px;cursor:pointer;font-size:.82rem;color:var(--txt-md)">
                                <input type="checkbox" name="languages_spoken[]" value="{{ $lang }}"
                                       {{ in_array($lang, old('languages_spoken', $dp?->languages_spoken ?? [])) ? 'checked' : '' }}
                                       style="accent-color:var(--leaf)">
                                {{ $lang }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:6px">
                <button type="submit" class="pf-btn">Save Clinic Details</button>
            </div>
        </form>
    </div>
    @endif

    {{-- ── TAB: Security ──────────────────────────────────────────────────── --}}
    <div x-show="tab === 'security'" x-transition>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')

            @if($errors->has('current_password') || $errors->has('password'))
            <div style="padding:11px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:14px">
                @foreach($errors->only('current_password','password') as $msgs)
                    @foreach((array)$msgs as $m)<div style="font-size:.8rem;color:#dc2626;margin-bottom:2px">• {{ $m }}</div>@endforeach
                @endforeach
            </div>
            @endif

            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Change Password
                </div>
                <div class="pf-body pf-grid" x-data="{show:false}">
                    <div>
                        <label class="pf-label">Current Password</label>
                        <input :type="show ? 'text' : 'password'" name="current_password" class="pf-inp"
                               autocomplete="current-password" placeholder="Enter current password" required>
                        @error('current_password')<div class="pf-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="pf-grid pf-2">
                        <div>
                            <label class="pf-label">New Password</label>
                            <input :type="show ? 'text' : 'password'" name="password" class="pf-inp"
                                   autocomplete="new-password" placeholder="Min 8 characters" required>
                            @error('password')<div class="pf-err">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="pf-label">Confirm New Password</label>
                            <input :type="show ? 'text' : 'password'" name="password_confirmation" class="pf-inp"
                                   autocomplete="new-password" placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <div>
                        <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.8rem;color:var(--txt-lt);font-weight:400">
                            <input type="checkbox" @change="show = !show" style="accent-color:var(--leaf);width:14px;height:14px">
                            Show passwords
                        </label>
                    </div>
                </div>
            </div>

            {{-- Account Info strip --}}
            <div class="pf-card">
                <div class="pf-head">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Account Info
                </div>
                <div class="pf-body" style="padding-top:14px;padding-bottom:14px">
                    @foreach([
                        'Mobile'       => auth()->user()->country_code.' '.auth()->user()->mobile_number,
                        'Role'         => ucfirst(auth()->user()->role),
                        'Joined'       => auth()->user()->created_at->format('d M Y'),
                        'Last Updated' => auth()->user()->updated_at->diffForHumans(),
                    ] as $k => $v)
                    <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--warm-bd);font-size:.82rem{{ $loop->last ? ';border-bottom:none' : '' }}">
                        <span style="color:var(--txt-lt)">{{ $k }}</span>
                        <span style="color:var(--txt-md);font-weight:500">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="margin-bottom:6px">
                <button type="submit" class="pf-btn">Update Password</button>
            </div>
        </form>
    </div>

</div>

{{-- ── RIGHT: Avatar + status cards ───────────────────────────────────────── --}}
<div class="pf-sidebar-sticky" style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Avatar --}}
    <div class="pf-card" style="overflow:hidden;text-align:center;padding:22px">
        @php
            $name     = $profile?->full_name ?? 'User';
            $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$name),0,2))));
            $photoUrl = $profile?->profile_photo ? Storage::disk('public')->url($profile->profile_photo) : null;
        @endphp

        <div style="display:inline-block;margin-bottom:12px">
            @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $name }}"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--warm-bd)">
            @else
            <div style="width:80px;height:80px;border-radius:50%;background:var(--leaf);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;margin:0 auto">
                {{ $initials }}
            </div>
            @endif
        </div>

        <div style="font-weight:600;font-size:.9375rem;color:var(--txt);margin-bottom:2px">{{ $name }}</div>
        <div style="font-size:.78rem;color:var(--txt-lt);margin-bottom:14px">
            {{ ucfirst(auth()->user()->role) }}
            @if($role==='doctor' && $dp?->specialization) · {{ $dp->specialization }} @endif
        </div>

        <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" id="photo-form">
            @csrf
            <label style="cursor:pointer;font-size:.8rem;padding:7px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt-md);background:var(--parch);transition:background .12s;display:inline-block"
                   onmouseover="this.style.background='var(--warm-bd)'" onmouseout="this.style.background='var(--parch)'">
                📷 Change Photo
                <input type="file" name="photo" accept="image/*" style="display:none"
                       onchange="document.getElementById('photo-form').submit()">
            </label>
        </form>
        @error('photo')<div class="pf-err" style="margin-top:6px">{{ $message }}</div>@enderror
    </div>

    {{-- Verification status (doctor) --}}
    @if($role === 'doctor')
    <div class="pf-card" style="background:{{ $dp?->is_verified ? '#eef5f3' : '#fef9ec' }};border-color:{{ $dp?->is_verified ? '#b5ddd5' : '#fde68a' }};overflow:visible">
        <div style="padding:14px 16px;display:flex;gap:10px;align-items:center">
            <span style="font-size:1.2rem">{{ $dp?->is_verified ? '✅' : '⏳' }}</span>
            <div>
                <div style="font-size:.8125rem;font-weight:600;color:{{ $dp?->is_verified ? '#2a7a6a' : '#b45309' }}">
                    {{ $dp?->is_verified ? 'Verified' : 'Pending Verification' }}
                </div>
                <div style="font-size:.72rem;color:{{ $dp?->is_verified ? '#3a8a7a' : '#92400e' }}">
                    {{ $dp?->is_verified ? 'Your account is verified and active.' : 'Admin will verify your credentials.' }}
                </div>
            </div>
        </div>
        @if($dp?->rejection_reason)
        <div style="margin:0 14px 12px;padding:8px 10px;background:#fef2f2;border-radius:8px;font-size:.75rem;color:#dc2626">
            Rejection reason: {{ $dp->rejection_reason }}
        </div>
        @endif
    </div>

    {{-- Premium status --}}
    <div class="pf-card" style="background:{{ $dp?->is_premium ? '#f4f0fa' : 'var(--parch)' }};overflow:visible">
        <div style="padding:14px 16px">
            <div style="font-size:.8rem;font-weight:600;color:{{ $dp?->is_premium ? '#4a3760' : 'var(--txt-md)' }}">
                {{ $dp?->is_premium ? '⭐ Premium Account' : '🆓 Free Account' }}
            </div>
            @if($dp?->is_premium && $dp?->premium_expires_at)
            <div style="font-size:.72rem;color:var(--txt-lt);margin-top:3px">
                Expires {{ \Carbon\Carbon::parse($dp->premium_expires_at)->format('d M Y') }}
            </div>
            @elseif(!$dp?->is_premium)
            <div style="font-size:.72rem;color:var(--txt-lt);margin-top:3px">Upgrade for WhatsApp, timelines &amp; Excel</div>
            @endif
        </div>
    </div>
    @endif

</div>

</div>
</div>
@endsection
