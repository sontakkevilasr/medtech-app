@extends('layouts.patient')
@section('title', 'Health Tracker')
@section('page-title', 'Health Tracker')

@push('styles')
<style>
/* Vital status chips */
.vs-ok      { background: var(--sage-lt); color: #2a7a6a; border: 1px solid #b5ddd5; }
.vs-warning { background: var(--amber-lt); color: #8a5c10; border: 1px solid #f0c97a; }
.vs-danger  { background: var(--rose-lt); color: #8a3a40; border: 1px solid #f0b0b5; }
.vs-na      { background: var(--sand); color: var(--txt-lt); border: 1px solid var(--warm-bd); }

/* Vital cards */
.vital-card { transition: box-shadow .18s; cursor: pointer; }
.vital-card:hover { box-shadow: 0 4px 22px rgba(74,55,96,.12); }
.vital-card.active-vital { border-color: var(--plum) !important; box-shadow: 0 0 0 2px rgba(74,55,96,.12); }

/* Chart tab buttons */
.chart-tab { padding: 5px 14px; border-radius: 7px; font-size: .78rem; font-weight: 500; border: 1.5px solid var(--warm-bd); color: var(--txt-md); background: transparent; cursor: pointer; font-family: 'Plus Jakarta Sans', sans-serif; transition: all .15s; }
.chart-tab.active { background: var(--plum); color: #fff; border-color: var(--plum); }

/* Log form inputs */
.log-inp { width: 100%; padding: .55rem .8rem; border: 1.5px solid var(--warm-bd); border-radius: 9px; font-size: .875rem; color: var(--txt); background: #fff; outline: none; font-family: 'Plus Jakarta Sans', sans-serif; transition: border-color .15s; }
.log-inp:focus { border-color: var(--plum); }

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeSlide { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
.fade-slide { animation: fadeSlide .22s ease; }
</style>
@endpush

@section('content')
@php
    $vitals = [
        'bp'          => ['label'=>'Blood Pressure', 'icon'=>'❤️',  'unit'=>'mmHg', 'two'=>true,  'color'=>'#c0737a', 'bg'=>'#fce7ef'],
        'sugar'       => ['label'=>'Blood Sugar',    'icon'=>'🩸',  'unit'=>'mg/dL','two'=>false, 'color'=>'#c98a3a', 'bg'=>'#fdf5e8'],
        'weight'      => ['label'=>'Weight',         'icon'=>'⚖️',  'unit'=>'kg',   'two'=>false, 'color'=>'#4a3760', 'bg'=>'#f0ecf7'],
        'oxygen'      => ['label'=>'Oxygen (SpO₂)',  'icon'=>'💨',  'unit'=>'%',    'two'=>false, 'color'=>'#3d7a8a', 'bg'=>'#e8f5f9'],
        'temperature' => ['label'=>'Temperature',    'icon'=>'🌡️', 'unit'=>'°C',   'two'=>false, 'color'=>'#6a9e8e', 'bg'=>'#eef5f3'],
        'pulse'       => ['label'=>'Pulse',          'icon'=>'💓',  'unit'=>'bpm',  'two'=>false, 'color'=>'#8a6aaa', 'bg'=>'#f4f0fa'],
    ];
    $statusClass = ['ok'=>'vs-ok','warning'=>'vs-warning','danger'=>'vs-danger'];
@endphp

<div x-data="healthTracker()" x-init="init()" class="fade-slide">

{{-- ── Family member switcher ───────────────────────────────────────────────── -- --}}
@if($patient->familyMembers->isNotEmpty())
<div style="display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
    <a href="{{ route('patient.health.index') }}"
       class="{{ !$memberId ? 'active' : '' }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ !$memberId ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ !$memberId ? 'var(--plum)' : 'transparent' }};color:{{ !$memberId ? '#fff' : 'var(--txt-md)' }};transition:all .15s">
        {{ $patient->profile?->full_name ?? 'Me' }}
    </a>
    @foreach($patient->familyMembers as $fm)
    <a href="{{ route('patient.health.index', ['member'=>$fm->id]) }}"
       style="padding:5px 14px;border-radius:20px;font-size:.8rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $memberId==$fm->id ? 'var(--plum)' : 'var(--warm-bd)' }};background:{{ $memberId==$fm->id ? 'var(--plum)' : 'transparent' }};color:{{ $memberId==$fm->id ? '#fff' : 'var(--txt-md)' }};transition:all .15s">
        {{ $fm->full_name }}
    </a>
    @endforeach
</div>
@endif

{{-- ── Stats strip ──────────────────────────────────────────────────────────── -- --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:22px">
    @php
    $strips = [
        ['v'=>$stats['logs_7d'],      'l'=>'Last 7 days',    'sub'=>'readings logged'],
        ['v'=>$stats['logs_30d'],     'l'=>'Last 30 days',   'sub'=>'readings logged'],
        ['v'=>$stats['types_tracked'],'l'=>'Vitals tracked', 'sub'=>'of 6 types'],
        ['v'=>$stats['last_logged'] ? \Carbon\Carbon::parse($stats['last_logged'])->diffForHumans() : 'Never',
         'l'=>'Last logged', 'sub'=>'most recent entry', 'raw'=>true],
    ];
    @endphp
    @foreach($strips as $s)
    <div class="panel" style="padding:14px 16px;text-align:center">
        <div style="font-family:'Lora',serif;font-size:{{ $s['raw']??false ? '1.1rem' : '1.8rem' }};font-weight:500;color:var(--txt);line-height:1.2">
            {{ $s['v'] }}
        </div>
        <div style="font-size:.72rem;font-weight:600;color:var(--txt-md);margin-top:3px">{{ $s['l'] }}</div>
        <div style="font-size:.68rem;color:var(--txt-lt)">{{ $s['sub'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── Main grid: LEFT charts + RIGHT log form ─────────────────────────────── -- --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

{{-- ─── LEFT ────────────────────────────────────────────────────────────────── -- --}}
<div style="display:flex;flex-direction:column;gap:18px">

    {{-- 6 vital summary cards (clickable → loads chart) --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px">
        @foreach($vitals as $key => $cfg)
        @php
            $reading = $latestReadings[$key] ?? null;
            $st      = $reading ? ($statusClass[$reading['status']] ?? 'vs-ok') : 'vs-na';
        @endphp
        <div class="panel vital-card"
             :class="activeType === '{{ $key }}' ? 'active-vital' : ''"
             @click="selectType('{{ $key }}')"
             style="padding:14px 16px;border:1.5px solid var(--warm-bd);cursor:pointer">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                <span style="font-size:1.2rem">{{ $cfg['icon'] }}</span>
                <span class="hs-badge {{ $st }}" style="font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:20px">
                    {{ $reading ? ucfirst($reading['status']) : 'No data' }}
                </span>
            </div>
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:3px">
                {{ $cfg['label'] }}
            </div>
            @if($reading)
            <div style="font-family:'Lora',serif;font-size:1.3rem;font-weight:500;color:var(--txt);line-height:1.1">
                {{ $reading['value'] }}
            </div>
            <div style="font-size:.7rem;color:var(--txt-lt);margin-top:3px">
                {{ $reading['logged_at']->diffForHumans() }}
                @if($reading['context']) · {{ str_replace('_',' ',$reading['context']) }} @endif
            </div>
            @else
            <div style="font-family:'Lora',serif;font-size:1.1rem;color:var(--txt-lt)">—</div>
            <div style="font-size:.7rem;color:var(--txt-lt);margin-top:3px">Not logged yet</div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Chart panel --}}
    <div class="panel" style="padding:0;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
            <div>
                <div x-text="vitalLabel" style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)"></div>
                <div x-text="chartSubtitle" style="font-size:.72rem;color:var(--txt-lt);margin-top:1px"></div>
            </div>
            <div style="display:flex;gap:5px">
                @foreach([7=>'7d',14=>'14d',30=>'30d',60=>'60d'] as $d => $lbl)
                <button type="button" class="chart-tab" :class="activeDays==={{ $d }} ? 'active' : ''"
                        @click="selectDays({{ $d }})">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>

        <div style="padding:20px;position:relative;min-height:240px">
            {{-- Loading state --}}
            <div x-show="chartLoading" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(255,252,248,.8)">
                <div style="width:24px;height:24px;border:2.5px solid var(--warm-bd);border-top-color:var(--plum);border-radius:50%;animation:spin .6s linear infinite"></div>
            </div>

            {{-- No data --}}
            <div x-show="!chartLoading && chartEmpty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:200px;color:var(--txt-lt);text-align:center">
                <div style="font-size:2rem;margin-bottom:8px">📊</div>
                <div style="font-family:'Lora',serif;font-size:.9375rem;color:var(--txt-md)">No data yet</div>
                <p style="font-size:.78rem;margin-top:4px">Log a reading to see your chart here.</p>
            </div>

            <canvas id="vitalChart" style="max-height:220px" x-show="!chartLoading && !chartEmpty"></canvas>
        </div>

        {{-- Reference range legend --}}
        <div style="padding:10px 18px;border-top:1px solid var(--warm-bd);display:flex;gap:14px;flex-wrap:wrap" x-show="!chartEmpty">
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-md)">
                <div style="width:12px;height:3px;border-radius:2px;background:#6a9e8e"></div> Normal
            </div>
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-md)">
                <div style="width:12px;height:3px;border-radius:2px;background:#c98a3a"></div> Warning
            </div>
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--txt-md)">
                <div style="width:12px;height:3px;border-radius:2px;background:#c0737a"></div> High/Low
            </div>
            <span x-text="chartCount + ' readings'" style="margin-left:auto;font-size:.72rem;color:var(--txt-lt)"></span>
        </div>
    </div>

    {{-- Recent log table --}}
    <div class="panel" style="padding:0;overflow:hidden">
        <div style="padding:12px 18px;border-bottom:1px solid var(--warm-bd);display:flex;align-items:center;justify-content:space-between">
            <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt)">Recent Readings</div>
            <a href="{{ route('patient.health.logs', $memberId ? ['member'=>$memberId] : []) }}"
               style="font-size:.75rem;color:var(--plum);text-decoration:none">Full history →</a>
        </div>
        @if($logs->isEmpty())
        <div style="padding:28px;text-align:center;color:var(--txt-lt);font-size:.875rem">
            No readings logged yet.
        </div>
        @else
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1.5px solid var(--warm-bd)">
                    <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Type</th>
                    <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Reading</th>
                    <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Context</th>
                    <th style="padding:8px 16px;text-align:left;font-size:.65rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--txt-lt)">Date & Time</th>
                    <th style="padding:8px 16px"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($logs->take(15) as $log)
            @php
                $vcfg  = $vitals[$log->log_type] ?? ['icon'=>'📋','label'=>ucfirst($log->log_type),'color'=>'#888','bg'=>'#f3f4f6'];
                $stKey = in_array($log->log_type,['bp','sugar','oxygen','pulse','temperature']) ? 'ok' : 'ok'; // simplified
            @endphp
            <tr style="border-bottom:1px solid var(--warm-bd);transition:background .1s" onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
                <td style="padding:10px 16px">
                    <div style="display:flex;align-items:center;gap:7px">
                        <span style="font-size:.95rem">{{ $vcfg['icon'] }}</span>
                        <span style="font-size:.8rem;font-weight:500;color:var(--txt-md)">{{ $vcfg['label'] }}</span>
                    </div>
                </td>
                <td style="padding:10px 16px;font-family:'Lora',serif;font-size:.95rem;font-weight:500;color:var(--txt)">
                    {{ $log->formatted_value }}
                </td>
                <td style="padding:10px 16px">
                    @if($log->context)
                    <span style="font-size:.7rem;padding:2px 8px;border-radius:20px;background:var(--sand);color:var(--txt-md)">
                        {{ ucwords(str_replace('_',' ',$log->context)) }}
                    </span>
                    @else
                    <span style="color:var(--txt-lt);font-size:.8rem">—</span>
                    @endif
                </td>
                <td style="padding:10px 16px;font-size:.78rem;color:var(--txt-lt)">
                    {{ $log->logged_at->format('d M Y') }}<br>
                    <span style="font-size:.7rem">{{ $log->logged_at->format('h:i A') }}</span>
                </td>
                <td style="padding:10px 16px;text-align:right">
                    <form method="POST" action="{{ route('patient.health.logs.destroy', $log) }}" style="display:inline"
                          onsubmit="return confirm('Delete this reading?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="width:24px;height:24px;border:1px solid #fecaca;border-radius:6px;background:transparent;color:#dc2626;cursor:pointer;font-size:.7rem;display:inline-flex;align-items:center;justify-content:center" title="Delete">×</button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- ─── RIGHT: Log entry form ────────────────────────────────────────────────── -- --}}
<div style="position:sticky;top:78px;display:flex;flex-direction:column;gap:14px">
    <div class="panel" style="padding:20px 22px">
        <div style="font-family:'Lora',serif;font-size:1rem;font-weight:500;color:var(--txt);margin-bottom:4px">Log a Reading</div>
        <div style="font-size:.75rem;color:var(--txt-lt);margin-bottom:16px">Record your latest vital sign</div>

        <form method="POST" action="{{ route('patient.health.logs.store') }}" x-data="logForm()">
            @csrf
            @if($memberId)
            <input type="hidden" name="family_member_id" value="{{ $memberId }}">
            @endif

            {{-- Vital type selector --}}
            <div style="margin-bottom:14px">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Vital Type</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px">
                    @foreach($vitals as $key => $cfg)
                    <label style="cursor:pointer">
                        <input type="radio" name="log_type" value="{{ $key }}" x-model="logType" style="display:none">
                        <div @click="logType='{{ $key }}'"
                             :style="logType==='{{ $key }}' ? 'background:{{ $cfg['bg'] }};border-color:{{ $cfg['color'] }};color:{{ $cfg['color'] }}' : ''"
                             style="display:flex;align-items:center;gap:5px;padding:6px 9px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.76rem;font-weight:500;color:var(--txt-md);transition:all .15s;cursor:pointer">
                            <span>{{ $cfg['icon'] }}</span>
                            <span>{{ explode(' ',$cfg['label'])[0] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Value inputs (dynamic based on type) --}}
            <div style="margin-bottom:12px">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                    <span x-text="logType === 'bp' ? 'Systolic (upper)' : 'Value'"></span>
                    <span x-text="' (' + unitFor(logType) + ')'" style="font-weight:400;text-transform:none"></span>
                </label>
                <input type="number" name="value_1" x-ref="val1"
                       step="0.1" min="0" max="999"
                       :placeholder="logType === 'bp' ? 'e.g. 120' : logType === 'sugar' ? 'e.g. 95' : logType === 'weight' ? 'e.g. 70' : logType === 'oxygen' ? 'e.g. 98' : logType === 'temperature' ? 'e.g. 37.0' : 'e.g. 72'"
                       class="log-inp" required>
            </div>

            {{-- BP diastolic --}}
            <div style="margin-bottom:12px" x-show="logType === 'bp'" x-transition>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                    Diastolic (lower) <span style="font-weight:400">(mmHg)</span>
                </label>
                <input type="number" name="value_2" step="1" min="30" max="200"
                       placeholder="e.g. 80" class="log-inp" :required="logType === 'bp'">
            </div>

            {{-- Context --}}
            <div style="margin-bottom:12px" x-show="['sugar','bp','pulse'].includes(logType)">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Context</label>
                <select name="context" class="log-inp">
                    <option value="">— Optional —</option>
                    <option value="fasting">Fasting</option>
                    <option value="post_meal">Post-meal (2hr)</option>
                    <option value="random">Random</option>
                    <option value="morning">Morning</option>
                    <option value="night">Night</option>
                    <option value="other">Other</option>
                </select>
            </div>

            {{-- Date & time --}}
            <div style="margin-bottom:12px">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Date & Time</label>
                <input type="datetime-local" name="logged_at"
                       value="{{ now()->format('Y-m-d\TH:i') }}"
                       max="{{ now()->format('Y-m-d\TH:i') }}"
                       class="log-inp">
            </div>

            {{-- Notes --}}
            <div style="margin-bottom:16px">
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">Notes (optional)</label>
                <input type="text" name="notes" placeholder="Any notes…" class="log-inp" maxlength="255">
            </div>

            <button type="submit"
                    style="width:100%;padding:.75rem;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:opacity .15s;display:flex;align-items:center;justify-content:center;gap:8px"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Log Reading
            </button>
        </form>
    </div>

    {{-- Quick tips --}}
    <div class="panel" style="padding:14px 16px;background:var(--sand)">
        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);margin-bottom:8px">Tips</div>
        <div style="font-size:.75rem;color:var(--txt-md);line-height:1.6">
            • Log BP at the same time each day<br>
            • Sugar: mark fasting or post-meal<br>
            • Take 3 readings, log the average<br>
            • Rest 5 min before measuring
        </div>
    </div>

    {{-- Reminders shortcut --}}
    <a href="{{ route('patient.reminders.index') }}"
       style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border:1.5px solid var(--warm-bd);border-radius:12px;text-decoration:none;color:var(--txt-md);background:transparent;transition:all .15s"
       onmouseover="this.style.background='var(--sand)'" onmouseout="this.style.background='transparent'">
        <div style="display:flex;align-items:center;gap:9px">
            <span style="font-size:1.2rem">⏰</span>
            <div>
                <div style="font-size:.8125rem;font-weight:600;color:var(--txt)">Medication Reminders</div>
                <div style="font-size:.7rem;color:var(--txt-lt)">Set up WhatsApp / SMS alerts</div>
            </div>
        </div>
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;color:var(--txt-lt)"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

</div>{{-- end main grid --}}
</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const VITALS_META = @json($vitals);
const MEMBER_PARAM = @json($memberId ? '&member='.$memberId : '');

let chartInstance = null;

function healthTracker() {
    return {
        activeType:    'bp',
        activeDays:    30,
        chartLoading:  false,
        chartEmpty:    false,
        chartCount:    0,
        vitalLabel:    'Blood Pressure',
        chartSubtitle: '',

        init() {
            this.selectType('{{ array_key_first($latestReadings) ?? 'bp' }}');
        },

        selectType(type) {
            this.activeType  = type;
            this.vitalLabel  = VITALS_META[type]?.label ?? type;
            this.loadChart();
        },

        selectDays(days) {
            this.activeDays = days;
            this.loadChart();
        },

        async loadChart() {
            this.chartLoading = true;
            this.chartEmpty   = false;

            try {
                const res  = await fetch(`/patient/health/chart/${this.activeType}?days=${this.activeDays}${MEMBER_PARAM}`);
                const json = await res.json();

                this.chartCount    = json.count;
                this.chartSubtitle = `Last ${this.activeDays} days · ${json.count} reading${json.count !== 1 ? 's' : ''}`;

                if (json.count === 0) {
                    this.chartEmpty = true;
                    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
                    return;
                }

                this.renderChart(json);
            } catch (e) {
                this.chartEmpty = true;
            } finally {
                this.chartLoading = false;
            }
        },

        renderChart(json) {
            const ctx    = document.getElementById('vitalChart').getContext('2d');
            const meta   = VITALS_META[json.type] ?? {};
            const labels = json.data.map(d => d.date);
            const color  = meta.color ?? '#4a3760';

            if (chartInstance) chartInstance.destroy();

            const datasets = [{
                label:       meta.label ?? json.type,
                data:        json.data.map(d => d.v1),
                borderColor: color,
                backgroundColor: color + '18',
                fill:        true,
                tension:     0.4,
                pointRadius: 4,
                pointHoverRadius: 7,
                pointBackgroundColor: json.data.map(d => {
                    // colour each point by status
                    return color;
                }),
            }];

            // Second line for BP diastolic
            if (json.type === 'bp' && json.data.some(d => d.v2 !== null)) {
                datasets.push({
                    label:       'Diastolic',
                    data:        json.data.map(d => d.v2),
                    borderColor: '#c98a3a',
                    backgroundColor: 'transparent',
                    fill:        false,
                    tension:     0.4,
                    pointRadius: 3,
                    borderDash:  [4, 3],
                });
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: json.type === 'bp',
                            position: 'top',
                            labels: { font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 10 }
                        },
                        tooltip: {
                            callbacks: {
                                afterLabel: (ctx) => {
                                    const d = json.data[ctx.dataIndex];
                                    return d?.context ? `Context: ${d.context.replace('_',' ')}` : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, maxTicksLimit: 10 }
                        },
                        y: {
                            grid: { color: '#ede8e0' },
                            ticks: { font: { family: 'Plus Jakarta Sans', size: 10 }, precision: 1 }
                        }
                    }
                }
            });
        }
    };
}

function logForm() {
    return {
        logType: 'bp',
        units: {
            bp: 'mmHg', sugar: 'mg/dL', weight: 'kg',
            oxygen: '%', temperature: '°C', pulse: 'bpm'
        },
        unitFor(t) { return this.units[t] ?? ''; }
    };
}
</script>
@endpush
