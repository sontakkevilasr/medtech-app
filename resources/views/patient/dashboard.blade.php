@extends('layouts.patient')
@section('title', 'My Dashboard')
@section('page-title', 'Hello, ' . (auth()->user()->profile ? explode(' ', auth()->user()->profile->full_name)[0] : 'there') . ' 👋')

@section('content')
@php
    use App\Enums\HealthLogType;

    // Helper: get ring stroke for a value between 0–100
    $ringStroke = fn(int $pct) => round(113 - (113 * $pct / 100), 1); // circ = 2π×18 ≈ 113

    // BP status helper
    $bpStatus = function($systolic, $diastolic) {
        if ($systolic > 140 || $diastolic > 90) return ['hi', 'High'];
        if ($systolic > 120 || $diastolic > 80) return ['warn', 'Elevated'];
        if ($systolic < 90 || $diastolic < 60)  return ['warn', 'Low'];
        return ['ok', 'Normal'];
    };

    // Sugar status helper
    $sugarStatus = fn($v, $type) => match(true) {
        $type === 'fasting' && $v > 125 => ['hi', 'High'],
        $type === 'fasting' && $v > 100 => ['warn', 'Elevated'],
        $type === 'pp'      && $v > 199 => ['hi', 'High'],
        $type === 'pp'      && $v > 140 => ['warn', 'Elevated'],
        default => ['ok', 'Normal'],
    };

    $avatarColors = ['#7c5cbf','#c0737a','#4f87b0','#6a9e8e','#c98a3a','#6b87bf','#a06bb0'];
    $relationIcons = ['self' => '🧑', 'spouse' => '💑', 'child' => '👶', 'parent' => '👴', 'sibling' => '🤝', 'other' => '👤'];
    $timelineEmojis = ['obstetrics' => '🤰', 'pediatrics' => '💉', 'ivf' => '🧬', 'orthodontics' => '😁', 'default' => '📋'];
@endphp

{{-- ── Pending OTP Access Requests ─────────────────────────────────────────── -- --}}
@foreach($pendingAccessReqs as $req)
<div class="otp-alert fu">
    <div class="otp-alert-ic">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#e8724a" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
        </svg>
    </div>
    <div style="flex:1;min-width:0">
        <div style="font-size:.875rem;font-weight:600;color:#8a3a20;margin-bottom:2px">
            Dr. {{ $req->doctor->profile?->full_name }} wants to access your records
        </div>
        <div style="font-size:.78rem;color:#a06040">
            {{ $req->doctor->doctorProfile?->specialization ?? 'Specialist' }} ·
            OTP expires {{ $req->otp_expires_at->diffForHumans() }}
        </div>
    </div>
    <div style="display:flex;gap:7px;flex-shrink:0">
        <form method="POST" action="{{ route('patient.access.approve', $req) }}">
            @csrf
            <button style="padding:6px 12px;background:#059669;color:#fff;border:none;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
                Approve
            </button>
        </form>
        <form method="POST" action="{{ route('patient.access.deny', $req) }}">
            @csrf
            <button style="padding:6px 12px;background:#fff;color:#c0737a;border:1.5px solid #f0b99e;border-radius:8px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
                Deny
            </button>
        </form>
    </div>
</div>
@endforeach

