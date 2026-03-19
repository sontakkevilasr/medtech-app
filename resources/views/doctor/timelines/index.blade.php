@extends('layouts.doctor')
@section('title', 'Care Timelines')
@section('page-title', 'Care Timelines')

@section('content')
@php
$specMeta = [
    'obstetrics' => ['icon'=>'🤰','color'=>'#c0737a','bg'=>'#fce7ef','label'=>'Pregnancy'],
    'pediatrics' => ['icon'=>'👶','color'=>'#3d7a8a','bg'=>'#e8f5f9','label'=>'Paediatric'],
    'ivf'        => ['icon'=>'🧬','color'=>'#8a6aaa','bg'=>'#f4f0fa','label'=>'IVF'],
    'dental'     => ['icon'=>'🦷','color'=>'#3d7a6e','bg'=>'#eef5f3','label'=>'Dental'],
    'cardiology' => ['icon'=>'❤️','color'=>'#c98a3a','bg'=>'#fdf5e8','label'=>'Cardiology'],
];
@endphp

<div class="fade-in">

{{-- ── System Templates ─────────────────────────────────────────────────────── -- --}}
<div style="margin-bottom:28px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:500;color:var(--txt)">System Templates</h2>
            <div style="font-size:.75rem;color:var(--txt-lt);margin-top:1px">Clinically curated templates — Pregnancy, Vaccination, IVF, Ortho</div>
        </div>
        <a href="{{ route('doctor.timelines.create') }}"
           style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--leaf);color:#fff;border-radius:9px;font-size:.8rem;font-weight:600;text-decoration:none">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Template
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
        @foreach($systemTemplates as $t)
        @php $m = $specMeta[$t->specialty_type] ?? ['icon'=>'📅','color'=>'#4a3760','bg'=>'#f4f0fa','label'=>ucfirst($t->specialty_type)]; @endphp
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;overflow:hidden;transition:box-shadow .15s"
             onmouseover="this.style.boxShadow='0 4px 20px rgba(28,43,42,.1)'" onmouseout="this.style.boxShadow='none'">
            {{-- Colour header --}}
            <div style="background:linear-gradient(135deg,{{ $m['color'] }} 0%,{{ $m['color'] }}cc 100%);padding:14px 18px;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.6);margin-bottom:2px">{{ $m['label'] }}</div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;font-weight:500;color:#fff;line-height:1.3">{{ $t->title }}</div>
                </div>
                <span style="font-size:1.6rem;opacity:.85">{{ $m['icon'] }}</span>
            </div>

            <div style="padding:12px 16px">
                <div style="font-size:.78rem;color:var(--txt-lt);margin-bottom:10px;line-height:1.5">{{ Str::limit($t->description, 90) }}</div>
                <div style="display:flex;gap:12px;font-size:.72rem;color:var(--txt-lt);margin-bottom:12px">
                    <span>📋 {{ $t->milestones_count }} milestones</span>
                    <span>👥 {{ $t->patient_timelines_count }} assigned</span>
                    <span>⏱ {{ $t->total_duration_days }}d</span>
                </div>
                <div style="display:flex;gap:6px">
                    <a href="{{ route('doctor.timelines.show', $t) }}"
                       style="flex:1;text-align:center;padding:6px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.78rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:all .12s"
                       onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                        View Milestones
                    </a>
                    <a href="{{ route('doctor.patients.index') }}?assign_template={{ $t->id }}"
                       style="flex:1;text-align:center;padding:6px;background:{{ $m['color'] }};border-radius:8px;font-size:.78rem;font-weight:600;color:#fff;text-decoration:none;opacity:.9;transition:opacity .12s"
                       onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='.9'">
                        Assign to Patient
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Active Assignments ───────────────────────────────────────────────────── -- --}}
<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:18px">

