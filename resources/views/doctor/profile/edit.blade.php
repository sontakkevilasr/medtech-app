@extends('layouts.doctor')
@section('title', 'Edit Profile')
@section('page-title', 'My Profile')

@section('content')
<div style="max-width:640px">

@if(session('success'))
<div style="padding:10px 16px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;color:#166534;font-size:.875rem;margin-bottom:16px">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('doctor.profile.update') }}">
    @csrf @method('PUT')

    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:16px">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">Personal</div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div style="grid-column:1/-1">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $profile?->full_name) }}" required
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
                @error('full_name')<div style="font-size:.75rem;color:#ef4444;margin-top:3px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Specialization</label>
                <input type="text" name="specialization" value="{{ old('specialization', $dp?->specialization) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Consultation Fee (₹)</label>
                <input type="number" name="consultation_fee" value="{{ old('consultation_fee', $dp?->consultation_fee) }}" min="0" step="0.01"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Bio</label>
                <textarea name="bio" rows="3" style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box;resize:vertical">{{ old('bio', $dp?->bio) }}</textarea>
            </div>
        </div>
    </div>

    <div style="background:var(--cream);border:1px solid var(--warm-bd);border-radius:14px;margin-bottom:16px">
        <div style="padding:13px 20px;border-bottom:1px solid var(--warm-bd);font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">Clinic</div>
        <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div style="grid-column:1/-1">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Clinic Name</label>
                <input type="text" name="clinic_name" value="{{ old('clinic_name', $dp?->clinic_name) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">City</label>
                <input type="text" name="clinic_city" value="{{ old('clinic_city', $dp?->clinic_city) }}"
                       style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box">
            </div>
            <div style="grid-column:1/-1">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt);display:block;margin-bottom:5px">Address</label>
                <textarea name="clinic_address" rows="2" style="width:100%;padding:9px 12px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.9rem;font-family:'Outfit',sans-serif;color:var(--txt);background:#fff;box-sizing:border-box;resize:vertical">{{ old('clinic_address', $dp?->clinic_address) }}</textarea>
            </div>
        </div>
    </div>

    <button type="submit" style="padding:10px 24px;background:var(--ink);color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif">Save Changes</button>
</form>

</div>
@endsection
