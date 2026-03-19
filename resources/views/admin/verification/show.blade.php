@extends('layouts.admin')
@section('title', 'Verify — '.($doctor->profile?->full_name ?? 'Doctor'))
@section('page-title')
    <a href="{{ route('admin.verification.pending') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Verification</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Dr. {{ $doctor->profile?->full_name ?? 'Unknown' }}
@endsection

@section('content')
@php
    $dp      = $doctor->doctorProfile;
    $profile = $doctor->profile;
    $name    = $profile?->full_name ?? 'Unknown';
@endphp
<div class="fade-in" style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

{{-- ── LEFT: Doctor details ─────────────────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Header --}}
    <div class="card" style="padding:22px 24px">
        <div style="display:flex;gap:16px;align-items:center">
            <div style="width:56px;height:56px;border-radius:14px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#6366f1;flex-shrink:0">
                {{ strtoupper(substr($name,0,1)) }}
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:500;color:var(--txt)">Dr. {{ $name }}</h2>
                    @if($dp?->is_verified)
                    <span class="badge badge-green">✓ Verified</span>
                    @elseif($dp?->rejection_reason)
                    <span class="badge badge-red">Previously Rejected</span>
                    @else
                    <span class="badge badge-yellow">Awaiting Verification</span>
                    @endif
                </div>
                <div style="font-size:.875rem;color:var(--txt-md);margin-top:3px">{{ $dp?->specialization }} · {{ $dp?->qualification }}</div>
                <div style="font-size:.78rem;color:var(--txt-lt);margin-top:2px">{{ $doctor->country_code }} {{ $doctor->mobile_number }} · Joined {{ $doctor->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Registration Info (the key verification data) --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd);background:#f8f9fc">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Registration & Credentials</div>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">Verify against MCI / State Medical Council records</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
            @php
            $regFields = [
                'Registration Number' => $dp?->registration_number,
                'Registration Council'=> $dp?->registration_council,
                'Specialization'      => $dp?->specialization,
                'Sub-Specialization'  => $dp?->sub_specialization,
                'Qualification'       => $dp?->qualification,
                'Experience (Years)'  => $dp?->experience_years,
            ];
            @endphp
            @foreach($regFields as $label => $val)
            <div style="padding:14px 20px;border-bottom:1px solid var(--bd);{{ $loop->odd ? 'border-right:1px solid var(--bd)' : '' }}">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:4px">{{ $label }}</div>
                <div style="font-size:.9rem;font-weight:{{ $label==='Registration Number' ? '700' : '400' }};color:var(--txt);font-family:{{ $label==='Registration Number' ? 'monospace' : 'inherit' }}">
                    {{ $val ?? '—' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Clinic Info --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Clinic & Practice</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
            @php
            $clinicFields = [
                'Clinic Name'      => $dp?->clinic_name,
                'Clinic City'      => $dp?->clinic_city,
                'Consultation Fee' => $dp?->consultation_fee ? '₹'.number_format($dp->consultation_fee) : null,
                'UPI ID'           => $dp?->upi_id,
                'Languages'        => $dp?->languages_spoken ? implode(', ', $dp->languages_spoken) : null,
                'Premium'          => $dp?->is_premium ? 'Yes' : 'No',
            ];
            @endphp
            @foreach($clinicFields as $label => $val)
            <div style="padding:12px 20px;border-bottom:1px solid var(--bd);{{ $loop->odd ? 'border-right:1px solid var(--bd)' : '' }}">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">{{ $label }}</div>
                <div style="font-size:.875rem;color:var(--txt-md)">{{ $val ?? '—' }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Bio --}}
    @if($dp?->bio)
    <div class="card" style="padding:18px 22px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Professional Bio</div>
        <p style="font-size:.875rem;color:var(--txt-md);line-height:1.6">{{ $dp->bio }}</p>
    </div>
    @endif

    {{-- Previous rejection --}}
    @if($dp?->rejection_reason)
    <div style="padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px">
        <div style="font-size:.75rem;font-weight:700;color:#dc2626;margin-bottom:4px;display:flex;align-items:center;gap:5px">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Previous Rejection Reason
        </div>
        <p style="font-size:.875rem;color:#b91c1c">{{ $dp->rejection_reason }}</p>
    </div>
    @endif

</div>

{{-- ── RIGHT: Approve / Reject panel ───────────────────────────────────────── -- --}}
<div style="position:sticky;top:82px;display:flex;flex-direction:column;gap:14px">

    @if(!$dp?->is_verified)
    {{-- Approve --}}
    <div class="card" style="padding:18px 20px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">Approve Verification</div>
        <p style="font-size:.8rem;color:var(--txt-md);line-height:1.5;margin-bottom:14px">
            Approving will mark this doctor as verified and allow them to accept appointments on the platform.
        </p>
        <form method="POST" action="{{ route('admin.verification.approve', $doctor->id) }}">
            @csrf
            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;padding:10px"
                    onclick="return confirm('Approve Dr. {{ $name }}?')">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Approve Verification
            </button>
        </form>
    </div>

    {{-- Reject --}}
    <div class="card" style="padding:18px 20px" x-data="{ open: false }">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">Reject Verification</div>
        <button type="button" @click="open=!open" class="btn btn-danger" style="width:100%;justify-content:center">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Reject &amp; Suspend
        </button>
        <div x-show="open" x-transition style="margin-top:12px">
            <form method="POST" action="{{ route('admin.verification.reject', $doctor->id) }}">
                @csrf
                <label style="font-size:.72rem;color:var(--txt-lt);display:block;margin-bottom:6px">
                    Reason for rejection <span style="color:var(--danger)">*</span>
                </label>
                <textarea name="reason" rows="4" class="inp" style="width:100%;resize:none;font-size:.8rem;margin-bottom:10px"
                          placeholder="e.g. Registration number not found in MCI records. Please submit correct credentials."
                          required minlength="10"></textarea>
                <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center"
                        onclick="return confirm('Reject and suspend Dr. {{ $name }}?')">
                    Confirm Rejection
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Already verified --}}
    <div class="card" style="padding:18px 20px;text-align:center">
        <div style="width:44px;height:44px;border-radius:12px;background:#d1fae5;display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#065f46" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div style="font-weight:600;color:#065f46;font-size:.875rem">Verification Approved</div>
        @if($dp?->verified_at)
        <div style="font-size:.75rem;color:var(--txt-lt);margin-top:4px">{{ \Carbon\Carbon::parse($dp->verified_at)->format('d M Y') }}</div>
        @endif
    </div>
    @endif

    {{-- Verification checklist --}}
    <div class="card" style="padding:16px 18px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Verification Checklist</div>
        @php
        $checks = [
            'Registration number provided'   => !empty($dp?->registration_number),
            'Registration council named'     => !empty($dp?->registration_council),
            'Qualification listed'           => !empty($dp?->qualification),
            'Specialization specified'       => !empty($dp?->specialization),
            'Clinic information complete'    => !empty($dp?->clinic_name),
            'Contact mobile verified'        => $doctor->is_verified,
            'Profile photo / bio added'      => !empty($dp?->bio),
        ];
        @endphp
        @foreach($checks as $label => $done)
        <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid var(--bd)">
            <div style="width:18px;height:18px;border-radius:50%;background:{{ $done ? '#d1fae5' : '#f3f4f6' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                @if($done)
                <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#065f46" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                @else
                <div style="width:4px;height:4px;border-radius:50%;background:#d1d5db"></div>
                @endif
            </div>
            <span style="font-size:.78rem;color:{{ $done ? 'var(--txt-md)' : 'var(--txt-lt)' }}">{{ $label }}</span>
        </div>
        @endforeach

        @php $score = count(array_filter(array_values($checks))); $total = count($checks); @endphp
        <div style="margin-top:10px;display:flex;align-items:center;gap:8px">
            <div style="flex:1;height:4px;background:var(--bd);border-radius:2px;overflow:hidden">
                <div style="height:100%;background:{{ $score>=$total ? '#10b981' : ($score>=$total*0.7 ? '#f59e0b' : '#ef4444') }};width:{{ round($score/$total*100) }}%"></div>
            </div>
            <span style="font-size:.75rem;font-weight:600;color:var(--txt-md)">{{ $score }}/{{ $total }}</span>
        </div>
    </div>

    <a href="{{ route('admin.users.show', $doctor->id) }}" class="btn btn-ghost" style="justify-content:center">
        View Full Profile
    </a>
    <a href="{{ route('admin.verification.pending') }}" style="text-align:center;font-size:.8rem;color:var(--txt-lt);text-decoration:none">← Back to list</a>
</div>

</div>
@endsection
