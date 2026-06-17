@extends('layouts.patient')
@section('title', 'Pick a Slot')
@section('page-title')
    <a href="{{ route('patient.appointments.book') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Book Appointment</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Dr. {{ preg_replace('/^Dr\.?\s*/i', '', $doctor->profile?->full_name ?? '') }}
@endsection

@section('content')
@php
    $dp       = $doctor->doctorProfile;
    $name     = preg_replace('/^Dr\.?\s*/i', '', $doctor->profile?->full_name ?? 'Doctor');
    $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
    $color    = $colors[$doctor->id % count($colors)];
    $slots    = $dp?->available_slots ?? [];
    $dayFull  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    $availDays = array_values(array_filter($dayFull, fn($d) => !empty($slots[$d])));
@endphp

@php
$memberUrlMap = $familyMembers->mapWithKeys(
    fn($m) => [$m->id => route('patient.appointments.store.member', [$doctor->id, $m->id])]
)->toArray();
$memberList = $familyMembers->map(
    fn($m) => [
        'id'        => $m->id,
        'name'      => $m->full_name,
        'relation'  => ucfirst($m->relation),
        'photo_url' => $m->profile_photo ? \Storage::disk('public')->url($m->profile_photo) : null,
    ]
)->values()->toArray();
@endphp
<div x-data="bookingFlow({
    doctorId:        {{ $doctor->id }},
    slotsUrl:        '{{ route('patient.appointments.slots', $doctor->id) }}',
    datesUrl:        '{{ route('patient.appointments.dates', $doctor->id) }}',
    availDays:       {{ json_encode($availDays) }},
    fee:             {{ $dp?->consultation_fee ?? 0 }},
    selfUrl:         '{{ route('patient.appointments.store', $doctor->id) }}',
    memberUrls:      {{ Js::from($memberUrlMap) }},
    members:         {{ Js::from($memberList) }},
    initialMemberId: {{ $familyMember?->id ?? 'null' }},
})" x-init="init()" class="fade-in">

<div style="display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start">

