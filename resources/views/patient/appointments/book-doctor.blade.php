@extends('layouts.patient')
@section('title', 'Book Appointment')
@section('page-title', 'Book an Appointment')

@section('content')
<div class="fade-in">

{{-- Search + filter bar --}}
<form method="GET" action="{{ route('patient.appointments.book') }}"
      style="display:flex;gap:10px;align-items:center;margin-bottom:14px;flex-wrap:wrap">
    <div style="position:relative;flex:1;min-width:220px">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
             style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--txt-lt);pointer-events:none">
            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <input type="text" name="q" value="{{ $search }}" placeholder="Search doctor, clinic or specialization…"
               style="width:100%;padding:.6rem .85rem .6rem 2.2rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif"
               onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'">
    </div>

    {{-- City filter --}}
    <div style="position:relative;min-width:160px">
        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
             style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--txt-lt);pointer-events:none;z-index:1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <select name="city" onchange="this.form.submit()"
                style="width:100%;padding:.6rem .85rem .6rem 2.1rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;appearance:none;cursor:pointer"
                onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'">
            <option value="all" @selected($cityFilter === 'all')>All Cities</option>
            @foreach($cities as $city)
            <option value="{{ $city }}" @selected($activeCity === $city)>{{ $city }}</option>
            @endforeach
            @if($patientCity && !$cities->contains($patientCity))
            <option value="{{ $patientCity }}" @selected($activeCity === $patientCity)>{{ $patientCity }}</option>
            @endif
        </select>
    </div>

    <button type="submit" style="padding:.6rem 16px;background:var(--plum);color:#fff;border:none;border-radius:10px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif">
        Search
    </button>
    @if($search || $specialization || $cityFilter === 'all')
    <a href="{{ route('patient.appointments.book') }}"
       style="font-size:.8rem;color:var(--txt-lt);text-decoration:none;padding:6px 10px"
       onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
        ✕ Clear
    </a>
    @endif
</form>

{{-- Location context banner --}}
@if($activeCity)
<div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;padding:8px 14px;background:var(--parch);border:1px solid var(--warm-bd);border-left:3px solid var(--plum);border-radius:10px;font-size:.8rem">
    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--plum);flex-shrink:0">
        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <span style="color:var(--txt-md)">
        Showing doctors in <strong style="color:var(--txt)">{{ $activeCity }}</strong>
        @if($activeCity === $patientCity && $cityFilter === null)
        <span style="color:var(--txt-lt)"> · your registered city</span>
        @endif
    </span>
    <a href="{{ route('patient.appointments.book', array_merge(request()->only('q','spec'), ['city' => 'all'])) }}"
       style="margin-left:auto;font-size:.775rem;color:var(--plum);text-decoration:none;white-space:nowrap;font-weight:500"
       onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
        Search other cities →
    </a>
</div>
@elseif(!$patientCity)
<div style="margin-bottom:16px;padding:8px 14px;background:#f9f4e840;border:1px dashed var(--warm-bd);border-radius:10px;font-size:.8rem;color:var(--txt-lt)">
    Add your city in <a href="{{ route('profile.edit') }}" style="color:var(--plum);text-decoration:none;font-weight:500">your profile</a> to see local doctors first.
</div>
@endif

{{-- Specialization filter chips --}}
@php
$cityParam = $cityFilter ?? $patientCity;
@endphp
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:22px">
    <a href="{{ route('patient.appointments.book', array_filter(['q' => $search, 'city' => $cityParam], fn($v) => $v !== null)) }}"
       style="font-size:.78rem;font-weight:500;padding:5px 13px;border-radius:20px;border:1.5px solid;text-decoration:none;transition:all .15s;
              {{ !$specialization ? 'background:var(--plum);color:#fff;border-color:var(--plum)' : 'background:transparent;color:var(--txt-md);border-color:var(--warm-bd)' }}">
        All
    </a>
    @foreach($specializations->take(12) as $spec)
    <a href="{{ route('patient.appointments.book', array_filter(['q' => $search, 'spec' => $spec, 'city' => $cityParam], fn($v) => $v !== null)) }}"
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
            @include('patient.appointments._doctor-card', ['doc' => $doc, 'isMyDoctor' => true, 'isTopDoctor' => false])
        @endforeach
    </div>
</div>
@endif

{{-- Top Doctors in [City] section --}}
@if($topDoctors->isNotEmpty())
<div style="margin-bottom:24px">
    <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px;display:flex;align-items:center;gap:8px">
        <span style="width:8px;height:8px;border-radius:50%;background:#e8a020;display:inline-block"></span>
        Top Doctors in {{ $activeCity }}
        <span style="font-size:.72rem;font-weight:400;color:var(--txt-lt);font-family:'Plus Jakarta Sans',sans-serif">· by completed appointments</span>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
        @foreach($topDoctors as $doc)
            @include('patient.appointments._doctor-card', ['doc' => $doc, 'isMyDoctor' => false, 'isTopDoctor' => true])
        @endforeach
    </div>
</div>
@endif

{{-- All / remaining doctors --}}
@if($otherDoctors->isNotEmpty())
<div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:12px;display:flex;align-items:center;gap:8px">
    <span style="width:8px;height:8px;border-radius:50%;background:var(--warm-bd);display:inline-block"></span>
    @if($topDoctors->isNotEmpty() || $myDoctors->isNotEmpty())
        More Doctors{{ $activeCity ? ' in '.$activeCity : '' }}
    @else
        {{ $activeCity ? 'Doctors in '.$activeCity : 'All Doctors' }}
    @endif
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
    @foreach($otherDoctors as $doc)
        @include('patient.appointments._doctor-card', ['doc' => $doc, 'isMyDoctor' => false, 'isTopDoctor' => false])
    @endforeach
</div>
@endif

@if($myDoctors->isEmpty() && $topDoctors->isEmpty() && $otherDoctors->isEmpty())
<div style="text-align:center;padding:52px 24px;color:var(--txt-lt)">
    <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt-md);margin-bottom:6px">No doctors found</div>
    @if($activeCity)
    <p style="font-size:.8125rem">No verified doctors in <strong>{{ $activeCity }}</strong> yet.</p>
    <a href="{{ route('patient.appointments.book', array_merge(request()->only('q','spec'), ['city' => 'all'])) }}"
       style="display:inline-block;margin-top:10px;font-size:.8rem;color:var(--plum);text-decoration:none;font-weight:500">
        Search all cities →
    </a>
    @else
    <p style="font-size:.8125rem">Try a different search term or clear your filters.</p>
    @endif
</div>
@endif

</div>
@endsection
