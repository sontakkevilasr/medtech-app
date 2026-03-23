@extends('layouts.patient')
@section('title', 'Family & Sub-IDs')
@section('page-title', 'Family Members & Sub-IDs')

@section('content')
@php
    $patient     = auth()->user();
    $patientName = $patient->profile?->full_name ?? 'You';
    // Generate a "self" sub-id display – MED-{padded_id}-A convention
    $prefix  = config('medtech.sub_id.prefix', 'MED');
    $padded  = str_pad($patient->id, 5, '0', STR_PAD_LEFT);
    $selfId  = $self?->sub_id ?? "{$prefix}-{$padded}-A";
    $relationColors = [
        'self'        => '#4a3760',
        'spouse'      => '#7a3d6e',
        'child'       => '#3d7a6e',
        'parent'      => '#7a6e3d',
        'sibling'     => '#3d5e7a',
        'grandparent' => '#6e3d7a',
        'other'       => '#5a6e7a',
    ];
@endphp

<div class="fade-in">

{{-- ══ YOUR OWN SUB-ID CARD ═══════════════════════════════════════════════════ -- --}}
<div style="background:linear-gradient(135deg,#4a3760 0%,#2d1f47 100%);border-radius:18px;padding:28px 32px;margin-bottom:26px;color:#fff;position:relative;overflow:hidden">
    {{-- Decorative circles --}}
    <div style="position:absolute;right:-30px;top:-30px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.05)"></div>
    <div style="position:absolute;right:60px;bottom:-50px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04)"></div>

    <div style="position:relative;z-index:1">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
            <div>
                <div style="font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:6px">Your Primary Sub-ID</div>
                <div style="font-family:'Lora',serif;font-size:2.4rem;font-weight:500;letter-spacing:.06em;color:#fff;margin-bottom:4px">
                    {{ $selfId }}
                </div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.6)">
                    {{ $patientName }} · Primary account holder
                </div>
            </div>

            {{-- Copy + share buttons --}}
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start">
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $selfId }}').then(()=>{ const b=this; b.textContent='Copied!'; setTimeout(()=>b.textContent='Copy ID',2000) })"
                        style="padding:8px 16px;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;background:rgba(255,255,255,.1);color:#fff;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .15s;display:flex;align-items:center;gap:6px"
                        onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Copy ID
                </button>
                <a href="https://wa.me/?text={{ urlencode('My Naumah Clinic Sub-ID is: '.$selfId.' — share with your doctor for quick record lookup.') }}"
                   target="_blank"
                   style="padding:8px 16px;border:1.5px solid rgba(37,211,102,.5);border-radius:9px;background:rgba(37,211,102,.15);color:#fff;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;text-decoration:none;display:flex;align-items:center;gap:6px;transition:all .15s"
                   onmouseover="this.style.background='rgba(37,211,102,.25)'" onmouseout="this.style.background='rgba(37,211,102,.15)'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.997 0C5.373 0 0 5.373 0 12c0 2.115.555 4.102 1.523 5.827L.057 23.999l6.307-1.654A11.954 11.954 0 0011.997 24c6.624 0 11.997-5.373 11.997-12S18.621 0 11.997 0zm0 21.818a9.818 9.818 0 01-5.003-1.368l-.359-.213-3.742.981 1-3.634-.233-.374A9.786 9.786 0 012.182 12c0-5.414 4.402-9.818 9.815-9.818 5.414 0 9.818 4.404 9.818 9.818 0 5.413-4.404 9.818-9.818 9.818z"/></svg>
                    Share via WhatsApp
                </a>
            </div>
        </div>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,.12);font-size:.75rem;color:rgba(255,255,255,.45);line-height:1.6">
            💡 Share this Sub-ID with your doctor to give them a quick way to access your records — they can look you up without knowing your mobile number.
        </div>
    </div>
</div>