{{-- ══ LEFT: Calendar + Slot Grid ═══════════════════════════════════════════ --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Doctor summary bar --}}
    <div class="panel" style="padding:16px 22px">
        <div style="display:flex;align-items:center;gap:14px">
            <x-avatar name="{{ $name }}" :photo="$doctor->profile?->profile_photo"
                       :size="46" :radius="12" :bg="$color" font-size="1rem" />
            <div style="flex:1;min-width:0">
                <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt)">Dr. {{ $name }}</div>
                <div style="font-size:.8rem;color:var(--txt-md)">{{ $dp?->specialization }}</div>
                @if($dp?->clinic_name)
                <div style="font-size:.75rem;color:var(--txt-lt)">{{ $dp->clinic_name }}{{ $dp?->clinic_city ? ', ' . $dp->clinic_city : '' }}</div>
                @endif
            </div>
            @if($dp?->consultation_fee)
            <div style="text-align:right;flex-shrink:0">
                <div style="font-family:'Lora',serif;font-size:1.4rem;font-weight:500;color:var(--txt)">₹{{ number_format($dp->consultation_fee) }}</div>
                <div style="font-size:.72rem;color:var(--txt-lt)">Consultation fee</div>
            </div>
            @endif
        </div>
        <template x-if="selectedMemberId !== null">
            <div style="margin-top:10px;padding:8px 12px;background:var(--parch);border-radius:8px;font-size:.8125rem;color:var(--txt-md);display:flex;align-items:center;gap:6px">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Booking for
                <strong style="color:var(--txt)" x-text="members.find(m => m.id === selectedMemberId)?.name"></strong>
                <span style="color:var(--txt-lt)" x-text="'(' + (members.find(m => m.id === selectedMemberId)?.relation ?? '') + ')'"></span>
            </div>
        </template>
    </div>

    {{-- ── Calendar ──────────────────────────────────────────────────────── --}}
    <div class="panel" style="overflow:hidden">

        {{-- Month navigation --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 22px 16px">
            <button type="button" x-on:click="prevMonth()"
                    :disabled="isCurrentMonth"
                    class="cal-nav-btn"
                    :style="isCurrentMonth ? 'opacity:.28;cursor:not-allowed;pointer-events:none' : ''">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <div style="display:flex;align-items:center;gap:10px">
                <span style="font-family:'Lora',serif;font-size:1.15rem;font-weight:500;color:var(--txt)" x-text="monthLabel"></span>
                <div x-show="loadingCalendar"
                     style="width:14px;height:14px;border:2px solid var(--warm-bd);border-top-color:var(--plum);border-radius:50%;animation:spin .55s linear infinite;flex-shrink:0"></div>
            </div>

            <button type="button" x-on:click="nextMonth()" class="cal-nav-btn">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Calendar grid — headers + cells share ONE grid so columns always align --}}
        <div style="padding:0 18px 20px">
            <div class="cal-grid">

                {{-- ── Day-of-week headers (PHP, static) ── --}}
                @foreach([['Mon',false],['Tue',false],['Wed',false],['Thu',false],['Fri',false],['Sat',true],['Sun',true]] as [$dh,$wknd])
                <div class="cal-dow {{ $wknd ? 'cal-dow--wknd' : '' }}">{{ $dh }}</div>
                @endforeach

                {{-- ── Skeleton cells while loading ── --}}
                <template x-if="loadingCalendar">
                    <template x-for="n in 35" :key="'sk'+n">
                        <div class="cal-skel"></div>
                    </template>
                </template>

                {{-- ── Actual date cells ── --}}
                <template x-if="!loadingCalendar">
                    <template x-for="cell in calendarCells" :key="cell.key">
                        <div>
                            {{-- Leading empty spacer --}}
                            <div x-show="!cell.date" class="cal-cell cal-cell--blank"></div>
                            {{-- Date button --}}
                            <button x-show="cell.date" type="button"
                                    x-on:click="cell.available && selectDate(cell.date)"
                                    :disabled="!cell.available"
                                    :class="{
                                        'cal-cell':           true,
                                        'cal-cell--avail':    cell.available && !cell.isSelected && !cell.isToday,
                                        'cal-cell--today':    cell.isToday   && !cell.isSelected,
                                        'cal-cell--selected': cell.isSelected,
                                        'cal-cell--past':     !cell.available && !cell.isToday,
                                    }">
                                <span x-text="cell.day" class="cal-day-num"></span>
                                <span x-show="cell.available && !cell.isSelected" class="cal-dot"></span>
                            </button>
                        </div>
                    </template>
                </template>

            </div>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:18px;align-items:center;padding:11px 22px 13px;border-top:1px solid var(--warm-bd);flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:6px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:11px;height:11px;border-radius:4px;background:var(--sage-lt,#edf6f4);border:1.5px solid var(--sage,#2d6a62)"></div>
                Available
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:11px;height:11px;border-radius:4px;background:var(--plum)"></div>
                Selected
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:11px;height:11px;border-radius:4px;background:var(--parch);border:2px solid var(--plum)"></div>
                Today
            </div>
            <div style="display:flex;align-items:center;gap:6px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:11px;height:11px;border-radius:4px;background:#f1f0ef"></div>
                Unavailable
            </div>
        </div>
    </div>

    {{-- ── Slot grid ────────────────────────────────────────────────────── --}}
    <div class="panel" x-show="selectedDate">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt)">
                Available Slots
                <span x-show="selectedDateLabel" x-text="' — ' + selectedDateLabel" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);font-family:'Plus Jakarta Sans',sans-serif"></span>
            </div>
            <div x-show="loadingSlots" style="width:18px;height:18px;border:2px solid var(--warm-bd);border-top-color:var(--plum);border-radius:50%;animation:spin .6s linear infinite"></div>
        </div>

        <div style="padding:16px 20px">
            <div x-show="!loadingSlots && slots.length === 0"
                 style="text-align:center;padding:28px 16px;color:var(--txt-lt)">
                <div style="font-size:.875rem;margin-bottom:4px">No available slots on this day.</div>
                <div style="font-size:.78rem">Please try another date.</div>
            </div>

            <div x-show="!loadingSlots && slots.length > 0" style="display:flex;flex-direction:column;gap:16px">

                <div x-show="groupedSlots.morning.length > 0">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <span style="font-size:.95rem">🌅</span>
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt)">Morning</span>
                        <span style="font-size:.68rem;color:var(--txt-lt);font-weight:400" x-text="groupedSlots.morning.length + ' slots'"></span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <template x-for="slot in groupedSlots.morning" :key="slot">
                            <button type="button" x-on:click="selectSlot(slot)" class="slot-chip" :class="selectedSlot === slot ? 'slot-active' : ''" x-text="formatTime(slot)"></button>
                        </template>
                    </div>
                </div>

                <div x-show="groupedSlots.afternoon.length > 0">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <span style="font-size:.95rem">☀️</span>
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt)">Afternoon</span>
                        <span style="font-size:.68rem;color:var(--txt-lt);font-weight:400" x-text="groupedSlots.afternoon.length + ' slots'"></span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <template x-for="slot in groupedSlots.afternoon" :key="slot">
                            <button type="button" x-on:click="selectSlot(slot)" class="slot-chip" :class="selectedSlot === slot ? 'slot-active' : ''" x-text="formatTime(slot)"></button>
                        </template>
                    </div>
                </div>

                <div x-show="groupedSlots.evening.length > 0">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                        <span style="font-size:.95rem">🌆</span>
                        <span style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt)">Evening</span>
                        <span style="font-size:.68rem;color:var(--txt-lt);font-weight:400" x-text="groupedSlots.evening.length + ' slots'"></span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px">
                        <template x-for="slot in groupedSlots.evening" :key="slot">
                            <button type="button" x-on:click="selectSlot(slot)" class="slot-chip" :class="selectedSlot === slot ? 'slot-active' : ''" x-text="formatTime(slot)"></button>
                        </template>
                    </div>
                </div>
            </div>

            <div x-show="!loadingSlots && slots.length > 0" style="margin-top:14px;padding-top:12px;border-top:1px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
                <span style="font-size:.75rem;color:var(--txt-lt)" x-text="slots.length + ' slots available'"></span>
                <span x-show="selectedSlot" style="font-size:.75rem;font-weight:600;color:var(--plum)" x-text="'Selected: ' + formatTime(selectedSlot)"></span>
            </div>
        </div>
    </div>

    {{-- No date selected prompt --}}
    <div x-show="!selectedDate" class="panel"
         style="padding:36px 24px;text-align:center;color:var(--txt-lt)">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--parch);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt-md)">Pick a date on the calendar</div>
        <p style="font-size:.8rem;margin-top:4px">Highlighted dates have available slots.</p>
    </div>

