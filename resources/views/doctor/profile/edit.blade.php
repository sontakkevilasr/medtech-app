@php $role = auth()->user()->role; @endphp
@extends($role === 'doctor' ? 'layouts.doctor' : 'layouts.patient')
@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

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
    outline: none; font-family: inherit; transition: border-color .15s;
}
.field-inp:focus { border-color: {{ $role === 'doctor' ? 'var(--leaf)' : 'var(--plum)' }}; }
.field-select { cursor: pointer; }
.section-panel {
    background: #fff; border: 1.5px solid var(--warm-bd);
    border-radius: 14px; padding: 20px 24px; margin-bottom: 16px;
}
.tab-btn {
    padding: 8px 18px; font-size: .85rem; font-weight: 500;
    border: none; background: transparent; cursor: pointer;
    border-bottom: 2px solid transparent; color: var(--txt-lt);
    font-family: inherit; transition: all .15s;
}
.tab-btn.active {
    color: {{ $role === 'doctor' ? 'var(--leaf)' : 'var(--plum)' }};
    border-bottom-color: {{ $role === 'doctor' ? 'var(--leaf)' : 'var(--plum)' }};
    font-weight: 600;
}
</style>
@endpush

@section('content')
<div class="fade-in" x-data="{ tab: 'personal' }">

@if(session('success'))
<div style="padding:12px 16px;background:#eef5f3;border:1px solid #b5ddd5;border-radius:10px;margin-bottom:18px;font-size:.875rem;color:#2a7a6a;display:flex;align-items:center;gap:8px">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start">

