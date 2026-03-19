<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    @page {
        margin: 16mm 14mm 20mm 14mm;
    }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 9pt;
        color: #1c2b2a;
        line-height: 1.45;
    }

    /* ── Header / Letterhead ─────────────────────────────────────── */
    .letterhead {
        display: table;
        width: 100%;
        border-bottom: 2.5pt solid #3d7a6e;
        padding-bottom: 10pt;
        margin-bottom: 8pt;
    }
    .lh-left  { display: table-cell; width: 60%; vertical-align: top; }
    .lh-right { display: table-cell; width: 40%; vertical-align: top; text-align: right; }

    .clinic-name {
        font-size: 16pt;
        font-weight: bold;
        color: #1c2b2a;
        letter-spacing: -0.3pt;
        margin-bottom: 2pt;
    }
    .doctor-name {
        font-size: 12pt;
        font-weight: bold;
        color: #3d7a6e;
        margin-bottom: 2pt;
    }
    .doctor-quals {
        font-size: 8pt;
        color: #5a6e6c;
    }
    .clinic-address {
        font-size: 7.5pt;
        color: #5a6e6c;
        margin-top: 4pt;
        line-height: 1.5;
    }
    .reg-number {
        font-size: 7.5pt;
        color: #5a6e6c;
        margin-top: 3pt;
    }
    .contact-line {
        font-size: 7.5pt;
        color: #3d7a6e;
        margin-top: 2pt;
    }

    /* ── Rx Symbol + Patient info ────────────────────────────────── */
    .rx-meta {
        display: table;
        width: 100%;
        margin-bottom: 8pt;
        background: #f4f1ec;
        border-radius: 4pt;
        padding: 8pt 10pt;
    }
    .rx-left  { display: table-cell; width: 65%; vertical-align: top; }
    .rx-right { display: table-cell; width: 35%; vertical-align: top; text-align: right; }

    .rx-symbol {
        font-size: 22pt;
        font-weight: bold;
        color: #3d7a6e;
        line-height: 1;
        margin-bottom: 2pt;
    }
    .meta-label {
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        color: #8fa09e;
        margin-bottom: 1pt;
    }
    .meta-value {
        font-size: 9.5pt;
        font-weight: bold;
        color: #1c2b2a;
    }
    .meta-value-sm {
        font-size: 8.5pt;
        color: #1c2b2a;
    }
    .rx-number {
        font-size: 8pt;
        color: #8fa09e;
        font-family: 'Courier New', monospace;
    }

    /* ── Diagnosis block ─────────────────────────────────────────── */
    .diagnosis-block {
        border-left: 3pt solid #3d7a6e;
        padding-left: 8pt;
        margin-bottom: 9pt;
        background: #faf8f5;
        padding: 6pt 6pt 6pt 10pt;
        border-radius: 0 4pt 4pt 0;
    }
    .section-label {
        font-size: 7.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        color: #3d7a6e;
        margin-bottom: 2pt;
    }

    /* ── Medicines table ─────────────────────────────────────────── */
    .med-section-title {
        font-size: 9pt;
        font-weight: bold;
        color: #1c2b2a;
        border-bottom: 1pt solid #3d7a6e;
        padding-bottom: 3pt;
        margin-bottom: 5pt;
        letter-spacing: 0.3pt;
        text-transform: uppercase;
    }

    .med-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 9pt;
    }
    .med-table th {
        font-size: 7pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.4pt;
        color: #fff;
        background: #3d7a6e;
        padding: 5pt 7pt;
        text-align: left;
    }
    .med-table td {
        padding: 6pt 7pt;
        font-size: 8.5pt;
        color: #1c2b2a;
        vertical-align: top;
        border-bottom: 0.5pt solid #e8e2da;
    }
    .med-table tr:nth-child(even) td {
        background: #faf8f5;
    }
    .med-num {
        width: 16pt;
        font-weight: bold;
        color: #3d7a6e;
        font-size: 10pt;
    }
    .med-name    { font-weight: bold; font-size: 9pt; }
    .med-generic { font-size: 7.5pt; color: #8fa09e; font-style: italic; }
    .med-dosage  { color: #3d7a6e; font-weight: bold; }
    .timing-pill {
        display: inline-block;
        font-size: 7pt;
        padding: 1pt 5pt;
        border-radius: 10pt;
        border: 0.5pt solid #c2ddd8;
        color: #1a6a5a;
        background: #edf6f4;
        white-space: nowrap;
    }

    /* ── Instructions boxes ──────────────────────────────────────── */
    .instr-table { display: table; width: 100%; margin-bottom: 9pt; }
    .instr-cell  { display: table-cell; width: 33.33%; padding-right: 6pt; vertical-align: top; }
    .instr-cell:last-child { padding-right: 0; }
    .instr-box {
        border: 0.5pt solid #e8e2da;
        border-radius: 4pt;
        padding: 6pt;
        height: 100%;
    }

    /* ── Follow-up ───────────────────────────────────────────────── */
    .followup-bar {
        background: #edf6f4;
        border: 0.75pt solid #b8dbd7;
        border-radius: 4pt;
        padding: 6pt 10pt;
        margin-bottom: 10pt;
        display: table;
        width: 100%;
    }
    .fu-left  { display: table-cell; vertical-align: middle; }
    .fu-right { display: table-cell; vertical-align: middle; text-align: right; }

    /* ── Signature footer ────────────────────────────────────────── */
    .sig-footer {
        border-top: 1pt solid #e8e2da;
        padding-top: 8pt;
        display: table;
        width: 100%;
    }
    .sig-left  { display: table-cell; width: 60%; vertical-align: bottom; }
    .sig-right { display: table-cell; width: 40%; vertical-align: bottom; text-align: right; }
    .sig-name  { font-size: 9.5pt; font-weight: bold; color: #1c2b2a; }
    .sig-quals { font-size: 7.5pt; color: #5a6e6c; margin-top: 2pt; }
    .sig-line  { border-top: 1pt solid #1c2b2a; margin-bottom: 3pt; width: 120pt; margin-left: auto; }

    .disclaimer {
        font-size: 6.5pt;
        color: #a0b0ae;
        text-align: center;
        margin-top: 8pt;
        border-top: 0.5pt solid #e8e2da;
        padding-top: 5pt;
        line-height: 1.5;
    }

    /* Watermark for cancelled */
    .watermark {
        position: fixed;
        top: 45%;
        left: 50%;
        transform: translate(-50%,-50%) rotate(-30deg);
        font-size: 56pt;
        font-weight: bold;
        color: rgba(220,38,38,.08);
        letter-spacing: 4pt;
        text-transform: uppercase;
        pointer-events: none;
        z-index: -1;
    }
</style>
</head>
<body>

@if($prescription->status === 'cancelled')
<div class="watermark">CANCELLED</div>
@endif

{{-- ── Letterhead ─────────────────────────────────────────────────────────── -- --}}
<div class="letterhead">
    <div class="lh-left">
        <div class="clinic-name">{{ $prescription->doctor->doctorProfile?->clinic_name ?? 'MedTech Clinic' }}</div>
        <div class="doctor-name">Dr. {{ $prescription->doctor->profile?->full_name }}</div>
        <div class="doctor-quals">
            {{ $prescription->doctor->doctorProfile?->specialization }}
            @if($prescription->doctor->doctorProfile?->qualification)
                · {{ $prescription->doctor->doctorProfile->qualification }}
            @endif
            @if($prescription->doctor->doctorProfile?->experience_years)
                · {{ $prescription->doctor->doctorProfile->experience_years }} yrs exp.
            @endif
        </div>
        <div class="reg-number">
            Reg. No: {{ $prescription->doctor->doctorProfile?->registration_number ?? 'N/A' }}
            @if($prescription->doctor->doctorProfile?->registration_council)
                ({{ $prescription->doctor->doctorProfile->registration_council }})
            @endif
        </div>
    </div>
    <div class="lh-right">
        @if($prescription->doctor->doctorProfile?->clinic_address)
        <div class="clinic-address">
            {{ $prescription->doctor->doctorProfile->clinic_address }}<br>
            {{ $prescription->doctor->doctorProfile->clinic_city }}
            @if($prescription->doctor->doctorProfile->clinic_pincode)
                – {{ $prescription->doctor->doctorProfile->clinic_pincode }}
            @endif
            @if($prescription->doctor->doctorProfile->clinic_state)
                <br>{{ $prescription->doctor->doctorProfile->clinic_state }}
            @endif
        </div>
        @endif
        @if($prescription->doctor->mobile_number)
        <div class="contact-line">
            📞 {{ $prescription->doctor->country_code }} {{ $prescription->doctor->mobile_number }}
        </div>
        @endif
    </div>
</div>

{{-- ── Rx Meta: patient info + date ──────────────────────────────────────── -- --}}
@php
    $patientName = $prescription->familyMember
        ? $prescription->familyMember->full_name . ' (' . $prescription->patient->profile?->full_name . ')'
        : ($prescription->patient->profile?->full_name ?? 'Patient');
    $patientProfile = $prescription->patient->profile;
@endphp
<div class="rx-meta">
    <div class="rx-left">
        <div class="rx-symbol">℞</div>
        <div class="meta-label">Patient</div>
        <div class="meta-value">{{ $patientName }}</div>
        <div style="margin-top:3pt;font-size:8pt;color:#5a6e6c">
            {{ $patientProfile?->age ? 'Age: ' . $patientProfile->age : '' }}
            {{ $patientProfile?->gender ? ' &nbsp;·&nbsp; ' . ucfirst($patientProfile->gender) : '' }}
            {{ $patientProfile?->blood_group ? ' &nbsp;·&nbsp; ' . $patientProfile->blood_group : '' }}
        </div>
    </div>
    <div class="rx-right">
        <div class="rx-number">{{ $prescription->prescription_number }}</div>
        <div style="margin-top:6pt">
            <div class="meta-label">Date</div>
            <div class="meta-value-sm">{{ $prescription->prescribed_date->format('d F Y') }}</div>
        </div>
        @if($prescription->patient->profile?->city)
        <div style="margin-top:5pt">
            <div class="meta-label">Location</div>
            <div style="font-size:8pt;color:#1c2b2a">{{ $prescription->patient->profile->city }}</div>
        </div>
        @endif
    </div>
</div>

{{-- ── Diagnosis ──────────────────────────────────────────────────────────── -- --}}
@if($prescription->diagnosis_summary)
<div class="diagnosis-block">
    <div class="section-label">Diagnosis / Clinical Summary</div>
    <div style="font-size:9pt;color:#1c2b2a">{{ $prescription->diagnosis_summary }}</div>
</div>
@endif

{{-- ── Medicines ──────────────────────────────────────────────────────────── -- --}}
<div class="med-section-title">Prescribed Medicines</div>
<table class="med-table">
    <thead>
        <tr>
            <th style="width:16pt">#</th>
            <th style="width:32%">Medicine</th>
            <th style="width:12%">Form</th>
            <th style="width:13%">Dosage</th>
            <th style="width:10%">Frequency</th>
            <th style="width:10%">Duration</th>
            <th style="width:13%">Timing</th>
            <th>Instructions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prescription->medicines as $i => $med)
        <tr>
            <td class="med-num">{{ $i + 1 }}</td>
            <td>
                <div class="med-name">{{ $med->medicine_name }}</div>
                @if($med->generic_name)
                <div class="med-generic">({{ $med->generic_name }})</div>
                @endif
            </td>
            <td>{{ ucfirst($med->form ?? '—') }}</td>
            <td class="med-dosage">{{ $med->dosage ?? '—' }}</td>
            <td style="font-weight:600;color:#3d7a6e">{{ $med->frequency ?? '—' }}</td>
            <td>
                @if($med->duration_days)
                    <strong>{{ $med->duration_days }}</strong> days
                @else
                    —
                @endif
            </td>
            <td>
                @if($med->timing && $med->timing !== 'any_time')
                <span class="timing-pill">{{ $med->timing_label }}</span>
                @else
                <span style="color:#8fa09e">Any time</span>
                @endif
            </td>
            <td style="font-size:8pt;color:#5a6e6c">{{ $med->special_instructions ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── Instructions row ──────────────────────────────────────────────────── -- --}}
@if($prescription->general_instructions || $prescription->diet_advice)
<div class="instr-table">
    @if($prescription->general_instructions)
    <div class="instr-cell">
        <div class="instr-box">
            <div class="section-label">General Instructions</div>
            <div style="font-size:8.5pt;color:#1c2b2a;margin-top:2pt">{{ $prescription->general_instructions }}</div>
        </div>
    </div>
    @endif
    @if($prescription->diet_advice)
    <div class="instr-cell">
        <div class="instr-box">
            <div class="section-label">Diet Advice</div>
            <div style="font-size:8.5pt;color:#1c2b2a;margin-top:2pt">{{ $prescription->diet_advice }}</div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- ── Follow-up ───────────────────────────────────────────────────────────── -- --}}
@if($prescription->follow_up_date || $prescription->follow_up_instructions)
<div class="followup-bar">
    <div class="fu-left">
        <div class="section-label" style="color:#1a6a5a">Follow-up</div>
        @if($prescription->follow_up_instructions)
        <div style="font-size:8.5pt;color:#1c2b2a;margin-top:2pt">{{ $prescription->follow_up_instructions }}</div>
        @endif
    </div>
    @if($prescription->follow_up_date)
    <div class="fu-right">
        <div class="section-label" style="color:#1a6a5a">Next Visit</div>
        <div style="font-size:10pt;font-weight:bold;color:#1c2b2a;margin-top:2pt">
            {{ $prescription->follow_up_date->format('d M Y') }}
        </div>
        <div style="font-size:7.5pt;color:#3d7a6e">
            ({{ $prescription->follow_up_date->diffForHumans() }})
        </div>
    </div>
    @endif
</div>
@endif

{{-- ── Signature footer ────────────────────────────────────────────────────── -- --}}
<div class="sig-footer">
    <div class="sig-left">
        <div style="font-size:7.5pt;color:#8fa09e;margin-bottom:4pt">
            Generated on {{ now()->format('d M Y, h:i A') }}
            @if($prescription->is_sent_whatsapp)
                &nbsp;·&nbsp; Sent via WhatsApp {{ $prescription->whatsapp_sent_at?->format('d M Y') }}
            @endif
        </div>
        <div style="font-size:7pt;color:#a0b0ae">
            This prescription is valid for 30 days from the date of issue.
        </div>
    </div>
    <div class="sig-right">
        <div class="sig-line"></div>
        <div class="sig-name">Dr. {{ $prescription->doctor->profile?->full_name }}</div>
        <div class="sig-quals">
            {{ $prescription->doctor->doctorProfile?->specialization }}
            @if($prescription->doctor->doctorProfile?->qualification)
                · {{ $prescription->doctor->doctorProfile->qualification }}
            @endif
        </div>
        @if($prescription->doctor->doctorProfile?->registration_number)
        <div style="font-size:7pt;color:#8fa09e;margin-top:2pt">
            Reg: {{ $prescription->doctor->doctorProfile->registration_number }}
        </div>
        @endif
    </div>
</div>

<div class="disclaimer">
    This is a computer-generated prescription issued by a registered medical practitioner.
    Please store safely. Do not self-medicate beyond the prescribed duration.
</div>

</body>
</html>
