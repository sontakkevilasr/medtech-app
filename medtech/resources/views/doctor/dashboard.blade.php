@extends('layouts.doctor')
@section('title', 'Dashboard')
@section('page-title', 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')) . ', Dr. ' . (auth()->user()->profile?->full_name ? explode(' ', auth()->user()->profile->full_name)[0] : 'Doctor'))

@section('content')

{{-- ── Stat Cards ─────────────────────────────────────────────────────── --}}
<div class="stat-grid">

    {{-- Today's Appointments --}}
    <div class="stat-card fade-in" style="border-top: 3px solid var(--coral)">
        <div class="stat-label">Today's Appointments</div>
        <div class="stat-value">{{ $todayApts->count() }}</div>
        <div class="stat-sub">
            <span>{{ $todayApts->where('status','completed')->count() }} completed</span>
            @if($todayApts->where('status','confirmed')->count() > 0)
                <span class="stat-trend up">· {{ $todayApts->where('status','confirmed')->count() }} pending</span>
            @endif
        </div>
        <div class="stat-icon" style="background:var(--coral-lt)">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--coral)" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    {{-- This Month's Appointments --}}
    <div class="stat-card fade-in-d1" style="border-top: 3px solid var(--leaf)">
        <div class="stat-label">This Month</div>
        <div class="stat-value">{{ $thisMonthApts }}</div>
        <div class="stat-sub">appointments in {{ now()->format('F') }}</div>
        <div class="stat-icon" style="background: #edf6f4">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="stat-card fade-in-d2" style="border-top: 3px solid var(--amber)">
        <div class="stat-label">Revenue This Month</div>
        <div class="stat-value">₹{{ number_format($thisMonthRevenue / 1000, 1) }}k</div>
        <div class="stat-sub">
            collected via payments
        </div>
        <div class="stat-icon" style="background: var(--amber-lt)">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    {{-- Total Patients --}}
    <div class="stat-card fade-in-d3" style="border-top: 3px solid var(--sage)">
        <div class="stat-label">Total Patients</div>
        <div class="stat-value">{{ $totalPatients }}</div>
        <div class="stat-sub">active in records</div>
        <div class="stat-icon" style="background: #eef7f6">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--sage)" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
    </div>

</div>

{{-- ── Main two-column grid ─────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start"
     class="fade-in-d2">

    {{-- LEFT: Today's Appointments --}}
    <div>
        <div class="panel" style="margin-bottom:20px">
            <div class="panel-head">
                <div class="panel-title">
                    <span style="width:8px;height:8px;border-radius:50%;background:var(--coral);display:inline-block;animation:pulse-ring 2s infinite"></span>
                    Today's Schedule
                    <span style="font-size:.75rem;font-weight:400;color:var(--txt-lt);font-family:'Outfit',sans-serif">
                        {{ now()->format('l, d M Y') }}
                    </span>
                </div>
                <a href="{{ route('doctor.appointments') }}" class="panel-action">View all →</a>
            </div>

            @if($todayApts->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="empty-title">No appointments today</div>
                <div class="empty-sub">Enjoy the day off, or check upcoming slots.</div>
            </div>
            @else
            @foreach($todayApts as $apt)
            @php
                $name     = $apt->patient->profile?->full_name ?? 'Patient';
                $initials = strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $name), 0, 2))));
                $time     = $apt->slot_datetime;
                $isPast   = $time->isPast();
            @endphp
            <div class="apt-row">
                <div class="apt-time">
                    <div class="apt-time-val">{{ $time->format('h:i') }}</div>
                    <div class="apt-time-period">{{ $time->format('A') }}</div>
                </div>

                <div class="apt-divider"></div>

                <div class="apt-avatar">{{ $initials }}</div>

                <div class="apt-info">
                    <div class="apt-name">{{ $name }}</div>
                    <div class="apt-meta">
                        <span>{{ ucfirst($apt->type ?? 'consultation') }}</span>
                        @if($apt->reason)
                        <span class="apt-meta-dot"></span>
                        <span>{{ Str::limit($apt->reason, 30) }}</span>
                        @endif
                        @if($apt->fee)
                        <span class="apt-meta-dot"></span>
                        <span>₹{{ number_format($apt->fee) }}</span>
                        @endif
                    </div>
                </div>

                <span class="apt-status s-{{ $apt->status }}">{{ ucfirst(str_replace('_', ' ', $apt->status)) }}</span>

                <div class="apt-actions">
                    {{-- View / Start Record --}}
                    <a href="{{ route('doctor.patients.history', $apt->patient_user_id) }}"
                       class="apt-btn green" title="View Patient">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>

                    {{-- New Prescription --}}
                    <a href="{{ route('doctor.prescriptions.create', ['patient' => $apt->patient_user_id, 'appointment' => $apt->id]) }}"
                       class="apt-btn" title="New Prescription">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </a>

                    {{-- Mark Complete --}}
                    @if($apt->status === 'confirmed')
                    <form method="POST" action="{{ route('doctor.dashboard.update-status', $apt) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="apt-btn green" title="Mark Completed">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Upcoming Appointments --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Upcoming — Next 7 Days
                </div>
                <a href="{{ route('doctor.appointments') }}" class="panel-action">Full calendar →</a>
            </div>

            @if($upcomingApts->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="empty-title">No upcoming appointments</div>
                <div class="empty-sub">Your schedule looks clear for the next 7 days.</div>
            </div>
            @else
            @foreach($upcomingApts as $apt)
            @php
                $name     = $apt->patient->profile?->full_name ?? 'Patient';
                $initials = strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $name), 0, 2))));
                $time     = $apt->slot_datetime;
                $isToday  = $time->isToday();
                $dayLabel = $time->isToday() ? 'Today' : ($time->isTomorrow() ? 'Tomorrow' : $time->format('D, d M'));
            @endphp
            <div class="apt-row">
                <div class="apt-time" style="min-width:72px">
                    <div class="apt-time-val">{{ $time->format('h:i A') }}</div>
                    <div class="apt-time-period" style="color:{{ $isToday ? 'var(--coral)' : 'inherit' }}">
                        {{ $dayLabel }}
                    </div>
                </div>

                <div class="apt-divider"></div>
                <div class="apt-avatar">{{ $initials }}</div>

                <div class="apt-info">
                    <div class="apt-name">{{ $name }}</div>
                    <div class="apt-meta">
                        <span>{{ ucfirst($apt->type ?? 'consultation') }}</span>
                        @if($apt->appointment_number)
                        <span class="apt-meta-dot"></span>
                        <span style="font-family:monospace;font-size:.7rem">{{ $apt->appointment_number }}</span>
                        @endif
                    </div>
                </div>

                <span class="apt-status s-{{ $apt->status }}">{{ ucfirst(str_replace('_',' ',$apt->status)) }}</span>

                <div class="apt-actions">
                    <a href="{{ route('doctor.appointments.show', $apt) }}"
                       class="apt-btn" title="Details">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div style="display:flex;flex-direction:column;gap:20px">

        {{-- Revenue Chart --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Revenue (6 months)
                </div>
                @if(auth()->user()->doctorProfile?->is_premium)
                    <a href="{{ route('doctor.analytics') }}" class="panel-action">Details →</a>
                @endif
            </div>
            @php $maxRevenue = $revenueChart->max('amount') ?: 1; @endphp
            <div class="bar-chart">
                @foreach($revenueChart as $i => $bar)
                @php $isCurrentMonth = $i === $revenueChart->count() - 1; @endphp
                <div class="bar-wrap">
                    <div class="bar {{ $isCurrentMonth ? 'current' : '' }}"
                         style="height: {{ max(4, round(($bar['amount'] / $maxRevenue) * 44)) }}px"
                         title="₹{{ number_format($bar['amount']) }}">
                    </div>
                    <div class="bar-lbl">{{ $bar['month'] }}</div>
                </div>
                @endforeach
            </div>
            <div style="padding: 0 20px 14px;display:flex;justify-content:space-between;font-size:.75rem;color:var(--txt-md)">
                <span>This month: <strong style="color:var(--amber)">₹{{ number_format($thisMonthRevenue) }}</strong></span>
                @if($revenueChart->count() >= 2)
                @php
                    $prev = $revenueChart[$revenueChart->count()-2]['amount'];
                    $curr = $revenueChart[$revenueChart->count()-1]['amount'];
                    $diff = $prev > 0 ? round((($curr - $prev) / $prev) * 100) : 0;
                @endphp
                @if($diff !== 0)
                    <span class="stat-trend {{ $diff > 0 ? 'up' : 'down' }}">
                        {{ $diff > 0 ? '↑' : '↓' }} {{ abs($diff) }}% vs last month
                    </span>
                @endif
                @endif
            </div>
        </div>

        {{-- Pending Access Requests --}}
        @if($pendingAccess > 0)
        <div class="panel" style="border: 1.5px solid #f0b99e">
            <div class="panel-head" style="background:var(--coral-lt)">
                <div class="panel-title" style="color:var(--coral)">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Pending Access
                    <span style="font-family:'Outfit',sans-serif;font-size:.75rem;font-weight:400;color:var(--coral)">{{ $pendingAccess }} request{{ $pendingAccess > 1 ? 's' : '' }}</span>
                </div>
                <a href="{{ route('doctor.patients') }}" class="panel-action" style="color:var(--coral)">Resolve →</a>
            </div>
            <div style="padding:12px 20px;font-size:.8125rem;color:var(--txt-md)">
                You have pending OTP access requests waiting for patient approval.
            </div>
        </div>
        @endif

        {{-- Recent Prescriptions --}}
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="var(--leaf)" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Recent Prescriptions
                </div>
                <a href="{{ route('doctor.prescriptions') }}" class="panel-action">All →</a>
            </div>

            @if($recentRx->isEmpty())
            <div class="empty-state" style="padding:24px 20px">
                <div class="empty-title">No prescriptions yet</div>
            </div>
            @else
            @foreach($recentRx as $rx)
            <div class="rx-row">
                <div class="rx-icon">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="rx-info">
                    <div class="rx-name">{{ $rx->patient->profile?->full_name ?? 'Patient' }}</div>
                    <div class="rx-meta">
                        {{ $rx->prescription_number }}
                        · {{ $rx->medicines->count() }} medicine{{ $rx->medicines->count() > 1 ? 's' : '' }}
                        · {{ $rx->prescribed_date->diffForHumans() }}
                    </div>
                </div>
                @if(!$rx->is_sent_whatsapp)
                    <div class="rx-no-whatsapp" title="WhatsApp not sent"></div>
                @endif
                <a href="{{ route('doctor.prescriptions.show', $rx) }}"
                   style="color:var(--txt-lt);font-size:.75rem;text-decoration:none;padding:4px 8px;border-radius:6px;transition:all .12s"
                   onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                    View →
                </a>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Premium upsell if not premium --}}
        @if(!auth()->user()->doctorProfile?->is_premium)
        <div style="background: linear-gradient(135deg, var(--ink) 0%, #2a4a48 100%);border-radius:14px;padding:18px;position:relative;overflow:hidden">
            <div style="position:absolute;top:-10px;right:-10px;width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,.04)"></div>
            <div style="position:absolute;bottom:-20px;right:20px;width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,.03)"></div>
            <div style="font-size:.65rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--amber);margin-bottom:6px">Upgrade to Premium</div>
            <div class="font-display" style="font-size:1.1rem;color:#fff;margin-bottom:8px;line-height:1.3">
                Unlock Care Timelines, Analytics & Excel Exports
            </div>
            <div style="font-size:.8rem;color:rgba(255,255,255,.55);margin-bottom:14px;line-height:1.5">
                Specialised workflows for OBG, IVF, Paediatrics, Orthodontics and more.
            </div>
            <a href="{{ route('doctor.subscription') }}"
               style="display:inline-flex;align-items:center;gap:6px;background:var(--amber);color:#fff;border:none;border-radius:9px;padding:8px 16px;font-size:.8125rem;font-weight:600;text-decoration:none;transition:opacity .15s"
               onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                View Plans
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
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
    @keyframes pulse-ring {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: .5; }
    }
</style>
@endpush
