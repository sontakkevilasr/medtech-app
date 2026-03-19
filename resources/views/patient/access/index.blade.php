@extends('layouts.patient')
@section('title', 'Data Access')
@section('page-title', 'Data Access & Privacy')

@section('content')
@php
    $globalType = $globalPermission?->access_type ?? 'otp_required';
@endphp

<div class="fade-in" x-data="accessHub()" x-init="init()">

{{-- ══ PENDING REQUESTS (top — if any) ══════════════════════════════════════ -- --}}
@if($pending->isNotEmpty())
<div style="margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
        <div style="width:8px;height:8px;border-radius:50%;background:#ef4444;animation:pulse 1.5s infinite"></div>
        <h2 style="font-family:'Lora',serif;font-size:1.1rem;font-weight:500;color:var(--txt)">
            Pending Access Requests
        </h2>
        <span style="font-size:.7rem;font-weight:700;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:2px 9px;border-radius:20px">
            {{ $pending->count() }} waiting
        </span>
    </div>

    <div style="display:flex;flex-direction:column;gap:10px">
    @foreach($pending as $req)
    @php
        $drName   = $req->doctor?->profile?->full_name ?? 'Doctor';
        $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$drName),0,2))));
        $dp       = $req->doctor?->doctorProfile;
        $expiresIn= now()->diffInMinutes($req->otp_expires_at, false);
        $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
        $color    = $colors[$req->doctor_user_id % count($colors)];
    @endphp
    <div class="panel" style="padding:18px 22px;border-left:4px solid #ef4444"
         x-data="{ processing: false, done: false, action: '' }"
         x-show="!done" x-transition>

        <div style="display:flex;align-items:flex-start;gap:14px">
            {{-- Doctor avatar --}}
            <div style="width:46px;height:46px;border-radius:12px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>

            {{-- Info --}}
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:3px">
                    <span style="font-size:.9375rem;font-weight:600;color:var(--txt)">Dr. {{ $drName }}</span>
                    @if($dp?->specialization)
                    <span style="font-size:.75rem;color:var(--txt-lt)">{{ $dp->specialization }}</span>
                    @endif
                </div>
                @if($dp?->clinic_name)
                <div style="font-size:.78rem;color:var(--txt-lt);margin-bottom:6px">
                    {{ $dp->clinic_name }}@if($dp->clinic_city), {{ $dp->clinic_city }}@endif
                </div>
                @endif
                <div style="display:flex;gap:10px;flex-wrap:wrap;font-size:.78rem;color:var(--txt-lt)">
                    <span>Requested {{ $req->created_at->diffForHumans() }}</span>
                    @if($req->familyMember)
                    <span style="display:flex;align-items:center;gap:4px">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        For {{ $req->familyMember->full_name }}
                    </span>
                    @endif
                    {{-- OTP countdown --}}
                    <span style="color:#dc2626;font-weight:500">
                        ⏳ Expires in {{ max(0, $expiresIn) }} min
                    </span>
                </div>

                {{-- OTP notice --}}
                <div style="margin-top:10px;padding:9px 12px;background:#fef9ec;border:1px solid #fde68a;border-radius:8px;font-size:.78rem;color:#92400e">
                    <strong>OTP Access Request:</strong> Dr. {{ $drName }} is asking to view your health records.
                    Approving will give them access for
                    <strong>{{ config('medtech.access.duration_days', 30) }} days</strong>.
                    The OTP sent to their phone is valid for {{ $expiresIn }} minutes.
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;min-width:110px" x-show="!processing">
                {{-- Approve --}}
                <button type="button"
                        @click="handleRequest('{{ route('patient.access.approve', $req) }}', 'approved')"
                        style="padding:9px 16px;background:var(--sage);color:#fff;border:none;border-radius:9px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;gap:5px;justify-content:center;transition:opacity .15s"
                        onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Approve
                </button>

                {{-- Deny --}}
                <button type="button"
                        @click="handleRequest('{{ route('patient.access.deny', $req) }}', 'denied')"
                        style="padding:9px 16px;background:transparent;color:#dc2626;border:1.5px solid #fecaca;border-radius:9px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;gap:5px;justify-content:center;transition:all .15s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Deny
                </button>

                {{-- Re-send OTP --}}
                <form method="POST" action="{{ route('patient.access.send-otp', $req) }}">
                    @csrf
                    <button type="submit"
                            style="width:100%;padding:6px;background:transparent;color:var(--txt-lt);border:1px solid var(--warm-bd);border-radius:8px;font-size:.72rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .15s"
                            onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
                        Resend OTP
                    </button>
                </form>
            </div>

            {{-- Processing spinner --}}
            <div x-show="processing" style="flex-shrink:0;display:flex;align-items:center;justify-content:center;min-width:110px">
                <div style="width:24px;height:24px;border:2.5px solid var(--warm-bd);border-top-color:var(--plum);border-radius:50%;animation:spin .6s linear infinite"></div>
            </div>
        </div>

        {{-- Outcome flash (shown briefly after action) --}}
        <div x-show="action === 'approved'" style="margin-top:10px;padding:8px 12px;background:#e8f5f3;border-radius:8px;font-size:.8rem;color:#1a7a6a;font-weight:500">
            ✓ Access approved — Dr. {{ $drName }} can now view your records.
        </div>
        <div x-show="action === 'denied'" style="margin-top:10px;padding:8px 12px;background:#fef2f2;border-radius:8px;font-size:.8rem;color:#dc2626;font-weight:500">
            ✗ Request denied.
        </div>
    </div>
    @endforeach
    </div>