</div>{{-- end left --}}

{{-- ══ RIGHT: Booking Form ══════════════════════════════════════════════════ --}}
<div style="position:sticky;top:calc(var(--topbar-h)+20px);display:flex;flex-direction:column;gap:14px">

    <div class="panel" style="padding:18px 20px">

        {{-- Who is this for? --}}
        <div style="margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:12px">Who is this for?</div>

            <div style="overflow-x:auto;padding-bottom:2px;margin:0 -2px;padding:0 2px 2px">
                <div style="display:flex;gap:14px;width:max-content">

                    {{-- Myself --}}
                    <button type="button" x-on:click="selectedMemberId = null"
                            class="patient-avatar-btn"
                            :class="selectedMemberId === null ? 'pab--active' : ''">
                        <div class="pab__ring" :class="selectedMemberId === null ? 'pab__ring--active' : ''">
                            <div class="pab__circle" :style="selectedMemberId === null ? 'background:var(--plum)' : 'background:#d4cfc9'">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                        </div>
                        <span class="pab__name" :style="selectedMemberId === null ? 'color:var(--plum);font-weight:700' : 'color:var(--txt-md)'">Myself</span>
                    </button>

                    {{-- Family members --}}
                    <template x-for="(m, idx) in members" :key="m.id">
                        <button type="button" x-on:click="selectedMemberId = m.id"
                                class="patient-avatar-btn"
                                :class="selectedMemberId === m.id ? 'pab--active' : ''">
                            <div class="pab__ring" :class="selectedMemberId === m.id ? 'pab__ring--active' : ''">
                                <template x-if="m.photo_url">
                                    <img :src="m.photo_url" class="pab__circle" style="object-fit:cover">
                                </template>
                                <template x-if="!m.photo_url">
                                    <div class="pab__circle"
                                         :style="selectedMemberId === m.id ? 'background:var(--plum)' : memberColor(idx)"
                                         x-text="initials(m.name)">
                                    </div>
                                </template>
                            </div>
                            <span class="pab__name"
                                  :style="selectedMemberId === m.id ? 'color:var(--plum);font-weight:700' : 'color:var(--txt-md)'"
                                  x-text="m.name.split(' ')[0]"></span>
                        </button>
                    </template>

                    {{-- Add member shortcut --}}
                    <a href="{{ route('patient.family.index') }}" class="patient-avatar-btn" style="text-decoration:none">
                        <div class="pab__ring">
                            <div class="pab__circle" style="background:var(--parch);border:2px dashed var(--warm-bd)">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </div>
                        <span class="pab__name" style="color:var(--txt-lt)">Add</span>
                    </a>

                </div>
            </div>
        </div>

        <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt);margin-bottom:14px">Your Booking</div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
            <div style="padding:10px 12px;border:1.5px solid var(--warm-bd);border-radius:10px;background:var(--parch)">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">Date</div>
                <div style="font-size:.9rem;font-weight:600;color:var(--txt)" x-text="selectedDate ? selectedDateLabel : '—'"></div>
            </div>
            <div style="padding:10px 12px;border:1.5px solid var(--warm-bd);border-radius:10px;background:var(--parch)">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">Time</div>
                <div style="font-size:.9rem;font-weight:600;color:var(--txt)" x-text="selectedSlot ? formatTime(selectedSlot) : '—'"></div>
            </div>
        </div>

        @if($dp?->consultation_fee)
        <div style="display:flex;justify-content:space-between;padding:10px 12px;border:1.5px solid var(--warm-bd);border-radius:10px;background:var(--parch);margin-bottom:14px">
            <div>
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">Consultation Fee</div>
                <div style="font-family:'Lora',serif;font-size:1.1rem;font-weight:500;color:var(--txt)">₹{{ number_format($dp->consultation_fee) }}</div>
            </div>
            @if($dp?->upi_id)
            <div style="text-align:right">
                <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">UPI</div>
                <div style="font-size:.78rem;font-family:monospace;color:var(--txt-md)">{{ $dp->upi_id }}</div>
            </div>
            @endif
        </div>
        @endif

        <form method="POST" :action="storeUrl" x-on:submit.prevent="submitBooking($el)" id="booking-form">
            @csrf
            <input type="hidden" name="slot_date" :value="selectedDate">
            <input type="hidden" name="slot_time" :value="selectedSlot">

            <div style="margin-bottom:12px">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Visit Type</label>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    @foreach(['consultation' => 'Consultation', 'follow_up' => 'Follow-up', 'emergency' => 'Urgent'] as $val => $lbl)
                    <label style="flex:1;min-width:80px">
                        <input type="radio" name="type" value="{{ $val }}" {{ $val === 'consultation' ? 'checked' : '' }} style="display:none" class="type-radio">
                        <div style="text-align:center;padding:7px 8px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.78rem;font-weight:500;cursor:pointer;transition:all .15s;color:var(--txt-md)"
                             onclick="document.querySelectorAll('.type-pill').forEach(p=>{p.style.background='transparent';p.style.color='var(--txt-md)';p.style.borderColor='var(--warm-bd)'});this.style.background='var(--plum)';this.style.color='#fff';this.style.borderColor='var(--plum)'"
                             class="type-pill {{ $val === 'consultation' ? 'active-type' : '' }}">
                            {{ $lbl }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="margin-bottom:14px">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">
                    Reason <span style="font-weight:400;color:var(--txt-lt)">(optional)</span>
                </label>
                <textarea name="reason" rows="3"
                          placeholder="Briefly describe your symptoms or reason for visit…"
                          style="width:100%;padding:.6rem .85rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8125rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;resize:none"
                          onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'"></textarea>
            </div>

            <div x-show="bookingError" x-text="bookingError"
                 style="padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#dc2626;margin-bottom:10px"></div>

            <button type="submit"
                    :disabled="!selectedDate || !selectedSlot || submitting"
                    class="book-btn"
                    :class="(!selectedDate || !selectedSlot || submitting) ? 'book-btn-disabled' : ''">
                <span x-show="submitting" style="width:16px;height:16px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                <svg x-show="!submitting" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span x-text="submitting ? 'Booking…' : 'Confirm Appointment'"></span>
            </button>
        </form>
    </div>

    {{-- Weekly schedule sidebar --}}
    <div class="panel" style="padding:14px 18px">
        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:10px">Weekly Schedule</div>
        @foreach(['monday' => 'Mon','tuesday' => 'Tue','wednesday' => 'Wed','thursday' => 'Thu','friday' => 'Fri','saturday' => 'Sat','sunday' => 'Sun'] as $day => $abbr)
        @php $daySlots = $slots[$day] ?? []; @endphp
        @if(count($daySlots) > 0)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--parch)">
            <span style="font-size:.8rem;font-weight:600;color:var(--txt-md);width:32px">{{ $abbr }}</span>
            <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end">
                @foreach($daySlots as $b)
                <span style="font-size:.7rem;color:var(--txt-lt);padding:2px 7px;border:1px solid var(--warm-bd);border-radius:20px">{{ $b['start'] }}–{{ $b['end'] }}</span>
                @endforeach
            </div>
        </div>
        @else
        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--parch)">
            <span style="font-size:.8rem;font-weight:600;color:var(--txt-lt);width:32px">{{ $abbr }}</span>
            <span style="font-size:.72rem;color:var(--txt-lt)">Off</span>
        </div>
        @endif
        @endforeach
    </div>

