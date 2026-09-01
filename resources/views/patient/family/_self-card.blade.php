{{-- Self-member card (rendered above the primary Sub-ID card) --}}
@if($self)
@php
    $selfDob   = $self->dob ? $self->dob->format('d M Y') : '—';
    $selfAge   = $self->dob ? $self->dob->age : null;
    $selfAgeSx = $selfAge ? ' · Age '.$selfAge : '';
    $selfGen   = $self->gender ? ucfirst($self->gender) : '—';
    $selfInit  = strtoupper(substr($self->full_name ?? 'Y', 0, 1));
    $selfColor = $relationColors['self'] ?? '#4a3760';
@endphp
<div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;overflow:hidden;margin-bottom:18px;transition:box-shadow .15s"
     onmouseover="this.style.boxShadow='0 4px 14px rgba(74,55,96,.10)'" onmouseout="this.style.boxShadow='none'">

    {{-- Plum header with initials avatar --}}
    <div style="background:linear-gradient(135deg,{{ $selfColor }} 0%,{{ $selfColor }}dd 100%);padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px">
        <div style="display:flex;align-items:center;gap:12px;min-width:0">
            <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.18);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;flex-shrink:0">
                {{ $selfInit }}
            </div>
            <div style="min-width:0">
                <div style="font-size:1.1rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $self->full_name }}</div>
                <div style="font-size:.72rem;color:rgba(255,255,255,.75);margin-top:2px">
                    {{ $selfDob }}{{ $selfAgeSx }} · {{ $selfGen }}
                </div>
            </div>
        </div>
        <span style="font-size:.66rem;font-weight:700;letter-spacing:.08em;padding:3px 9px;border-radius:20px;background:rgba(255,255,255,.2);color:#fff;flex-shrink:0">SELF</span>
    </div>

    {{-- Sub-ID row with Copy + WhatsApp --}}
    <div style="padding:12px 20px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--warm-bd)">
        <div style="flex:1;min-width:0">
            <div style="font-size:.66rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Sub-ID</div>
            <code style="font-family:'JetBrains Mono',ui-monospace,monospace;font-size:.875rem;font-weight:600;color:var(--txt);letter-spacing:.04em">{{ $selfId }}</code>
        </div>
        <div style="display:flex;gap:5px;flex-shrink:0">
            <button type="button"
                    onclick="navigator.clipboard.writeText('{{ $selfId }}').then(()=>{ const b=this; b.textContent='✓'; setTimeout(()=>b.textContent='⧉',1500) })"
                    style="width:28px;height:28px;border:1px solid var(--warm-bd);border-radius:7px;background:transparent;cursor:pointer;color:var(--txt-lt);display:flex;align-items:center;justify-content:center;transition:all .12s"
                    onmouseover="this.style.background='var(--parch)';this.style.color='var(--txt-md)'" onmouseout="this.style.background='transparent';this.style.color='var(--txt-lt)'"
                    title="Copy Sub-ID">⧉</button>
            <a href="https://wa.me/?text={{ urlencode('My Naumah Clinic Sub-ID is: '.$selfId.' — share with your doctor for quick record lookup.') }}"
               target="_blank"
               style="width:28px;height:28px;border:1px solid #bbf7d0;border-radius:7px;background:#f0fdf4;cursor:pointer;color:#15803d;display:flex;align-items:center;justify-content:center;text-decoration:none;transition:all .12s"
               onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'"
               title="Share via WhatsApp">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.997 0C5.373 0 0 5.373 0 12c0 2.115.555 4.102 1.523 5.827L.057 23.999l6.307-1.654A11.954 11.954 0 0011.997 24c6.624 0 11.997-5.373 11.997-12S18.621 0 11.997 0zm0 21.818a9.818 9.818 0 01-5.003-1.368l-.359-.213-3.742.981 1-3.634-.233-.374A9.786 9.786 0 012.182 12c0-5.414 4.402-9.818 9.815-9.818 5.414 0 9.818 4.404 9.818 9.818 0 5.413-4.404 9.818-9.818 9.818z"/></svg>
            </a>
        </div>
    </div>

    {{-- Actions row --}}
    <div style="padding:10px 20px;display:flex;gap:6px;justify-content:flex-end;align-items:center">
        @if($self->blood_group)
        <span style="font-size:.7rem;padding:3px 8px;border-radius:6px;background:var(--parch);color:var(--txt-lt);margin-right:auto">{{ $self->blood_group }}</span>
        @endif
        <a href="{{ route('patient.family.show', $self->id) }}"
           style="font-size:.75rem;font-weight:500;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:all .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">View →</a>
        <a href="{{ route('patient.profile.edit') }}"
           style="font-size:.75rem;font-weight:500;padding:5px 11px;border:1.5px solid var(--warm-bd);border-radius:8px;color:var(--txt-md);text-decoration:none;transition:all .12s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">Edit</a>
    </div>
</div>
@endif
