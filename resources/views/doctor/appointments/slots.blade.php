@extends('layouts.doctor')
@section('title', 'Manage Availability')
@section('page-title')
    <a href="{{ route('doctor.appointments.calendar') }}" style="color:var(--txt-lt);text-decoration:none;font-size:.85rem;font-weight:400">Appointments</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    Manage Availability
@endsection

@section('content')
@php
    $days     = ['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday','sunday'=>'Sunday'];
    $duration = config('medtech.appointment.default_duration', 15);
@endphp

@if(session('success'))
<div style="padding:12px 16px;background:#eef5f3;border:1px solid #b5ddd5;border-radius:10px;margin-bottom:16px;font-size:.875rem;color:#2a7a6a;display:flex;align-items:center;gap:8px">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div x-data="slotsManager()" class="fade-in">

{{-- Hidden form — inputs injected by submitForm() before submit --}}
<form id="slots-form" method="POST" action="{{ route('doctor.appointments.slots.save') }}">
    @csrf
    <div id="slots-hidden-inputs"></div>
</form>

<div style="display:grid;grid-template-columns:1fr 260px;gap:20px;align-items:start">

{{-- ── LEFT: Day blocks ─────────────────────────────────────────────────────── --}}
<div class="panel">
    <div style="padding:16px 22px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;color:var(--txt)">Weekly Schedule</div>
            <div style="font-size:.78rem;color:var(--txt-lt);margin-top:2px">Set which days and hours you are available</div>
        </div>
        <div style="font-size:.75rem;color:var(--txt-lt)">{{ $duration }}-min slots</div>
    </div>

    @foreach($days as $dayKey => $dayLabel)
    @php $isWeekend = in_array($dayKey, ['saturday','sunday']); @endphp

    <div style="border-bottom:1px solid var(--warm-bd);padding:14px 22px;transition:background .15s"
         :style="schedule['{{ $dayKey }}'].enabled ? '' : 'background:#fdfcfb;opacity:.75'">

        <div style="display:flex;align-items:flex-start;gap:14px">

            {{-- Toggle --}}
            <div style="padding-top:2px;flex-shrink:0">
                <button type="button"
                        @click="schedule['{{ $dayKey }}'].enabled = !schedule['{{ $dayKey }}'].enabled"
                        style="position:relative;width:38px;height:22px;border:none;border-radius:11px;cursor:pointer;padding:0;transition:background .2s;outline:none;flex-shrink:0"
                        :style="schedule['{{ $dayKey }}'].enabled ? 'background:var(--leaf)' : 'background:#d1d5db'">
                    <span style="position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;top:2px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2)"
                          :style="schedule['{{ $dayKey }}'].enabled ? 'left:18px' : 'left:2px'"></span>
                </button>
            </div>

            {{-- Day name --}}
            <div style="min-width:100px;padding-top:1px">
                <div style="font-size:.9rem;font-weight:600;color:{{ $isWeekend ? 'var(--coral,#c0737a)' : 'var(--txt)' }}">{{ $dayLabel }}</div>
                <div style="font-size:.72rem;color:var(--txt-lt);margin-top:1px"
                     x-text="schedule['{{ $dayKey }}'].enabled ? slotCount('{{ $dayKey }}') + ' slots' : 'Off'"></div>
            </div>

            {{-- Time blocks --}}
            <div x-show="schedule['{{ $dayKey }}'].enabled" style="flex:1;display:flex;flex-direction:column;gap:7px">
                <template x-for="(block, bi) in schedule['{{ $dayKey }}'].blocks" :key="bi">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <div style="display:flex;align-items:center;gap:6px;background:var(--parch);padding:6px 12px;border-radius:9px;border:1.5px solid var(--warm-bd)">
                            <input type="time" x-model="block.start"
                                   @change="validateBlock('{{ $dayKey }}', bi)"
                                   style="border:none;background:transparent;font-size:.875rem;color:var(--txt);outline:none;font-family:'Outfit',sans-serif;cursor:pointer">
                            <span style="font-size:.75rem;color:var(--txt-lt)">to</span>
                            <input type="time" x-model="block.end"
                                   @change="validateBlock('{{ $dayKey }}', bi)"
                                   style="border:none;background:transparent;font-size:.875rem;color:var(--txt);outline:none;font-family:'Outfit',sans-serif;cursor:pointer">
                        </div>
                        <div x-show="blockSlotCount(block) > 0"
                             style="font-size:.72rem;color:var(--txt-lt)"
                             x-text="blockSlotCount(block) + ' slots'"></div>
                        <div x-show="block.error"
                             x-text="block.error"
                             style="font-size:.72rem;color:#dc2626;font-weight:500"></div>
                        <button type="button"
                                @click="removeBlock('{{ $dayKey }}', bi)"
                                x-show="schedule['{{ $dayKey }}'].blocks.length > 1"
                                title="Remove"
                                style="width:26px;height:26px;border:1px solid var(--warm-bd);border-radius:7px;background:transparent;cursor:pointer;color:var(--txt-lt);display:inline-flex;align-items:center;justify-content:center;transition:all .12s;flex-shrink:0"
                                onmouseover="this.style.color='#dc2626';this.style.background='#fef2f2';this.style.borderColor='#fecaca'"
                                onmouseout="this.style.color='var(--txt-lt)';this.style.background='transparent';this.style.borderColor='var(--warm-bd)'">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                <button type="button" @click="addBlock('{{ $dayKey }}')"
                        style="align-self:flex-start;display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--leaf);background:none;border:none;cursor:pointer;font-family:'Outfit',sans-serif;font-weight:500;padding:2px 0">
                    <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add break
                </button>
            </div>

            <div x-show="!schedule['{{ $dayKey }}'].enabled"
                 style="flex:1;font-size:.8rem;color:var(--txt-lt);padding-top:2px">
                Toggle on to enable this day
            </div>
        </div>
    </div>
    @endforeach

    {{-- Quick actions --}}
    <div style="padding:12px 22px;background:var(--parch);display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <span style="font-size:.78rem;color:var(--txt-lt)">Quick:</span>
        <button type="button" @click="copyMonToWeekdays()"
                style="font-size:.75rem;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;background:#fff;cursor:pointer;color:var(--txt-md);font-family:'Outfit',sans-serif;transition:background .12s"
                onmouseover="this.style.background='var(--sage-lt)'" onmouseout="this.style.background='#fff'">
            Copy Mon → Tue–Fri
        </button>
        <button type="button" @click="clearWeekend()"
                style="font-size:.75rem;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;background:#fff;cursor:pointer;color:var(--txt-md);font-family:'Outfit',sans-serif;transition:background .12s"
                onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'">
            Clear weekends
        </button>
        <button type="button" @click="enableAll()"
                style="font-size:.75rem;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;background:#fff;cursor:pointer;color:var(--txt-md);font-family:'Outfit',sans-serif;transition:background .12s"
                onmouseover="this.style.background='var(--sage-lt)'" onmouseout="this.style.background='#fff'">
            Enable all days
        </button>
    </div>
