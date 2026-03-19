@extends('layouts.patient')
@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

@section('content')
<div style="max-width:580px">

@if(session('success'))
<div style="padding:10px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;color:#166534;font-size:.875rem;margin-bottom:16px">
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('patient.profile.update') }}">
    @csrf @method('PUT')

    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:16px">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">Personal Details</div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px">

            <div style="grid-column:1/-1">
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $profile?->full_name) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box"
                       required>
                @error('full_name')<div style="font-size:.75rem;color:#ef4444;margin-top:3px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
            </div>

            <div>
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Gender</label>
                <select name="gender" style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
                    <option value="">— Select —</option>
                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ old('gender', $profile?->gender) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Blood Group</label>
                <select name="blood_group" style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
                    <option value="">— Select —</option>
                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                    <option value="{{ $bg }}" {{ old('blood_group', $profile?->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>

            <div style="grid-column:1/-1">
                <label style="font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Address</label>
                <textarea name="address" rows="3"
                          style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box;resize:vertical">{{ old('address', $profile?->address) }}</textarea>
            </div>

        </div>
    </div>

    <button type="submit" style="padding:10px 24px;background:var(--ink);color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif">
        Save Changes
    </button>
</form>

</div>
@endsection
