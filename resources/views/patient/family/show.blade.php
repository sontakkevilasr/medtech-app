@extends('layouts.patient')
@section('title', $member->full_name)
@section('page-title')
    <a href="{{ route('patient.family.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Family Members</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $member->full_name }}
@endsection

@section('content')
@php
    $relationColors = ['self'=>'#4a3760','spouse'=>'#7a3d6e','child'=>'#3d7a6e','parent'=>'#7a6e3d','sibling'=>'#3d5e7a','grandparent'=>'#6e3d7a','other'=>'#5a6e7a'];
    $relColor = $relationColors[$member->relation] ?? '#5a6e7a';
    $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$member->full_name),0,2))));
@endphp
<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start">

{{-- ── LEFT ─────────────────────────────────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Profile card --}}
    <div class="panel" style="padding:0;overflow:hidden">
        {{-- Coloured banner --}}
        <div style="background:linear-gradient(135deg,{{ $relColor }} 0%,{{ $relColor }}bb 100%);padding:22px 24px;display:flex;align-items:center;gap:16px">
            <div style="width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:9px;flex-wrap:wrap">
                    <span style="font-family:'Lora',serif;font-size:1.3rem;font-weight:500;color:#fff">{{ $member->full_name }}</span>
                    <span style="font-size:.7rem;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.2);color:#fff;text-transform:uppercase;letter-spacing:.05em">{{ $member->relation }}</span>
                    @if($member->is_delinked)
                    <span style="font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;background:#fef3c7;color:#92400e">Delinked</span>
                    @endif
                </div>
                @if($member->dob)
                <div style="font-size:.8rem;color:rgba(255,255,255,.7);margin-top:3px">
                    Born {{ $member->dob->format('d M Y') }} · Age {{ $member->age }}
                    @if($member->gender) · {{ ucfirst($member->gender) }} @endif
                    @if($member->blood_group) · <strong style="color:#fff">{{ $member->blood_group }}</strong> @endif
                </div>
                @endif
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0">
                <a href="{{ route('patient.family.edit', $member->id) }}"
                   style="padding:7px 14px;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;background:rgba(255,255,255,.1);color:#fff;font-size:.78rem;font-weight:500;text-decoration:none;transition:all .15s"
                   onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                    Edit
                </a>
            </div>
        </div>

        {{-- Details grid --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0">
            @php
            $details = [
                'Full Name'    => $member->full_name,
                'Relation'     => ucfirst($member->relation),
                'Date of Birth'=> $member->dob?->format('d M Y') ?? '—',
                'Age'          => $member->age ? $member->age.' years' : '—',
                'Gender'       => $member->gender ? ucfirst($member->gender) : '—',
                'Blood Group'  => $member->blood_group ?? '—',
                'Added On'     => $member->created_at->format('d M Y'),
                'Status'       => $member->is_delinked ? 'Delinked' : 'Active',
            ];
            @endphp
            @foreach($details as $label => $val)
            <div style="padding:11px 20px;border-bottom:1px solid var(--warm-bd);{{ $loop->odd ? 'border-right:1px solid var(--warm-bd)' : '' }}">
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:2px">{{ $label }}</div>
                <div style="font-size:.875rem;color:var(--txt-md)">{{ $val }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent appointments --}}
    @if($appointments->isNotEmpty())
    <div class="panel">
        <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--warm-bd)">
            Recent Appointments
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($appointments as $apt)
            @php
                $drName = $apt->doctor?->profile?->full_name ?? 'Doctor';
                $stCfg  = match($apt->status) {
                    'confirmed'  => 'color:#1a7a6a',
                    'completed'  => 'color:#0369a1',
                    'cancelled'  => 'color:#dc2626',
                    default      => 'color:var(--txt-lt)',
                };
            @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--parch);font-size:.8125rem">
                <div>
                    <span style="font-weight:500;color:var(--txt)">Dr. {{ $drName }}</span>
                    <span style="color:var(--txt-lt);margin-left:8px">{{ $apt->slot_datetime->format('d M Y, h:i A') }}</span>
                </div>
                <span style="font-size:.7rem;font-weight:600;{{ $stCfg }}">{{ ucfirst($apt->status) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent prescriptions --}}
    @if($prescriptions->isNotEmpty())
    <div class="panel">
        <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--warm-bd)">
            Recent Prescriptions
        </div>
        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($prescriptions as $rx)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--parch);font-size:.8125rem">
                <div>
                    <span style="font-weight:500;color:var(--txt)">{{ $rx->rx_number }}</span>
                    <span style="color:var(--txt-lt);margin-left:8px">Dr. {{ $rx->doctor?->profile?->full_name }}</span>
                </div>
                <span style="font-size:.72rem;color:var(--txt-lt)">{{ \Carbon\Carbon::parse($rx->prescribed_date)->format('d M Y') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ── RIGHT: Sub-ID card + actions ──────────────────────────────────────────── -- --}}
