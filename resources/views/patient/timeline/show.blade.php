@extends('layouts.patient')
@section('title', $patientTimeline->template?->title ?? 'Timeline')
@section('page-title')
    <a href="{{ route('patient.timelines.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Timelines</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $patientTimeline->template?->title }}
@endsection

@push('styles')
<style>
/* Vertical timeline line */
.tl-line { position: relative; }
.tl-line::before {
    content: ''; position: absolute; left: 19px; top: 0; bottom: 0;
    width: 2px; background: var(--warm-bd); z-index: 0;
}

/* Milestone row */
.ms-row { display: flex; gap: 16px; align-items: flex-start; position: relative; padding-bottom: 20px; }
.ms-dot  {
    width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; z-index: 1; border: 3px solid #fff;
    box-shadow: 0 0 0 2px var(--warm-bd);
}
.ms-dot.done    { box-shadow: 0 0 0 2px #6a9e8e; }
.ms-dot.today   { box-shadow: 0 0 0 3px #c0737a; animation: pulse-ring 1.5s infinite; }
.ms-dot.future  { box-shadow: 0 0 0 2px var(--warm-bd); opacity: .7; }

@keyframes pulse-ring {
    0%   { box-shadow: 0 0 0 3px #c0737a; }
    70%  { box-shadow: 0 0 0 8px rgba(192,115,122,0); }
    100% { box-shadow: 0 0 0 3px #c0737a; }
}

.ms-card {
    flex: 1; border-radius: 12px; padding: 14px 16px;
    border: 1.5px solid var(--warm-bd); background: #fff;
    transition: box-shadow .15s;
}
.ms-card.done   { background: #f9fffe; border-color: #c4e8e0; }
.ms-card.today  { background: #fff5f6; border-color: #f0b0b5; border-left: 4px solid #c0737a; }
.ms-card.future { background: #fff; }

/* Type badges */
.type-badge { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; padding: 2px 8px; border-radius: 20px; }

@keyframes fadeSlide { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
.fade-slide { animation: fadeSlide .22s ease; }
</style>
@endpush

@section('content')
@php
$spec = $patientTimeline->template?->specialty_type ?? 'other';
$specMeta = [
    'obstetrics' => ['color'=>'#c0737a','bg'=>'#fce7ef','icon'=>'🤰','label'=>'Pregnancy'],
    'pediatrics' => ['color'=>'#3d7a8a','bg'=>'#e8f5f9','icon'=>'👶','label'=>'Paediatric'],
    'ivf'        => ['color'=>'#8a6aaa','bg'=>'#f4f0fa','icon'=>'🧬','label'=>'IVF'],
    'dental'     => ['color'=>'#3d7a6e','bg'=>'#eef5f3','icon'=>'🦷','label'=>'Dental'],
    'cardiology' => ['color'=>'#c98a3a','bg'=>'#fdf5e8','icon'=>'❤️','label'=>'Cardiology'],
    'oncology'   => ['color'=>'#6b7280','bg'=>'#f3f4f6','icon'=>'🎗️','label'=>'Oncology'],
];
$meta = $specMeta[$spec] ?? ['color'=>'#4a3760','bg'=>'#f4f0fa','icon'=>'📅','label'=>ucfirst($spec)];

$typeMeta = [
    'visit'       => ['label'=>'Visit',       'color'=>'#6a9e8e', 'bg'=>'#eef5f3'],
    'scan'        => ['label'=>'Scan',        'color'=>'#3d7a8a', 'bg'=>'#e8f5f9'],
    'test'        => ['label'=>'Lab Test',    'color'=>'#c0737a', 'bg'=>'#fce7ef'],
    'vaccination' => ['label'=>'Vaccine',     'color'=>'#8a6aaa', 'bg'=>'#f4f0fa'],
    'medication'  => ['label'=>'Medication',  'color'=>'#c98a3a', 'bg'=>'#fdf5e8'],
    'procedure'   => ['label'=>'Procedure',   'color'=>'#5a6e7a', 'bg'=>'#eff5f8'],
    'info'        => ['label'=>'Info',        'color'=>'#6b7280', 'bg'=>'#f3f4f6'],
];
@endphp

<div class="fade-slide" style="display:grid;grid-template-columns:1fr 280px;gap:22px;align-items:start">

{{-- ── LEFT: Vertical timeline ─────────────────────────────────────────────── -- --}}
<div>

{{-- Header card --}}
<div style="background:linear-gradient(135deg,{{ $meta['color'] }} 0%,{{ $meta['color'] }}99 100%);border-radius:16px;padding:22px 24px;margin-bottom:22px;color:#fff;position:relative;overflow:hidden">
    <div style="position:absolute;right:-20px;top:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.06)"></div>
    <div style="position:relative;z-index:1">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.6);margin-bottom:4px">
                    {{ $meta['label'] }} · Care Plan
                </div>
                <div style="font-family:'Lora',serif;font-size:1.4rem;font-weight:500;color:#fff;line-height:1.3">
                    {{ $patientTimeline->template?->title }}
                </div>
                @if($patientTimeline->familyMember)
                <div style="font-size:.78rem;color:rgba(255,255,255,.7);margin-top:3px">
                    For {{ $patientTimeline->familyMember->full_name }}
                </div>
                @endif
            </div>
            <div style="font-size:2.5rem;opacity:.8">{{ $meta['icon'] }}</div>
        </div>

        {{-- Progress --}}
        <div style="margin-top:18px">
            <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                <span style="font-size:.72rem;color:rgba(255,255,255,.7)">
                    {{ $past->count() }} of {{ $milestones->count() }} milestones
                </span>
                <span style="font-size:.8rem;font-weight:700;color:#fff">{{ $progress }}%</span>
            </div>
            <div style="height:6px;border-radius:3px;background:rgba(255,255,255,.25);overflow:hidden">
                <div style="height:100%;border-radius:3px;background:#fff;width:{{ $progress }}%"></div>
            </div>
        </div>
    </div>
</div>

{{-- "Upcoming first" toggle --}}
<div x-data="{ showPast: false }">

{{-- Upcoming milestones --}}
@if($upcoming->isNotEmpty())
<div style="margin-bottom:6px">
    <div style="font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt);margin-bottom:14px;display:flex;align-items:center;gap:8px">
        <span>Upcoming Milestones</span>
        <span style="font-size:.72rem;padding:2px 9px;border-radius:20px;background:{{ $meta['bg'] }};color:{{ $meta['color'] }};font-weight:600">
            {{ $upcoming->count() }} remaining
        </span>
    </div>

    <div class="tl-line">
        @foreach($upcoming as $ms)
        @php
            $tm    = $typeMeta[$ms->milestone_type] ?? $typeMeta['info'];
            $state = $ms->is_today ? 'today' : 'future';
        @endphp
        <div class="ms-row">
            {{-- Dot --}}
            <div class="ms-dot {{ $state }}" style="background:{{ $ms->is_today ? $meta['color'] : 'var(--sand)' }}">
                {{ $ms->icon ?? '📋' }}
            </div>

            {{-- Card --}}
            <div class="ms-card {{ $state }}" x-data="{ open: {{ $ms->is_today ? 'true' : 'false' }} }">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;cursor:pointer" @click="open=!open">
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:3px">
                            <span style="font-size:.875rem;font-weight:600;color:var(--txt)">{{ $ms->title }}</span>
                            <span class="type-badge" style="background:{{ $tm['bg'] }};color:{{ $tm['color'] }}">{{ $tm['label'] }}</span>
                            @if($ms->is_today)
                            <span style="font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:20px;background:#fee2e2;color:#dc2626;animation:pulse 1.5s infinite">TODAY</span>
                            @endif
                        </div>
                        <div style="font-size:.75rem;color:var(--txt-lt)">
                            @if($ms->is_today)
                                <strong style="color:var(--rose)">Due today</strong>
                            @elseif($ms->days_away > 0)
                                In <strong style="color:var(--txt-md)">{{ $ms->days_away }}</strong> day{{ $ms->days_away != 1 ? 's' : '' }}
                                · {{ $ms->actual_date->format('d M Y') }}
                                @if($ms->offset_unit === 'week') · Week {{ $ms->offset_value }} @elseif($ms->offset_unit === 'month') · Month {{ $ms->offset_value }} @endif
                            @else
                                {{ $ms->actual_date->format('d M Y') }}
                            @endif
                        </div>
                    </div>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" :style="open ? 'transform:rotate(180deg)' : ''" style="flex-shrink:0;color:var(--txt-lt);margin-top:3px;transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>

                {{-- Expanded details --}}
                <div x-show="open" x-transition style="margin-top:12px;padding-top:12px;border-top:1px solid var(--warm-bd)">
                    @if($ms->description)
                    <p style="font-size:.8125rem;color:var(--txt-md);line-height:1.6;margin-bottom:10px">{{ $ms->description }}</p>
                    @endif

                    @if($ms->precautions)
                    <div style="padding:9px 12px;background:#fef9ec;border:1px solid #fde68a;border-radius:8px;margin-bottom:8px">
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#b45309;margin-bottom:4px">⚠ Precautions</div>
                        <p style="font-size:.78rem;color:#92400e;line-height:1.5">{{ $ms->precautions }}</p>
                    </div>
                    @endif

                    @if($ms->diet_advice)
                    <div style="padding:9px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;margin-bottom:8px">
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#15803d;margin-bottom:4px">🥗 Diet Advice</div>
                        <p style="font-size:.78rem;color:#166534;line-height:1.5">{{ $ms->diet_advice }}</p>
                    </div>
                    @endif

                    @if($ms->exercise_advice)
                    <div style="padding:9px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px">
                        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1d4ed8;margin-bottom:4px">🏃 Exercise</div>
                        <p style="font-size:.78rem;color:#1e40af;line-height:1.5">{{ $ms->exercise_advice }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Past milestones (collapsible) --}}
@if($past->isNotEmpty())
<div style="margin-top:8px">
    <button @click="showPast=!showPast" type="button"
            style="display:flex;align-items:center;gap:7px;font-family:'Lora',serif;font-size:.9rem;font-weight:500;color:var(--txt-md);background:transparent;border:none;cursor:pointer;padding:4px 0;margin-bottom:12px">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" :style="showPast ? 'transform:rotate(90deg)' : ''" style="transition:transform .2s;color:var(--txt-lt)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        Completed Milestones
        <span style="font-size:.72rem;padding:2px 9px;border-radius:20px;background:var(--sage-lt);color:#2a7a6a;font-weight:600">
            {{ $past->count() }} done
        </span>
    </button>

    <div x-show="showPast" x-transition class="tl-line">
        @foreach($past->sortByDesc('actual_date') as $ms)
        @php $tm = $typeMeta[$ms->milestone_type] ?? $typeMeta['info']; @endphp
        <div class="ms-row">
            <div class="ms-dot done" style="background:var(--sage-lt)">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2a7a6a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="ms-card done" x-data="{ open: false }">
                <div style="display:flex;align-items:center;gap:8px;cursor:pointer" @click="open=!open">
                    <span style="font-size:.875rem;font-weight:500;color:var(--txt-md)">{{ $ms->title }}</span>
                    <span class="type-badge" style="background:{{ $tm['bg'] }};color:{{ $tm['color'] }}">{{ $tm['label'] }}</span>
                    <span style="margin-left:auto;font-size:.72rem;color:var(--txt-lt)">{{ $ms->actual_date->format('d M Y') }}</span>
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" :style="open ? 'transform:rotate(180deg)' : ''" style="color:var(--txt-lt);transition:transform .2s"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div x-show="open" x-transition style="margin-top:10px;padding-top:10px;border-top:1px solid var(--warm-bd)">
                    @if($ms->description)
                    <p style="font-size:.8rem;color:var(--txt-md);line-height:1.5">{{ $ms->description }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>{{-- end x-data showPast --}}
</div>{{-- end LEFT --}}

{{-- ── RIGHT: Info sidebar ─────────────────────────────────────────────────── -- --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Timeline info --}}
    <div class="panel" style="padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Timeline Details</div>
        @php
        $infoRows = [
            'Specialty'    => $meta['label'],
            'Start Date'   => $patientTimeline->start_date->format('d M Y'),
            'Expected End' => $patientTimeline->expected_end_date?->format('d M Y') ?? '—',
            'Duration'     => $patientTimeline->template?->total_duration_days
                ? ceil($patientTimeline->template->total_duration_days / ($patientTimeline->template->duration_unit === 'week' ? 7 : ($patientTimeline->template->duration_unit === 'month' ? 30 : 1)))
                  .' '.Str::plural($patientTimeline->template->duration_unit, 2)
                : '—',
            'Assigned by'  => $patientTimeline->assignedByDoctor?->profile?->full_name
                ? 'Dr. '.$patientTimeline->assignedByDoctor->profile->full_name
                : '—',
            'For'          => $patientTimeline->familyMember?->full_name ?? 'Self',
        ];
        @endphp
        @foreach($infoRows as $k => $v)
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--warm-bd);font-size:.8rem">
            <span style="color:var(--txt-lt)">{{ $k }}</span>
            <span style="color:var(--txt-md);font-weight:500;text-align:right;max-width:150px">{{ $v }}</span>
        </div>
        @endforeach
    </div>

    {{-- Next milestone callout --}}
    @if($next)
    <div style="padding:14px 16px;background:{{ $meta['bg'] }};border:1.5px solid {{ $meta['color'] }}33;border-radius:12px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $meta['color'] }};margin-bottom:8px">Next Up</div>
        <div style="font-size:.95rem;font-weight:600;color:var(--txt);margin-bottom:3px">{{ $next->icon ?? '' }} {{ $next->title }}</div>
        <div style="font-size:.78rem;color:var(--txt-md)">
            @if($next->is_today)
                <strong style="color:var(--rose)">Due today!</strong>
            @else
                {{ $next->actual_date->format('d M Y') }}
                <br><span style="color:{{ $meta['color'] }};font-weight:600">In {{ max(0, $next->days_away) }} day{{ $next->days_away != 1 ? 's' : '' }}</span>
            @endif
        </div>
        @if($next->precautions)
        <div style="margin-top:9px;padding:8px 10px;background:rgba(255,255,255,.6);border-radius:8px;font-size:.72rem;color:var(--txt-md);line-height:1.5">
            ⚠ {{ Str::limit($next->precautions, 120) }}
        </div>
        @endif
    </div>
    @else
    <div style="padding:16px;background:var(--sage-lt);border:1.5px solid #b5ddd5;border-radius:12px;text-align:center">
        <div style="font-size:1.8rem;margin-bottom:6px">🎉</div>
        <div style="font-size:.875rem;font-weight:600;color:#2a7a6a">All done!</div>
        <div style="font-size:.75rem;color:#3a8a7a;margin-top:3px">All milestones completed</div>
    </div>
    @endif

    {{-- Progress ring (visual) --}}
    <div class="panel" style="padding:16px 18px;text-align:center">
        <svg width="90" height="90" viewBox="0 0 90 90" style="display:block;margin:0 auto 8px">
            <circle cx="45" cy="45" r="36" fill="none" stroke="var(--warm-bd)" stroke-width="7"/>
            <circle cx="45" cy="45" r="36" fill="none"
                    stroke="{{ $meta['color'] }}" stroke-width="7"
                    stroke-dasharray="{{ round(2 * 3.14159 * 36) }}"
                    stroke-dashoffset="{{ round(2 * 3.14159 * 36 * (1 - $progress/100)) }}"
                    stroke-linecap="round"
                    transform="rotate(-90 45 45)"/>
            <text x="45" y="50" text-anchor="middle" font-family="Lora, serif" font-size="16" font-weight="500" fill="{{ $meta['color'] }}">{{ $progress }}%</text>
        </svg>
        <div style="font-size:.78rem;font-weight:500;color:var(--txt-md)">{{ $past->count() }} / {{ $milestones->count() }} done</div>
    </div>

    <a href="{{ route('patient.timelines.index') }}"
       style="display:flex;align-items:center;justify-content:center;gap:5px;padding:9px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.8rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .15s"
       onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
        ← Back to timelines
    </a>
</div>

</div>{{-- end grid --}}
@endsection
