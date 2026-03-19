@extends('layouts.admin')
@section('title', ($user->profile?->full_name ?? 'User').' — User Detail')
@section('page-title')
    <a href="{{ route('admin.users.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Users</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $user->profile?->full_name ?? 'Unknown' }}
@endsection

@section('content')
@php
    $name     = $user->profile?->full_name ?? 'Unknown';
    $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$name),0,2))));
    $palette  = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6'];
    $color    = $palette[$user->id % count($palette)];
    $dp       = $user->doctorProfile;
    $profile  = $user->profile;
@endphp
<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ── LEFT ─────────────────────────────────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Header card --}}
    <div class="card" style="padding:22px 24px">
        <div style="display:flex;gap:16px;align-items:flex-start">
            <div style="width:58px;height:58px;border-radius:14px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                    <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:500;color:var(--txt)">
                        {{ $user->isDoctor() ? 'Dr. ' : '' }}{{ $name }}
                    </h1>
                    <span class="badge {{ $user->isDoctor() ? 'badge-purple' : 'badge-blue' }}">{{ ucfirst($user->role) }}</span>
                    <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Active' : 'Suspended' }}</span>
                    @if($dp?->is_verified)<span class="badge badge-green">✓ Verified</span>@endif
                    @if($dp?->is_premium)<span class="badge badge-purple">⭐ Premium</span>@endif
                </div>
                @if($user->isDoctor())
                <div style="font-size:.875rem;color:var(--txt-md);margin-top:4px">{{ $dp?->specialization }} · {{ $dp?->qualification }}</div>
                @endif
                <div style="font-size:.8rem;color:var(--txt-lt);margin-top:3px">
                    {{ $user->country_code }} {{ $user->mobile_number }}
                    @if($profile?->email) · {{ $profile->email }} @endif
                    · Joined {{ $user->created_at->format('d M Y') }}
                </div>
            </div>
        </div>

        {{-- Stats row --}}
        @if(!empty($stats))
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:18px;padding-top:18px;border-top:1px solid var(--bd)">
            @foreach($stats as $k => $v)
            <div style="text-align:center">
                <div style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-weight:500;color:var(--txt)">{{ number_format($v) }}</div>
                <div style="font-size:.7rem;color:var(--txt-lt);text-transform:capitalize">{{ str_replace('_',' ',$k) }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Profile details --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Profile Details</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
            @php
                $fields = [
                    'Date of Birth'    => $profile?->date_of_birth?->format('d M Y'),
                    'Gender'           => ucfirst($profile?->gender ?? ''),
                    'Blood Group'      => $profile?->blood_group,
                    'City'             => $profile?->city,
                    'State'            => $profile?->state,
                    'Address'          => $profile?->address,
                ];
                if ($user->isDoctor()) {
                    $fields = array_merge($fields, [
                        'Registration No.' => $dp?->registration_number,
                        'Council'          => $dp?->registration_council,
                        'Experience'       => $dp?->experience_years ? $dp->experience_years.' yrs' : null,
                        'Clinic'           => $dp?->clinic_name,
                        'Clinic City'      => $dp?->clinic_city,
                        'Consultation Fee' => $dp?->consultation_fee ? '₹'.number_format($dp->consultation_fee) : null,
                        'UPI ID'           => $dp?->upi_id,
                        'Languages'        => $dp?->languages_spoken ? implode(', ', $dp->languages_spoken) : null,
                    ]);
                }
            @endphp
            @foreach($fields as $label => $val)
            @if($val)
            <div style="padding:11px 20px;border-bottom:1px solid var(--bd);{{ $loop->odd ? 'border-right:1px solid var(--bd)' : '' }}">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">{{ $label }}</div>
                <div style="font-size:.875rem;color:var(--txt-md)">{{ $val }}</div>
            </div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Family members (patients) --}}
    @if($user->isPatient() && $user->familyMembers->count())
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Family Members</div>
        </div>
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Relation</th><th>DOB</th><th>Blood Group</th></tr></thead>
            <tbody>
                @foreach($user->familyMembers as $fm)
                <tr>
                    <td style="font-weight:500;color:var(--txt)">{{ $fm->full_name }}</td>
                    <td><span class="badge badge-gray">{{ ucfirst($fm->relation) }}</span></td>
                    <td>{{ $fm->date_of_birth?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $fm->blood_group ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Recent appointments --}}
    @if($recentAppointments->count())
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:14px 20px;border-bottom:1px solid var(--bd)">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Recent Appointments</div>
        </div>
        <table class="admin-table">
            <thead><tr>
                <th>{{ $user->isDoctor() ? 'Patient' : 'Doctor' }}</th>
                <th>Date & Time</th><th>Type</th><th>Status</th><th>Fee</th>
            </tr></thead>
            <tbody>
            @foreach($recentAppointments as $apt)
            @php
                $person = $user->isDoctor() ? $apt->patient?->profile?->full_name : ('Dr. '.($apt->doctor?->profile?->full_name ?? ''));
                $stcfg  = match($apt->status) {
                    'confirmed'  => 'badge-green',
                    'booked'     => 'badge-yellow',
                    'completed'  => 'badge-blue',
                    'cancelled'  => 'badge-red',
                    default      => 'badge-gray',
                };
            @endphp
            <tr>
                <td style="font-weight:500;color:var(--txt)">{{ $person }}</td>
                <td style="font-size:.8rem;color:var(--txt-md)">{{ $apt->slot_datetime->format('d M Y h:i A') }}</td>
                <td><span class="badge badge-gray" style="font-size:.62rem">{{ ucwords(str_replace('_',' ',$apt->type)) }}</span></td>
                <td><span class="badge {{ $stcfg }}">{{ ucfirst($apt->status) }}</span></td>
                <td style="font-size:.8rem">{{ $apt->fee ? '₹'.number_format($apt->fee) : '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── RIGHT: Action panel ──────────────────────────────────────────────────── -- --}}
<div style="position:sticky;top:calc(58px+24px);display:flex;flex-direction:column;gap:14px">

    {{-- Quick actions --}}
    <div class="card" style="padding:18px 20px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">Quick Actions</div>

        {{-- Activate / Suspend --}}
        @if($user->is_active)
        <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
            @csrf
            <div style="margin-bottom:8px">
                <label style="font-size:.72rem;color:var(--txt-lt);display:block;margin-bottom:4px">Reason (optional)</label>
                <textarea name="reason" rows="2" class="inp" style="width:100%;resize:none;font-size:.8rem" placeholder="Suspension reason…"></textarea>
            </div>
            <button type="submit" class="btn btn-danger" style="width:100%;justify-content:center"
                    onclick="return confirm('Suspend {{ $name }}?')">
                Suspend User
            </button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.users.activate', $user) }}">
            @csrf
            <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;margin-bottom:8px">
                Activate User
            </button>
        </form>
        @endif

        {{-- Grant premium (doctor only) --}}
        @if($user->isDoctor() && !$dp?->is_premium)
        <form method="POST" action="{{ route('admin.users.grant-premium', $user) }}" style="margin-top:8px">
            @csrf
            <div style="margin-bottom:6px">
                <label style="font-size:.72rem;color:var(--txt-lt);display:block;margin-bottom:4px">Premium Until</label>
                <input type="date" name="until" class="inp" style="width:100%"
                       value="{{ now()->addYear()->format('Y-m-d') }}" min="{{ today()->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center">
                ⭐ Grant Premium
            </button>
        </form>
        @elseif($dp?->is_premium)
        <div style="padding:9px 12px;background:#f5f3ff;border-radius:8px;font-size:.78rem;color:#5b21b6;text-align:center;margin-top:8px;border:1px solid #ddd6fe">
            ⭐ Premium active
            @if($dp->premium_expires_at)
            <br><span style="font-size:.7rem;opacity:.7">Until {{ \Carbon\Carbon::parse($dp->premium_expires_at)->format('d M Y') }}</span>
            @endif
        </div>
        @endif

        {{-- Doctor verification shortcut --}}
        @if($user->isDoctor() && !$dp?->is_verified)
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--bd)">
            <a href="{{ route('admin.verification.show', $user->id) }}" class="btn btn-primary" style="width:100%;justify-content:center">
                Review Verification →
            </a>
        </div>
        @endif

        {{-- Delete --}}
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--bd)">
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                @csrf @method('DELETE')
                <button type="submit" style="width:100%;padding:7px;border:1px solid #fecaca;border-radius:8px;background:transparent;color:#dc2626;font-size:.78rem;cursor:pointer;font-family:'Outfit',sans-serif;transition:background .12s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
                        onclick="return confirm('Permanently delete {{ $name }}? This cannot be undone.')">
                    Remove from Platform
                </button>
            </form>
        </div>
    </div>

    {{-- Account info --}}
    <div class="card" style="padding:16px 18px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Account Info</div>
        @php
            $info = [
                'User ID'       => '#'.$user->id,
                'Mobile'        => $user->country_code.' '.$user->mobile_number,
                'Verified'      => $user->is_verified ? '✓ Mobile verified' : '✗ Not verified',
                'Registered'    => $user->created_at->format('d M Y, h:i A'),
                'Last Updated'  => $user->updated_at->diffForHumans(),
            ];
        @endphp
        @foreach($info as $k => $v)
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--bd);font-size:.8rem">
            <span style="color:var(--txt-lt)">{{ $k }}</span>
            <span style="color:var(--txt-md);font-weight:500">{{ $v }}</span>
        </div>
        @endforeach
    </div>
</div>

</div>
@endsection