<div>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:500;color:var(--txt);margin-bottom:14px">
        Active Assignments
        <span style="font-size:.75rem;font-weight:400;color:var(--txt-lt);margin-left:6px">patients currently on a timeline you assigned</span>
    </h2>

    @if($activeAssignments->isEmpty())
    <div style="padding:28px;text-align:center;color:var(--txt-lt);border:1.5px dashed var(--warm-bd);border-radius:12px">
        <div style="font-size:1.6rem;margin-bottom:8px">📅</div>
        <div style="font-size:.875rem">No active assignments yet. Assign a template to a patient from their profile.</div>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:8px">
    @foreach($activeAssignments as $pt)
    @php
        $pname  = $pt->patient?->profile?->full_name ?? 'Patient';
        $tname  = $pt->template?->title ?? 'Timeline';
        $spec   = $pt->template?->specialty_type ?? 'other';
        $m      = $specMeta[$spec] ?? ['icon'=>'📅','color'=>'#4a3760','bg'=>'#f4f0fa'];
        $milestones = $pt->getMilestonesWithDates();
        $done   = $milestones->filter(fn($m) => $m->is_past && !$m->is_today)->count();
        $total  = $milestones->count();
        $pct    = $total ? round($done/$total*100) : 0;
        $next   = $milestones->filter(fn($m) => !$m->is_past || $m->is_today)->first();
    @endphp
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:11px;padding:13px 16px;display:flex;align-items:center;gap:12px;transition:box-shadow .12s"
         onmouseover="this.style.boxShadow='0 2px 12px rgba(28,43,42,.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="width:36px;height:36px;border-radius:9px;background:{{ $m['bg'] }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
            {{ $m['icon'] }}
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.875rem;color:var(--txt)">{{ $pname }}</div>
            <div style="font-size:.72rem;color:var(--txt-lt)">{{ $tname }}
                @if($pt->familyMember) · For {{ $pt->familyMember->full_name }} @endif
            </div>
            {{-- mini progress bar --}}
            <div style="height:3px;background:var(--warm-bd);border-radius:2px;overflow:hidden;margin-top:5px">
                <div style="height:100%;background:{{ $m['color'] }};width:{{ $pct }}%"></div>
            </div>
            <div style="font-size:.68rem;color:var(--txt-lt);margin-top:2px">
                {{ $done }}/{{ $total }} · {{ $pct }}%
                @if($next) · Next: <strong>{{ $next->title }}</strong> ({{ $next->actual_date->format('d M') }}) @endif
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:4px;flex-shrink:0">
            <a href="{{ route('doctor.patients.history', $pt->patient_user_id) }}?tab=timelines"
               style="font-size:.72rem;padding:4px 10px;border:1.5px solid var(--warm-bd);border-radius:7px;color:var(--txt-md);text-decoration:none;transition:background .12s"
               onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
               View
            </a>
        </div>
    </div>
    @endforeach
    </div>
    @endif
</div>

{{-- ── My Custom Templates ─────────────────────────────────────────────────── -- --}}
<div>
    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:500;color:var(--txt);margin-bottom:14px">My Templates</h2>

    @if($myTemplates->isEmpty())
    <div style="padding:24px;text-align:center;color:var(--txt-lt);border:1.5px dashed var(--warm-bd);border-radius:12px">
        <div style="font-size:.875rem;margin-bottom:10px">Create custom timelines for your specialty</div>
        <a href="{{ route('doctor.timelines.create') }}"
           style="display:inline-flex;align-items:center;gap:5px;padding:7px 16px;background:var(--leaf);color:#fff;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none">
            + Create Template
        </a>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:8px">
    @foreach($myTemplates as $t)
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:11px;padding:12px 15px;display:flex;align-items:center;gap:10px;transition:box-shadow .12s"
         onmouseover="this.style.boxShadow='0 2px 12px rgba(28,43,42,.08)'" onmouseout="this.style.boxShadow='none'">
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.875rem;color:var(--txt)">{{ $t->title }}</div>
            <div style="font-size:.72rem;color:var(--txt-lt)">{{ $t->milestones_count }} milestones · {{ $t->patient_timelines_count }} assigned</div>
        </div>
        <a href="{{ route('doctor.timelines.show', $t) }}"
           style="font-size:.72rem;padding:4px 10px;border:1.5px solid var(--warm-bd);border-radius:7px;color:var(--txt-md);text-decoration:none">Edit</a>
        <form method="POST" action="{{ route('doctor.timelines.destroy', $t) }}" onsubmit="return confirm('Delete this template?')">
            @csrf @method('DELETE')
            <button type="submit" style="font-size:.72rem;padding:4px 10px;border:1px solid #fecaca;border-radius:7px;background:transparent;color:#dc2626;cursor:pointer;font-family:inherit">Del</button>
        </form>
    </div>
    @endforeach
    </div>
    @endif
</div>

</div>{{-- end 2-col grid --}}
</div>
@endsection