{{-- ── Health Vitals Row ─────────────────────────────────────────────────────── -- --}}
<div class="health-row fu">

    {{-- Blood Pressure --}}
    @php $bpLog = $latestVitals['blood_pressure'] ?? null; @endphp
    <div class="h-card">
        @if($bpLog)
            @php [$bpSt, $bpLbl] = $bpStatus($bpLog->value_1, $bpLog->value_2) @endphp
            @php $bpPct = max(0, min(100, round(100 - (($bpLog->value_1 - 90) / 80 * 100)))) @endphp
        @endif
        <div class="h-ring">
            <svg width="52" height="52" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="18" fill="none" stroke="#ede8e0" stroke-width="5"/>
                <circle cx="26" cy="26" r="18" fill="none"
                    stroke="{{ $bpLog ? ($bpSt === 'ok' ? '#6a9e8e' : ($bpSt === 'warn' ? '#c98a3a' : '#c0737a')) : '#ede8e0' }}"
                    stroke-width="5" stroke-dasharray="113"
                    stroke-dashoffset="{{ $bpLog ? $ringStroke($bpPct) : 113 }}"
                    stroke-linecap="round"/>
            </svg>
            <div class="h-ring-val" style="font-size:.6rem">
                {{ $bpLog ? '❤️' : '—' }}
            </div>
        </div>
        <div class="h-info">
            <div class="h-label">Blood Pressure</div>
            @if($bpLog)
                <div class="h-value">{{ $bpLog->value_1 }}/{{ $bpLog->value_2 }}</div>
                <div class="h-date">mmHg · {{ $bpLog->logged_at->diffForHumans() }}</div>
                <span class="h-status hs-{{ $bpSt }}">{{ $bpLbl }}</span>
            @else
                <div class="h-value" style="font-size:.9rem;color:var(--txt-lt)">No data</div>
                <a href="{{ route('patient.health.index') }}" style="font-size:.72rem;color:var(--plum);text-decoration:none">Log now →</a>
            @endif
        </div>
    </div>

    {{-- Blood Sugar --}}
    @php $sgLog = $latestVitals['blood_sugar'] ?? null; @endphp
    <div class="h-card">
        @if($sgLog)
            @php
                $subtype  = $sgLog->sub_type ?? 'fasting';
                [$sgSt,$sgLbl] = $sugarStatus($sgLog->value_1, $subtype);
                $sgPct = max(0, min(100, round(100 - (($sgLog->value_1 - 70) / 180 * 100))));
            @endphp
        @endif
        <div class="h-ring">
            <svg width="52" height="52" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="18" fill="none" stroke="#ede8e0" stroke-width="5"/>
                <circle cx="26" cy="26" r="18" fill="none"
                    stroke="{{ $sgLog ? ($sgSt === 'ok' ? '#6a9e8e' : ($sgSt === 'warn' ? '#c98a3a' : '#c0737a')) : '#ede8e0' }}"
                    stroke-width="5" stroke-dasharray="113"
                    stroke-dashoffset="{{ $sgLog ? $ringStroke($sgPct) : 113 }}"
                    stroke-linecap="round"/>
            </svg>
            <div class="h-ring-val">🍬</div>
        </div>
        <div class="h-info">
            <div class="h-label">Blood Sugar</div>
            @if($sgLog)
                <div class="h-value">{{ $sgLog->value_1 }}<span style="font-size:.75rem;font-weight:400"> mg/dL</span></div>
                <div class="h-date">{{ ucfirst($subtype) }} · {{ $sgLog->logged_at->diffForHumans() }}</div>
                <span class="h-status hs-{{ $sgSt }}">{{ $sgLbl }}</span>
            @else
                <div class="h-value" style="font-size:.9rem;color:var(--txt-lt)">No data</div>
                <a href="{{ route('patient.health.index') }}" style="font-size:.72rem;color:var(--plum);text-decoration:none">Log now →</a>
            @endif
        </div>
    </div>

    {{-- Weight --}}
    @php $wtLog = $latestVitals['weight'] ?? null; @endphp
    <div class="h-card">
        <div class="h-ring">
            <svg width="52" height="52" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="18" fill="none" stroke="#ede8e0" stroke-width="5"/>
                @if($wtLog)
                <circle cx="26" cy="26" r="18" fill="none" stroke="#4f87b0"
                    stroke-width="5" stroke-dasharray="113" stroke-dashoffset="45" stroke-linecap="round"/>
                @endif
            </svg>
            <div class="h-ring-val">⚖️</div>
        </div>
        <div class="h-info">
            <div class="h-label">Weight</div>
            @if($wtLog)
                <div class="h-value">{{ $wtLog->value_1 }}<span style="font-size:.75rem;font-weight:400"> kg</span></div>
                <div class="h-date">{{ $wtLog->logged_at->diffForHumans() }}</div>
                <span class="h-status hs-ok">Tracked</span>
            @else
                <div class="h-value" style="font-size:.9rem;color:var(--txt-lt)">No data</div>
                <a href="{{ route('patient.health.index') }}" style="font-size:.72rem;color:var(--plum);text-decoration:none">Log now →</a>
            @endif
        </div>
    </div>

    {{-- Oxygen / Pulse --}}
    @php $o2Log = $latestVitals['oxygen_saturation'] ?? $latestVitals['pulse'] ?? null; @endphp
    <div class="h-card">
        <div class="h-ring">
            <svg width="52" height="52" viewBox="0 0 52 52">
                <circle cx="26" cy="26" r="18" fill="none" stroke="#ede8e0" stroke-width="5"/>
                @if($o2Log)
                <circle cx="26" cy="26" r="18" fill="none" stroke="#6a9e8e"
                    stroke-width="5" stroke-dasharray="113"
                    stroke-dashoffset="{{ $o2Log->log_type === 'oxygen_saturation' ? $ringStroke(min(100, $o2Log->value_1)) : $ringStroke(min(100, round(($o2Log->value_1 / 120) * 100))) }}"
                    stroke-linecap="round"/>
                @endif
            </svg>
            <div class="h-ring-val">{{ $o2Log?->log_type === 'oxygen_saturation' ? '🫁' : '💓' }}</div>
        </div>
        <div class="h-info">
            <div class="h-label">{{ $o2Log?->log_type === 'oxygen_saturation' ? 'SpO₂' : 'Pulse' }}</div>
            @if($o2Log)
                <div class="h-value">{{ $o2Log->value_1 }}<span style="font-size:.75rem;font-weight:400"> {{ $o2Log->log_type === 'oxygen_saturation' ? '%' : 'bpm' }}</span></div>
                <div class="h-date">{{ $o2Log->logged_at->diffForHumans() }}</div>
                <span class="h-status hs-ok">Normal</span>
            @else
                <div class="h-value" style="font-size:.9rem;color:var(--txt-lt)">No data</div>
                <a href="{{ route('patient.health.index') }}" style="font-size:.72rem;color:var(--plum);text-decoration:none">Log now →</a>
            @endif
        </div>
    </div>
