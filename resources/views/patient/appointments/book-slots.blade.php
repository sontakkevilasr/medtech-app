@extends('layouts.patient')
@section('title', 'Pick a Slot')
@section('page-title')
    <a href="{{ route('patient.appointments.book') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Book Appointment</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Dr. {{ $doctor->profile?->full_name }}
@endsection

@section('content')
@php
    $dp       = $doctor->doctorProfile;
    $name     = $doctor->profile?->full_name ?? 'Doctor';
    $initials = strtoupper(implode('', array_map(fn($x) => $x[0], array_slice(explode(' ',$name),0,2))));
    $colors   = ['#4a3760','#3d7a6e','#7a5c3d','#3d5e7a','#7a3d4a'];
    $color    = $colors[$doctor->id % count($colors)];
    $slots    = $dp?->available_slots ?? [];
    $dayFull  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    $availDays = array_values(array_filter($dayFull, fn($d) => !empty($slots[$d])));
@endphp

<div x-data="bookingFlow({
    doctorId:     {{ $doctor->id }},
    slotsUrl:     '{{ route('patient.appointments.slots', $doctor->id) }}',
    datesUrl:     '{{ route('patient.appointments.dates', $doctor->id) }}',
    availDays:    {{ json_encode($availDays) }},
    fee:          {{ $dp?->consultation_fee ?? 0 }},
    storeUrl:     '{{ $familyMember ? route('patient.appointments.store.member', [$doctor->id, $familyMember->id]) : route('patient.appointments.store', $doctor->id) }}',
})" x-init="init()" class="fade-in">

<div style="display:grid;grid-template-columns:1fr 320px;gap:22px;align-items:start">

{{-- ══ LEFT: Calendar + Slot Grid ═══════════════════════════════════════════ -- --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- Doctor summary bar --}}
    <div class="panel" style="padding:16px 22px">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:46px;height:46px;border-radius:12px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;color:#fff;flex-shrink:0">
                {{ $initials }}
            </div>
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
        @if($familyMember ?? false)
        <div style="margin-top:10px;padding:8px 12px;background:var(--parch);border-radius:8px;font-size:.8125rem;color:var(--txt-md);display:flex;align-items:center;gap:6px">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Booking for <strong style="color:var(--txt)">{{ $familyMember->full_name }}</strong> ({{ $familyMember->relation }})
        </div>
        @endif
    </div>

    {{-- Calendar --}}
    <div class="panel">
        {{-- Month nav --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--warm-bd)">
            <button type="button" x-on:click="prevMonth()"
                    :disabled="isCurrentMonth"
                    style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;transition:all .15s"
                    :style="isCurrentMonth ? 'opacity:.3;cursor:not-allowed' : ''"
                    onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt)" x-text="monthLabel"></div>
            <button type="button" x-on:click="nextMonth()"
                    style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;transition:all .15s"
                    onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Day headers --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);padding:10px 16px 0">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dh)
            <div style="text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);padding:6px 0">{{ $dh }}</div>
            @endforeach
        </div>

        {{-- Calendar grid --}}
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:3px;padding:6px 16px 16px" x-ref="calendarGrid">
            <template x-for="cell in calendarCells" :key="cell.key">
                <div>
                    {{-- Empty cell --}}
                    <div x-show="!cell.date" style="padding:6px"></div>
                    {{-- Date cell --}}
                    <button x-show="cell.date" type="button"
                            x-on:click="cell.available && selectDate(cell.date)"
                            :disabled="!cell.available"
                            style="width:100%;aspect-ratio:1;border:none;border-radius:10px;cursor:pointer;transition:all .15s;font-size:.875rem;font-weight:500;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:2px;position:relative"
                            :style="
                                cell.isSelected ? 'background:var(--plum);color:#fff;font-weight:700;box-shadow:0 3px 12px rgba(74,55,96,.35)' :
                                cell.isToday    ? 'background:var(--parch);color:var(--plum);border:2px solid var(--plum);font-weight:700' :
                                cell.available  ? 'background:var(--sage-lt,#edf6f4);color:var(--sage,#2d6a62);' :
                                                  'background:transparent;color:var(--txt-lt);cursor:not-allowed;opacity:.4'
                            ">
                        <span x-text="cell.day"></span>
                        <span x-show="cell.available && !cell.isSelected"
                              style="width:4px;height:4px;border-radius:50%;background:currentColor;opacity:.5"></span>
                    </button>
                </div>
            </template>
        </div>

        {{-- Legend --}}
        <div style="display:flex;gap:14px;padding:8px 20px 14px;border-top:1px solid var(--warm-bd);flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:10px;height:10px;border-radius:3px;background:var(--sage-lt,#edf6f4);border:1px solid var(--sage,#2d6a62)"></div>
                Available
            </div>
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:10px;height:10px;border-radius:3px;background:var(--plum)"></div>
                Selected
            </div>
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-lt)">
                <div style="width:10px;height:10px;border-radius:3px;background:var(--parch);border:1.5px solid var(--plum)"></div>
                Today
            </div>
        </div>
    </div>

    {{-- Slot grid --}}
    <div class="panel" x-show="selectedDate">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid var(--warm-bd)">
            <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt)">
                Available Slots
                <span x-show="selectedDateLabel" x-text="' — ' + selectedDateLabel" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);font-family:'Plus Jakarta Sans',sans-serif"></span>
            </div>
            <div x-show="loadingSlots" style="width:18px;height:18px;border:2px solid var(--warm-bd);border-top-color:var(--plum);border-radius:50%;animation:spin .6s linear infinite"></div>
        </div>

        <div style="padding:16px 20px">
            {{-- No slots state --}}
            <div x-show="!loadingSlots && slots.length === 0"
                 style="text-align:center;padding:28px 16px;color:var(--txt-lt)">
                <div style="font-size:.875rem;margin-bottom:4px">No available slots on this day.</div>
                <div style="font-size:.78rem">Please try another date.</div>
            </div>

            {{-- Slot chips --}}
            <div x-show="slots.length > 0"
                 style="display:grid;grid-template-columns:repeat(auto-fill,minmax(72px,1fr));gap:8px">
                <template x-for="slot in slots" :key="slot">
                    <button type="button" x-on:click="selectSlot(slot)"
                            style="padding:8px 4px;border-radius:9px;font-size:.8rem;font-weight:600;cursor:pointer;transition:all .15s;border:1.5px solid"
                            :style="selectedSlot === slot
                                ? 'background:var(--plum);color:#fff;border-color:var(--plum);box-shadow:0 2px 10px rgba(74,55,96,.3)'
                                : 'background:transparent;color:var(--txt-md);border-color:var(--warm-bd)'"
                            onmouseover="if(this.getAttribute('data-sel')!='true'){this.style.borderColor='var(--plum)';this.style.color='var(--plum)';}"
                            onmouseout="if(this.getAttribute('data-sel')!='true'){this.style.borderColor='var(--warm-bd)';this.style.color='var(--txt-md)';}"
                            :data-sel="selectedSlot===slot?'true':'false'"
                            x-text="formatTime(slot)">
                    </button>
                </template>
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