{{-- ── LEFT ─────────────────────────────────────────────────────────────────── --}}
<div>

    {{-- Tab bar --}}
    <div style="border-bottom:2px solid var(--warm-bd);margin-bottom:20px;display:flex;gap:0">
        <button class="tab-btn" :class="tab==='personal' ? 'active' : ''" @click="tab='personal'">Personal</button>
        @if($role === 'doctor')
        <button class="tab-btn" :class="tab==='professional' ? 'active' : ''" @click="tab='professional'">Professional</button>
        @endif
        <button class="tab-btn" :class="tab==='security' ? 'active' : ''" @click="tab='security'">Security</button>
        <button class="tab-btn" :class="tab==='danger' ? 'active' : ''" @click="tab='danger'" style="margin-left:auto;color:#dc2626">Danger Zone</button>
    </div>

    {{-- ── TAB: Personal ───────────────────────────────────────────────────── --}}
    <div x-show="tab === 'personal'" x-transition>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')

            @if($errors->any())
            <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:14px">
                @foreach($errors->all() as $e)<div style="font-size:.8rem;color:#dc2626">• {{ $e }}</div>@endforeach
            </div>
            @endif

            <div class="section-panel">
                <div style="font-family:'{{ $role==='doctor' ? 'Cormorant Garamond' : 'Lora' }}',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
                    Personal Details
                </div>

                <div style="margin-bottom:14px">
                    <label class="field-label">Full Name *</label>
                    <input type="text" name="full_name" class="field-inp"
                           value="{{ old('full_name', $profile?->full_name) }}" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label class="field-label">Date of Birth</label>
                        <input type="date" name="dob" class="field-inp"
                               value="{{ old('dob', $profile?->dob?->format('Y-m-d')) }}"
                               max="{{ today()->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="field-label">Gender</label>
                        <select name="gender" class="field-inp field-select">
                            <option value="">— Select —</option>
                            @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v => $l)
                            <option value="{{ $v }}" {{ old('gender',$profile?->gender)===$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Blood Group</label>
                        <select name="blood_group" class="field-inp field-select">
                            <option value="">— Select —</option>
                            @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}" {{ old('blood_group',$profile?->blood_group)===$bg?'selected':'' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="section-panel">
                <div style="font-family:'{{ $role==='doctor' ? 'Cormorant Garamond' : 'Lora' }}',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
                    Address
                </div>

                <div style="margin-bottom:12px">
                    <label class="field-label">Address</label>
                    <input type="text" name="address" class="field-inp"
                           value="{{ old('address', $profile?->address) }}" placeholder="Street, Area, Landmark">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
                    <div>
                        <label class="field-label">City</label>
                        <input type="text" name="city" class="field-inp"
                               value="{{ old('city', $profile?->city) }}" placeholder="e.g. Nagpur">
                    </div>
                    <div>
                        <label class="field-label">State</label>
                        <select name="state" class="field-inp field-select">
                            <option value="">— State —</option>
                            @foreach(['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir','Ladakh','Puducherry','Chandigarh'] as $st)
                            <option value="{{ $st }}" {{ old('state',$profile?->state)===$st?'selected':'' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Pincode</label>
                        <input type="text" name="pincode" class="field-inp"
                               value="{{ old('pincode', $profile?->pincode) }}" maxlength="6" placeholder="6 digits">
                    </div>
                </div>
            </div>

            <div class="section-panel">
                <div style="font-family:'{{ $role==='doctor' ? 'Cormorant Garamond' : 'Lora' }}',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
                    Emergency Contact
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <label class="field-label">Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="field-inp"
                               value="{{ old('emergency_contact_name', $profile?->emergency_contact_name) }}" placeholder="Spouse / Parent name">
                    </div>
                    <div>
                        <label class="field-label">Contact Mobile</label>
                        <input type="tel" name="emergency_contact_number" class="field-inp"
                               value="{{ old('emergency_contact_number', $profile?->emergency_contact_number) }}" maxlength="10">
                    </div>
                </div>
            </div>

            <button type="submit"
                    style="padding:.75rem 28px;background:{{ $role==='doctor' ? 'var(--leaf)' : 'var(--plum)' }};color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                Save Changes
            </button>
        </form>
    </div>

    {{-- ── TAB: Professional (doctor only) ─────────────────────────────────── --}}
    @if($role === 'doctor')
    <div x-show="tab === 'professional'" x-transition>
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PUT')

            <div class="section-panel">
                <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
                    Clinic & Practice
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px;margin-bottom:12px">
                    <div>
                        <label class="field-label">Clinic / Hospital Name</label>
                        <input type="text" name="clinic_name" class="field-inp"
                               value="{{ old('clinic_name', $dp?->clinic_name) }}" placeholder="e.g. Apollo Clinic">
                    </div>
                    <div>
                        <label class="field-label">Consultation Fee (₹)</label>
                        <input type="number" name="consultation_fee" class="field-inp" min="0"
                               value="{{ old('consultation_fee', $dp?->consultation_fee) }}" placeholder="e.g. 500">
                    </div>
                </div>

                <div style="margin-bottom:12px">
                    <label class="field-label">Clinic Address</label>
                    <input type="text" name="clinic_address" class="field-inp"
                           value="{{ old('clinic_address', $dp?->clinic_address) }}" placeholder="Street, Area">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div>
                        <label class="field-label">City</label>
                        <input type="text" name="clinic_city" class="field-inp"
                               value="{{ old('clinic_city', $dp?->clinic_city) }}">
                    </div>
                    <div>
                        <label class="field-label">UPI ID</label>
                        <input type="text" name="upi_id" class="field-inp"
                               value="{{ old('upi_id', $dp?->upi_id) }}" placeholder="doctor@upi">
                    </div>
                </div>

                <div style="margin-bottom:12px">
                    <label class="field-label">Professional Bio</label>
                    <textarea name="bio" rows="4" class="field-inp" style="resize:vertical;line-height:1.6"
                              placeholder="Brief description of your practice and expertise…">{{ old('bio', $dp?->bio) }}</textarea>
                </div>

                <div>
                    <label class="field-label">Languages Spoken</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:10px;background:#fff">
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

            <button type="submit"
                    style="padding:.75rem 28px;background:var(--leaf);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                Save Clinic Details
            </button>
        </form>
    </div>
    @endif

    {{-- ── TAB: Security ────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'security'" x-transition>
        <form method="POST" action="{{ route('profile.password') }}">
            @csrf @method('PUT')

            @if($errors->has('current_password') || $errors->has('password'))
            <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:14px">
                @foreach($errors->only('current_password','password') as $msgs)
                    @foreach((array)$msgs as $m)<div style="font-size:.8rem;color:#dc2626">• {{ $m }}</div>@endforeach
                @endforeach
            </div>
            @endif

            <div class="section-panel">
                <div style="font-family:'{{ $role==='doctor' ? 'Cormorant Garamond' : 'Lora' }}',serif;font-size:1rem;color:var(--txt);margin-bottom:16px;padding-bottom:10px;border-bottom:1.5px solid var(--warm-bd)">
                    Change Password
                </div>

                <div style="margin-bottom:12px">
                    <label class="field-label">Current Password</label>
                    <input type="password" name="current_password" class="field-inp" placeholder="Enter current password" required>
                </div>
                <div style="margin-bottom:12px">
                    <label class="field-label">New Password</label>
                    <input type="password" name="password" class="field-inp" placeholder="Min 8 chars, 1 uppercase, 1 number" required>
                </div>
                <div>
                    <label class="field-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="field-inp" placeholder="Repeat new password" required>
                </div>
            </div>

            <div class="section-panel" style="background:var(--parch)">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Account Info</div>
                @foreach([
                    'Mobile'    => auth()->user()->country_code.' '.auth()->user()->mobile_number,
                    'Role'      => ucfirst(auth()->user()->role),
                    'Joined'    => auth()->user()->created_at->format('d M Y'),
                    'Last updated' => auth()->user()->updated_at->diffForHumans(),
                ] as $k => $v)
                <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--warm-bd);font-size:.8rem">
                    <span style="color:var(--txt-lt)">{{ $k }}</span>
                    <span style="color:var(--txt-md);font-weight:500">{{ $v }}</span>
                </div>
                @endforeach
            </div>

            <button type="submit"
                    style="padding:.75rem 28px;background:{{ $role==='doctor' ? 'var(--leaf)' : 'var(--plum)' }};color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                Update Password
            </button>
        </form>
    </div>

    {{-- ── TAB: Danger Zone ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'danger'" x-transition>
        <div class="section-panel" style="border-color:#fecaca">
            <div style="font-family:'{{ $role==='doctor' ? 'Cormorant Garamond' : 'Lora' }}',serif;font-size:1rem;color:#dc2626;margin-bottom:10px">Delete Account</div>
            <p style="font-size:.85rem;color:var(--txt-md);margin-bottom:16px;line-height:1.6">
                Permanently delete your account and all associated data. This action <strong>cannot be undone</strong>.
                All your {{ $role === 'doctor' ? 'patients, prescriptions, appointments' : 'health records, prescriptions, appointments' }} will be removed.
            </p>
            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('Are you absolutely sure? This will permanently delete your account.')">
                @csrf @method('DELETE')
                <div style="margin-bottom:12px">
                    <label class="field-label">Confirm with your password</label>
                    <input type="password" name="password" class="field-inp" style="max-width:320px"
                           placeholder="Enter your password" required>
                    @error('password') <div style="font-size:.78rem;color:#dc2626;margin-top:4px">{{ $message }}</div> @enderror
                </div>
                <button type="submit"
                        style="padding:.7rem 20px;border:1.5px solid #fecaca;border-radius:10px;background:transparent;color:#dc2626;font-size:.875rem;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    Delete My Account
                </button>
            </form>
        </div>
    </div>

</div>

{{-- ── RIGHT: Avatar + quick info ──────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Avatar upload --}}
    <div class="section-panel" style="text-align:center;padding:22px">
        @php
            $name     = $profile?->full_name ?? 'User';
            $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$name),0,2))));
            $photoUrl = $profile?->profile_photo ? Storage::url($profile->profile_photo) : null;
        @endphp

        <div style="position:relative;display:inline-block;margin-bottom:12px">
            @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $name }}"
                 style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--warm-bd)">
            @else
            <div style="width:80px;height:80px;border-radius:50%;background:{{ $role==='doctor' ? 'var(--leaf)' : 'var(--plum)' }};display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;margin:0 auto">
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
            <label style="cursor:pointer;font-size:.8rem;padding:7px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;color:var(--txt-md);transition:background .12s;display:inline-block"
                   onmouseover="this.style.background='var(--sand,#f4efe8)'" onmouseout="this.style.background='transparent'">
                📷 Change Photo
                <input type="file" name="photo" accept="image/*" style="display:none"
                       onchange="document.getElementById('photo-form').submit()">
            </label>
        </form>
        @error('photo')<div style="font-size:.72rem;color:#dc2626;margin-top:6px">{{ $message }}</div>@enderror
    </div>

    {{-- Verification status (doctor) --}}
    @if($role === 'doctor')
    <div class="section-panel" style="padding:14px 16px;background:{{ $dp?->is_verified ? '#eef5f3' : '#fef9ec' }};border-color:{{ $dp?->is_verified ? '#b5ddd5' : '#fde68a' }}">
        <div style="display:flex;gap:8px;align-items:center">
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
        <div style="margin-top:10px;padding:8px 10px;background:#fef2f2;border-radius:8px;font-size:.75rem;color:#dc2626">
            Rejection reason: {{ $dp->rejection_reason }}
        </div>
        @endif
    </div>
    @endif

    {{-- Premium status (doctor) --}}
    @if($role === 'doctor')
    <div class="section-panel" style="padding:14px 16px;background:{{ $dp?->is_premium ? '#f4f0fa' : 'var(--parch)' }}">
        <div style="font-size:.8rem;font-weight:600;color:{{ $dp?->is_premium ? '#4a3760' : 'var(--txt-md)' }}">
            {{ $dp?->is_premium ? '⭐ Premium Account' : '🆓 Free Account' }}
        </div>
        @if($dp?->is_premium && $dp?->premium_expires_at)
        <div style="font-size:.72rem;color:var(--txt-lt);margin-top:3px">
            Expires {{ \Carbon\Carbon::parse($dp->premium_expires_at)->format('d M Y') }}
        </div>
        @elseif(!$dp?->is_premium)
        <div style="font-size:.72rem;color:var(--txt-lt);margin-top:3px">Upgrade for WhatsApp, timelines & Excel</div>
        @endif
    </div>
    @endif

</div>

</div>
</div>
@endsection
