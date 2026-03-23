@extends('layouts.doctor')
@section('title', 'Manage Availability')
@section('page-title')
    <a href="{{ route('doctor.appointments.calendar') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Appointments</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Manage Availability
@endsection

@section('content')
@php
    $days = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday','sunday'=>'Sunday'];
    $duration = config('medtech.appointment.default_duration', 15);
@endphp

<div x-data="slotsManager({{ json_encode($slots) }})" x-init="init()" class="fade-in">
<form method="POST" action="{{ route('doctor.appointments.slots.save') }}" x-on:submit.prevent="save($el)">
@csrf

<div style="display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start">

{{-- LEFT: Day blocks --}}
<div class="panel">
    <div style="padding:16px 22px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Weekly Schedule</div>
            <div style="font-size:.78rem;color:var(--txt-lt);margin-top:2px">Set which days and hours you're available for appointments</div>
        </div>
        <div style="font-size:.75rem;color:var(--txt-lt)">{{ $duration }}-minute slots</div>
    </div>

    @foreach($days as $dayKey => $dayLabel)
    @php $isWeekend = in_array($dayKey, ['saturday','sunday']); @endphp
    <div id="day-{{ $dayKey }}" style="border-bottom:1px solid var(--warm-bd);padding:14px 22px;transition:background .15s"
         :style="schedule['{{ $dayKey }}']?.enabled ? '' : 'background:#fdfcfb;opacity:.7'">

        <div style="display:flex;align-items:center;gap:14px">
            {{-- Toggle --}}
            <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;flex-shrink:0">
                <input type="checkbox" :name="`slots[{{ $dayKey }}][enabled]`" :value="1"
                       x-model="schedule['{{ $dayKey }}'].enabled"
                       style="opacity:0;width:0;height:0;position:absolute">
                <div style="width:36px;height:20px;border-radius:10px;transition:background .2s;position:relative"
                     :style="schedule['{{ $dayKey }}'].enabled ? 'background:var(--leaf)' : 'background:#d1d5db'">
                    <div style="width:16px;height:16px;border-radius:50%;background:#fff;position:absolute;top:2px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"
                         :style="schedule['{{ $dayKey }}'].enabled ? 'left:18px' : 'left:2px'"></div>
                </div>
            </label>

            {{-- Day name --}}
            <div style="min-width:90px">
                <div style="font-size:.9rem;font-weight:600;color:var(--txt);
                            {{ $isWeekend ? 'color:var(--coral,#c0737a)' : '' }}">
                    {{ $dayLabel }}
                </div>
                <div x-show="schedule['{{ $dayKey }}'].enabled"
                     style="font-size:.72rem;color:var(--txt-lt);margin-top:1px"
                     x-text="slotCount('{{ $dayKey }}') + ' slots / day'"></div>
                <div x-show="!schedule['{{ $dayKey }}'].enabled"
                     style="font-size:.72rem;color:var(--txt-lt)">Off</div>
            </div>

            {{-- Time blocks --}}
            <div x-show="schedule['{{ $dayKey }}'].enabled" style="flex:1;display:flex;flex-direction:column;gap:6px">
                <template x-for="(block, bi) in schedule['{{ $dayKey }}'].blocks" :key="bi">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <div style="display:flex;align-items:center;gap:6px;background:var(--parch);padding:5px 10px;border-radius:8px;border:1px solid var(--warm-bd)">
                            <input type="time" :name="`slots[{{ $dayKey }}][blocks][${bi}][start]`"
                                   x-model="block.start"
                                   style="border:none;background:transparent;font-size:.875rem;color:var(--txt);outline:none;font-family:'Outfit',sans-serif;cursor:pointer">
                            <span style="font-size:.75rem;color:var(--txt-lt)">to</span>
                            <input type="time" :name="`slots[{{ $dayKey }}][blocks][${bi}][end]`"
                                   x-model="block.end"
                                   style="border:none;background:transparent;font-size:.875rem;color:var(--txt);outline:none;font-family:'Outfit',sans-serif;cursor:pointer">
                        </div>
                        <div style="font-size:.72rem;color:var(--txt-lt)" x-text="blockSlotCount(block) + ' slots'"></div>
                        <button type="button"
                                x-on:click="removeBlock('{{ $dayKey }}', bi)"
                                x-show="schedule['{{ $dayKey }}'].blocks.length > 1"
                                style="width:24px;height:24px;border:none;background:none;cursor:pointer;color:var(--txt-lt);display:flex;align-items:center;justify-content:center;border-radius:6px;transition:all .12s"
                                onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2'" onmouseout="this.style.color='var(--txt-lt)';this.style.background='transparent'">
                            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                <button type="button" x-on:click="addBlock('{{ $dayKey }}')"
                        style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--leaf);background:none;border:none;cursor:pointer;font-family:'Outfit',sans-serif;font-weight:500;padding:2px 0">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add break
                </button>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Copy row --}}
    <div style="padding:14px 22px;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <span style="font-size:.8rem;color:var(--txt-lt)">Copy Mon–Fri schedule to:</span>
        <button type="button" x-on:click="copyWeekday()"
                style="font-size:.78rem;padding:5px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;color:var(--txt-md);font-family:'Outfit',sans-serif;transition:background .12s"
                onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            All weekdays
        </button>
        <button type="button" x-on:click="clearWeekend()"
                style="font-size:.78rem;padding:5px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;color:var(--txt-md);font-family:'Outfit',sans-serif;transition:background .12s"
                onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            Clear weekends
        </button>
    </div>
