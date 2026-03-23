@extends('layouts.doctor')
@section('title', 'Send via WhatsApp')
@section('page-title')
    <a href="{{ route('doctor.prescriptions.show', $prescription) }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">{{ $prescription->prescription_number }}</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Send via WhatsApp
@endsection

@section('content')
@php
    $patient = $prescription->patient;
    $name    = $prescription->family_member_id
        ? $patient->familyMembers->find($prescription->family_member_id)?->full_name
        : ($patient->profile?->full_name ?? 'Patient');
@endphp

<div style="max-width:580px;margin:0 auto">

    {{-- WhatsApp preview card --}}
    <div style="background:var(--white);border:1px solid var(--warm-bd);border-radius:16px;overflow:hidden;margin-bottom:20px">

        {{-- Header --}}
        <div style="background:#128C7E;padding:16px 20px;display:flex;align-items:center;gap:12px">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <div>
                <div style="font-size:1rem;font-weight:600;color:#fff">WhatsApp Prescription Send</div>
                <div style="font-size:.75rem;color:rgba(255,255,255,.7)">Naumah Clinic Health · Automated Prescription Delivery</div>
            </div>
        </div>

        {{-- Recipient --}}
        <div style="padding:16px 20px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;gap:12px">
            <div style="width:42px;height:42px;border-radius:50%;background:var(--ink);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:1rem;flex-shrink:0">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:.9rem;font-weight:600;color:var(--txt)">{{ $name }}</div>
                <div style="font-size:.8rem;color:var(--txt-lt)">
                    {{ $patient->country_code }} {{ $patient->mobile_number }}
                    <span style="background:#dcf8c6;color:#128C7E;font-size:.7rem;font-weight:600;padding:1px 7px;border-radius:20px;margin-left:6px">WhatsApp</span>
                </div>
            </div>
        </div>

        {{-- Message preview (mock WhatsApp bubble) --}}
        <div style="padding:16px 20px;background:#e5ddd5">
            <div style="max-width:85%;background:#fff;border-radius:0 12px 12px 12px;padding:12px 14px;box-shadow:0 1px 2px rgba(0,0,0,.1);position:relative">
                <div style="font-size:.72rem;font-weight:700;color:#128C7E;margin-bottom:5px">
                    Dr. {{ $prescription->doctor->profile?->full_name }} — Naumah Clinic
                </div>
                <div style="font-size:.875rem;color:#111;line-height:1.6">
                    Hello <strong>{{ $name }}</strong>,<br><br>
                    Your prescription <strong>{{ $prescription->prescription_number }}</strong> dated
                    {{ $prescription->prescribed_date->format('d M Y') }} has been issued by
                    Dr. {{ $prescription->doctor->profile?->full_name }}.<br><br>
                    @if($prescription->medicines->count() > 0)
                    <strong>Medicines prescribed:</strong><br>
                    @foreach($prescription->medicines->take(4) as $i => $med)
                    {{ $i+1 }}. {{ $med->medicine_name }}{{ $med->dosage ? ' '.$med->dosage : '' }}{{ $med->frequency ? ' — '.$med->frequency : '' }}<br>
                    @endforeach
                    @if($prescription->medicines->count() > 4)
                    <em>+ {{ $prescription->medicines->count()-4 }} more medicines</em><br>
                    @endif
                    @endif
                    @if($prescription->follow_up_date)
                    <br><strong>Follow-up:</strong> {{ $prescription->follow_up_date->format('d M Y') }}
                    @endif
                </div>
                <div style="font-size:.72rem;color:#999;text-align:right;margin-top:6px">
                    {{ now()->format('h:i A') }} ✓✓
                </div>
                @if($prescription->pdf_path)
                <div style="margin-top:10px;background:#f5f5f5;border-radius:8px;padding:10px;display:flex;align-items:center;gap:8px">
                    <div style="width:32px;height:32px;background:#e74c3c;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/><path d="M14 2v6h6"/></svg>
                    </div>
                    <div>
                        <div style="font-size:.8rem;font-weight:600;color:#111">{{ $prescription->prescription_number }}.pdf</div>
                        <div style="font-size:.7rem;color:#999">Prescription PDF · Tap to download</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- PDF status --}}
        <div style="padding:12px 20px;border-top:1px solid var(--warm-bd);display:flex;align-items:center;gap:8px">
            @if($prescription->pdf_path)
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span style="font-size:.8rem;color:#16a34a;font-weight:500">PDF ready — will be attached to the message</span>
            @else
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span style="font-size:.8rem;color:var(--amber);font-weight:500">PDF not yet generated — will be created before sending</span>
            @endif
        </div>
    </div>

    {{-- Already sent warning --}}
    @if($prescription->is_sent_whatsapp)
    <div style="background:#fff8e6;border:1.5px solid #f0c050;border-radius:12px;padding:14px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:flex-start">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--amber)" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <div style="font-size:.875rem;font-weight:600;color:#8a6000">Already Sent</div>
            <div style="font-size:.8rem;color:#a07020;margin-top:2px">
                This prescription was already sent on {{ $prescription->whatsapp_sent_at?->format('d M Y \a\t h:i A') }}.
                Sending again will deliver a duplicate to the patient.
            </div>
        </div>
    </div>
    @endif

    @if(session('error') || $errors->has('whatsapp'))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:.875rem;color:#991b1b">
        {{ session('error') ?? $errors->first('whatsapp') }}
    </div>
    @endif

    {{-- Action buttons --}}
    <div style="display:flex;gap:10px;justify-content:flex-end">
        <a href="{{ route('doctor.prescriptions.show', $prescription) }}"
           style="padding:10px 18px;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;font-weight:500;color:var(--txt-md);text-decoration:none">
            Cancel
        </a>
        <form method="POST" action="{{ route('doctor.prescriptions.send-whatsapp.send', $prescription) }}">
            @csrf
            <button type="submit"
                    style="display:flex;align-items:center;gap:8px;padding:10px 22px;background:#25D366;color:#fff;border:none;border-radius:10px;font-size:.9rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;box-shadow:0 2px 10px rgba(37,211,102,.3)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                {{ $prescription->is_sent_whatsapp ? 'Resend via WhatsApp' : 'Send Prescription via WhatsApp' }}
            </button>
        </form>
    </div>
</div>
@endsection