{{-- ══ RIGHT: Booking Form ══════════════════════════════════════════════════ -- --}}
<div style="position:sticky;top:calc(var(--topbar-h)+20px);display:flex;flex-direction:column;gap:14px">

    {{-- Booking summary --}}
    <div class="panel" style="padding:18px 20px">
        <div style="font-family:'Lora',serif;font-size:1rem;color:var(--txt);margin-bottom:14px">Your Booking</div>

        {{-- Date + Time display --}}
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

        {{-- Booking form --}}
        <form method="POST"
              :action="storeUrl"
              x-on:submit.prevent="submitBooking($el)"
              id="booking-form">
            @csrf

            <input type="hidden" name="slot_date" :value="selectedDate">
            <input type="hidden" name="slot_time" :value="selectedSlot">

            {{-- Visit type --}}
            <div style="margin-bottom:12px">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Visit Type</label>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                    @foreach(['consultation' => 'Consultation', 'follow_up' => 'Follow-up', 'emergency' => 'Urgent'] as $val => $lbl)
                    <label style="flex:1;min-width:80px">
                        <input type="radio" name="type" value="{{ $val }}" {{ $val === 'consultation' ? 'checked' : '' }} style="display:none" class="type-radio">
                        <div style="text-align:center;padding:7px 8px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.78rem;font-weight:500;cursor:pointer;transition:all .15s;color:var(--txt-md)"
                             onclick="document.querySelectorAll('.type-pill').forEach(p=>p.style.background='transparent');document.querySelectorAll('.type-pill').forEach(p=>{p.style.color='var(--txt-md)';p.style.borderColor='var(--warm-bd)'});this.style.background='var(--plum)';this.style.color='#fff';this.style.borderColor='var(--plum)'"
                             class="type-pill {{ $val === 'consultation' ? 'active-type' : '' }}">
                            {{ $lbl }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Reason --}}
            <div style="margin-bottom:14px">
                <label style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:4px">
                    Reason <span style="font-weight:400;color:var(--txt-lt)">(optional)</span>
                </label>
                <textarea name="reason" rows="3"
                          placeholder="Briefly describe your symptoms or reason for visit…"
                          style="width:100%;padding:.6rem .85rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8125rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;resize:none"
                          onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'"></textarea>
            </div>

            {{-- Error message --}}
            <div x-show="bookingError" x-text="bookingError"
                 style="padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#dc2626;margin-bottom:10px"></div>

            <button type="submit"
                    :disabled="!selectedDate || !selectedSlot || submitting"
                    style="width:100%;padding:.8rem;border:none;border-radius:11px;font-size:.9375rem;font-weight:700;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;background:var(--plum);color:#fff"
                    :style="(!selectedDate || !selectedSlot || submitting) ? 'opacity:.45;cursor:not-allowed' : 'box-shadow:0 4px 16px rgba(74,55,96,.35)'">
                <span x-show="submitting" style="width:16px;height:16px;border:2.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                <span x-text="submitting ? 'Booking…' : 'Confirm Appointment'"></span>
            </button>
        </form>
    </div>

    {{-- Doctor availability summary --}}
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
@keyframes spin { to { transform: rotate(360deg); } }
.active-type { background: var(--plum) !important; color: #fff !important; border-color: var(--plum) !important; }
</style>
@endpush

@push('scripts')
<script>
function bookingFlow({ doctorId, slotsUrl, datesUrl, availDays, fee, storeUrl }) {
    return {
        // Calendar state
        today:            new Date(),
        currentYear:      new Date().getFullYear(),
        currentMonth:     new Date().getMonth(), // 0-indexed
        selectedDate:     null,
        availableDates:   [],
        calendarCells:    [],

        // Slot state
        slots:            [],
        selectedSlot:     null,
        loadingSlots:     false,

        // Computed labels
        monthLabel:       '',
        selectedDateLabel:'',
        isCurrentMonth:   true,

        // Form state
        submitting:       false,
        bookingError:     '',
        storeUrl,

        async init() {
            await this.buildCalendar();
            // Highlight first pill
            const firstPill = document.querySelector('.type-pill');
            if (firstPill) {
                firstPill.style.background = 'var(--plum)';
                firstPill.style.color = '#fff';
                firstPill.style.borderColor = 'var(--plum)';
            }
        },

        async buildCalendar() {
            const monthStr = `${this.currentYear}-${String(this.currentMonth+1).padStart(2,'0')}`;
            this.monthLabel = new Date(this.currentYear, this.currentMonth, 1)
                .toLocaleString('en-IN', { month: 'long', year: 'numeric' });
            this.isCurrentMonth = (this.currentYear === this.today.getFullYear() && this.currentMonth === this.today.getMonth());

            // Fetch available dates from API
            try {
                const r = await fetch(`${datesUrl}?month=${monthStr}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.availableDates = d.available_dates || [];
            } catch { this.availableDates = []; }

            // Build cells
            const firstDay  = new Date(this.currentYear, this.currentMonth, 1);
            const daysInMon = new Date(this.currentYear, this.currentMonth+1, 0).getDate();
            let startDow    = firstDay.getDay(); // 0=Sun
            startDow        = startDow === 0 ? 6 : startDow - 1; // convert to Mon=0

            const cells = [];
            for (let i = 0; i < startDow; i++) cells.push({ key: `e${i}`, date: null });
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
            this.calendarCells = cells;
        },

        async selectDate(dateStr) {
            this.selectedDate  = dateStr;
            this.selectedSlot  = null;
            this.slots         = [];
            this.bookingError  = '';

            const d = new Date(dateStr + 'T00:00:00');
            this.selectedDateLabel = d.toLocaleDateString('en-IN', { weekday:'short', day:'numeric', month:'short', year:'numeric' });

            // Update isSelected in cells
            this.calendarCells = this.calendarCells.map(c => ({ ...c, isSelected: c.date === dateStr }));

            // Fetch slots
            this.loadingSlots = true;
            try {
                const r = await fetch(`${slotsUrl}?date=${dateStr}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                const d = await r.json();
                this.slots = d.available || [];
            } catch { this.slots = []; }
            this.loadingSlots = false;
        },

        selectSlot(slot) {
            this.selectedSlot = slot;
            this.bookingError  = '';
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

            const fd = new FormData(form);
            fd.set('slot_date', this.selectedDate);
            fd.set('slot_time', this.selectedSlot);

            try {
                const resp = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: fd,
                });

                if (resp.redirected) { window.location.href = resp.url; return; }
                if (resp.ok) {
                    const data = await resp.json().catch(() => null);
                    if (data?.redirect) { window.location.href = data.redirect; return; }
                    window.location.href = '{{ route("patient.appointments.index") }}';
                    return;
                }

                // Validation errors
                const data = await resp.json().catch(() => ({}));
                if (data.errors?.slot_time) {
                    this.bookingError = data.errors.slot_time[0];
                    // Remove that slot from grid
                    this.slots = this.slots.filter(s => s !== this.selectedSlot);
                    this.selectedSlot = null;
                } else {
                    this.bookingError = data.message || 'Something went wrong. Please try again.';
                }
            } catch (e) {
                this.bookingError = 'Network error. Please try again.';
            } finally {
                this.submitting = false;
            }
        },
    }
}
</script>
@endpush