</div>

{{-- RIGHT: Summary + Save --}}
<div style="position:sticky;top:calc(var(--topbar-h)+20px);display:flex;flex-direction:column;gap:14px">

    {{-- Weekly summary --}}
    <div class="panel" style="padding:16px 18px">
        <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:12px">Weekly Summary</div>
        @foreach($days as $dayKey => $dayLabel)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--parch)">
            <span style="font-size:.8rem;font-weight:500;color:var(--txt-md)">{{ substr($dayLabel,0,3) }}</span>
            <span x-show="schedule['{{ $dayKey }}'].enabled"
                  style="font-size:.75rem;color:var(--leaf);font-weight:600"
                  x-text="slotCount('{{ $dayKey }}') + ' slots'"></span>
            <span x-show="!schedule['{{ $dayKey }}'].enabled"
                  style="font-size:.75rem;color:var(--txt-lt)">Off</span>
        </div>
        @endforeach
        <div style="margin-top:10px;display:flex;justify-content:space-between">
            <span style="font-size:.8rem;color:var(--txt-md)">Total/week</span>
            <span style="font-size:.9rem;font-weight:600;color:var(--txt)" x-text="totalWeekSlots() + ' slots'"></span>
        </div>
    </div>

    {{-- Slot info --}}
    <div class="panel" style="padding:14px 18px;background:var(--parch)">
        <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:8px">Info</div>
        <div style="font-size:.78rem;color:var(--txt-md);line-height:1.6">
            Each slot is <strong>{{ $duration }} minutes</strong>.<br>
            Patients can book any available slot online.<br>
            Already-booked slots won't be affected by changes here.
        </div>
    </div>

    {{-- Save button --}}
    <button type="submit" :disabled="saving"
            style="width:100%;padding:.8rem;background:var(--ink);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px"
            :style="saving ? 'opacity:.6;cursor:not-allowed' : ''">
        <span x-show="saving" style="width:15px;height:15px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
        <span x-text="saving ? 'Saving…' : 'Save Availability'"></span>
    </button>

    <a href="{{ route('doctor.appointments.calendar') }}"
       style="display:block;text-align:center;font-size:.8125rem;color:var(--txt-lt);text-decoration:none;padding:6px"
       onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
        ← Back to Calendar
    </a>
</div>

</div>
</form>
</div>
@endsection

@push('styles')
<style>
@@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush
@push('scripts')
<script>
function slotsManager(initial) {
    const duration = {{ $duration }};
    const days     = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    function ensureDay(sch, day) {
        if (!sch[day]) sch[day] = { enabled: false, blocks: [{ start: '09:00', end: '13:00' }] };
        return sch;
    }

    // Normalise initial data
    const norm = {};
    days.forEach(d => {
        const raw = initial[d] || [];
        norm[d] = {
            enabled: Array.isArray(raw) ? raw.length > 0 : false,
            blocks:  Array.isArray(raw) && raw.length ? raw : [{ start: '09:00', end: '13:00' }],
        };
    });

    return {
        schedule: norm,
        saving:   false,

        init() {},

        slotCount(day) {
            if (!this.schedule[day]?.enabled) return 0;
            return this.schedule[day].blocks.reduce((s, b) => s + this.blockSlotCount(b), 0);
        },

        blockSlotCount(block) {
            if (!block.start || !block.end) return 0;
            const [sh, sm] = block.start.split(':').map(Number);
            const [eh, em] = block.end.split(':').map(Number);
            const mins = (eh * 60 + em) - (sh * 60 + sm);
            return mins > 0 ? Math.floor(mins / duration) : 0;
        },

        totalWeekSlots() {
            return days.reduce((s, d) => s + this.slotCount(d), 0);
        },

        addBlock(day) {
            const lastEnd = this.schedule[day].blocks.at(-1)?.end || '13:00';
            // Default 1h after last end
            const [h, m] = lastEnd.split(':').map(Number);
            const newStart = `${String(h+1).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
            const newEnd   = `${String(h+3).padStart(2,'0')}:00`;
            this.schedule[day].blocks.push({ start: newStart, end: newEnd.replace(/^2[4-9]/, '23') });
        },

        removeBlock(day, idx) {
            this.schedule[day].blocks.splice(idx, 1);
        },

        copyWeekday() {
            const monBlocks = JSON.parse(JSON.stringify(this.schedule.monday.blocks));
            ['tuesday','wednesday','thursday','friday'].forEach(d => {
                this.schedule[d].enabled = this.schedule.monday.enabled;
                this.schedule[d].blocks  = JSON.parse(JSON.stringify(monBlocks));
            });
        },

        clearWeekend() {
            ['saturday','sunday'].forEach(d => { this.schedule[d].enabled = false; });
        },

        save(form) {
            this.saving = true;
            form.submit();
        },
    }
}
</script>
@endpush
