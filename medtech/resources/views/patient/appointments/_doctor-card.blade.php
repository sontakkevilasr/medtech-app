@php
    $name     = $doc->profile?->full_name ?? 'Doctor';
    $initials = strtoupper(implode('', array_map(fn($x) => $x[0], array_slice(explode(' ', $name), 0, 2))));
    $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
    $color    = $colors[$doc->id % count($colors)];
    $dp       = $doc->doctorProfile;
@endphp
<div style="background:var(--cream);border:1.5px solid var(--warm-bd);border-radius:14px;overflow:hidden;transition:box-shadow .15s,border-color .15s"
     onmouseover="this.style.boxShadow='0 4px 20px rgba(74,55,96,.12)';this.style.borderColor='var(--plum-lt)'"
     onmouseout="this.style.boxShadow='none';this.style.borderColor='var(--warm-bd)'">

    {{-- Top bar --}}
    <div style="background:{{ $color }}18;padding:16px 18px 12px;border-bottom:1px solid {{ $color }}22">
        <div style="display:flex;gap:12px;align-items:flex-start">
            <div style="width:44px;height:44px;border-radius:11px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.9375rem;font-weight:600;color:var(--txt);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    Dr. {{ $name }}
                </div>
                <div style="font-size:.78rem;font-weight:600;color:{{ $color }};margin-top:1px">
                    {{ $dp?->specialization ?? 'General Physician' }}
                </div>
                @if($dp?->qualification)
                <div style="font-size:.72rem;color:var(--txt-lt)">{{ $dp->qualification }}</div>
                @endif
            </div>
            @if($isMyDoctor)
            <span style="font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:20px;background:{{ $color }}20;color:{{ $color }};white-space:nowrap;border:1px solid {{ $color }}44">
                My Doctor
            </span>
            @endif
        </div>
    </div>

    {{-- Details --}}
    <div style="padding:12px 18px">
        @if($dp?->clinic_name)
        <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--txt-md);margin-bottom:5px">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $dp->clinic_name }}@if($dp->clinic_city), {{ $dp->clinic_city }}@endif
        </div>
        @endif
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px">
            @if($dp?->experience_years)
            <span style="font-size:.75rem;color:var(--txt-lt)">{{ $dp->experience_years }} yrs exp</span>
            @endif
            @if($dp?->consultation_fee)
            <span style="font-size:.75rem;font-weight:600;color:var(--txt-md)">₹{{ number_format($dp->consultation_fee) }}</span>
            @endif
            @if($dp?->languages_spoken)
            <span style="font-size:.75rem;color:var(--txt-lt)">{{ implode(', ', array_slice($dp->languages_spoken, 0, 2)) }}</span>
            @endif
        </div>

        {{-- Available days this week --}}
        @php
            $slots    = $dp?->available_slots ?? [];
            $dayAbbr  = ['mon','tue','wed','thu','fri','sat','sun'];
            $dayFull  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
            $todayIdx = now()->dayOfWeek === 0 ? 6 : now()->dayOfWeek - 1; // 0=Mon
        @endphp
        <div style="display:flex;gap:4px;margin-bottom:14px">
            @foreach($dayAbbr as $i => $d)
            @php $active = !empty($slots[$dayFull[$i]]); @endphp
            <div style="flex:1;text-align:center;padding:4px 0;border-radius:6px;font-size:.65rem;font-weight:600;
                        {{ $active ? 'background:'.$color.'18;color:'.$color.';border:1px solid '.$color.'33' : 'background:var(--parch);color:var(--txt-lt);border:1px solid transparent' }}">
                {{ strtoupper($d) }}
            </div>
            @endforeach
        </div>

        <a href="{{ route('patient.appointments.book.slots', $doc->id) }}"
           style="display:block;text-align:center;padding:9px;background:var(--plum);color:#fff;border-radius:10px;font-size:.875rem;font-weight:600;text-decoration:none;transition:opacity .15s"
           onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
            Book Appointment →
        </a>
    </div>
</div>
