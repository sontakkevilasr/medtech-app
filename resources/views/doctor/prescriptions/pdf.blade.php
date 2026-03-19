<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $prescription->prescription_number }}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    @page { margin: 14mm 14mm 18mm 14mm; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9.5pt;
        color: #1c2b2a;
        line-height: 1.45;
        background: #fff;
    }

    /* ── Letterhead ─────────────────────────────────────── */
    .letterhead {
        border-bottom: 2px solid #1c2b2a;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .lh-top {
        display: table;
        width: 100%;
    }
    .lh-left  { display: table-cell; vertical-align: top; width: 70%; }
    .lh-right { display: table-cell; vertical-align: top; width: 30%; text-align: right; }

    .dr-name {
        font-size: 16pt;
        font-weight: bold;
        color: #1c2b2a;
        letter-spacing: -0.3px;
        line-height: 1.1;
    }
    .dr-qual {
        font-size: 8.5pt;
        color: #3d7a6e;
        margin-top: 2px;
        font-weight: bold;
    }
    .dr-spec {
        font-size: 8pt;
        color: #5a6e6c;
        margin-top: 1px;
    }
    .dr-reg {
        font-size: 7.5pt;
        color: #8fa09e;
        margin-top: 3px;
    }
    .clinic-name {
        font-size: 9pt;
        font-weight: bold;
        color: #2c3a38;
        margin-top: 4px;
    }
    .clinic-addr {
        font-size: 7.5pt;
        color: #5a6e6c;
        margin-top: 1px;
    }

    /* ── RX Header Bar ──────────────────────────────────── */
    .rx-bar {
        background: #1c2b2a;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        display: table;
        width: 100%;
        margin-bottom: 10px;
    }
    .rx-bar-left  { display: table-cell; vertical-align: middle; }
    .rx-bar-right { display: table-cell; vertical-align: middle; text-align: right; }
    .rx-number    { font-size: 10pt; font-weight: bold; letter-spacing: 0.5px; }
    .rx-date      { font-size: 8pt; opacity: .75; }

    /* ── Patient Info Box ───────────────────────────────── */
    .pt-box {
        border: 1px solid #e8e2da;
        border-radius: 5px;
        padding: 7px 10px;
        margin-bottom: 10px;
        background: #f7f3ee;
        display: table;
        width: 100%;
    }
    .pt-row   { display: table-row; }
    .pt-label { display: table-cell; font-size: 7.5pt; font-weight: bold; color: #8fa09e; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 8px; white-space: nowrap; padding-bottom: 2px; }
    .pt-value { display: table-cell; font-size: 9pt; color: #1c2b2a; padding-bottom: 2px; }

    /* ── Diagnosis ──────────────────────────────────────── */
    .section-label {
        font-size: 7.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #8fa09e;
        margin-bottom: 3px;
    }
    .diag-box {
        border-left: 3px solid #3d7a6e;
        padding: 4px 8px;
        background: #f2f8f7;
        border-radius: 0 4px 4px 0;
        margin-bottom: 10px;
        font-size: 9.5pt;
        color: #1c2b2a;
        font-weight: bold;
    }

    /* ── Rx Symbol + Medicines ──────────────────────────── */
    .rx-symbol {
        font-size: 22pt;
        font-weight: bold;
        color: #3d7a6e;
        line-height: 1;
        float: left;
        margin-right: 6px;
        margin-top: -4px;
    }
    .med-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
    }
    .med-table thead tr {
        background: #f7f3ee;
        border-bottom: 1.5px solid #e8e2da;
    }
    .med-table thead th {
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8fa09e;
        padding: 5px 8px;
        text-align: left;
    }
    .med-table tbody tr {
        border-bottom: 1px solid #f0ece6;
    }
    .med-table tbody tr:last-child { border-bottom: none; }
    .med-table tbody tr:nth-child(even) { background: #fdfcfb; }
    .med-table td {
        padding: 6px 8px;
        vertical-align: top;
    }
    .med-sno   { font-size: 8pt; color: #8fa09e; font-weight: bold; width: 20px; }
    .med-name  { font-size: 9.5pt; font-weight: bold; color: #1c2b2a; }
    .med-gen   { font-size: 7.5pt; color: #8fa09e; margin-top: 1px; }
    .med-dose  { font-size: 8.5pt; color: #2c3a38; }
    .med-freq  { font-size: 8.5pt; font-weight: bold; color: #1c2b2a; }
    .med-dur   { font-size: 8.5pt; color: #2c3a38; white-space: nowrap; }
    .med-tim   { font-size: 8pt; color: #5a6e6c; }
    .med-note  { font-size: 7.5pt; color: #8fa09e; font-style: italic; }

    /* ── Instructions / Diet ────────────────────────────── */
    .note-box {
        border: 1px solid #e8e2da;
        border-radius: 5px;
        padding: 7px 10px;
        margin-bottom: 8px;
    }
    .note-title { font-size: 7.5pt; font-weight: bold; color: #3d7a6e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
    .note-text  { font-size: 8.5pt; color: #2c3a38; }

    /* ── Follow-up ──────────────────────────────────────── */
    .followup-bar {
        background: #e8f5f3;
        border: 1px solid #b8dbd7;
        border-radius: 5px;
        padding: 6px 10px;
        margin-bottom: 10px;
        display: table;
        width: 100%;
    }
    .fu-left  { display: table-cell; vertical-align: middle; }
    .fu-right { display: table-cell; vertical-align: middle; text-align: right; }
    .fu-label { font-size: 7.5pt; font-weight: bold; color: #3d7a6e; text-transform: uppercase; letter-spacing: 0.5px; }
    .fu-date  { font-size: 10pt; font-weight: bold; color: #1c2b2a; }
    .fu-instr { font-size: 8pt; color: #5a6e6c; margin-top: 2px; }

    /* ── Footer / Signature ─────────────────────────────── */
    .footer {
        border-top: 1.5px solid #e8e2da;
        padding-top: 8px;
        margin-top: 12px;
        display: table;
        width: 100%;
    }
    .sig-right  { display: table-cell; text-align: right; vertical-align: bottom; }
    .sig-line   { border-top: 1px solid #1c2b2a; width: 100px; display: inline-block; margin-bottom: 3px; }
    .sig-name   { font-size: 8pt; font-weight: bold; color: #1c2b2a; }
    .sig-reg    { font-size: 7pt; color: #8fa09e; }
    .footer-left { display: table-cell; vertical-align: bottom; }
    .watermark-text { font-size: 7pt; color: #c0c0c0; margin-top: 2px; }

    /* ── QR / UPI box ───────────────────────────────────── */
    .upi-box {
        border: 1px solid #e8e2da;
        border-radius: 5px;
        padding: 5px 8px;
        text-align: center;
        display: inline-block;
    }
    .upi-label { font-size: 7pt; color: #8fa09e; text-transform: uppercase; letter-spacing: 0.5px; }
    .upi-id    { font-size: 8pt; font-weight: bold; color: #1c2b2a; font-family: monospace; }

    /* ── Divider ────────────────────────────────────────── */
    .divider { height: 1px; background: #f0ece6; margin: 8px 0; }
    .clearfix { clear: both; }
</style>
</head>
<body>

{{-- ── Letterhead ────────────────────────────────────────────────────────── -- --}}
@php
    $doctor  = $prescription->doctor;
    $profile = $doctor?->doctorProfile;
    $patient = $prescription->patient;
    $member  = $prescription->familyMember;
    $patientName = $member ? $member->full_name : ($patient->profile?->full_name ?? 'Patient');
    $patientAge  = $member ? $member->age : $patient->profile?->age;
    $patientGen  = $member ? $member->gender : $patient->profile?->gender;
    $patientBg   = $patient->profile?->blood_group;
@endphp

<div class="letterhead">
    <div class="lh-top">
        <div class="lh-left">
            <div class="dr-name">Dr. {{ $doctor?->profile?->full_name ?? 'Doctor' }}</div>
            @if($profile?->qualification)
                <div class="dr-qual">{{ $profile->qualification }}</div>
            @endif
            @if($profile?->specialization)
                <div class="dr-spec">{{ $profile->specialization }}</div>
            @endif
            @if($profile?->registration_number)
                <div class="dr-reg">Reg. No: {{ $profile->registration_number }}
                    @if($profile?->registration_council) | {{ $profile->registration_council }} @endif
                </div>
            @endif
        </div>
        <div class="lh-right">
            @if($profile?->clinic_name)
                <div class="clinic-name">{{ $profile->clinic_name }}</div>
            @endif
            @if($profile?->clinic_address)
                <div class="clinic-addr">{{ $profile->clinic_address }}</div>
            @endif
            @if($profile?->clinic_city)
                <div class="clinic-addr">{{ $profile->clinic_city }}{{ $profile?->clinic_state ? ', ' . $profile->clinic_state : '' }}</div>
            @endif
            @if($doctor?->mobile_number)
                <div class="clinic-addr">📞 {{ $doctor->country_code }} {{ $doctor->mobile_number }}</div>
            @endif
            @if($profile?->upi_id)
            <div style="margin-top:5px">
                <div class="upi-box">
                    <div class="upi-label">UPI Payment</div>
                    <div class="upi-id">{{ $profile->upi_id }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── RX Number bar ──────────────────────────────────────────────────────── -- --}}
<div class="rx-bar">
    <div class="rx-bar-left">
        <span class="rx-number">{{ $prescription->prescription_number }}</span>
    </div>
    <div class="rx-bar-right">
        <span class="rx-date">Date: {{ $prescription->prescribed_date->format('d F Y') }}</span>
    </div>
</div>

{{-- ── Patient Info ───────────────────────────────────────────────────────── -- --}}
<table class="pt-box" style="width:100%;border:1px solid #e8e2da;border-radius:5px;background:#f7f3ee;padding:7px 10px;margin-bottom:10px">
    <tr>
        <td style="font-size:7.5pt;font-weight:bold;color:#8fa09e;text-transform:uppercase;letter-spacing:.5px;padding-right:16px;white-space:nowrap;padding-bottom:2px">Patient</td>
        <td style="font-size:9.5pt;font-weight:bold;color:#1c2b2a;padding-bottom:2px">{{ $patientName }}</td>
        <td style="font-size:7.5pt;font-weight:bold;color:#8fa09e;text-transform:uppercase;letter-spacing:.5px;padding-right:8px;padding-left:16px;white-space:nowrap">Age</td>
        <td style="font-size:9pt;color:#1c2b2a">{{ $patientAge ? $patientAge.' yrs' : '—' }}</td>
        <td style="font-size:7.5pt;font-weight:bold;color:#8fa09e;text-transform:uppercase;letter-spacing:.5px;padding-right:8px;padding-left:16px;white-space:nowrap">Sex</td>
        <td style="font-size:9pt;color:#1c2b2a">{{ $patientGen ? ucfirst($patientGen) : '—' }}</td>
        <td style="font-size:7.5pt;font-weight:bold;color:#8fa09e;text-transform:uppercase;letter-spacing:.5px;padding-right:8px;padding-left:16px;white-space:nowrap">Blood Grp</td>
        <td style="font-size:9pt;color:#1c2b2a">{{ $patientBg ?? '—' }}</td>
    </tr>
</table>

{{-- ── Diagnosis ──────────────────────────────────────────────────────────── -- --}}
@if($prescription->diagnosis_summary)
<div class="section-label">Diagnosis</div>
<div class="diag-box">{{ $prescription->diagnosis_summary }}</div>
@endif

{{-- ── Medicines ───────────────────────────────────────────────────────────── -- --}}
<div style="margin-bottom:4px">
    <span class="rx-symbol">℞</span>
    <span class="section-label" style="line-height:2">Medicines</span>
    <div class="clearfix"></div>
</div>

<table class="med-table">
    <thead>
        <tr>
            <th style="width:20px">#</th>
            <th style="width:32%">Medicine</th>
            <th style="width:12%">Dosage</th>
            <th style="width:12%">Frequency</th>
            <th style="width:10%">Duration</th>
            <th style="width:14%">Timing</th>
            <th>Instructions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prescription->medicines as $i => $med)
        <tr>
            <td class="med-sno">{{ $i + 1 }}</td>
            <td>
                <div class="med-name">{{ $med->medicine_name }}</div>
                @if($med->generic_name)
                    <div class="med-gen">({{ $med->generic_name }})</div>
                @endif
                @if($med->form)
                    <div class="med-gen">{{ ucfirst($med->form) }}</div>
                @endif
            </td>
            <td class="med-dose">{{ $med->dosage ?? '—' }}</td>
            <td class="med-freq">{{ $med->frequency ?? '—' }}</td>
            <td class="med-dur">{{ $med->duration_days ? $med->duration_days.' days' : '—' }}</td>
            <td class="med-tim">{{ $med->timing ?? '—' }}</td>
            <td class="med-note">{{ $med->special_instructions ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── Instructions ───────────────────────────────────────────────────────── -- --}}
@if($prescription->general_instructions || $prescription->diet_advice)
<table style="width:100%;border-collapse:collapse;margin-bottom:10px">
    <tr>
        @if($prescription->general_instructions)
        <td style="padding-right:6px;vertical-align:top;width:{{ $prescription->diet_advice ? '60%' : '100%' }}">
            <div class="note-box">
                <div class="note-title">📝 General Instructions</div>
                <div class="note-text">{{ $prescription->general_instructions }}</div>
            </div>
        </td>
        @endif
        @if($prescription->diet_advice)
        <td style="padding-left:{{ $prescription->general_instructions ? '0' : '0' }};vertical-align:top">
            <div class="note-box">
                <div class="note-title">🥗 Diet Advice</div>
                <div class="note-text">{{ $prescription->diet_advice }}</div>
            </div>
        </td>
        @endif
    </tr>
</table>
@endif

{{-- ── Follow-up ───────────────────────────────────────────────────────────── -- --}}
@if($prescription->follow_up_date)
<div class="followup-bar">
    <div class="fu-left">
        <div class="fu-label">📅 Follow-up Visit</div>
        @if($prescription->follow_up_instructions)
            <div class="fu-instr">{{ $prescription->follow_up_instructions }}</div>
        @endif
    </div>
    <div class="fu-right">
        <div class="fu-date">{{ $prescription->follow_up_date->format('d M Y') }}</div>
        <div class="fu-instr">{{ $prescription->follow_up_date->diffForHumans() }}</div>
    </div>
</div>
@endif

{{-- ── Footer / Signature ─────────────────────────────────────────────────── -- --}}
<div class="footer">
    <div class="footer-left">
        <div style="font-size:7pt;color:#c0c0c0">Generated by MedTech · {{ $prescription->prescription_number }}</div>
        <div style="font-size:7pt;color:#c0c0c0">This prescription is valid for 30 days from the date of issue.</div>
        @if($member)
        <div style="font-size:7pt;color:#8fa09e;margin-top:2px">
            Family member: {{ $member->full_name }} ({{ $member->relation }}) · Sub-ID: {{ $member->sub_id }}
        </div>
        @endif
    </div>
    <div class="sig-right">
        <div>
            <div class="sig-line"></div><br>
            <div class="sig-name">Dr. {{ $doctor?->profile?->full_name }}</div>
            @if($profile?->registration_number)
                <div class="sig-reg">Reg: {{ $profile->registration_number }}</div>
            @endif
        </div>
    </div>
</div>

</body>
</html>
