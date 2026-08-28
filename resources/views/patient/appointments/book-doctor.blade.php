@extends('layouts.patient')
@section('title', 'Book Appointment')
@section('page-title', 'Book an Appointment')

@section('content')
<div class="fade-in">

{{-- Search + filter bar --}}
<form method="GET" action="{{ route('patient.appointments.book') }}"
      style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
    <div style="position:relative;flex:1;min-width:220px">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
             style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--txt-lt);pointer-events:none">
            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search doctor, clinic or specialization…"
               style="width:100%;padding:.6rem .85rem .6rem 2.2rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif"
               onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'">
    </div>
    <button type="submit" style="padding:.6rem 16px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
        Search
    </button>
    @if($search || $specialization)
    <a href="{{ route('patient.appointments.book') }}"
       style="font-size:.8rem;color:var(--txt-lt);text-decoration:none;padding:6px 10px"
       onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
        ✕ Clear
    </a>
    @endif
</form>

{{-- Specialization filter chips --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:22px">
    <a href="{{ route('patient.appointments.book', array_merge(request()->only('q'), [])) }}"
       style="font-size:.78rem;font-weight:500;padding:5px 13px;border-radius:20px;border:1.5px solid;text-decoration:none;transition:all .15s;
              {{ !$specialization ? 'background:var(--plum);color:#fff;border-color:var(--plum)' : 'background:transparent;color:var(--txt-md);border-color:var(--warm-bd)' }}">
        All
    </a>
    @foreach($specializations->take(12) as $spec)
    <a href="{{ route('patient.appointments.book', array_merge(request()->only('q'), ['spec' => $spec])) }}"
       style="font-size:.78rem;font-weight:500;padding:5px 13px;border-radius:20px;border:1.5px solid;text-decoration:none;transition:all .15s;
              {{ $specialization === $spec ? 'background:var(--plum);color:#fff;border-color:var(--plum)' : 'background:transparent;color:var(--txt-md);border-color:var(--warm-bd)' }}">
        {{ $spec }}
    </a>
    @endforeach
</div>

{{-- My doctors section --}}
@if($myDoctors->isNotEmpty())
<div style="margin-bottom:24px">
    <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <span style="width:8px;height:8px;border-radius:50%;background:var(--sage);display:inline-block"></span>
        My Doctors
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
        @foreach($myDoctors as $doc)
            @include('patient.appointments._doctor-card', ['doc' => $doc, 'isMyDoctor' => true])
        @endforeach
    </div>
</div>
@endif

{{-- All doctors --}}
@if($otherDoctors->isNotEmpty())
<div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px;display:flex;align-items:center;gap:8px">
    <span style="width:8px;height:8px;border-radius:50%;background:var(--warm-bd);display:inline-block"></span>
    {{ $myDoctors->isNotEmpty() ? 'Other Doctors' : 'All Doctors' }}
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
    @foreach($otherDoctors as $doc)
        @include('patient.appointments._doctor-card', ['doc' => $doc, 'isMyDoctor' => false])
    @endforeach
</div>
@endif

@if($myDoctors->isEmpty() && $otherDoctors->isEmpty())
<div style="text-align:center;padding:52px 24px;color:var(--txt-lt)">
    <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt-md);margin-bottom:6px">No doctors found</div>
    <p style="font-size:.8125rem">Try a different search term or clear your filters.</p>
</div>
@endif

</div>
@endsection
