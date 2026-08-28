@extends('layouts.patient')
@section('title', 'Access History')
@section('page-title')
    <a href="{{ route('patient.access.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Data Access</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Full History
@endsection

@section('content')
<div class="fade-in">

{{-- Tabs --}}
<div style="display:flex;gap:2px;border-bottom:2px solid var(--warm-bd);margin-bottom:20px;flex-wrap:wrap">
    @php
        $tabs = [
            'all'      => 'All',
            'approved' => 'Approved',
            'pending'  => 'Pending',
            'denied'   => 'Denied',
            'expired'  => 'Expired',
        ];
    @endphp
    @foreach($tabs as $t => $lbl)
    @php $cnt = $counts[$t] ?? ($t==='all' ? array_sum($counts) : 0); @endphp
    <a href="{{ route('patient.access.history', ['tab'=>$t]) }}"
       style="padding:9px 16px;font-size:.875rem;font-weight:500;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .15s;
              {{ $tab===$t ? 'color:var(--plum);border-bottom-color:var(--plum);font-weight:600' : 'color:var(--txt-lt)' }}"
       onmouseover="if('{{$tab}}' !== '{{$t}}') this.style.color='var(--txt)'" onmouseout="if('{{$tab}}' !== '{{$t}}') this.style.color='var(--txt-lt)'">
        {{ $lbl }}@if($cnt) <span style="font-size:.72rem;color:inherit;opacity:.7">({{ $cnt }})</span>@endif
    </a>
    @endforeach
    <a href="{{ route('patient.access.index') }}"
       style="margin-left:auto;display:flex;align-items:center;gap:5px;font-size:.78rem;color:var(--txt-lt);text-decoration:none;padding:9px 4px;align-self:flex-end;margin-bottom:4px"
       onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
        ← Back to Access Hub
    </a>
</div>

@if($requests->isEmpty())
<div style="text-align:center;padding:52px 24px;color:var(--txt-lt)">
    <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt-md);margin-bottom:5px">No access requests found</div>
    <p style="font-size:.8rem">{{ $tab !== 'all' ? 'Try a different filter.' : 'No doctors have requested access to your records yet.' }}</p>
</div>
@else

<div class="panel" style="padding:0;overflow:hidden">
    @foreach($requests as $req)
    @php
        $drName   = $req->doctor?->profile?->full_name ?? 'Doctor';
        $initials = strtoupper(implode('', array_map(fn($x)=>$x[0], array_slice(explode(' ',$drName),0,2))));
        $dp       = $req->doctor?->doctorProfile;
        $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
        $color    = $colors[$req->doctor_user_id % count($colors)];
        $stCfg    = match($req->status) {
            'approved' => ['bg'=>'#e8f5f3','color'=>'#1a7a6a','dot'=>'#10b981','label'=>'Approved'],
            'pending'  => ['bg'=>'#fef9ec','color'=>'#b45309','dot'=>'#f59e0b','label'=>'Pending'],
            'denied'   => ['bg'=>'#fef2f2','color'=>'#dc2626','dot'=>'#ef4444','label'=>'Denied'],
            'expired'  => ['bg'=>'#f3f4f6','color'=>'#6b7280','dot'=>'#9ca3af','label'=>'Expired'],
            default    => ['bg'=>'#f3f4f6','color'=>'#6b7280','dot'=>'#9ca3af','label'=>ucfirst($req->status)],
        };
    @endphp
    <div style="display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--warm-bd);transition:background .1s"
         onmouseover="this.style.background='#faf8f4'" onmouseout="this.style.background='transparent'">

        {{-- Avatar --}}
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:700;color:#fff;flex-shrink:0">
            {{ $initials }}
        </div>

        {{-- Info --}}
        <div style="flex:1;min-width:0">
            <div style="font-weight:600;font-size:.875rem;color:var(--txt);margin-bottom:2px">
                Dr. {{ $drName }}
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;font-size:.75rem;color:var(--txt-lt)">
                @if($dp?->specialization)<span>{{ $dp->specialization }}</span>@endif
                @if($req->familyMember)
                <span style="display:flex;align-items:center;gap:3px">
                    <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    For {{ $req->familyMember->full_name }}
                </span>
                @endif
                <span>Requested {{ $req->created_at->format('d M Y, h:i A') }}</span>
                @if($req->approved_at)
                <span>Approved {{ $req->approved_at->format('d M Y') }}</span>
                @endif
                @if($req->access_expires_at && $req->status === 'approved')
                @php $daysLeft = now()->diffInDays($req->access_expires_at, false); @endphp
                <span style="color:{{ $daysLeft <= 5 ? '#b45309' : 'inherit' }};font-weight:{{ $daysLeft <= 5 ? '600' : '400' }}">
                    {{ $daysLeft > 0 ? 'Expires in '.$daysLeft.'d' : 'Expired' }}
                </span>
                @endif
            </div>
        </div>

        {{-- Status --}}
        <span style="flex-shrink:0;font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:20px;background:{{ $stCfg['bg'] }};color:{{ $stCfg['color'] }};display:flex;align-items:center;gap:4px">
            <span style="width:5px;height:5px;border-radius:50%;background:{{ $stCfg['dot'] }};display:inline-block"></span>
            {{ $stCfg['label'] }}
        </span>

        {{-- Context actions --}}
        <div style="flex-shrink:0;display:flex;gap:5px">
            @if($req->status === 'pending')
            <form method="POST" action="{{ route('patient.access.approve', $req) }}">
                @csrf
                <button type="submit"
                        style="font-size:.72rem;font-weight:600;padding:5px 10px;background:var(--sage);color:#fff;border:none;border-radius:8px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
                    Approve
                </button>
            </form>
            <form method="POST" action="{{ route('patient.access.deny', $req) }}">
                @csrf
                <button type="submit"
                        style="font-size:.72rem;font-weight:600;padding:5px 10px;border:1.5px solid #fecaca;background:transparent;color:#dc2626;border-radius:8px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
                    Deny
                </button>
            </form>
            @elseif($req->status === 'approved' && $req->access_expires_at > now())
            <form method="POST" action="{{ route('patient.access.revoke', $req->doctor_user_id) }}"
                  onsubmit="return confirm('Revoke access for Dr. {{ addslashes($drName) }}?')">
                @csrf
                <button type="submit"
                        style="font-size:.72rem;font-weight:500;padding:5px 10px;border:1.5px solid #fecaca;background:transparent;color:#dc2626;border-radius:8px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .12s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    Revoke
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if($requests->hasPages())
<div style="display:flex;justify-content:center;gap:6px;margin-top:16px">
    @if(!$requests->onFirstPage())
        <a href="{{ $requests->previousPageUrl() }}" style="padding:7px 14px;border-radius:9px;border:1.5px solid var(--warm-bd);color:var(--txt-md);text-decoration:none;font-size:.8rem">← Prev</a>
    @endif
    @if($requests->hasMorePages())
        <a href="{{ $requests->nextPageUrl() }}" style="padding:7px 14px;border-radius:9px;border:1.5px solid var(--warm-bd);color:var(--txt-md);text-decoration:none;font-size:.8rem">Next →</a>
    @endif
</div>
@endif
@endif

</div>
@endsection
