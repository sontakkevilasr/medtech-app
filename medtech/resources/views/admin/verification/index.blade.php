@extends('layouts.admin')
@section('title', 'Doctor Verification')
@section('page-title', 'Doctor Verification')

@section('content')
<div class="fade-in">

{{-- Tabs --}}
<div style="display:flex;gap:2px;border-bottom:2px solid var(--bd);margin-bottom:20px">
    @foreach(['pending'=>"Pending ({$counts['pending']})", 'verified'=>"Verified ({$counts['verified']})"] as $t => $lbl)
    <a href="{{ route('admin.verification.pending', ['tab'=>$t]) }}"
       style="padding:10px 18px;font-size:.875rem;font-weight:500;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;
              {{ $tab===$t ? 'color:var(--accent);border-bottom-color:var(--accent);font-weight:600' : 'color:var(--txt-lt)' }}">
        {{ $lbl }}
    </a>
    @endforeach
</div>

@if($doctors->isEmpty())
<div style="text-align:center;padding:52px;color:var(--txt-lt)">
    <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt-md);margin-bottom:6px">
        {{ $tab==='pending' ? '🎉 All doctors are verified!' : 'No verified doctors yet.' }}
    </div>
</div>
@else
<div class="card" style="overflow:hidden">
    <table class="admin-table">
        <thead><tr>
            <th>Doctor</th><th>Specialization</th><th>Registration</th><th>Joined</th>
            <th>{{ $tab==='pending' ? 'Status' : 'Verified' }}</th><th style="text-align:right">Action</th>
        </tr></thead>
        <tbody>
        @foreach($doctors as $doc)
        @php
            $name = $doc->profile?->full_name ?? 'Unknown';
            $dp   = $doc->doctorProfile;
        @endphp
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:9px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:.875rem;font-weight:700;color:#6366f1;flex-shrink:0">
                        {{ strtoupper(substr($name,0,1)) }}
                    </div>
                    <div>
                        <div style="font-weight:500;color:var(--txt)">Dr. {{ $name }}</div>
                        <div style="font-size:.72rem;color:var(--txt-lt)">{{ $doc->country_code }} {{ $doc->mobile_number }}</div>
                    </div>
                </div>
            </td>
            <td style="color:var(--txt-md)">
                {{ $dp?->specialization ?? '—' }}
                @if($dp?->qualification)<div style="font-size:.72rem;color:var(--txt-lt)">{{ $dp->qualification }}</div>@endif
            </td>
            <td style="font-family:monospace;font-size:.82rem;color:var(--txt-md)">
                {{ $dp?->registration_number ?? '—' }}
                @if($dp?->registration_council)<div style="font-size:.7rem;color:var(--txt-lt)">{{ $dp->registration_council }}</div>@endif
            </td>
            <td style="font-size:.78rem;color:var(--txt-lt)">{{ $doc->created_at->format('d M Y') }}</td>
            <td>
                @if($tab==='pending')
                    @if($dp?->rejection_reason)
                    <span class="badge badge-red">Rejected</span>
                    @else
                    <span class="badge badge-yellow">Awaiting Review</span>
                    @endif
                @else
                <span class="badge badge-green">✓ Verified</span>
                @if($dp?->verified_at)
                <div style="font-size:.7rem;color:var(--txt-lt);margin-top:2px">{{ \Carbon\Carbon::parse($dp->verified_at)->format('d M Y') }}</div>
                @endif
                @endif
            </td>
            <td style="text-align:right">
                <a href="{{ route('admin.verification.show', $doc->id) }}" class="btn btn-primary" style="font-size:.75rem;padding:5px 12px">
                    {{ $tab==='pending' ? 'Review' : 'View' }} →
                </a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>

    @if($doctors->hasPages())
    <div class="pager">
        <span>Showing {{ $doctors->firstItem() }}–{{ $doctors->lastItem() }} of {{ $doctors->total() }}</span>
        <div style="display:flex;gap:4px">
            @if(!$doctors->onFirstPage())<a href="{{ $doctors->previousPageUrl() }}">← Prev</a>@endif
            @if($doctors->hasMorePages())<a href="{{ $doctors->nextPageUrl() }}">Next →</a>@endif
        </div>
    </div>
    @endif
</div>
@endif
</div>
@endsection