<div style="position:sticky;top:calc(var(--topbar-h)+20px);display:flex;flex-direction:column;gap:14px">

    {{-- Sub-ID card --}}
    <div style="background:linear-gradient(135deg,{{ $relColor }} 0%,{{ $relColor }}88 100%);border-radius:16px;padding:22px 20px;color:#fff">
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.5);margin-bottom:8px">Sub-ID</div>
        <div style="font-family:'Lora',serif;font-size:1.8rem;font-weight:500;letter-spacing:.06em;margin-bottom:6px">
            {{ $member->sub_id }}
        </div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.6);margin-bottom:16px">{{ $member->full_name }}</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
            <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $member->sub_id }}').then(()=>{ const b=this; b.textContent='✓ Copied'; setTimeout(()=>{ b.innerHTML='<svg width=\'11\' height=\'11\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'2\'><rect x=\'9\' y=\'9\' width=\'13\' height=\'13\' rx=\'2\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1\'/></svg> Copy'; }, 1800) })"
                    style="padding:8px;border:1.5px solid rgba(255,255,255,.3);border-radius:8px;background:rgba(255,255,255,.1);color:#fff;font-size:.78rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;justify-content:center;gap:5px;transition:all .15s"
                    onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Copy
            </button>
            <a href="https://wa.me/?text={{ urlencode($member->full_name."'s Sub-ID: ".$member->sub_id." — share with your doctor for quick record lookup.") }}"
               target="_blank"
               style="padding:8px;border:1.5px solid rgba(37,211,102,.5);border-radius:8px;background:rgba(37,211,102,.15);color:#fff;font-size:.78rem;font-weight:600;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:5px;transition:all .15s"
               onmouseover="this.style.background='rgba(37,211,102,.3)'" onmouseout="this.style.background='rgba(37,211,102,.15)'">
                WhatsApp
            </a>
        </div>
    </div>

    {{-- Delink action --}}
    @if(!$member->is_delinked)
    <div class="panel" style="padding:16px 18px" x-data="{ open: false }">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Delink Sub-ID</div>
        <p style="font-size:.75rem;color:var(--txt-lt);line-height:1.5;margin-bottom:10px">
            Transfer this Sub-ID to {{ $member->full_name }}'s own mobile — for when they get their own account.
        </p>
        <button type="button" @click="open=!open"
                style="width:100%;padding:8px;border:1.5px solid var(--warm-bd);border-radius:9px;background:transparent;color:var(--txt-md);font-size:.8125rem;font-weight:500;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .15s"
                onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            🔗 Delink to mobile…
        </button>
        <div x-show="open" x-transition style="margin-top:10px">
            <form method="POST" action="{{ route('patient.family.delink', $member->id) }}">
                @csrf
                <div style="margin-bottom:8px">
                    <label style="font-size:.72rem;color:var(--txt-lt);display:block;margin-bottom:4px">Mobile to link to</label>
                    <div style="display:flex;gap:6px">
                        <input type="text" name="linked_country_code" value="+91"
                               style="width:52px;padding:7px 8px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif">
                        <input type="tel" name="linked_mobile" placeholder="10-digit mobile"
                               pattern="[6-9]\d{9}" maxlength="10"
                               style="flex:1;padding:7px 10px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif"
                               required>
                    </div>
                </div>
                <button type="submit"
                        style="width:100%;padding:8px;background:#7a5c3d;color:#fff;border:none;border-radius:9px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif"
                        onclick="return confirm('Delink {{ $member->full_name }}\'s Sub-ID? They will be removed from your household.')">
                    Confirm Delink
                </button>
            </form>
        </div>
    </div>
    @else
    {{-- Relink --}}
    <div class="panel" style="padding:16px 18px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Sub-ID Delinked</div>
        <p style="font-size:.75rem;color:var(--txt-lt);margin-bottom:10px">
            This Sub-ID was delinked on {{ $member->delinked_at?->format('d M Y') }}.
        </p>
        <form method="POST" action="{{ route('patient.family.relink', $member->id) }}">
            @csrf
            <button type="submit"
                    style="width:100%;padding:8px;border:1.5px solid var(--warm-bd);border-radius:9px;background:transparent;color:var(--txt-md);font-size:.8125rem;font-weight:500;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .15s"
                    onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'"
                    onclick="return confirm('Re-link this Sub-ID back to your account?')">
                ↩ Re-link to my account
            </button>
        </form>
    </div>
    @endif

    {{-- Danger zone --}}
    <div class="panel" style="padding:14px 16px;border-color:#fecaca">
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#dc2626;margin-bottom:8px">Danger Zone</div>

        {{-- Regenerate Sub-ID --}}
        <form method="POST" action="{{ route('patient.family.regenerate-id', $member->id) }}"
              onsubmit="return confirm('Regenerate Sub-ID? The current ID {{ $member->sub_id }} will stop working immediately.')"
              style="margin-bottom:6px">
            @csrf
            <button type="submit"
                    style="width:100%;padding:7px;border:1px solid #fecaca;border-radius:8px;background:transparent;color:#dc2626;font-size:.75rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:background .12s"
                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                🔄 Regenerate Sub-ID
            </button>
        </form>

        {{-- Remove member --}}
        <form method="POST" action="{{ route('patient.family.destroy', $member->id) }}"
              onsubmit="return confirm('Remove {{ $member->full_name }} from your household? Their Sub-ID record is kept for history.')">
            @csrf @method('DELETE')
            <button type="submit"
                    style="width:100%;padding:7px;border:1px solid #fecaca;border-radius:8px;background:transparent;color:#dc2626;font-size:.75rem;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:background .12s"
                    onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                Remove from household
            </button>
        </form>
    </div>
</div>

</div>
@endsection
