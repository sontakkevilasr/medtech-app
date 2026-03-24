@extends('layouts.patient')
@section('title', 'Medical Record')
@section('page-title')
    <a href="{{ route('patient.records.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Medical Records</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ $record->visit_date->format('d M Y') }}
@endsection

@section('content')
@php
$vtCfg = [
    'consultation'    => ['color'=>'#3d7a6e','bg'=>'#eef5f3','label'=>'Consultation'],
    'follow_up'       => ['color'=>'#6a9e8e','bg'=>'#eef5f3','label'=>'Follow-up'],
    'emergency'       => ['color'=>'#c0737a','bg'=>'#fce7ef','label'=>'Emergency'],
    'procedure'       => ['color'=>'#4a3760','bg'=>'#f4f0fa','label'=>'Procedure'],
    'teleconsultation'=> ['color'=>'#3d5e7a','bg'=>'#e8f0f9','label'=>'Teleconsultation'],
];
$vt = $vtCfg[$record->visit_type] ?? $vtCfg['consultation'];
@endphp

<div class="fade-slide" style="display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start">

{{-- ── LEFT ─────────────────────────────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Header ──────────────────────────────────────────────────────────────────}}
    <div class="panel" style="padding:20px 24px">
        <div style="display:flex;align-items:flex-start;gap:12px">
            <div style="flex:1;min-width:0">
                <div style="display:flex;gap:7px;flex-wrap:wrap;margin-bottom:5px">
                    <span style="font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;background:{{ $vt['bg'] }};color:{{ $vt['color'] }}">
                        {{ $vt['label'] }}
                    </span>
                    @if($record->familyMember)
                    <span style="font-size:.72rem;padding:3px 10px;border-radius:20px;background:var(--parch);color:var(--txt-lt)">
                        For {{ $record->familyMember->full_name }}
                    </span>
                    @endif
                </div>
                <div style="font-family:'Lora',serif;font-size:1.3rem;font-weight:500;color:var(--txt)">
                    {{ $record->visit_date->format('d F Y') }}
                </div>
                <div style="font-size:.78rem;color:var(--txt-lt);margin-top:4px">
                    Dr. {{ $record->doctor?->profile?->full_name }}
                    @if($record->doctor?->doctorProfile?->clinic_name)
                    · {{ $record->doctor->doctorProfile->clinic_name }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Vitals ───────────────────────────────────────────────────────────────}}
        @if($record->vitals && count($record->vitals))
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:14px;border-top:1px solid var(--warm-bd)">
            @php
            $vitalIcons = ['height'=>'📏','weight'=>'⚖️','bp'=>'❤️','pulse'=>'💓','temperature'=>'🌡️','spo2'=>'💨'];
            $vitalUnits = ['height'=>'','weight'=>'kg','bp'=>'mmHg','pulse'=>'bpm','temperature'=>'°C','spo2'=>'%'];
            @endphp
            @foreach($record->vitals as $key => $val)
            @if($val)
            <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:9px;background:var(--parch);border:1px solid var(--warm-bd)">
                <span>{{ $vitalIcons[$key] ?? '📋' }}</span>
                <div>
                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">{{ ucfirst($key) }}</div>
                    <div style="font-size:.875rem;font-weight:600;color:var(--txt)">{{ $val }}{{ $vitalUnits[$key] ? ' '.$vitalUnits[$key] : '' }}</div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- Complaint & Diagnosis ─────────────────────────────────────────────────}}
    <div class="panel" style="padding:20px 24px">
        @if($record->chief_complaint)
        <div style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:6px">What you reported</div>
            <p style="font-size:.9rem;color:var(--txt-md);line-height:1.65">{{ $record->chief_complaint }}</p>
        </div>
        @endif
        <div>
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:6px">Diagnosis</div>
            <p style="font-size:.9rem;color:var(--txt);line-height:1.65;font-weight:500">{{ $record->diagnosis ?? '—' }}</p>
        </div>
    </div>

    {{-- Treatment Plan ─────────────────────────────────────────────────────────}}
    @if($record->treatment_plan)
    <div style="background:#eef5f3;border:1.5px solid #b5ddd5;border-radius:14px;padding:18px 22px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#2a7a6a;margin-bottom:8px">Treatment Plan</div>
        <p style="font-size:.875rem;color:#1a5a4a;line-height:1.7;white-space:pre-line">{{ $record->treatment_plan }}</p>
    </div>
    @endif

    {{-- Examination Notes (shown to patient) ─────────────────────────────────}}
    @if($record->examination_notes)
    <div class="panel" style="padding:18px 22px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:8px">Examination Notes</div>
        <p style="font-size:.875rem;color:var(--txt-md);line-height:1.7;white-space:pre-line">{{ $record->examination_notes }}</p>
    </div>
    @endif

    {{-- Linked Prescription ────────────────────────────────────────────────────}}
    @if($record->prescription)
    <div class="panel" style="padding:16px 20px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt)">Prescription from this visit</div>
        </div>
        <div style="font-weight:600;color:var(--txt);font-size:.875rem;margin-bottom:8px">{{ $record->prescription->rx_number }}</div>
        <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:10px">
            @foreach($record->prescription->medicines as $med)
            <span style="font-size:.72rem;padding:3px 9px;border-radius:20px;background:var(--parch);color:var(--txt-md);border:1px solid var(--warm-bd)">
                {{ $med->medicine_name }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Attachments ───────────────────────────────────────────────────────────}}
    @if($record->attachments && count($record->attachments))
    <div class="panel" style="padding:16px 20px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:12px">
            Documents & Reports ({{ count($record->attachments) }})
        </div>
        <div style="display:flex;flex-direction:column;gap:7px">
            @foreach($record->attachments as $att)
            @php
            $isImage = str_contains($att['type'] ?? '', 'image');
            $isPdf   = str_contains($att['type'] ?? '', 'pdf');
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:10px 13px;background:var(--parch);border-radius:10px;border:1px solid var(--warm-bd)">
                <span style="font-size:1.3rem">{{ $isPdf ? '📄' : ($isImage ? '🖼️' : '📎') }}</span>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.8125rem;font-weight:500;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $att['name'] }}</div>
                    @if(isset($att['size']))<div style="font-size:.7rem;color:var(--txt-lt)">{{ round($att['size']/1024) }} KB</div>@endif
                </div>
                <a href="{{ route('attachments.medical-record', str_replace('medical-records/', '', $att['path'])) }}" target="_blank" download="{{ $att['name'] }}"
                   style="padding:6px 14px;background:var(--plum);color:#fff;border-radius:8px;font-size:.75rem;font-weight:600;text-decoration:none;white-space:nowrap">
                    Download
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ── RIGHT sidebar ───────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:13px">

    <div class="panel" style="padding:15px 17px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Visit Details</div>
        @foreach(['Date' => $record->visit_date->format('d M Y'), 'Doctor' => 'Dr. '.($record->doctor?->profile?->full_name ?? ''), 'Specialty' => $record->doctor?->doctorProfile?->specialization ?? '—', 'Created' => $record->created_at->diffForHumans()] as $k => $v)
        @if($v && $v !== '—')
        <div style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--warm-bd);font-size:.78rem">
            <span style="color:var(--txt-lt)">{{ $k }}</span>
            <span style="color:var(--txt-md);font-weight:500;text-align:right;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $v }}</span>
        </div>
        @endif
        @endforeach
    </div>

    @if($record->follow_up_date)
    @php $days = now()->diffInDays($record->follow_up_date, false); @endphp
    <div style="padding:13px 15px;border-radius:12px;background:{{ $days<0?'#fce7ef':($days<=3?'#fef9ec':'#eef5f3') }};border:1.5px solid {{ $days<0?'#f0b0b5':($days<=3?'#fde68a':'#b5ddd5') }}">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $days<0?'#c0737a':($days<=3?'#b45309':'#2a7a6a') }};margin-bottom:4px">Follow-up</div>
        <div style="font-size:.9rem;font-weight:600;color:var(--txt)">{{ $record->follow_up_date->format('d M Y') }}</div>
        <div style="font-size:.75rem;color:var(--txt-md);margin-top:2px">
            @if($days < 0) {{ abs($days) }} days overdue
            @elseif($days === 0) Today
            @else In {{ $days }} day{{ $days!=1?'s':'' }}
            @endif
        </div>
    </div>
    @endif

    <a href="{{ route('patient.records.index') }}"
       style="display:flex;align-items:center;justify-content:center;padding:9px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.8rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
       onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
        ← All Records
    </a>
</div>

</div>
@endsection
