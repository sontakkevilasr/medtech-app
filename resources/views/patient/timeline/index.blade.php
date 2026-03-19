@extends('layouts.patient')
@section('title', 'My Timelines')
@section('page-title', 'Care Timelines')

@push('styles')
<style>
.tl-card { transition: box-shadow .18s, transform .18s; }
.tl-card:hover { box-shadow: 0 6px 28px rgba(74,55,96,.13); transform: translateY(-2px); }

.spec-badge {
    font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em;
    padding: 2px 9px; border-radius: 20px;
}

.progress-bar-track {
    height: 6px; border-radius: 3px; background: var(--warm-bd); overflow: hidden;
}
.progress-bar-fill {
    height: 100%; border-radius: 3px; transition: width .5s ease;
}
@verbatim
@keyframes fadeSlide { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
@endverbatim
.fade-slide { animation: fadeSlide .25s ease; }
</style>
@endpush

@section('content')
@php
$specMeta = [
    'obstetrics' => ['color'=>'#c0737a','bg'=>'#fce7ef','label'=>'Pregnancy'],
    'pediatrics' => ['color'=>'#3d7a8a','bg'=>'#e8f5f9','label'=>'Paediatric'],
    'ivf'        => ['color'=>'#8a6aaa','bg'=>'#f4f0fa','label'=>'IVF'],
    'dental'     => ['color'=>'#3d7a6e','bg'=>'#eef5f3','label'=>'Dental'],
    'cardiology' => ['color'=>'#c98a3a','bg'=>'#fdf5e8','label'=>'Cardiology'],
    'oncology'   => ['color'=>'#6b7280','bg'=>'#f3f4f6','label'=>'Oncology'],
];
$allTimelines = $selfTimelines->concat($memberTimelines);
@endphp

<div class="fade-slide">

{{-- Empty state --}}
@if($allTimelines->isEmpty())
<div class="panel" style="padding:52px 24px;text-align:center;color:var(--txt-lt)">
    <div style="font-size:3rem;margin-bottom:14px">📅</div>
    <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt-md);margin-bottom:6px">No care timelines yet</div>
    <p style="font-size:.875rem;max-width:380px;margin:0 auto;line-height:1.6">
        Your doctor will assign a care timeline when you start treatment —
        like a pregnancy tracker, vaccination schedule, or IVF journey.
    </p>
</div>
@else

{{-- Self timelines --}}
@if($selfTimelines->isNotEmpty())
<div style="margin-bottom:28px">
    <div style="font-family:'Lora',serif;font-size:1.05rem;font-weight:500;color:var(--txt);margin-bottom:14px">
        Your Timelines
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
        @foreach($selfTimelines as $pt)
            @include('patient.timeline._card', ['pt' => $pt, 'specMeta' => $specMeta])
        @endforeach
    </div>
</div>
@endif

{{-- Family member timelines --}}
@if($memberTimelines->isNotEmpty())
<div>
    <div style="font-family:'Lora',serif;font-size:1.05rem;font-weight:500;color:var(--txt);margin-bottom:14px">
        Family Member Timelines
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
        @foreach($memberTimelines as $pt)
            @include('patient.timeline._card', ['pt' => $pt, 'specMeta' => $specMeta])
        @endforeach
    </div>
</div>
@endif

@endif
</div>
@endsection
