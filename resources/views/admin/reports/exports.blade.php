@extends('layouts.admin')
@section('title', 'Data Exports')
@section('page-title', 'Data Exports')

@section('content')
<div class="fade-in">

<div style="margin-bottom:22px">
    <div style="font-size:.875rem;color:var(--txt-md)">
        Download platform data as formatted Excel (.xlsx) files. All exports include headers and styled cells.
    </div>
</div>

{{-- ── Export cards grid ────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px">

@php
$exports = [
    [
        'title'  => 'All Users',
        'desc'   => 'Every doctor and patient on the platform with profile, status, verification, and join date.',
        'icon'   => '👥',
        'color'  => '#6366f1',
        'bg'     => '#e0e7ff',
        'route'  => 'admin.reports.export.users',
        'cols'   => 'ID, Name, Role, Mobile, Email, City, Status, Verified, Specialization, Premium, Joined',
        'filter' => null,
    ],
    [
        'title'  => 'Doctors Only',
        'desc'   => 'All doctor accounts with specialization, registration details, verification status and plan.',
        'icon'   => '🩺',
        'color'  => '#10b981',
        'bg'     => '#d1fae5',
        'route'  => 'admin.reports.export.doctors',
        'cols'   => 'ID, Name, Mobile, Specialty, Qualification, Reg No, Council, Verified, Status, Plan',
        'filter' => null,
    ],
    [
        'title'  => 'Patients Only',
        'desc'   => 'All patient accounts with profile info, city, status, and registration date.',
        'icon'   => '🏥',
        'color'  => '#3b82f6',
        'bg'     => '#dbeafe',
        'route'  => 'admin.reports.export.patients',
        'cols'   => 'ID, Name, Mobile, Email, City, State, Status, Joined',
        'filter' => null,
    ],
    [
        'title'  => 'Appointments',
        'desc'   => 'All appointments across the platform with doctor, patient, status, fee and payment info.',
        'icon'   => '📅',
        'color'  => '#f59e0b',
        'bg'     => '#fef9c3',
        'route'  => 'admin.reports.export.appointments',
        'cols'   => 'Apt No, Doctor, Patient, Date & Time, Type, Status, Fee, Payment, Reason',
        'filter' => 'date_range',
    ],
    [
        'title'  => 'Doctor Verification',
        'desc'   => 'All doctors with their registration numbers, councils, verification and account status.',
        'icon'   => '✅',
        'color'  => '#8b5cf6',
        'bg'     => '#ede9fe',
        'route'  => 'admin.reports.export.verification',
        'cols'   => 'ID, Name, Mobile, Specialty, Qualification, Reg No, Council, Verified, Status, Plan',
        'filter' => null,
    ],
    [
        'title'  => 'Platform Stats',
        'desc'   => 'Monthly summary — new users, appointments, prescriptions and revenue for last 12 months.',
        'icon'   => '📊',
        'color'  => '#ef4444',
        'bg'     => '#fee2e2',
        'route'  => 'admin.reports.export.revenue',
        'cols'   => 'Month, New Doctors, New Patients, Total Apts, Completed, Cancelled, Prescriptions, Revenue',
        'filter' => null,
    ],
];
@endphp

@foreach($exports as $exp)
<div class="card" style="padding:0;overflow:hidden;transition:box-shadow .15s"
     onmouseover="this.style.boxShadow='0 4px 20px rgba(0,0,0,.08)'" onmouseout="this.style.boxShadow='none'">

    {{-- Colour header --}}
    <div style="padding:16px 20px;display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:12px;background:{{ $exp['bg'] }};display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0">
            {{ $exp['icon'] }}
        </div>
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.05rem;font-weight:500;color:var(--txt)">{{ $exp['title'] }}</div>
            <div style="font-size:.72rem;color:var(--txt-lt)">Excel .xlsx</div>
        </div>
    </div>

    <div style="padding:0 20px 16px">
        <p style="font-size:.78rem;color:var(--txt-md);line-height:1.55;margin-bottom:12px">{{ $exp['desc'] }}</p>

        {{-- Columns preview --}}
        <div style="padding:8px 10px;background:var(--bg);border-radius:8px;font-size:.68rem;color:var(--txt-lt);line-height:1.5;margin-bottom:14px">
            <span style="font-weight:700;color:var(--txt-md)">Columns: </span>{{ $exp['cols'] }}
        </div>

        {{-- Date range filter --}}
        @if($exp['filter'] === 'date_range')
        <form action="{{ route($exp['route']) }}" method="GET" style="margin-bottom:10px">
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;margin-bottom:8px">
                <div>
                    <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:3px">From</label>
                    <input type="date" name="from"
                           value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                           class="inp" style="font-size:.8rem;padding:5px 9px">
                </div>
                <div>
                    <label style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:3px">To</label>
                    <input type="date" name="to"
                           value="{{ today()->format('Y-m-d') }}"
                           class="inp" style="font-size:.8rem;padding:5px 9px">
                </div>
            </div>
            <button type="submit"
                    style="width:100%;padding:9px;background:{{ $exp['color'] }};color:#fff;border:none;border-radius:9px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Excel
            </button>
        </form>
        @else
        <a href="{{ route($exp['route']) }}"
           style="display:flex;align-items:center;justify-content:center;gap:7px;padding:9px;background:{{ $exp['color'] }};color:#fff;border-radius:9px;font-size:.8125rem;font-weight:600;text-decoration:none;transition:opacity .15s"
           onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Excel
        </a>
        @endif
    </div>
</div>
@endforeach

</div>

{{-- ── Info footer ─────────────────────────────────────────────────────────── --}}
<div style="margin-top:22px;padding:14px 18px;background:var(--card);border:1px solid var(--bd);border-radius:12px;display:flex;gap:16px;align-items:center;flex-wrap:wrap">
    <div style="font-size:.8rem;color:var(--txt-md)">
        <strong style="color:var(--txt)">File format:</strong> Microsoft Excel (.xlsx) — opens in Excel, Google Sheets, LibreOffice.
    </div>
    <div style="font-size:.8rem;color:var(--txt-md)">
        <strong style="color:var(--txt)">Headers:</strong> Bold white text on blue background.
    </div>
    <div style="font-size:.8rem;color:var(--txt-md)">
        <strong style="color:var(--txt)">Privacy:</strong> Private doctor notes are never included in any export.
    </div>
</div>

</div>
@endsection
