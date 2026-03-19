@extends('layouts.doctor')
@section('title', $template->title)
@section('page-title')
    <a href="{{ route('doctor.timelines.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Timelines</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $template->title }}
@endsection

@section('content')
@php
$typeMeta = [
    'visit'       => ['label'=>'Visit',      'color'=>'#6a9e8e','bg'=>'#eef5f3'],
    'scan'        => ['label'=>'Scan',       'color'=>'#3d7a8a','bg'=>'#e8f5f9'],
    'test'        => ['label'=>'Lab Test',   'color'=>'#c0737a','bg'=>'#fce7ef'],
    'vaccination' => ['label'=>'Vaccine',    'color'=>'#8a6aaa','bg'=>'#f4f0fa'],
    'medication'  => ['label'=>'Medication', 'color'=>'#c98a3a','bg'=>'#fdf5e8'],
    'procedure'   => ['label'=>'Procedure',  'color'=>'#5a6e7a','bg'=>'#eff5f8'],
    'info'        => ['label'=>'Info',       'color'=>'#6b7280','bg'=>'#f3f4f6'],
];
@endphp

<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ── Milestones list ──────────────────────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:16px">
    {{-- Template header --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:18px 22px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div>
                <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--txt-lt);margin-bottom:4px">
                    {{ ucfirst($template->specialty_type) }} · {{ $template->is_system_template ? 'System Template' : 'My Template' }}
                </div>
                <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:500;color:var(--txt)">{{ $template->title }}</h1>
                @if($template->description)
                <p style="font-size:.8125rem;color:var(--txt-lt);margin-top:4px;line-height:1.5">{{ $template->description }}</p>
                @endif
                <div style="display:flex;gap:14px;margin-top:8px;font-size:.75rem;color:var(--txt-lt)">
                    <span>📋 {{ $template->milestones->count() }} milestones</span>
                    <span>⏱ {{ $template->total_duration_days }} {{ $template->duration_unit }}s total</span>
                    <span>👥 {{ $assignments->count() }} active assignments</span>
                </div>
            </div>
            @if(!$template->is_system_template)
            <a href="{{ route('doctor.timelines.edit', $template) }}"
               style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.78rem;font-weight:500;color:var(--txt-md);text-decoration:none;white-space:nowrap">
                Edit Template
            </a>
            @endif
        </div>
    </div>

    {{-- Milestones table --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;overflow:hidden">
        <div style="padding:12px 18px;border-bottom:1.5px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
            <div style="font-family:'Cormorant Garamond',serif;font-size:1rem;color:var(--txt)">Milestones</div>
            @if(!$template->is_system_template)
            <button onclick="document.getElementById('add-milestone-form').classList.toggle('hidden')"
                    style="display:flex;align-items:center;gap:5px;padding:5px 12px;background:var(--leaf);color:#fff;border:none;border-radius:8px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit">
                + Add
            </button>
            @endif
        </div>

        @if(!$template->is_system_template)
        {{-- Add milestone form (hidden by default) --}}
        <div id="add-milestone-form" class="hidden" style="padding:14px 18px;border-bottom:1.5px solid var(--warm-bd);background:var(--parch)">
            <form method="POST" action="{{ route('doctor.timelines.milestones.store', $template) }}">
                @csrf
                <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1.5fr;gap:8px;align-items:end">
                    <div>
                        <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">Title *</label>
                        <input type="text" name="title" class="dr-inp" placeholder="e.g. First consultation" required>
                    </div>
                    <div>
                        <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">Offset *</label>
                        <input type="number" name="offset_value" class="dr-inp" min="0" placeholder="7" required>
                    </div>
                    <div>
                        <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">Unit *</label>
                        <select name="offset_unit" class="dr-inp">
                            <option value="day">Day</option>
                            <option value="week">Week</option>
                            <option value="month">Month</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">Type *</label>
                        <select name="milestone_type" class="dr-inp">
                            @foreach(['visit','scan','test','vaccination','medication','procedure','info'] as $tp)
                            <option value="{{ $tp }}">{{ ucfirst($tp) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px">
                    <textarea name="description" class="dr-inp" rows="2" placeholder="Description..." style="resize:none"></textarea>
                    <textarea name="precautions" class="dr-inp" rows="2" placeholder="Precautions..." style="resize:none"></textarea>
                </div>
                <div style="display:flex;gap:6px;margin-top:8px">
                    <input type="text" name="icon" class="dr-inp" placeholder="Icon emoji" style="width:80px">
                    <button type="submit" style="padding:6px 16px;background:var(--leaf);color:#fff;border:none;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:inherit">
                        Add Milestone
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Milestone rows --}}
        <table style="width:100%;border-collapse:collapse">
            <thead><tr style="border-bottom:1.5px solid var(--warm-bd)">
                <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">When</th>
                <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Milestone</th>
                <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Type</th>
                <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt)">Notes</th>
                @if(!$template->is_system_template)<th></th>@endif
            </tr></thead>
            <tbody>
            @foreach($template->milestones as $ms)
            @php $tm = $typeMeta[$ms->milestone_type] ?? $typeMeta['info']; @endphp
            <tr style="border-bottom:1px solid var(--warm-bd);transition:background .1s" onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                <td style="padding:10px 16px;font-size:.8rem;color:var(--txt-lt);white-space:nowrap;font-weight:500">
                    {{ $ms->offset_value }} {{ $ms->offset_unit }}{{ $ms->offset_value != 1 ? 's' : '' }}
                </td>
                <td style="padding:10px 16px">
                    <div style="display:flex;align-items:center;gap:7px">
                        <span>{{ $ms->icon }}</span>
                        <div>
                            <div style="font-size:.875rem;font-weight:500;color:var(--txt)">{{ $ms->title }}</div>
                            @if($ms->description)
                            <div style="font-size:.72rem;color:var(--txt-lt);margin-top:1px">{{ Str::limit($ms->description, 80) }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="padding:10px 16px">
                    <span style="font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:20px;background:{{ $tm['bg'] }};color:{{ $tm['color'] }}">{{ $tm['label'] }}</span>
                </td>
                <td style="padding:10px 16px;font-size:.75rem;color:var(--txt-lt);max-width:200px">
                    {{ $ms->precautions ? Str::limit($ms->precautions, 70) : '—' }}
                </td>
                @if(!$template->is_system_template)
                <td style="padding:10px 16px;text-align:right">
                    <form method="POST" action="{{ route('doctor.timelines.milestones.destroy', [$template, $ms]) }}"
                          onsubmit="return confirm('Delete this milestone?')" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:22px;height:22px;border:1px solid #fecaca;border-radius:5px;background:transparent;color:#dc2626;cursor:pointer;font-size:.72rem">×</button>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── RIGHT sidebar ───────────────────────────────────────────────────────── -- --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Active assignments --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Active Assignments ({{ $assignments->count() }})</div>
        @if($assignments->isEmpty())
        <div style="font-size:.8rem;color:var(--txt-lt);text-align:center;padding:10px">No patients assigned yet</div>
        @else
        @foreach($assignments as $pt)
        <div style="display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid var(--warm-bd)">
            <div style="width:28px;height:28px;border-radius:7px;background:var(--leaf);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ strtoupper(substr($pt->patient?->profile?->full_name ?? 'P', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.8rem;font-weight:500;color:var(--txt)">{{ $pt->patient?->profile?->full_name }}</div>
                <div style="font-size:.68rem;color:var(--txt-lt)">Since {{ $pt->start_date->format('d M Y') }}</div>
            </div>
            <a href="{{ route('doctor.patients.history', $pt->patient_user_id) }}"
               style="font-size:.68rem;padding:3px 8px;border:1px solid var(--warm-bd);border-radius:6px;color:var(--txt-md);text-decoration:none">View</a>
        </div>
        @endforeach
        @endif
    </div>

    {{-- Assign CTA --}}
    <a href="{{ route('doctor.patients.index') }}"
       style="display:flex;align-items:center;justify-content:center;gap:7px;padding:10px;background:var(--leaf);color:#fff;border-radius:11px;font-size:.875rem;font-weight:600;text-decoration:none;transition:opacity .15s"
       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        Assign to a Patient →
    </a>
</div>

</div>
@endsection

@push('styles')
<style>
.hidden { display: none !important; }
.dr-inp { width:100%;padding:.5rem .7rem;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;color:var(--txt);background:#fff;outline:none;font-family:inherit; }
.dr-inp:focus { border-color:var(--leaf); }
</style>
@endpush