</div>

{{-- ── RIGHT sidebar ───────────────────────────────────────────────────────── --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">

    <div class="panel" style="padding:16px 18px">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:12px">Weekly Summary</div>
        @foreach($days as $dayKey => $dayLabel)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--parch)">
            <span style="font-size:.82rem;font-weight:500;color:var(--txt-md)">{{ substr($dayLabel,0,3) }}</span>
            <template x-if="schedule['{{ $dayKey }}'].enabled">
                <span style="font-size:.78rem;color:var(--leaf);font-weight:600"
                      x-text="slotCount('{{ $dayKey }}') + ' slots'"></span>
            </template>
            <template x-if="!schedule['{{ $dayKey }}'].enabled">
                <span style="font-size:.78rem;color:var(--txt-lt)">Off</span>
            </template>
        </div>
        @endforeach
        <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--warm-bd);display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:.82rem;color:var(--txt-md)">Total / week</span>
            <span style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;font-weight:500;color:var(--txt)"
                  x-text="totalWeekSlots()"></span>
        </div>
    </div>

    <div class="panel" style="padding:14px 18px;background:var(--parch)">
        <div style="font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:7px">Info</div>
        <div style="font-size:.78rem;color:var(--txt-md);line-height:1.6">
            Each slot is <strong>{{ $duration }} minutes</strong>.<br>
            Patients see available dates & times when booking.<br>
            Existing bookings are not affected by changes.
        </div>
    </div>

    <button type="button"
            @click="submitForm()"
            :disabled="saving || hasErrors()"
            style="width:100%;padding:.8rem;background:var(--ink);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .15s"
            :style="(saving || hasErrors()) ? 'opacity:.55;cursor:not-allowed' : ''"
            onmouseover="if(!this.disabled) this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <span x-show="saving" style="width:15px;height:15px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0"></span>
        <span x-text="saving ? 'Saving…' : (hasErrors() ? 'Fix time errors first' : 'Save Availability')"></span>
    </button>

    <div x-show="hasErrors()" style="font-size:.78rem;color:#dc2626;text-align:center;padding:0 4px">
        ⚠ One or more time blocks have errors.
    </div>

    <a href="{{ route('doctor.appointments.calendar') }}"
       style="display:block;text-align:center;font-size:.8125rem;color:var(--txt-lt);text-decoration:none;padding:4px"
       onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">
        ← Back to Calendar
    </a>
