@extends('layouts.doctor')
@section('title', 'Assign Timeline')
@section('page-title')
    <a href="{{ route('doctor.timelines.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Timelines</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Assign to {{ $patient->profile?->full_name ?? 'Patient' }}
@endsection

@section('content')
@php
$specMeta = [
    'obstetrics' => ['icon'=>'🤰','color'=>'#c0737a','label'=>'Pregnancy'],
    'pediatrics' => ['icon'=>'👶','color'=>'#3d7a8a','label'=>'Paediatric'],
    'ivf'        => ['icon'=>'🧬','color'=>'#8a6aaa','label'=>'IVF'],
    'dental'     => ['icon'=>'🦷','color'=>'#3d7a6e','label'=>'Dental'],
    'cardiology' => ['icon'=>'❤️','color'=>'#c98a3a','label'=>'Cardiology'],
    'oncology'   => ['icon'=>'🎗️','color'=>'#6b7280','label'=>'Oncology'],
];
@endphp

<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:22px;align-items:start">

{{-- ── Template picker ─────────────────────────────────────────────────────── -- --}}
<div>
    <div style="font-family:'Cormorant Garamond',serif;font-size:1.05rem;color:var(--txt);margin-bottom:16px">Select a Template</div>

    <form method="POST" action="{{ route('doctor.timelines.assign.save', $patient->id) }}"
          x-data="{ selected: null }">
        @csrf

        {{-- Already assigned notice --}}
        @if($existing->isNotEmpty())
        <div style="padding:12px 15px;background:#fef9ec;border:1px solid #fde68a;border-radius:10px;margin-bottom:16px;font-size:.8rem;color:#92400e">
            ⚠ This patient already has {{ $existing->count() }} active timeline{{ $existing->count()>1?'s':'' }}:
            @foreach($existing as $e)
            <strong>{{ $e->template?->title }}</strong>{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </div>
        @endif

        {{-- Template groups by specialty --}}
        @foreach($templates as $spec => $group)
        @php $m = $specMeta[$spec] ?? ['icon'=>'📅','color'=>'#4a3760','label'=>ucfirst($spec)]; @endphp
        <div style="margin-bottom:20px">
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--txt-lt);margin-bottom:8px;display:flex;align-items:center;gap:6px">
                <span>{{ $m['icon'] }}</span> {{ $m['label'] }}
            </div>
            <div style="display:flex;flex-direction:column;gap:6px">
                @foreach($group as $t)
                <label style="cursor:pointer;display:block">
                    <input type="radio" name="template_id" value="{{ $t->id }}"
                           x-model="selected" style="display:none">
                    <div :class="selected === '{{ $t->id }}' ? 'selected-tpl' : ''"
                         style="padding:13px 16px;border:1.5px solid var(--warm-bd);border-radius:11px;background:#fff;transition:all .15s;display:flex;align-items:flex-start;gap:12px"
                         :style="selected == '{{ $t->id }}' ? 'border-color:{{ $m['color'] }};background:{{ $m['color'] }}08;' : ''"
                         onclick="">
                        <div style="width:36px;height:36px;border-radius:9px;background:{{ $m['color'] }}18;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                            {{ $m['icon'] }}
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:.9rem;color:var(--txt);margin-bottom:2px">{{ $t->title }}</div>
                            <div style="font-size:.75rem;color:var(--txt-lt);line-height:1.4">{{ Str::limit($t->description, 100) }}</div>
                            <div style="display:flex;gap:10px;margin-top:5px;font-size:.7rem;color:var(--txt-lt)">
                                <span>📋 {{ $t->milestones_count }} milestones</span>
                                <span>⏱ {{ $t->total_duration_days }} days</span>
                                @if($t->is_system_template)<span>✓ System</span>@endif
                            </div>
                        </div>
                        <div :style="selected == '{{ $t->id }}' ? 'opacity:1' : 'opacity:0'"
                             style="width:18px;height:18px;border-radius:50%;background:{{ $m['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;transition:opacity .15s">
                            <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Start date + family member + notes --}}
        <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:18px 20px;margin-top:6px">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt);margin-bottom:14px">Assignment Details</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Start Date *</label>
                    <input type="date" name="start_date" value="{{ today()->format('Y-m-d') }}"
                           style="width:100%;padding:.55rem .8rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit"
                           required>
                </div>
                @if($patient->familyMembers->isNotEmpty())
                <div>
                    <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">For</label>
                    <select name="family_member_id"
                            style="width:100%;padding:.55rem .8rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit">
                        <option value="">{{ $patient->profile?->full_name ?? 'Patient' }} (self)</option>
                        @foreach($patient->familyMembers as $fm)
                        <option value="{{ $fm->id }}">{{ $fm->full_name }} ({{ $fm->relation }})</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Custom Notes (optional)</label>
                <textarea name="custom_notes" rows="2"
                          placeholder="Any specific notes for this patient's timeline..."
                          style="width:100%;padding:.55rem .8rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;color:var(--txt);background:#fff;outline:none;font-family:inherit;resize:none"></textarea>
            </div>
        </div>

        <button type="submit"
                :disabled="!selected"
                style="width:100%;margin-top:14px;padding:.8rem;background:var(--leaf);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .15s"
                :style="!selected ? 'opacity:.4;cursor:not-allowed' : 'opacity:1'"
                onmouseover="if(this.disabled!==true) this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            Assign Timeline to Patient
        </button>
    </form>
</div>

{{-- ── Patient info sidebar ─────────────────────────────────────────────────── -- --}}
<div style="position:sticky;top:78px">
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:18px 20px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">Assigning to</div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--warm-bd)">
            <div style="width:42px;height:42px;border-radius:11px;background:var(--leaf);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($patient->profile?->full_name ?? 'P', 0, 1)) }}
            </div>
            <div>
                <div style="font-weight:600;font-size:.9375rem;color:var(--txt)">{{ $patient->profile?->full_name }}</div>
                <div style="font-size:.75rem;color:var(--txt-lt)">
                    @if($patient->profile?->date_of_birth) Age {{ $patient->profile->date_of_birth->age }} · @endif
                    {{ ucfirst($patient->profile?->gender ?? '') }}
                </div>
            </div>
        </div>

        @if($patient->familyMembers->isNotEmpty())
        <div style="margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:6px">Family Members</div>
            @foreach($patient->familyMembers as $fm)
            <div style="display:flex;justify-content:space-between;font-size:.78rem;padding:3px 0">
                <span style="color:var(--txt)">{{ $fm->full_name }}</span>
                <span style="color:var(--txt-lt)">{{ $fm->relation }}</span>
            </div>
            @endforeach
        </div>
        @endif

        <div style="font-size:.72rem;color:var(--txt-lt);line-height:1.6;padding:10px 12px;background:var(--parch);border-radius:8px">
            💡 The patient will see this timeline in their portal with all milestones, dates, and reminders automatically calculated from the start date.
        </div>
    </div>
</div>

</div>
@endsection