</div>

{{-- ── Two column layout ────────────────────────────────────────────────────── -- --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:18px;align-items:start">

    {{-- LEFT --}}
    <div style="display:flex;flex-direction:column;gap:18px">

        {{-- Upcoming Appointments --}}
        <div class="panel fu-1">
            <div class="ph">
                <div class="ph-title">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--sky)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upcoming Appointments
                </div>
                <div style="display:flex;gap:7px;align-items:center">
                    <a href="{{ route('patient.appointments.book') }}"
                       style="display:flex;align-items:center;gap:5px;font-size:.8rem;font-weight:600;color:#fff;background:var(--plum);border-radius:8px;padding:5px 11px;text-decoration:none">
                        <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Book
                    </a>
                    <a href="{{ route('patient.appointments.index') }}" class="ph-link">All →</a>
                </div>
            </div>

            @if($upcomingApts->isEmpty())
            <div class="empty">
                <div class="empty-ic">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="empty-t">No upcoming appointments</div>
                <div class="empty-s">Book a visit with your doctor.</div>
            </div>
            @else
            @foreach($upcomingApts as $apt)
            @php
                $drName   = $apt->doctor->profile?->full_name ?? 'Doctor';
                $initials = strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $drName), 0, 2))));
                $daysAway = now()->diffInDays($apt->slot_datetime, false);
                $isSoon   = $daysAway <= 1;
            @endphp
            <div class="apt-r">
                <div class="dr-avatar">{{ $initials }}</div>
                <div class="apt-info">
                    <div class="apt-dr-name">Dr. {{ $drName }}</div>
                    <div class="apt-meta">
                        <span>{{ $apt->doctor->doctorProfile?->specialization ?? 'Specialist' }}</span>
                        <span class="apt-dot"></span>
                        <span>{{ ucfirst($apt->type ?? 'consultation') }}</span>
                        @if($apt->appointment_number)
                        <span class="apt-dot"></span>
                        <span style="font-family:monospace;font-size:.7rem">{{ $apt->appointment_number }}</span>
                        @endif
                    </div>
                </div>
                <div class="apt-date-badge {{ $isSoon ? 'soon' : '' }}">
                    {{ $apt->slot_datetime->isToday() ? 'Today' : ($apt->slot_datetime->isTomorrow() ? 'Tomorrow' : $apt->slot_datetime->format('d M')) }}
                    {{ $apt->slot_datetime->format('h:i A') }}
                </div>
                <a href="{{ route('patient.appointments.show', $apt) }}"
                   style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;border:1px solid var(--warm-bd);color:var(--txt-lt);text-decoration:none;transition:all .12s;flex-shrink:0"
                   onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Family Members --}}
        <div class="panel fu-2">
            <div class="ph">
                <div class="ph-title">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--mauve)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Family Members
                </div>
                <a href="{{ route('patient.family.index') }}" class="ph-link">Manage →</a>
            </div>

            <div class="fam-grid">
                {{-- Self --}}
                <a href="{{ route('patient.history.index') }}" class="fam-card" style="text-decoration:none">
                    <div class="fam-avatar" style="background:var(--plum)">
                        {{ strtoupper(substr(auth()->user()->profile?->full_name ?? 'Y', 0, 1)) }}
                    </div>
                    <div class="fam-name">
                        {{ explode(' ', auth()->user()->profile?->full_name ?? 'You')[0] }}
                    </div>
                    <div class="fam-rel">Myself</div>
                    <div class="fam-sub-id">
                        @php
                            // Self sub-ID is MED-XXXXX-A
                            $selfSubId = 'MED-' . str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) . '-A';
                        @endphp
                        {{ $selfSubId }}
                    </div>
                </a>

                {{-- Family members --}}
                @foreach($familyMembers as $i => $member)
                <a href="{{ route('patient.family.show', $member) }}" class="fam-card" style="text-decoration:none">
                    <div class="fam-avatar" style="background:{{ $avatarColors[$i % count($avatarColors)] }}">
                        {{ strtoupper(substr($member->full_name, 0, 1)) }}
                    </div>
                    <div class="fam-name">{{ explode(' ', $member->full_name)[0] }}</div>
                    <div class="fam-rel">
                        {{ $relationIcons[$member->relation] ?? '👤' }} {{ $member->relation }}
                        @if($member->age) · {{ $member->age }}y @endif
                    </div>
                    <div class="fam-sub-id">{{ $member->sub_id }}</div>
                </a>
                @endforeach

                {{-- Add member --}}
                <a href="{{ route('patient.family.create') }}" class="fam-add-card">
                    <div style="width:44px;height:44px;border-radius:50%;background:var(--sand);display:flex;align-items:center;justify-content:center;margin:0 auto 8px;font-size:1.2rem">
                        ＋
                    </div>
                    <div style="font-size:.78rem;font-weight:500;color:var(--txt-lt)">Add member</div>
                </a>
            </div>
        </div>

        {{-- Recent Prescriptions --}}
        <div class="panel fu-3">
            <div class="ph">
                <div class="ph-title">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--sage)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Recent Prescriptions
                </div>
                <a href="{{ route('patient.history.index') }}" class="ph-link">All →</a>
            </div>

            @if($recentRx->isEmpty())
            <div class="empty" style="padding:20px 18px">
                <div class="empty-t">No prescriptions yet</div>
            </div>
            @else
            @foreach($recentRx as $rx)
            <div class="apt-r">
                <div style="width:36px;height:36px;border-radius:9px;background:var(--sage-lt);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--sage)">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="apt-info">
                    <div class="apt-dr-name" style="font-size:.875rem">{{ $rx->prescription_number }}</div>
                    <div class="apt-meta">
                        <span>Dr. {{ $rx->doctor->profile?->full_name ?? '—' }}</span>
                        <span class="apt-dot"></span>
                        <span>{{ $rx->medicines->count() }} medicine{{ $rx->medicines->count() > 1 ? 's' : '' }}</span>
                        <span class="apt-dot"></span>
                        <span>{{ $rx->prescribed_date->format('d M Y') }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    @if($rx->pdf_path)
                    <a href="{{ route('patient.history.prescription.pdf', $rx) }}"
                       style="font-size:.72rem;color:var(--sage);font-weight:600;padding:4px 8px;border:1px solid var(--sage-lt);border-radius:7px;text-decoration:none;transition:all .12s"
                       onmouseover="this.style.background='var(--sage-lt)'" onmouseout="this.style.background='transparent'">
                        PDF
                    </a>
                    @endif
                    <a href="{{ route('patient.history.show', $rx->medical_record_id) }}"
                       style="font-size:.72rem;color:var(--txt-lt);padding:4px 8px;border:1px solid var(--warm-bd);border-radius:7px;text-decoration:none;transition:all .12s"
                       onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
                        View →
                    </a>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:18px">

        {{-- Quick Stats --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <div style="background:var(--white);border:1px solid var(--warm-bd);border-radius:12px;padding:14px;text-align:center">
                <div class="font-serif" style="font-size:1.8rem;font-weight:500;color:var(--txt)">{{ $totalVisits }}</div>
                <div style="font-size:.72rem;color:var(--txt-lt);font-weight:600;letter-spacing:.04em;text-transform:uppercase">Visits</div>
            </div>
            <div style="background:var(--white);border:1px solid var(--warm-bd);border-radius:12px;padding:14px;text-align:center">
                <div class="font-serif" style="font-size:1.8rem;font-weight:500;color:var(--txt)">{{ $totalPrescriptions }}</div>
                <div style="font-size:.72rem;color:var(--txt-lt);font-weight:600;letter-spacing:.04em;text-transform:uppercase">Rx Total</div>
            </div>
        </div>

        {{-- Active Timelines --}}
        @if($activeTimelines->count() > 0)
        <div class="panel">
            <div class="ph">
                <div class="ph-title">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--rose)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Care Timelines
                </div>
                <a href="{{ route('patient.timelines.index') }}" class="ph-link">All →</a>
            </div>
            @foreach($activeTimelines as $tl)
            @php
                $t        = $tl['timeline'];
                $pct      = $tl['total_milestones'] > 0 ? round(($tl['done_milestones'] / $tl['total_milestones']) * 100) : 0;
                $next     = $tl['next_milestone'];
                $spec     = $t->template?->specialty_type ?? 'default';
                $emoji    = $timelineEmojis[$spec] ?? $timelineEmojis['default'];
                $barColor = match($spec) {
                    'obstetrics' => '#c0737a',
                    'pediatrics' => '#4f87b0',
                    'ivf'        => '#7c5cbf',
                    default      => '#6a9e8e',
                };
            @endphp
            <div class="tl-card">
                <div class="tl-header">
                    <div class="tl-icon" style="background:var(--sand)">{{ $emoji }}</div>
                    <div>
                        <div class="tl-title">{{ $t->template?->name }}</div>
                        <div class="tl-sub">
                            @if($t->familyMember)
                                For {{ $t->familyMember->full_name }} ·
                            @endif
                            {{ $tl['done_milestones'] }}/{{ $tl['total_milestones'] }} milestones
                        </div>
                    </div>
                    <div style="margin-left:auto;font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt)">{{ $pct }}%</div>
                </div>
                <div class="tl-bar-bg">
                    <div class="tl-bar-fill" style="width:{{ $pct }}%;background:{{ $barColor }}"></div>
                </div>
                @if($next)
                <div class="tl-progress-row">
                    <span>Next: <strong style="color:var(--txt)">{{ $next->title }}</strong></span>
                    <span>{{ $next->days_away === 0 ? 'Today' : 'in ' . $next->days_away . 'd' }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Medications --}}
        <div class="panel">
            <div class="ph">
                <div class="ph-title">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    My Medications
                </div>
                <a href="{{ route('patient.reminders.index') }}" class="ph-link">Manage →</a>
            </div>

            @if($activeMeds->isEmpty())
            <div class="empty" style="padding:20px 18px">
                <div class="empty-t">No active medications</div>
                <div class="empty-s">Add a reminder from your prescription.</div>
            </div>
            @else
            @foreach($activeMeds as $med)
            <div class="med-r">
                <div class="med-ic">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="med-name">{{ $med->medicine_name }}</div>
                    <div class="med-meta">{{ $med->dosage }}</div>
                </div>
                <div class="med-times">
                    @foreach(array_slice($med->reminder_times ?? [], 0, 3) as $t)
                        <span class="med-time-pill">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Doctors with Access --}}
        @if($activeDoctors->count() > 0)
        <div class="panel">
            <div class="ph">
                <div class="ph-title">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--sage)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Doctors with Access
                </div>
                <a href="{{ route('patient.access.index') }}" class="ph-link">Manage →</a>
            </div>
            @foreach($activeDoctors as $ar)
            <div class="dr-chip">
                <div class="dr-avatar" style="width:32px;height:32px;font-size:.75rem;border-radius:8px">
                    {{ strtoupper(substr($ar->doctor->profile?->full_name ?? 'D', 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.875rem;font-weight:500;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        Dr. {{ $ar->doctor->profile?->full_name }}
                    </div>
                    <span class="dr-chip-spec">{{ $ar->doctor->doctorProfile?->specialization ?? 'Specialist' }}</span>
                </div>
                <div class="dr-expiry">
                    Expires {{ $ar->access_expires_at->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

@endsection

@push('styles')
<style>
    @media (max-width: 900px) {
        #content-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush
