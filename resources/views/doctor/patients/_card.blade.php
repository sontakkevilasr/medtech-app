@php
    $spec    = $pt->template?->specialty_type ?? 'other';
    $meta    = $specMeta[$spec] ?? ['color'=>'#6b7280','bg'=>'#f3f4f6','label'=>ucfirst($spec)];
    $pct     = $pt->progress_pct ?? 0;
    $next    = $pt->next_milestone;
    $forName = $pt->familyMember?->full_name ?? null;
@endphp
<a href="{{ route('patient.timelines.show', $pt->id) }}" style="text-decoration:none">
<div class="panel tl-card" style="padding:0;overflow:hidden">

    {{-- Top band --}}
    <div style="background:linear-gradient(135deg,{{ $meta['color'] }} 0%,{{ $meta['color'] }}99 100%);padding:18px 20px;position:relative;overflow:hidden">
        <div style="position:absolute;right:-20px;top:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.07)"></div>
        <div style="position:relative;z-index:1">
            <div style="display:flex;align-items:flex-start;justify-content:space-between">
                <div>
                    <span class="spec-badge" style="background:rgba(255,255,255,.2);color:#fff;margin-bottom:6px;display:inline-block">
                        {{ $meta['label'] }}
                    </span>
                    <div style="font-family:'Lora',serif;font-size:1.05rem;font-weight:500;color:#fff;line-height:1.3">
                        {{ $pt->template?->title ?? 'Unnamed Timeline' }}
                    </div>
                    @if($forName)
                    <div style="font-size:.72rem;color:rgba(255,255,255,.7);margin-top:3px">For {{ $forName }}</div>
                    @endif
                </div>
                <div style="font-size:2rem;opacity:.8">
                    @php
                    $specIcon = match($spec) {
                        'obstetrics' => '🤰',
                        'pediatrics' => '👶',
                        'ivf'        => '🧬',
                        'dental'     => '🦷',
                        'cardiology' => '❤️',
                        default      => '📅',
                    };
                    @endphp
                    {{ $specIcon }}
                </div>
            </div>

            {{-- Progress bar --}}
            <div style="margin-top:14px">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px">
                    <span style="font-size:.68rem;color:rgba(255,255,255,.7)">Progress</span>
                    <span style="font-size:.72rem;font-weight:700;color:#fff">{{ $pct }}%</span>
                </div>
                <div style="height:5px;border-radius:3px;background:rgba(255,255,255,.25);overflow:hidden">
                    <div style="height:100%;border-radius:3px;background:#fff;width:{{ $pct }}%"></div>
                </div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.6);margin-top:4px">
                    {{ $pt->milestones_done ?? 0 }} of {{ $pt->milestones_total ?? 0 }} milestones complete
                </div>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div style="padding:14px 18px">
        <div style="display:flex;gap:14px;margin-bottom:10px">
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--txt-lt);margin-bottom:2px">Started</div>
                <div style="font-size:.8125rem;font-weight:500;color:var(--txt-md)">{{ $pt->start_date->format('d M Y') }}</div>
            </div>
            @if($pt->expected_end_date)
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--txt-lt);margin-bottom:2px">Expected End</div>
                <div style="font-size:.8125rem;font-weight:500;color:var(--txt-md)">{{ $pt->expected_end_date->format('d M Y') }}</div>
            </div>
            @endif
            @if($pt->assignedByDoctor)
            <div>
                <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;font-weight:700;color:var(--txt-lt);margin-bottom:2px">Assigned by</div>
                <div style="font-size:.8125rem;font-weight:500;color:var(--txt-md)">{{ $pt->assignedByDoctor->profile?->full_name }}</div>
            </div>
            @endif
        </div>

        {{-- Next milestone --}}
        @if($next)
        <div style="padding:9px 12px;border-radius:9px;background:{{ $meta['bg'] }};border:1px solid {{ $meta['color'] }}22;display:flex;align-items:center;gap:9px">
            <span style="font-size:1.1rem">{{ $next->icon ?? '📋' }}</span>
            <div style="flex:1;min-width:0">
                <div style="font-size:.78rem;font-weight:600;color:var(--txt)">
                    {{ $next->is_today ? '🔴 Today: ' : '' }}{{ $next->title }}
                </div>
                <div style="font-size:.7rem;color:var(--txt-lt)">
                    @if($next->is_today)
                        Due today
                    @elseif($next->days_away > 0)
                        In {{ $next->days_away }} day{{ $next->days_away != 1 ? 's' : '' }} · {{ $next->actual_date->format('d M Y') }}
                    @else
                        {{ abs($next->days_away) }} days ago
                    @endif
                </div>
            </div>
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="{{ $meta['color'] }}" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </div>
        @else
        <div style="font-size:.8rem;color:var(--txt-lt);text-align:center;padding:8px">
            ✅ All milestones complete!
        </div>
        @endif
    </div>
</div>
</a>