</div>

</div>
</div>
@endsection

@push('styles')
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
function slotsManager() {
    const DURATION = {{ $duration }};
    const DAYS     = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const initial  = @json($slots);

    function normalise(raw) {
        if (Array.isArray(raw) && raw.length > 0) {
            return {
                enabled: true,
                blocks:  raw.map(b => ({ start: b.start || '09:00', end: b.end || '13:00', error: '' })),
            };
        }
        return { enabled: false, blocks: [{ start: '09:00', end: '13:00', error: '' }] };
    }

    const norm = {};
    DAYS.forEach(d => { norm[d] = normalise(initial[d]); });

    return {
        schedule: norm,
        saving:   false,

        blockSlotCount(block) {
            if (!block.start || !block.end) return 0;
            const [sh, sm] = block.start.split(':').map(Number);
            const [eh, em] = block.end.split(':').map(Number);
            const mins = (eh * 60 + em) - (sh * 60 + sm);
            return mins > 0 ? Math.floor(mins / DURATION) : 0;
        },

        slotCount(day) {
            if (!this.schedule[day]?.enabled) return 0;
            return this.schedule[day].blocks.reduce((s, b) => s + this.blockSlotCount(b), 0);
        },

        totalWeekSlots() {
            return DAYS.reduce((s, d) => s + this.slotCount(d), 0);
        },

        validateBlock(day, bi) {
            const b = this.schedule[day].blocks[bi];
            if (!b.start || !b.end) { b.error = ''; return; }
            const [sh, sm] = b.start.split(':').map(Number);
            const [eh, em] = b.end.split(':').map(Number);
            b.error = (eh * 60 + em) <= (sh * 60 + sm) ? 'End must be after start' : '';
        },

        hasErrors() {
            return DAYS.some(d =>
                this.schedule[d].enabled &&
                this.schedule[d].blocks.some(b => b.error)
            );
        },

        addBlock(day) {
            const last  = this.schedule[day].blocks.at(-1);
            if (!last?.end) return;
            const [eh, em] = last.end.split(':').map(Number);
            const pad = n => String(n).padStart(2, '0');
            // New block: 30 min after last end, 1 hour duration, capped at 23:00
            const sMin = Math.min(eh * 60 + em + 30, 22 * 60);
            const eMin = Math.min(sMin + 60, 23 * 60);
            this.schedule[day].blocks.push({
                start: `${pad(Math.floor(sMin / 60))}:${pad(sMin % 60)}`,
                end:   `${pad(Math.floor(eMin / 60))}:${pad(eMin % 60)}`,
                error: ''
            });
        },

        removeBlock(day, idx) {
            this.schedule[day].blocks.splice(idx, 1);
        },

        copyMonToWeekdays() {
            const src = this.schedule.monday.blocks.map(b => ({ ...b, error: '' }));
            ['tuesday','wednesday','thursday','friday'].forEach(d => {
                this.schedule[d].enabled = this.schedule.monday.enabled;
                this.schedule[d].blocks  = src.map(b => ({ ...b }));
            });
        },

        clearWeekend() {
            ['saturday','sunday'].forEach(d => { this.schedule[d].enabled = false; });
        },

        enableAll() {
            const src = this.schedule.monday.blocks.map(b => ({ ...b, error: '' }));
            DAYS.forEach(d => {
                this.schedule[d].enabled = true;
                if (!this.schedule[d].blocks.length) {
                    this.schedule[d].blocks = src.map(b => ({ ...b }));
                }
            });
        },

        // ── THE FIX: build hidden inputs from JS state, then submit the <form> ──
        submitForm() {
            if (this.hasErrors() || this.saving) return;
            this.saving = true;

            const container = document.getElementById('slots-hidden-inputs');
            container.innerHTML = '';

            const add = (name, value) => {
                const inp = document.createElement('input');
                inp.type  = 'hidden';
                inp.name  = name;
                inp.value = value;
                container.appendChild(inp);
            };

            DAYS.forEach(day => {
                if (this.schedule[day].enabled) {
                    add(`slots[${day}][enabled]`, '1');
                    this.schedule[day].blocks.forEach((b, bi) => {
                        if (b.start && b.end && !b.error) {
                            add(`slots[${day}][blocks][${bi}][start]`, b.start);
                            add(`slots[${day}][blocks][${bi}][end]`,   b.end);
                        }
                    });
                }
                // disabled days → no inputs → controller saves []
            });

            document.getElementById('slots-form').submit();
        },
    };
}
</script>
@endpush