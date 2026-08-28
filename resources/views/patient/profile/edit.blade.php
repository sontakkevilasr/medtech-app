@extends('layouts.patient')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@push('styles')
<style>
    .pf-card { background:var(--white);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:20px;overflow:hidden; }
    .pf-head { padding:13px 20px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;gap:8px;font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt); }
    .pf-body { padding:20px; }
    .pf-grid  { display:grid;gap:16px; }
    .pf-row2  { grid-template-columns:1fr 1fr; }
    .pf-row3  { grid-template-columns:1fr 2fr; }
    .pf-label { font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px; }
    .pf-inp   { width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;font-family:'Outfit',sans-serif;color:var(--txt);background:var(--cream);box-sizing:border-box;outline:none;transition:border-color .15s; }
    .pf-inp:focus { border-color:var(--plum,#6b4fa0); }
    select.pf-inp { cursor:pointer; }
    .pf-btn   { padding:9px 22px;background:var(--plum,#6b4fa0);color:#fff;border:none;border-radius:9px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s; }
    .pf-btn:hover { opacity:.88; }
    .pf-err   { font-size:.73rem;color:#dc2626;margin-top:4px; }
    .pf-location-status { font-size:.73rem;margin-top:4px; }
    .bg-pill  { padding:6px 13px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;font-weight:600;color:var(--txt-md);background:var(--cream);cursor:pointer;transition:all .15s; }
    .rel-pill { text-align:center;padding:8px 6px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;font-weight:500;color:var(--txt-md);background:var(--cream);cursor:pointer;transition:all .15s; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pincode = document.getElementById('patient-pincode');
    const city = document.getElementById('patient-city');
    const state = document.getElementById('patient-state');
    const status = document.getElementById('patient-pincode-status');

    if (!pincode || !city || !state || !status) return;

    let lookupId = 0;
    const stateCities = {
        'Andhra Pradesh': ['Amaravati', 'Visakhapatnam', 'Vijayawada', 'Tirupati'],
        'Arunachal Pradesh': ['Itanagar', 'Naharlagun', 'Pasighat'],
        'Assam': ['Guwahati', 'Dibrugarh', 'Jorhat', 'Silchar'],
        'Bihar': ['Patna', 'Gaya', 'Muzaffarpur', 'Bhagalpur'],
        'Chhattisgarh': ['Raipur', 'Bhilai', 'Bilaspur', 'Durg'],
        'Goa': ['Panaji', 'Margao', 'Vasco da Gama', 'Mapusa'],
        'Gujarat': ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Gandhinagar'],
        'Haryana': ['Gurugram', 'Faridabad', 'Panipat', 'Hisar'],
        'Himachal Pradesh': ['Shimla', 'Dharamshala', 'Solan', 'Mandi'],
        'Jharkhand': ['Ranchi', 'Jamshedpur', 'Dhanbad', 'Bokaro'],
        'Karnataka': ['Bengaluru', 'Mysuru', 'Mangaluru', 'Hubballi'],
        'Kerala': ['Thiruvananthapuram', 'Kochi', 'Kozhikode', 'Thrissur'],
        'Madhya Pradesh': ['Bhopal', 'Indore', 'Jabalpur', 'Gwalior'],
        'Maharashtra': ['Mumbai', 'Pune', 'Nagpur', 'Nashik', 'Thane'],
        'Manipur': ['Imphal', 'Thoubal', 'Churachandpur'],
        'Meghalaya': ['Shillong', 'Tura', 'Jowai'],
        'Mizoram': ['Aizawl', 'Lunglei', 'Champhai'],
        'Nagaland': ['Kohima', 'Dimapur', 'Mokokchung'],
        'Odisha': ['Bhubaneswar', 'Cuttack', 'Rourkela', 'Puri'],
        'Punjab': ['Chandigarh', 'Ludhiana', 'Amritsar', 'Jalandhar'],
        'Rajasthan': ['Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Ajmer'],
        'Sikkim': ['Gangtok', 'Namchi', 'Gyalshing'],
        'Tamil Nadu': ['Chennai', 'Coimbatore', 'Madurai', 'Salem'],
        'Telangana': ['Hyderabad', 'Warangal', 'Nizamabad', 'Karimnagar'],
        'Tripura': ['Agartala', 'Udaipur', 'Dharmanagar'],
        'Uttar Pradesh': ['Lucknow', 'Kanpur', 'Agra', 'Varanasi', 'Noida', 'Ghaziabad'],
        'Uttarakhand': ['Dehradun', 'Haridwar', 'Nainital', 'Haldwani'],
        'West Bengal': ['Kolkata', 'Siliguri', 'Asansol', 'Durgapur'],
        'Delhi': ['New Delhi', 'Delhi'],
        'Jammu & Kashmir': ['Srinagar', 'Jammu', 'Anantnag'],
        'Ladakh': ['Leh', 'Kargil'],
        'Puducherry': ['Puducherry', 'Karaikal'],
        'Chandigarh': ['Chandigarh']
    };

    function setCityOptions(offices, selectFirst = false) {
        const selectedCity = city.dataset.selectedCity || city.value;
        const cities = [...new Set(offices
            .map(office => office.District || office.Block || office.Name)
            .filter(Boolean))];

        city.innerHTML = '<option value="">— Select city —</option>';
        cities.forEach(cityName => {
            city.add(new Option(cityName, cityName));
        });
        if (cities.length) city.value = cities.includes(selectedCity) ? selectedCity : cities[0];
        city.disabled = cities.length === 0;
    }

    function setStateCities(stateName) {
        const selectedCity = city.dataset.selectedCity || city.value;
        const cities = stateCities[stateName] || [];
        city.innerHTML = cities.length
            ? '<option value="">— Select city —</option>'
            : '<option value="">No cities available</option>';
        cities.forEach(cityName => {
            city.add(new Option(cityName, cityName));
        });
        if (cities.length && cities.includes(selectedCity)) city.value = selectedCity;
        city.disabled = cities.length === 0;
    }

    async function lookupPincode() {
        const value = pincode.value.replace(/\D/g, '').slice(0, 6);
        pincode.value = value;
        status.textContent = '';

        if (value.length !== 6) {
            if (value.length === 0) {
                setStateCities(state.value);
            } else {
                city.innerHTML = '<option value="">Enter 6-digit pincode</option>';
                city.disabled = true;
            }
            return;
        }

        const currentLookup = ++lookupId;
        status.textContent = 'Finding location...';
        status.style.color = 'var(--txt-lt)';

        try {
            const response = await fetch(`https://api.postalpincode.in/pincode/${value}`);
            const result = await response.json();
            const offices = result[0]?.Status === 'Success' ? (result[0].PostOffice || []) : [];
            const office = offices[0];

            if (currentLookup !== lookupId) return;

            if (!office) {
                setStateCities(state.value);
                status.textContent = 'Pincode not found. Enter the location manually.';
                status.style.color = '#dc2626';
                return;
            }

            setCityOptions(offices, true);
            const stateOption = [...state.options].find(option => option.value === office.State);
            if (stateOption) state.value = stateOption.value;
            status.textContent = 'City and state updated from pincode.';
            status.style.color = 'var(--plum)';
        } catch (error) {
            if (currentLookup !== lookupId) return;
            status.textContent = 'Lookup unavailable. Enter the location manually.';
            status.style.color = '#dc2626';
        }
    }

    pincode.addEventListener('input', lookupPincode);
    state.addEventListener('change', () => {
        lookupId++;
        if (pincode.value.length === 6) pincode.value = '';
        setStateCities(state.value);
        status.textContent = 'Select your city. Pincode is optional.';
        status.style.color = 'var(--txt-lt)';
    });
    setStateCities(state.value);
    if (pincode.value.length === 6) lookupPincode();
});
</script>
@endpush

@section('content')
<div style="max-width:580px">

@if(session('success'))
<div style="padding:11px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;color:#166534;font-size:.85rem;margin-bottom:18px;display:flex;align-items:center;gap:8px">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="padding:11px 16px;background:#fee2e2;border:1px solid #fecaca;border-radius:10px;color:#991b1b;font-size:.85rem;margin-bottom:18px">
    {{ session('error') }}
</div>
@endif

{{-- ① Personal Details --}}
<form method="POST" action="{{ route('patient.profile.personal') }}">
    @csrf @method('PUT')

    <div class="pf-card">
        <div class="pf-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--plum,#6b4fa0)"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Personal Details
        </div>
        <div class="pf-body pf-grid">

            <div>
                <label class="pf-label">Full Name <span style="color:#dc2626">*</span></label>
                <input type="text" name="full_name" class="pf-inp"
                       value="{{ old('full_name', $profile?->full_name) }}"
                       placeholder="As it appears on official documents" required>
                @error('full_name')<div class="pf-err">{{ $message }}</div>@enderror
            </div>

            <div class="pf-grid pf-row2">
                <div>
                    <label class="pf-label">Date of Birth</label>
                    <input type="date" name="dob" class="pf-inp"
                           value="{{ old('dob', $profile?->dob?->format('Y-m-d')) }}"
                           max="{{ today()->format('Y-m-d') }}">
                    @error('dob')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="pf-label">Gender</label>
                    <select name="gender" class="pf-inp">
                        <option value="">— Select —</option>
                        @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(old('gender', $profile?->gender) === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="pf-label">Blood Group</label>
                <div style="display:flex;gap:7px;flex-wrap:wrap;margin-top:2px">
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    @php $bgSel = old('blood_group', $profile?->blood_group) === $bg; @endphp
                    <label style="cursor:pointer">
                        <input type="radio" name="blood_group" value="{{ $bg }}" {{ $bgSel ? 'checked' : '' }} style="display:none" class="bgr">
                        <div class="bg-pill" style="{{ $bgSel ? 'background:#fce7ef;color:var(--rose,#e05c7a);border-color:var(--rose,#e05c7a)' : '' }}"
                             onclick="document.querySelectorAll('.bg-pill').forEach(p=>{p.style.background='var(--cream)';p.style.color='var(--txt-md)';p.style.borderColor='var(--warm-bd)'}); this.style.background='#fce7ef'; this.style.color='var(--rose,#e05c7a)'; this.style.borderColor='var(--rose,#e05c7a)'">
                            {{ $bg }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- Location --}}
    <div class="pf-card">
        <div class="pf-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--plum,#6b4fa0)"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Location
        </div>
        <div class="pf-body pf-grid">

            <div class="pf-grid pf-row2">
                <div>
                    <label class="pf-label">City</label>
                    <select name="city" id="patient-city" class="pf-inp"
                            data-selected-city="{{ old('city', $profile?->city) }}">
                        <option value="">Enter pincode first</option>
                    </select>
                    @error('city')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="pf-label">State</label>
                    <select name="state" id="patient-state" class="pf-inp">
                        <option value="">— Select state —</option>
                        @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry','Chandigarh'] as $stateName)
                        <option value="{{ $stateName }}" @selected(old('state', $profile?->state) === $stateName)>{{ $stateName }}</option>
                        @endforeach
                    </select>
                    @error('state')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="pf-grid pf-row3">
                <div>
                    <label class="pf-label">Pincode</label>
                          <input type="text" name="pincode" id="patient-pincode" class="pf-inp"
                              value="{{ old('pincode', $profile?->pincode) }}" placeholder="400001" maxlength="6" inputmode="numeric">
                          <div id="patient-pincode-status" class="pf-location-status" role="status" aria-live="polite"></div>
                    @error('pincode')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="pf-label">Address</label>
                    <input type="text" name="address" class="pf-inp"
                           value="{{ old('address', $profile?->address) }}" placeholder="Street / area / landmark">
                    @error('address')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
            </div>

        </div>
    </div>

    <div style="margin-bottom:30px">
        <button type="submit" class="pf-btn">Save Personal Details</button>
    </div>
</form>

{{-- ② Account Info --}}
<form method="POST" action="{{ route('patient.profile.update') }}">
    @csrf @method('PUT')

    <div class="pf-card">
        <div class="pf-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--plum,#6b4fa0)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Account Info
        </div>
        <div class="pf-body pf-grid">

            <div>
                <label class="pf-label">
                    Mobile Number
                    <span style="font-size:.65rem;font-weight:400;text-transform:none;letter-spacing:0;margin-left:4px">(used to log in)</span>
                </label>
                <div style="display:flex;gap:8px">
                    <select name="country_code" class="pf-inp" style="width:100px;flex-shrink:0">
                        @php
                            $codes = ['+91'=>'+91 🇮🇳','+1'=>'+1 🇺🇸','+44'=>'+44 🇬🇧','+61'=>'+61 🇦🇺','+971'=>'+971 🇦🇪','+65'=>'+65 🇸🇬','+60'=>'+60 🇲🇾','+92'=>'+92 🇵🇰'];
                            $current = old('country_code', $patient->country_code ?? '+91');
                            if (!array_key_exists($current, $codes)) $codes[$current] = $current;
                        @endphp
                        @foreach($codes as $code => $label)
                        <option value="{{ $code }}" @selected($current === $code)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="tel" name="mobile_number" class="pf-inp"
                           value="{{ old('mobile_number', $patient->mobile_number) }}"
                           placeholder="Mobile number" required>
                </div>
                @error('mobile_number')<div class="pf-err">{{ $message }}</div>@enderror
                @error('country_code')<div class="pf-err">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="pf-label">
                    Email Address
                    <span style="font-size:.65rem;font-weight:400;text-transform:none;letter-spacing:0;margin-left:4px">(optional)</span>
                </label>
                <input type="email" name="email" class="pf-inp"
                       value="{{ old('email', $patient->email) }}" placeholder="your@email.com">
                @error('email')<div class="pf-err">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    <div style="margin-bottom:30px">
        <button type="submit" class="pf-btn">Save Account Info</button>
    </div>
</form>

{{-- ③ Change Password --}}
<form method="POST" action="{{ route('patient.profile.password') }}" x-data="{show:false}">
    @csrf @method('PUT')

    <div class="pf-card">
        <div class="pf-head">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--plum,#6b4fa0)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Change Password
        </div>
        <div class="pf-body pf-grid">

            <div>
                <label class="pf-label">Current Password</label>
                <input :type="show ? 'text' : 'password'" name="current_password" class="pf-inp"
                       autocomplete="current-password" required>
                @error('current_password')<div class="pf-err">{{ $message }}</div>@enderror
            </div>

            <div class="pf-grid pf-row2">
                <div>
                    <label class="pf-label">New Password</label>
                    <input :type="show ? 'text' : 'password'" name="password" class="pf-inp"
                           autocomplete="new-password" required>
                    @error('password')<div class="pf-err">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="pf-label">Confirm New Password</label>
                    <input :type="show ? 'text' : 'password'" name="password_confirmation" class="pf-inp"
                           autocomplete="new-password" required>
                </div>
            </div>

            <div>
                <label style="display:flex;align-items:center;gap:7px;cursor:pointer;font-size:.8rem;color:var(--txt-lt);font-weight:400">
                    <input type="checkbox" @change="show = !show" style="accent-color:var(--plum,#6b4fa0);width:14px;height:14px">
                    Show passwords
                </label>
            </div>

        </div>
    </div>

    <div style="margin-bottom:10px">
        <button type="submit" class="pf-btn">Update Password</button>
    </div>
</form>

</div>
@endsection
