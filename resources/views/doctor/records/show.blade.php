@extends('layouts.doctor')
@section('title', 'Medical Record')
@section('page-title')
    <a href="{{ route('doctor.patients.history', $record->patient_user_id) }}"
       style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">
        {{ $record->patient?->profile?->full_name ?? 'Patient' }}
    </a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Record · {{ $record->visit_date->format('d M Y') }}
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
$isMyRecord = $record->doctor_user_id === auth()->id();
@endphp

<div class="fade-in" style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ── LEFT ─────────────────────────────────────────────────────────────────── --}}
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- Header ──────────────────────────────────────────────────────────────────}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 24px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;background:{{ $vt['bg'] }};color:{{ $vt['color'] }}">
                        {{ $vt['label'] }}
                    </span>
                    @if($record->familyMember)
                    <span style="font-size:.72rem;padding:3px 10px;border-radius:20px;background:var(--parch);color:var(--txt-lt)">
                        For {{ $record->familyMember->full_name }}
                    </span>
                    @endif
                </div>
                <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:500;color:var(--txt);margin-bottom:3px">
                    {{ $record->visit_date->format('d F Y') }}
                </h1>
                <div style="font-size:.78rem;color:var(--txt-lt)">
                    Dr. {{ $record->doctor?->profile?->full_name }}
                    @if($record->doctor?->doctorProfile?->specialization)
                     · {{ $record->doctor->doctorProfile->specialization }}
                    @endif
                </div>
            </div>
            @if($isMyRecord)
            <div style="display:flex;gap:6px">
                <a href="{{ route('doctor.records.edit', $record) }}"
                   style="padding:7px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.78rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
                   onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">Edit</a>
                <form method="POST" action="{{ route('doctor.records.destroy', $record) }}"
                      onsubmit="return confirm('Permanently delete this record?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            style="padding:7px 14px;border:1px solid #fecaca;border-radius:8px;font-size:.78rem;color:#dc2626;background:transparent;cursor:pointer;font-family:'Outfit',sans-serif;transition:background .12s"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">Delete</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Vitals chips ─────────────────────────────────────────────────────────}}
        @if($record->vitals && count($record->vitals))
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--warm-bd)">
            @php
            $vitalIcons = ['height'=>'📏','weight'=>'⚖️','bp'=>'❤️','pulse'=>'💓','temperature'=>'🌡️','spo2'=>'💨'];
            $vitalUnits = ['height'=>'','weight'=>'kg','bp'=>'mmHg','pulse'=>'bpm','temperature'=>'°C','spo2'=>'%'];
            @endphp
            @foreach($record->vitals as $key => $val)
            @if($val)
            <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:9px;background:var(--parch);border:1px solid var(--warm-bd)">
                <span style="font-size:1rem">{{ $vitalIcons[$key] ?? '📋' }}</span>
                <div>
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--txt-lt)">{{ ucfirst($key) }}</div>
                    <div style="font-size:.875rem;font-weight:600;color:var(--txt)">{{ $val }} {{ $vitalUnits[$key] ?? '' }}</div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>

    {{-- Chief Complaint & Diagnosis ──────────────────────────────────────────────}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 24px">
        <div style="margin-bottom:16px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:6px">Chief Complaint</div>
            <p style="font-size:.9rem;color:var(--txt);line-height:1.65">{{ $record->chief_complaint ?? '—' }}</p>
        </div>
        <div style="padding-top:14px;border-top:1px solid var(--warm-bd)">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:6px">Diagnosis</div>
            <p style="font-size:.9rem;color:var(--txt);line-height:1.65">{{ $record->diagnosis ?? '—' }}</p>
        </div>
    </div>

    {{-- Examination Notes ──────────────────────────────────────────────────────}}
    @if($record->examination_notes)
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:20px 24px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:10px">Examination Notes</div>
        <p style="font-size:.875rem;color:var(--txt-md);line-height:1.7;white-space:pre-line">{{ $record->examination_notes }}</p>
    </div>
    @endif

    {{-- Treatment Plan ─────────────────────────────────────────────────────────}}
    @if($record->treatment_plan)
    <div style="background:#eef5f3;border:1.5px solid #b5ddd5;border-radius:14px;padding:20px 24px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#2a7a6a;margin-bottom:10px">Treatment Plan</div>
        <p style="font-size:.875rem;color:#1a5a4a;line-height:1.7;white-space:pre-line">{{ $record->treatment_plan }}</p>
    </div>
    @endif

    {{-- Doctor's Private Notes ────────────────────────────────────────────────}}
    @if($record->doctor_notes && $isMyRecord)
    <div style="background:#fef9ec;border:1.5px solid #fde68a;border-radius:14px;padding:20px 24px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#b45309;margin-bottom:6px;display:flex;align-items:center;gap:6px">
            🔒 Private Notes
            <span style="font-weight:400;text-transform:none;font-size:.65rem;padding:1px 7px;border-radius:20px;background:#fde68a;color:#b45309">Only visible to you</span>
        </div>
        <p style="font-size:.875rem;color:#92400e;line-height:1.65;white-space:pre-line">{{ $record->doctor_notes }}</p>
    </div>
    @endif

    {{-- Linked Prescription ────────────────────────────────────────────────────}}
    @if($record->prescription)
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:18px 22px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt)">Linked Prescription</div>
            <a href="{{ route('doctor.prescriptions.show', $record->prescription) }}"
               style="font-size:.75rem;color:var(--leaf);text-decoration:none">View full →</a>
        </div>
        <div style="font-weight:600;color:var(--txt);font-size:.875rem;margin-bottom:6px">{{ $record->prescription->rx_number }}</div>
        <div style="display:flex;flex-wrap:wrap;gap:5px">
            @foreach($record->prescription->medicines->take(5) as $med)
            <span style="font-size:.72rem;padding:3px 9px;border-radius:20px;background:var(--parch);color:var(--txt-md);border:1px solid var(--warm-bd)">
                {{ $med->medicine_name }}
            </span>
            @endforeach
            @if($record->prescription->medicines->count() > 5)
            <span style="font-size:.72rem;color:var(--txt-lt)">+{{ $record->prescription->medicines->count()-5 }} more</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Attachments ───────────────────────────────────────────────────────────}}
    @if($record->attachments && count($record->attachments))
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:18px 22px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--txt-lt);margin-bottom:12px">
            Attachments ({{ count($record->attachments) }})
        </div>
        <div style="display:flex;flex-direction:column;gap:7px">
            @foreach($record->attachments as $i => $att)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 13px;background:var(--parch);border-radius:10px;border:1px solid var(--warm-bd)">
                @php
                $isImage = str_contains($att['type'] ?? '', 'image');
                $isPdf   = str_contains($att['type'] ?? '', 'pdf');
                @endphp
                <span style="font-size:1.3rem">{{ $isPdf ? '📄' : ($isImage ? '🖼️' : '📎') }}</span>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.8125rem;font-weight:500;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $att['name'] }}</div>
                    <div style="font-size:.7rem;color:var(--txt-lt)">
                        {{ isset($att['size']) ? round($att['size']/1024).'KB' : '' }}
                        @if(isset($att['uploaded_at'])) · {{ \Carbon\Carbon::parse($att['uploaded_at'])->format('d M Y') }} @endif
                    </div>
                </div>
                <a href="{{ asset('storage/' . $att['path']) }}" target="_blank"
                   style="padding:5px 12px;background:var(--leaf);color:#fff;border-radius:8px;font-size:.75rem;font-weight:600;text-decoration:none;white-space:nowrap">
                    {{ $isImage ? 'View' : 'Download' }}
                </a>
                @if($isMyRecord)
                <form method="POST" action="{{ route('doctor.records.attachment', $record) }}?_method=DELETE"
                      onsubmit="return confirm('Remove this attachment?')">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="index" value="{{ $i }}">
                    <button type="submit"
                            style="width:26px;height:26px;border:1px solid #fecaca;border-radius:7px;background:transparent;color:#dc2626;cursor:pointer;font-size:.8rem">×</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- ── RIGHT sidebar ───────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    {{-- Key dates --}}
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Record Info</div>
        @foreach(['Visit Date'=>$record->visit_date->format('d M Y'), 'Follow-up'=>$record->follow_up_date?->format('d M Y') ?? 'Not scheduled', 'Created'=>$record->created_at->format('d M Y, h:i A'), 'Last Updated'=>$record->updated_at->diffForHumans()] as $k => $v)
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--warm-bd);font-size:.8rem">
            <span style="color:var(--txt-lt)">{{ $k }}</span>
            <span style="color:var(--txt-md);font-weight:500">{{ $v }}</span>
        </div>
        @endforeach
    </div>

    {{-- Follow-up callout --}}
    @if($record->follow_up_date)
    @php $daysToFollowup = now()->diffInDays($record->follow_up_date, false); @endphp
    <div style="padding:13px 15px;border-radius:12px;background:{{ $daysToFollowup < 0 ? '#fce7ef' : ($daysToFollowup <= 3 ? '#fef9ec' : '#eef5f3') }};border:1.5px solid {{ $daysToFollowup < 0 ? '#f0b0b5' : ($daysToFollowup <= 3 ? '#fde68a' : '#b5ddd5') }}">
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $daysToFollowup < 0 ? '#c0737a' : ($daysToFollowup <= 3 ? '#b45309' : '#2a7a6a') }};margin-bottom:4px">
            Follow-up
        </div>
        <div style="font-size:.9rem;font-weight:600;color:var(--txt)">{{ $record->follow_up_date->format('d M Y') }}</div>
        <div style="font-size:.75rem;color:var(--txt-md);margin-top:2px">
            @if($daysToFollowup < 0)
                {{ abs($daysToFollowup) }} days overdue
            @elseif($daysToFollowup === 0)
                Today
            @else
                In {{ $daysToFollowup }} day{{ $daysToFollowup != 1 ? 's' : '' }}
            @endif
        </div>
    </div>
    @endif

    {{-- Upload more files --}}
    @if($isMyRecord)
    <div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:13px;padding:14px 16px">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Upload Attachment</div>
        <form method="POST" action="{{ route('doctor.records.attachment', $record) }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"
                   style="width:100%;font-size:.78rem;color:var(--txt-md);margin-bottom:8px"
                   required>
            <button type="submit"
                    style="width:100%;padding:7px;background:var(--leaf);color:#fff;border:none;border-radius:9px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .12s"
                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                Upload File
            </button>
        </form>
    </div>
    @endif

    {{-- Quick nav --}}
    <div style="display:flex;flex-direction:column;gap:6px">
        @if($isMyRecord)
        <a href="{{ route('doctor.prescriptions.create', ['patient'=>$record->patient_user_id]) }}"
           style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--leaf);color:#fff;border-radius:10px;font-size:.8rem;font-weight:600;text-decoration:none;transition:opacity .12s"
           onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <span>Write Prescription</span>
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endif
        <a href="{{ route('doctor.patients.history', $record->patient_user_id) }}"
           style="display:flex;align-items:center;justify-content:center;gap:5px;padding:9px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.8rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            ← Patient History
        </a>
    </div>
</div>

</div>
@endsection