</div>{{-- end right --}}
</div>{{-- end grid --}}
</div>{{-- end x-data --}}
@endsection

@push('styles')
<style>
@keyframes spin    { to { transform: rotate(360deg); } }
@keyframes cal-pulse { 0%,100% { opacity:.55; } 50% { opacity:.25; } }

/* ── Calendar nav button ── */
.cal-nav-btn {
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    border: 1.5px solid var(--warm-bd);
    border-radius: 9px;
    background: transparent;
    cursor: pointer;
    color: var(--txt-md);
    transition: all .15s;
    flex-shrink: 0;
}
.cal-nav-btn:hover { background: var(--parch); border-color: var(--plum); color: var(--plum); }

/* ── Unified calendar grid ──
   Headers (PHP-rendered) and cells (Alpine) share ONE grid,
   so column widths are always identical — no offset drift.    */
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

/* ── Day-of-week header ── */
.cal-dow {
    text-align: center;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--txt-lt);
    padding: 10px 0 9px;
    user-select: none;
}
.cal-dow--wknd { color: var(--coral, #c0737a); }

/* ── Skeleton cell (pulse while loading) ── */
.cal-skel {
    height: 44px;
    border-radius: 9px;
    background: var(--parch, #f6f3ef);
    animation: cal-pulse 1.4s ease-in-out infinite;
}

/* ── Date cell base ── */
.cal-cell {
    width: 100%;
    height: 44px;
    border-radius: 9px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 3px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: .875rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .14s, color .14s, box-shadow .14s, transform .12s;
    position: relative;
}
.cal-cell--blank {
    background: transparent !important;
    cursor: default;
    pointer-events: none;
}

/* ── Available ── */
.cal-cell--avail {
    background: var(--sage-lt, #edf6f4);
    color: var(--sage, #2d6a62);
    font-weight: 600;
}
.cal-cell--avail:hover {
    background: var(--sage, #2d6a62);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(45,106,98,.22);
}

/* ── Today ── */
.cal-cell--today {
    background: var(--parch, #faf7f4);
    color: var(--plum, #4a3760);
    font-weight: 700;
    box-shadow: inset 0 0 0 2px var(--plum, #4a3760);
}
.cal-cell--today:hover {
    background: var(--plum, #4a3760);
    color: #fff;
    box-shadow: 0 4px 12px rgba(74,55,96,.28);
}

/* ── Selected ── */
.cal-cell--selected {
    background: var(--plum, #4a3760) !important;
    color: #fff !important;
    font-weight: 700;
    box-shadow: 0 4px 16px rgba(74,55,96,.38);
    transform: translateY(-1px);
}

/* ── Past / unavailable ── */
.cal-cell--past {
    background: transparent;
    color: var(--txt-lt);
    cursor: not-allowed;
    opacity: .38;
}

/* ── Availability dot ── */
.cal-day-num  { line-height: 1; }
.cal-dot {
    width: 4px; height: 4px;
    border-radius: 50%;
    background: currentColor;
    opacity: .55;
    flex-shrink: 0;
}

/* ── Slot chips ── */
.active-type { background: var(--plum) !important; color: #fff !important; border-color: var(--plum) !important; }
.slot-chip {
    padding: 8px 16px;
    border-radius: 9px;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    border: 1.5px solid var(--warm-bd);
    background: var(--cream, #fff);
    color: var(--txt-md);
    font-family: 'Plus Jakarta Sans', sans-serif;
    white-space: nowrap;
}
.slot-chip:hover     { border-color: var(--plum); color: var(--plum); background: var(--parch); }
.slot-chip.slot-active            { background: var(--plum); color: #fff; border-color: var(--plum); box-shadow: 0 2px 10px rgba(74,55,96,.3); }
.slot-chip.slot-active:hover      { background: var(--plum); color: #fff; }

/* ── Patient avatar picker ── */
.patient-avatar-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    cursor: pointer;
    font-family: inherit;
    padding: 2px;
    min-width: 54px;
}
.pab__ring {
    width: 54px; height: 54px;
    border-radius: 50%;
    padding: 3px;
    border: 2.5px solid transparent;
    transition: border-color .2s;
    flex-shrink: 0;
}
.pab__ring--active {
    border-color: var(--plum);
}
.pab__circle {
    width: 100%; height: 100%;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    font-weight: 700;
    color: #fff;
    transition: background .2s;
}
.pab__name {
    font-size: .7rem;
    font-weight: 500;
    color: var(--txt-md);
    max-width: 58px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-align: center;
    transition: color .2s, font-weight .2s;
}

/* ── Book button ── */
.book-btn {
    width: 100%; padding: .85rem 1rem;
    border: none; border-radius: 11px;
    font-size: .9375rem; font-weight: 700; cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    background: var(--plum); color: #fff;
    box-shadow: 0 4px 16px rgba(74,55,96,.35);
}
.book-btn:hover { opacity: .9; }
.book-btn-disabled {
    background: var(--warm-bd, #e2dcd5);
    color: var(--txt-lt, #a09890);
    box-shadow: none; cursor: not-allowed;
}
.book-btn-disabled:hover { opacity: 1; }
</style>
@endpush

@push('scripts')
<script>
function bookingFlow({ doctorId, slotsUrl, datesUrl, availDays, fee, selfUrl, memberUrls, members, initialMemberId }) {
    return {
        // Calendar state
        today:            new Date(),
        currentYear:      new Date().getFullYear(),
        currentMonth:     new Date().getMonth(),
        selectedDate:     null,
        availableDates:   [],
        calendarCells:    [],
        loadingCalendar:  false,

        // Slot state
        slots:            [],
        selectedSlot:     null,
        loadingSlots:     false,

        // Labels
        monthLabel:       '',
        selectedDateLabel:'',
        isCurrentMonth:   true,

        // Form state
        submitting:       false,
        bookingError:     '',
        selfUrl,
        memberUrls,
        members,
        selectedMemberId: initialMemberId,

        memberColor(idx) {
            const palette = ['#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a','#5c7a3d','#7a3d6a'];
            return `background:${palette[idx % palette.length]}`;
        },

        initials(name) {
            return (name || '').split(' ').slice(0, 2).map(w => w[0] || '').join('').toUpperCase();
        },

        get groupedSlots() {
            const morning = [], afternoon = [], evening = [];
            for (const s of this.slots) {
                const h = parseInt(s.split(':')[0], 10);
                if (h < 12)      morning.push(s);
                else if (h < 17) afternoon.push(s);
                else             evening.push(s);
            }
            return { morning, afternoon, evening };
        },

        async init() {
            await this.buildCalendar();
            const firstPill = document.querySelector('.type-pill');
            if (firstPill) {
                firstPill.style.background   = 'var(--plum)';
                firstPill.style.color        = '#fff';
                firstPill.style.borderColor  = 'var(--plum)';
            }
            // Re-fetch when page is restored from the browser's back-forward cache
            window.addEventListener('pageshow', (e) => {
                if (e.persisted) this.buildCalendar();
            });
        },

        async buildCalendar() {
            this.loadingCalendar = true;

            const monthStr = `${this.currentYear}-${String(this.currentMonth+1).padStart(2,'0')}`;
            this.monthLabel    = new Date(this.currentYear, this.currentMonth, 1)
                .toLocaleString('en-IN', { month: 'long', year: 'numeric' });
            this.isCurrentMonth = (
                this.currentYear  === this.today.getFullYear() &&
                this.currentMonth === this.today.getMonth()
            );

            // Fetch available dates (t= busts HTTP cache)
            try {
                const r = await fetch(
                    `${datesUrl}?month=${monthStr}&t=${Date.now()}`,
                    { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }
                );
                const d = await r.json();
                this.availableDates = d.available_dates || [];
            } catch { this.availableDates = []; }

            // Build cells
            const firstDay  = new Date(this.currentYear, this.currentMonth, 1);
            const daysInMon = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
            let startDow    = firstDay.getDay(); // 0 = Sun
            startDow        = startDow === 0 ? 6 : startDow - 1; // Mon = 0

            const cells = [];
            for (let i = 0; i < startDow; i++) {
                cells.push({ key: `e${i}`, date: null });
            }
            for (let d = 1; d <= daysInMon; d++) {
                const dateStr = `${this.currentYear}-${String(this.currentMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const isToday = dateStr === this.today.toISOString().split('T')[0];
                cells.push({
                    key:        dateStr,
                    date:       dateStr,
                    day:        d,
                    isToday,
                    isSelected: this.selectedDate === dateStr,
                    available:  this.availableDates.includes(dateStr),
                });
            }
            this.calendarCells  = cells;
            this.loadingCalendar = false;
        },

        async selectDate(dateStr) {
            this.selectedDate  = dateStr;
            this.selectedSlot  = null;
            this.slots         = [];
            this.bookingError  = '';

            const d = new Date(dateStr + 'T00:00:00');
            this.selectedDateLabel = d.toLocaleDateString('en-IN', {
                weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
            });

            this.calendarCells = this.calendarCells.map(c => ({ ...c, isSelected: c.date === dateStr }));

            this.loadingSlots = true;
            try {
                const r = await fetch(`${slotsUrl}?date=${dateStr}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const d = await r.json();
                this.slots = d.available || [];
            } catch { this.slots = []; }
            this.loadingSlots = false;
        },

        selectSlot(slot) {
            this.selectedSlot = slot;
            this.bookingError = '';
        },

        formatTime(t) {
            const [h, m] = t.split(':').map(Number);
            const suffix = h >= 12 ? 'PM' : 'AM';
            const hr     = h > 12 ? h - 12 : h === 0 ? 12 : h;
            return `${hr}:${String(m).padStart(2,'0')} ${suffix}`;
        },

        async prevMonth() {
            if (this.isCurrentMonth) return;
            if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
            else { this.currentMonth--; }
            this.selectedDate = null; this.slots = []; this.selectedSlot = null;
            await this.buildCalendar();
        },

        async nextMonth() {
            if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
            else { this.currentMonth++; }
            await this.buildCalendar();
        },

        async submitBooking(form) {
            if (!this.selectedDate || !this.selectedSlot) return;
            this.submitting   = true;
            this.bookingError = '';

            const storeUrl = this.selectedMemberId
                ? this.memberUrls[this.selectedMemberId]
                : this.selfUrl;

            const fd = new FormData(form);
            fd.set('slot_date', this.selectedDate);
            fd.set('slot_time', this.selectedSlot);

            try {
                const resp = await fetch(storeUrl, {
                    method:  'POST',
                    headers: {
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: fd,
                });

                if (resp.redirected) { window.location.href = resp.url; return; }
                if (resp.ok) {
                    const data = await resp.json().catch(() => null);
                    if (data?.redirect) { window.location.href = data.redirect; return; }
                    window.location.href = '{{ route("patient.appointments.index") }}';
                    return;
                }

                const data = await resp.json().catch(() => ({}));
                if (data.errors?.slot_time) {
                    this.bookingError = data.errors.slot_time[0];
                    this.slots        = this.slots.filter(s => s !== this.selectedSlot);
                    this.selectedSlot = null;
                } else {
                    this.bookingError = data.message || 'Something went wrong. Please try again.';
                }
            } catch {
                this.bookingError = 'Network error. Please try again.';
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