{{-- ══ ACTIVE FAMILY MEMBERS ══════════════════════════════════════════════════ -- --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
    <div>
        <h2 style="font-family:'Lora',serif;font-size:1.1rem;font-weight:500;color:var(--txt)">Family Members</h2>
        <div style="font-size:.75rem;color:var(--txt-lt);margin-top:2px">Each member gets their own unique Sub-ID for medical record tracking</div>
    </div>
    <a href="{{ route('patient.family.create') }}"
       style="display:flex;align-items:center;gap:6px;padding:8px 16px;background:var(--plum);color:#fff;border-radius:9px;font-size:.8rem;font-weight:600;text-decoration:none;transition:opacity .15s"
       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Member
    </a>
</div>

@if($active->isEmpty())
<div class="panel" style="padding:36px 24px;text-align:center;color:var(--txt-lt);margin-bottom:24px">
    <div style="width:48px;height:48px;border-radius:13px;background:var(--parch);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
        <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    </div>
    <div style="font-family:'Lora',serif;font-size:.9375rem;color:var(--txt-md)">No family members added yet</div>
    <p style="font-size:.78rem;margin-top:4px;margin-bottom:14px">Add your spouse, children, or parents to manage their health records under your account.</p>
    <a href="{{ route('patient.family.create') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:9px 20px;background:var(--plum);color:#fff;border-radius:10px;font-size:.875rem;font-weight:600;text-decoration:none">
        Add First Member
    </a>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;margin-bottom:26px">
    @foreach($active as $member)
    @php
        $relColor = $relationColors[$member->relation] ?? '#5a6e7a';
        $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$member->full_name),0,2))));
    @endphp
    <div class="panel" style="padding:0;overflow:hidden;transition:box-shadow .15s"
         onmouseover="this.style.boxShadow='0 4px 20px rgba(74,55,96,.1)'" onmouseout="this.style.boxShadow='none'">

        {{-- Top colour bar with avatar --}}
        <div style="background:linear-gradient(135deg,{{ $relColor }} 0%,{{ $relColor }}bb 100%);padding:18px 20px 16px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <div style="width:42px;height:42px;border-radius:11px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#fff">
                    {{ $initials }}
                </div>
                <span style="font-size:.68rem;font-weight:700;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.2);color:#fff;text-transform:uppercase;letter-spacing:.06em">
                    {{ $member->relation }}
                </span>
            </div>
            <div style="color:#fff">
                <div style="font-size:1rem;font-weight:600">{{ $member->full_name }}</div>
                @if($member->dob)
                <div style="font-size:.75rem;color:rgba(255,255,255,.7);margin-top:2px">
                    {{ $member->dob->format('d M Y') }} · Age {{ $member->age }}
                    @if($member->gender) · {{ ucfirst($member->gender) }} @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Sub-ID display --}}
        <div style="padding:14px 20px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--txt-lt);margin-bottom:5px">Sub-ID</div>
            <div style="display:flex;align-items:center;gap:8px">
                <code style="font-size:1rem;font-weight:700;color:var(--txt);letter-spacing:.06em;flex:1">{{ $member->sub_id }}</code>
                <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $member->sub_id }}').then(()=>{ const b=this; b.style.color='var(--sage)'; setTimeout(()=>b.style.color='var(--txt-lt)',1500) })"
                        style="width:28px;height:28px;border:1px solid var(--warm-bd);border-radius:7px;background:transparent;cursor:pointer;color:var(--txt-lt);display:flex;align-items:center;justify-content:center;transition:all .12s"
                        title="Copy Sub-ID">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                </button>
                <a href="https://wa.me/?text={{ urlencode($member->full_name."'s Naumah Clinic Sub-ID: ".$member->sub_id) }}"
                   target="_blank"
                   style="width:28px;height:28px;border:1px solid #bbf7d0;border-radius:7px;background:#f0fdf4;cursor:pointer;color:#15803d;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s"
                   title="Share via WhatsApp">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.997 0C5.373 0 0 5.373 0 12c0 2.115.555 4.102 1.523 5.827L.057 23.999l6.307-1.654A11.954 11.954 0 0011.997 24c6.624 0 11.997-5.373 11.997-12S18.621 0 11.997 0zm0 21.818a9.818 9.818 0 01-5.003-1.368l-.359-.213-3.742.981 1-3.634-.233-.374A9.786 9.786 0 012.182 12c0-5.414 4.402-9.818 9.815-9.818 5.414 0 9.818 4.404 9.818 9.818 0 5.413-4.404 9.818-9.818 9.818z"/></svg>
                </a>
            </div>
        </div>

        {{-- Actions row --}}
        <div style="padding:10px 20px;display:flex;gap:6px;justify-content:flex-end">
            @if($member->blood_group)
            <span style="font-size:.7rem;padding:3px 8px;border-radius:6px;background:var(--parch);color:var(--txt-lt);margin-right:auto">
                {{ $member->blood_group }}
            </span>
            @endif
            <a href="{{ route('patient.family.show', $member->id) }}"
               style="font-size:.75rem;font-weight:500;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:all .12s"
               onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                View →
            </a>
            <a href="{{ route('patient.family.edit', $member->id) }}"
               style="font-size:.75rem;font-weight:500;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:all .12s"
               onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                Edit
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ══ DELINKED SUB-IDs ════════════════════════════════════════════════════════ -- --}}
@if($delinked->isNotEmpty())
<div style="margin-bottom:24px">
    <h3 style="font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt-md);margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        Delinked Sub-IDs
    </h3>
    <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($delinked as $member)
        <div style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--cream);border:1.5px solid var(--warm-bd);border-radius:12px;opacity:.8">
            <div style="width:36px;height:36px;border-radius:9px;background:var(--parch);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:var(--txt-lt);flex-shrink:0">
                {{ strtoupper(substr($member->full_name,0,1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:500;font-size:.875rem;color:var(--txt-md)">{{ $member->full_name }}</div>
                <div style="font-size:.72rem;color:var(--txt-lt)">
                    <code>{{ $member->sub_id }}</code> · Delinked {{ $member->delinked_at?->diffForHumans() }}
                    @if($member->linked_mobile) → linked to {{ $member->linked_country_code }} {{ $member->linked_mobile }} @endif
                </div>
            </div>
            <span style="font-size:.68rem;padding:2px 8px;border-radius:20px;background:#fef3c7;color:#92400e;font-weight:600">Delinked</span>
            <form method="POST" action="{{ route('patient.family.relink', $member->id) }}">
                @csrf
                <button type="submit"
                        style="font-size:.75rem;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);background:transparent;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .12s"
                        onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'"
                        onclick="return confirm('Re-link {{ $member->full_name }} back to your account?')">
                    Re-link
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══ WHAT IS A SUB-ID? INFO CARD ════════════════════════════════════════════ -- --}}
<div class="panel" style="padding:18px 22px;background:var(--parch)">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">About Sub-IDs</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
        @foreach([
            ['🏷️', 'Unique identifier', 'Every person in your family gets a permanent ID like MED-00001-A that stays with them forever.'],
            ['🔒', 'Privacy first',     'Doctors search by Sub-ID instead of your mobile number — no personal data is exposed during lookup.'],
            ['🔗', 'Delink anytime',    "When a family member gets their own account, you can delink their Sub-ID so it moves with them."],
        ] as [$icon, $title, $desc])
        <div>
            <div style="font-size:1.2rem;margin-bottom:5px">{{ $icon }}</div>
            <div style="font-size:.8125rem;font-weight:600;color:var(--txt);margin-bottom:3px">{{ $title }}</div>
            <div style="font-size:.75rem;color:var(--txt-lt);line-height:1.5">{{ $desc }}</div>
        </div>
        @endforeach
    </div>
</div>

</div>
@endsection