</div>
@endif

{{-- ══ MAIN GRID ═══════════════════════════════════════════════════════════════ -- --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start">

{{-- ── LEFT: Active grants + History ──────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:20px">

    {{-- Active Grants --}}
    <div class="panel">
        <div style="display:flex;align-items:center;justify-content:space-between;padding-bottom:14px;border-bottom:1px solid var(--warm-bd);margin-bottom:16px">
            <div>
                <h3 style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)">Active Doctor Access</h3>
                <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">Doctors currently authorized to view your health records</div>
            </div>
            <span style="font-size:.78rem;font-weight:600;padding:4px 10px;border-radius:20px;background:{{ $active->isEmpty() ? 'var(--parch)' : '#e8f5f3' }};color:{{ $active->isEmpty() ? 'var(--txt-lt)' : '#1a7a6a' }}">
                {{ $active->count() }} active
            </span>
        </div>

        @if($active->isEmpty())
        <div style="text-align:center;padding:28px 16px;color:var(--txt-lt)">
            <div style="width:42px;height:42px;border-radius:11px;background:var(--parch);display:flex;align-items:center;justify-content:center;margin:0 auto 10px">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <div style="font-family:'Lora',serif;font-size:.9375rem;color:var(--txt-md)">No active access grants</div>
            <p style="font-size:.78rem;margin-top:4px">When a doctor requests access and you approve, they'll appear here.</p>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach($active as $grant)
            @php
                $drName   = $grant->doctor?->profile?->full_name ?? 'Doctor';
                $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$drName),0,2))));
                $dp       = $grant->doctor?->doctorProfile;
                $daysLeft = now()->diffInDays($grant->access_expires_at, false);
                $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
                $color    = $colors[$grant->doctor_user_id % count($colors)];
                $expiringSoon = $daysLeft <= 5;
            @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid {{ $expiringSoon ? '#fde68a' : 'var(--warm-bd)' }};border-radius:12px;background:{{ $expiringSoon ? '#fffbeb' : 'var(--cream)' }};transition:all .15s"
                 x-data="{ revoking: false, revoked: false }"
                 x-show="!revoked" x-transition>

                <div style="width:40px;height:40px;border-radius:10px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;color:#fff;flex-shrink:0">
                    {{ $initials }}
                </div>

                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:.875rem;color:var(--txt)">Dr. {{ $drName }}</div>
                    @if($dp?->specialization)
                    <div style="font-size:.75rem;color:var(--txt-lt)">{{ $dp->specialization }}</div>
                    @endif
                    <div style="display:flex;align-items:center;gap:10px;margin-top:4px;flex-wrap:wrap">
                        @if($grant->familyMember)
                        <span style="font-size:.7rem;padding:2px 7px;border-radius:20px;background:var(--parch);color:var(--txt-lt);border:1px solid var(--warm-bd)">
                            For {{ $grant->familyMember->full_name }}
                        </span>
                        @else
                        <span style="font-size:.7rem;padding:2px 7px;border-radius:20px;background:var(--parch);color:var(--txt-lt);border:1px solid var(--warm-bd)">
                            Self
                        </span>
                        @endif
                        <span style="font-size:.7rem;font-weight:600;color:{{ $expiringSoon ? '#b45309' : 'var(--txt-lt)' }}">
                            {{ $expiringSoon ? '⚠ ' : '' }}Expires {{ $grant->access_expires_at->format('d M Y') }} ({{ max(0, $daysLeft) }}d)
                        </span>
                        <span style="font-size:.7rem;color:var(--txt-lt)">Since {{ $grant->approved_at->format('d M Y') }}</span>
                    </div>
                </div>

                {{-- Revoke button --}}
                <button type="button" x-show="!revoking"
                        @click="revoking=true;
                            fetch('{{ route('patient.access.revoke', $grant->doctor_user_id) }}', {
                                method:'POST',
                                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json','Content-Type':'application/json'}
                            }).then(r=>r.json()).then(d=>{ if(d.success){ revoked=true } else { revoking=false } })"
                        style="flex-shrink:0;padding:7px 12px;border:1.5px solid #fecaca;border-radius:9px;color:#dc2626;background:transparent;font-size:.75rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .15s;white-space:nowrap"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'"
                        onclick="if(!confirm('Revoke Dr. {{ addslashes($drName) }}\'s access?')) { event.stopImmediatePropagation(); }">
                    Revoke
                </button>
                <div x-show="revoking" style="flex-shrink:0;width:16px;height:16px;border:2px solid #fecaca;border-top-color:#dc2626;border-radius:50%;animation:spin .6s linear infinite"></div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Family Member Overrides --}}
    @if($patient->familyMembers->isNotEmpty())
    <div class="panel">
        <div style="margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--warm-bd)">
            <h3 style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)">Family Member Settings</h3>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">
                Override the default access setting for individual family members
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($patient->familyMembers as $fm)
            @php
                $fmPerm   = $memberPermissions->get($fm->id);
                $fmType   = $fmPerm?->access_type ?? $globalType; // inherit from global if no override
                $hasOverride = $fmPerm !== null;
            @endphp
            <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--parch);border-radius:10px">
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;font-size:.875rem;color:var(--txt)">{{ $fm->full_name }}</div>
                    <div style="font-size:.72rem;color:var(--txt-lt)">{{ ucfirst($fm->relation) }}
                        @if(!$hasOverride) · <em>inheriting global setting</em>@endif
                    </div>
                </div>

                {{-- Access type selector --}}
                <form method="POST" action="{{ route('patient.access.member', $fm->id) }}"
                      onchange="this.submit()" style="display:flex;gap:6px;align-items:center">
                    @csrf @method('PUT')
                    <select name="access_type"
                            style="padding:5px 10px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.78rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer">
                        <option value="full"         {{ $fmType==='full'         ? 'selected':'' }}>Full access</option>
                        <option value="otp_required" {{ $fmType==='otp_required' ? 'selected':'' }}>OTP required</option>
                        <option value="blocked"      {{ $fmType==='blocked'      ? 'selected':'' }}>Blocked</option>
                    </select>
                </form>

                @if($hasOverride)
                <form method="POST" action="{{ route('patient.access.member', $fm->id) }}" onsubmit="return true">
                    @csrf @method('PUT')
                    <input type="hidden" name="access_type" value="{{ $globalType }}">
                    <button type="submit"
                            title="Reset to global default"
                            style="width:26px;height:26px;border:1px solid var(--warm-bd);border-radius:7px;background:transparent;cursor:pointer;color:var(--txt-lt);font-size:.7rem;transition:all .12s;display:flex;align-items:center;justify-content:center"
                            onmouseover="this.style.color='var(--txt)';this.style.background='var(--cream)'" onmouseout="this.style.color='var(--txt-lt)';this.style.background='transparent'"
                            onclick="return confirm('Reset {{ $fm->full_name }} to global default?')">
                        ↺
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recently closed --}}
    @if($recentClosed->isNotEmpty())
    <div class="panel">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;padding-bottom:12px;border-bottom:1px solid var(--warm-bd)">
            <h3 style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)">Recent Activity</h3>
            <a href="{{ route('patient.access.history') }}" style="font-size:.75rem;color:var(--plum);text-decoration:none" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">Full history →</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($recentClosed as $req)
            @php
                $drName   = $req->doctor?->profile?->full_name ?? 'Doctor';
                $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$drName),0,2))));
                $color    = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'][$req->doctor_user_id % 5];
                $stCfg    = match($req->status) {
                    'denied'  => ['bg'=>'#fef2f2','color'=>'#dc2626','label'=>'Denied'],
                    'expired' => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>'Expired'],
                    default   => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>ucfirst($req->status)],
                };
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--parch)">
                <div style="width:30px;height:30px;border-radius:8px;background:{{ $color }}22;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:{{ $color }};flex-shrink:0">
                    {{ $initials }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.8125rem;font-weight:500;color:var(--txt-md)">Dr. {{ $drName }}</div>
                    @if($req->familyMember)
                    <div style="font-size:.7rem;color:var(--txt-lt)">For {{ $req->familyMember->full_name }}</div>
                    @endif
                </div>
                <span style="font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $stCfg['bg'] }};color:{{ $stCfg['color'] }}">{{ $stCfg['label'] }}</span>
                <span style="font-size:.68rem;color:var(--txt-lt)">{{ $req->updated_at->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- end left --}}

{{-- ── RIGHT: Global settings + info ────────────────────────────────────────── -- --}}
<div style="position:sticky;top:calc(var(--topbar-h)+20px);display:flex;flex-direction:column;gap:14px">

    {{-- Global access type --}}
    <div class="panel" style="padding:18px 20px">
        <h3 style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:4px">Default Access Setting</h3>
        <p style="font-size:.75rem;color:var(--txt-lt);margin-bottom:14px">Applies to all doctors unless you set a per-member override</p>

        <form method="POST" action="{{ route('patient.access.type') }}">
            @csrf @method('PUT')

            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px">
                @foreach(['otp_required' => ['icon'=>'🔐', 'label'=>'OTP Required', 'desc'=>'Doctor must enter a one-time code sent to their mobile. You control access.'],
                          'full'         => ['icon'=>'🔓', 'label'=>'Full Access', 'desc'=>'Any verified doctor can view your records immediately when granted.']] as $val => $cfg)
                <label style="display:flex;align-items:flex-start;gap:10px;padding:12px 13px;border:1.5px solid {{ $globalType===$val ? 'var(--plum)' : 'var(--warm-bd)' }};border-radius:10px;cursor:pointer;background:{{ $globalType===$val ? '#f7f4fc' : 'var(--cream)' }};transition:all .15s">
                    <input type="radio" name="access_type" value="{{ $val }}" {{ $globalType===$val ? 'checked':'' }}
                           style="margin-top:2px;accent-color:var(--plum)">
                    <div>
                        <div style="font-size:.875rem;font-weight:600;color:var(--txt)">{{ $cfg['icon'] }} {{ $cfg['label'] }}</div>
                        <div style="font-size:.72rem;color:var(--txt-lt);margin-top:2px;line-height:1.4">{{ $cfg['desc'] }}</div>
                    </div>
                </label>
                @endforeach
            </div>

            <button type="submit"
                    style="width:100%;padding:9px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                Save Setting
            </button>
        </form>
    </div>

    {{-- Info card --}}
    <div class="panel" style="padding:16px 18px;background:#fafaf8">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">How it works</div>
        @foreach([
            ['🏥', 'Doctor searches for you by mobile, Sub-ID, or Aadhaar'],
            ['📲', 'An OTP is sent to their registered mobile number'],
            ['✅', 'You see the request here and choose to approve or deny'],
            ['⏱', 'Approved access lasts '.config('medtech.access.duration_days',30).' days, then expires automatically'],
            ['🔒', 'You can revoke access any time from this page'],
        ] as [$icon, $text])
        <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--warm-bd);font-size:.78rem;color:var(--txt-md)">
            <span style="flex-shrink:0">{{ $icon }}</span>
            <span style="line-height:1.4">{{ $text }}</span>
        </div>
        @endforeach
    </div>

    {{-- History link --}}
    <a href="{{ route('patient.access.history') }}"
       style="display:flex;align-items:center;justify-content:center;gap:6px;padding:9px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.8125rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:all .15s"
       onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        View Full Access History
    </a>
</div>{{-- end right --}}

</div>{{-- end grid --}}
</div>{{-- end x-data --}}
@endsection

@push('styles')
<style>
@keyframes spin   { to { transform: rotate(360deg); } }
@keyframes pulse  { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>
@endpush

@push('scripts')
<script>
function accessHub() {
    return {
        init() {},

        async handleRequest(url, expectedAction) {
            const comp = this;
            // Find the parent Alpine component
            // handled inline via x-data on each card
        }
    }
}
</script>
@endpush
