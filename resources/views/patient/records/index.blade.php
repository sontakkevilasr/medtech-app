@extends('layouts.patient')
@section('title', 'Medical Records')
@section('page-title', 'Medical Records')

@section('content')
@php
$vtCfg = [
    'consultation'    => ['color'=>'#3d7a6e','bg'=>'#eef5f3','label'=>'Consultation'],
    'follow_up'       => ['color'=>'#6a9e8e','bg'=>'#eef5f3','label'=>'Follow-up'],
    'emergency'       => ['color'=>'#c0737a','bg'=>'#fce7ef','label'=>'Emergency'],
    'procedure'       => ['color'=>'#4a3760','bg'=>'#f4f0fa','label'=>'Procedure'],
    'teleconsultation'=> ['color'=>'#3d5e7a','bg'=>'#e8f0f9','label'=>'Teleconsultation'],
];
@endphp

<div class="fade-slide">

{{-- Family member switcher --}}
@if($patient->familyMembers->isNotEmpty())
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:18px">
    <a href="{{ route('patient.records.index') }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ !$memberId ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ !$memberId ? 'var(--plum)' : 'transparent' }};color:{{ !$memberId ? '#fff' : 'var(--txt-md)' }}">
        {{ $patient->profile?->full_name ?? 'Me' }}
    </a>
    @foreach($patient->familyMembers as $fm)
    <a href="{{ route('patient.records.index', ['member'=>$fm->id]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $memberId==$fm->id ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $memberId==$fm->id ? 'var(--plum)' : 'transparent' }};color:{{ $memberId==$fm->id ? '#fff' : 'var(--txt-md)' }}">
        {{ $fm->full_name }}
    </a>
    @endforeach
</div>
@endif

{{-- Type filter tabs --}}
<div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:18px;align-items:center">
    @foreach([null=>'All'] + array_map(fn($v) => $v['label'], $vtCfg) as $val => $lbl)
    <a href="{{ route('patient.records.index', array_filter(['member'=>$memberId,'type'=>$val])) }}"
       style="padding:5px 12px;border-radius:7px;font-size:.78rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $type===$val ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $type===$val ? 'var(--plum)' : 'transparent' }};color:{{ $type===$val ? '#fff' : 'var(--txt-md)' }};transition:all .12s">
        {{ $lbl }}
    </a>
    @endforeach
    <span style="margin-left:auto;font-size:.75rem;color:var(--txt-lt)">{{ $records->total() }} records</span>
</div>

{{-- Records list --}}
@if($records->isEmpty())
<div class="panel" style="padding:44px 24px;text-align:center;color:var(--txt-lt)">
    <div style="font-size:2.5rem;margin-bottom:12px">🏥</div>
    <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt-md)">No medical records yet</div>
    <p style="font-size:.8rem;margin-top:4px">Your doctor will add records after each visit.</p>
</div>
@else
<div style="display:flex;flex-direction:column;gap:10px">
@foreach($records as $record)
@php $vt = $vtCfg[$record->visit_type] ?? $vtCfg['consultation']; @endphp
<a href="{{ route('patient.records.show', $record) }}" style="text-decoration:none">
<div class="panel" style="padding:16px 20px;display:flex;gap:14px;align-items:flex-start;transition:box-shadow .15s"
     onmouseover="this.style.boxShadow='0 4px 18px rgba(74,55,96,.1)'" onmouseout="this.style.boxShadow='none'">

    {{-- Date badge --}}
    <div style="width:52px;text-align:center;flex-shrink:0">
        <div style="font-family:'Lora',serif;font-size:1.5rem;font-weight:500;color:var(--txt);line-height:1">
            {{ $record->visit_date->format('d') }}
        </div>
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt)">
            {{ $record->visit_date->format('M Y') }}
        </div>
    </div>

    {{-- Separator --}}
    <div style="width:1px;background:var(--warm-bd);align-self:stretch;flex-shrink:0"></div>

    {{-- Main content --}}
    <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-bottom:5px">
            <span style="font-size:.7rem;font-weight:700;padding:2px 9px;border-radius:20px;background:{{ $vt['bg'] }};color:{{ $vt['color'] }}">
                {{ $vt['label'] }}
            </span>
            @if($record->familyMember)
            <span style="font-size:.7rem;padding:2px 9px;border-radius:20px;background:var(--parch);color:var(--txt-lt)">
                For {{ $record->familyMember->full_name }}
            </span>
            @endif
        </div>

        <div style="font-weight:600;font-size:.9rem;color:var(--txt);margin-bottom:3px">
            {{ Str::limit($record->diagnosis, 80) }}
        </div>

        @if($record->chief_complaint)
        <div style="font-size:.78rem;color:var(--txt-lt);margin-bottom:6px">
            {{ Str::limit($record->chief_complaint, 100) }}
        </div>
        @endif

        <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:.72rem;color:var(--txt-lt)">
            <span>Dr. {{ $record->doctor?->profile?->full_name }}</span>
            @if($record->doctor?->doctorProfile?->specialization)
            <span>{{ $record->doctor->doctorProfile->specialization }}</span>
            @endif
            @if($record->vitals) <span>🔬 Vitals recorded</span> @endif
            @if($record->attachments && count($record->attachments)) <span>📎 {{ count($record->attachments) }} file{{ count($record->attachments)>1?'s':'' }}</span> @endif
            @if($record->follow_up_date)
            @php $days = now()->diffInDays($record->follow_up_date, false); @endphp
            <span style="color:{{ $days < 0 ? 'var(--rose)' : ($days <= 3 ? 'var(--amber)' : 'inherit') }}">
                📅 Follow-up {{ $record->follow_up_date->format('d M Y') }}
            </span>
            @endif
        </div>
    </div>

    {{-- Arrow --}}
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
         style="flex-shrink:0;color:var(--txt-lt);margin-top:4px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
</div>
</a>
@endforeach
</div>

{{-- Pagination --}}
@if($records->hasPages())
<div style="display:flex;justify-content:center;gap:6px;margin-top:18px">
    @if(!$records->onFirstPage())
    <a href="{{ $records->previousPageUrl() }}" style="padding:7px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;text-decoration:none;color:var(--txt-md);transition:background .12s" onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">← Prev</a>
    @endif
    <span style="padding:7px 14px;font-size:.78rem;color:var(--txt-lt)">{{ $records->currentPage() }} / {{ $records->lastPage() }}</span>
    @if($records->hasMorePages())
    <a href="{{ $records->nextPageUrl() }}" style="padding:7px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;text-decoration:none;color:var(--txt-md);transition:background .12s" onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">Next →</a>
    @endif
</div>
@endif
@endif

</div>
@endsection
