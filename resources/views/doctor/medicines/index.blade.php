@extends('layouts.doctor')
@section('title', 'My Medicine List')
@section('page-title', 'Medicine List')

@push('styles')
<style>
.med-table th { font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--txt-lt);padding:10px 14px;border-bottom:1.5px solid var(--warm-bd);white-space:nowrap; }
.med-table td { padding:10px 14px;border-bottom:1px solid var(--parch);font-size:.8625rem;color:var(--txt);vertical-align:middle; }
.med-table tr:last-child td { border-bottom:none; }
.med-table tr:hover td { background:var(--parch); }
.badge-form { display:inline-block;padding:2px 8px;border-radius:20px;font-size:.68rem;font-weight:600;background:var(--parch);color:var(--txt-md);border:1px solid var(--warm-bd); }
.inp { width:100%;padding:.5rem .75rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8625rem;color:var(--txt);background:#fff;outline:none;font-family:'Outfit',sans-serif;transition:border-color .15s;box-sizing:border-box; }
.inp:focus { border-color:var(--leaf); }
.inp.err { border-color:#ef4444; }
select.inp { cursor:pointer; }
.med-fg-3 { grid-template-columns:2fr 1.5fr 1fr; }
.med-fg-5 { grid-template-columns:1fr 1fr 1fr 1fr 2fr; }
.med-fg-7 { grid-template-columns:2fr 1.5fr 1fr 1fr 1fr 1fr 1fr; }
@media (max-width:900px) {
    .med-fg-3, .med-fg-5, .med-fg-7 { grid-template-columns:1fr 1fr; }
}
@media (max-width:500px) {
    .med-fg-3, .med-fg-5, .med-fg-7 { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')

@if(session('success'))
<div style="padding:11px 16px;background:#eef5f3;border:1px solid #b5ddd5;border-radius:10px;margin-bottom:16px;font-size:.875rem;color:#2a7a6a;display:flex;align-items:center;gap:8px">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div x-data="medicineManager()" x-init="init()">

{{-- ── Header row ──────────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:18px">
    <div>
        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:500;color:var(--txt);margin:0">My Medicine List</h2>
        <p style="font-size:.78rem;color:var(--txt-lt);margin:3px 0 0">
            <strong style="color:var(--txt)">{{ $total }}</strong> medicine{{ $total !== 1 ? 's' : '' }} saved · used as fast-fill suggestions when writing prescriptions
        </p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <a href="{{ route('doctor.prescriptions.create') }}"
           style="display:flex;align-items:center;gap:6px;padding:8px 14px;border:1.5px solid var(--warm-bd);border-radius:9px;background:transparent;font-size:.8125rem;color:var(--txt-md);text-decoration:none;font-family:'Outfit',sans-serif;transition:all .15s"
           onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            New Prescription
        </a>
        <button type="button" @click="showAdd = !showAdd"
                style="display:flex;align-items:center;gap:6px;padding:8px 16px;background:var(--ink,#2d2d2d);color:#fff;border:none;border-radius:9px;font-size:.8125rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s"
                onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span x-text="showAdd ? 'Cancel' : 'Add Medicine'"></span>
        </button>
    </div>
</div>

{{-- ── Add Medicine Form ────────────────────────────────────────────────── --}}
<div x-show="showAdd" x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="panel" style="padding:20px 22px;margin-bottom:18px;border:1.5px solid var(--leaf,#3d7a5c)">
    <div style="font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--leaf);margin-bottom:14px">New Medicine</div>
    <form @submit.prevent="submitAdd()">
        <div class="med-fg-3" style="display:grid;gap:12px;margin-bottom:12px">
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Medicine Name *</label>
                <input type="text" x-model="addForm.medicine_name" class="inp" :class="addErr.medicine_name?'err':''" placeholder="e.g. Paracetamol" autocomplete="off">
                <p x-show="addErr.medicine_name" x-text="addErr.medicine_name" style="font-size:.7rem;color:#ef4444;margin-top:3px"></p>
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Generic Name</label>
                <input type="text" x-model="addForm.generic_name" class="inp" placeholder="e.g. Acetaminophen" autocomplete="off">
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Form</label>
                <select x-model="addForm.form" class="inp">
                    <option value="">Select…</option>
                    <option>Tablet</option><option>Capsule</option><option>Syrup</option>
                    <option>Drops</option><option>Injection</option><option>Cream</option>
                    <option>Ointment</option><option>Inhaler</option><option>Sachet</option>
                    <option>Suppository</option><option>Patch</option><option>Other</option>
                </select>
            </div>
        </div>
        <div class="med-fg-5" style="display:grid;gap:12px;margin-bottom:14px">
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Dosage</label>
                <input type="text" x-model="addForm.dosage" class="inp" placeholder="500mg">
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Frequency</label>
                <select x-model="addForm.frequency" class="inp">
                    <option value="">—</option>
                    <option>Once daily</option><option>Twice daily</option>
                    <option>Three times daily</option><option>Four times daily</option>
                    <option>Every 6 hours</option><option>Every 8 hours</option>
                    <option>Every 12 hours</option><option>Once weekly</option>
                    <option>As needed (SOS)</option><option>At bedtime</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Timing</label>
                <select x-model="addForm.timing" class="inp">
                    <option value="">—</option>
                    <option value="before_food">Before food</option>
                    <option value="after_food">After food</option>
                    <option value="with_food">With food</option>
                    <option value="empty_stomach">Empty stomach</option>
                    <option value="bed_time">Bedtime</option>
                    <option value="any_time">Anytime</option>
                </select>
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Duration (days)</label>
                <input type="number" x-model="addForm.duration_days" class="inp" placeholder="5" min="1" max="365">
            </div>
            <div>
                <label style="display:block;font-size:.7rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:4px">Special Instructions</label>
                <input type="text" x-model="addForm.special_instructions" class="inp" placeholder="e.g. Avoid alcohol">
            </div>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" :disabled="saving"
                    style="display:flex;align-items:center;gap:6px;padding:8px 20px;background:var(--leaf,#3d7a5c);color:#fff;border:none;border-radius:9px;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:opacity .15s"
                    :style="saving?'opacity:.6;cursor:not-allowed':''">
                <span x-show="saving" style="width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0"></span>
                <span x-text="saving ? 'Saving…' : 'Save Medicine'"></span>
            </button>
            <button type="button" @click="resetAdd()"
                    style="padding:8px 14px;border:1.5px solid var(--warm-bd);border-radius:9px;background:transparent;font-size:.875rem;color:var(--txt-md);cursor:pointer;font-family:'Outfit',sans-serif">
                Clear
            </button>
        </div>
    </form>
</div>

{{-- ── Filter bar ──────────────────────────────────────────────────────── --}}
<div class="panel" style="padding:12px 16px;margin-bottom:2px">
    <form method="GET" action="{{ route('doctor.medicines.index') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
        <div style="flex:1;min-width:200px;position:relative">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="var(--txt-lt)" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%)"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Search by name or generic…"
                   style="width:100%;padding:.5rem .75rem .5rem 32px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;color:var(--txt);background:#fff;outline:none;font-family:'Outfit',sans-serif;box-sizing:border-box"
                   onfocus="this.style.borderColor='var(--leaf)'" onblur="this.style.borderColor='var(--warm-bd)'">
        </div>
        @if($forms->isNotEmpty())
        <select name="form" onchange="this.form.submit()"
                style="padding:.5rem .75rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;color:var(--txt-md);background:#fff;cursor:pointer;font-family:'Outfit',sans-serif;outline:none">
            <option value="">All forms</option>
            @foreach($forms as $f)
            <option value="{{ $f }}" {{ request('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
            @endforeach
        </select>
        @endif
        @if(request('q') || request('form'))
        <a href="{{ route('doctor.medicines.index') }}"
           style="font-size:.78rem;color:var(--txt-lt);text-decoration:none;padding:6px 10px;border-radius:7px;border:1px solid var(--warm-bd)"
           onmouseover="this.style.color='var(--txt)'" onmouseout="this.style.color='var(--txt-lt)'">Clear</a>
        @endif
        <button type="submit"
                style="padding:.5rem 14px;background:var(--leaf,#3d7a5c);color:#fff;border:none;border-radius:9px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif">
            Search
        </button>
    </form>
</div>

{{-- ── Table ───────────────────────────────────────────────────────────── --}}
<div class="panel" style="overflow:hidden;padding:0">
    @if($medicines->isEmpty())
    <div style="padding:52px 24px;text-align:center;color:var(--txt-lt)">
        <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 12px;display:block;opacity:.3"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
        <p style="font-size:.9rem;font-weight:500;color:var(--txt-md)">
            @if(request('q') || request('form')) No medicines match your filter. @else Your medicine list is empty. @endif
        </p>
        <p style="font-size:.78rem;margin-top:4px">Click <strong>Add Medicine</strong> above to build your personal fast-fill library.</p>
    </div>
    @else
    <div class="dr-table-wrap">
    <table style="width:100%;border-collapse:collapse;min-width:720px" class="med-table">
        <thead>
            <tr style="background:var(--parch)">
                <th style="text-align:left">Medicine</th>
                <th style="text-align:left">Form</th>
                <th style="text-align:left">Dosage</th>
                <th style="text-align:left">Frequency</th>
                <th style="text-align:left">Timing</th>
                <th style="text-align:left">Days</th>
                <th style="text-align:right;padding-right:18px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicines as $med)
            <tr x-data="editRow({{ $med->id }}, @js(['medicine_name'=>$med->medicine_name,'generic_name'=>$med->generic_name,'form'=>$med->form,'dosage'=>$med->dosage,'frequency'=>$med->frequency,'timing'=>$med->timing,'duration_days'=>$med->duration_days,'special_instructions'=>$med->special_instructions]))"
                style="position:relative">

                {{-- View mode --}}
                <td x-show="!editing">
                    <div style="font-weight:600;color:var(--txt)">{{ $med->medicine_name }}</div>
                    @if($med->generic_name)<div style="font-size:.72rem;color:var(--txt-lt);margin-top:1px">{{ $med->generic_name }}</div>@endif
                    @if($med->special_instructions)<div style="font-size:.7rem;color:var(--txt-lt);margin-top:2px;font-style:italic">{{ $med->special_instructions }}</div>@endif
                </td>
                <td x-show="!editing"><span class="badge-form">{{ $med->form ?: '—' }}</span></td>
                <td x-show="!editing" style="color:var(--txt-md)">{{ $med->dosage ?: '—' }}</td>
                <td x-show="!editing" style="color:var(--txt-md)">{{ $med->frequency ?: '—' }}</td>
                <td x-show="!editing" style="color:var(--txt-md)">{{ $med->timing ? ucwords(str_replace('_',' ',$med->timing)) : '—' }}</td>
                <td x-show="!editing" style="color:var(--txt-md)">{{ $med->duration_days ? $med->duration_days.'d' : '—' }}</td>
                <td x-show="!editing" style="text-align:right;padding-right:18px;white-space:nowrap">
                    <button type="button" @click="editing=true"
                            style="padding:4px 11px;border:1.5px solid var(--warm-bd);border-radius:7px;background:transparent;font-size:.75rem;color:var(--txt-md);cursor:pointer;font-family:'Outfit',sans-serif;transition:all .12s;margin-right:4px"
                            onmouseover="this.style.background='var(--parch)';this.style.color='var(--txt)'" onmouseout="this.style.background='transparent';this.style.color='var(--txt-md)'">
                        Edit
                    </button>
                    <button type="button" @click="confirmDelete({{ $med->id }}, '{{ addslashes($med->medicine_name) }}')"
                            style="padding:4px 11px;border:1.5px solid #fecaca;border-radius:7px;background:transparent;font-size:.75rem;color:#dc2626;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .12s"
                            onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                        Delete
                    </button>
                </td>

                {{-- Edit mode (inline, spans all columns) --}}
                <td x-show="editing" colspan="7" style="padding:14px 16px;background:var(--parch)">
                    <div class="med-fg-7" style="display:grid;gap:8px;margin-bottom:8px">
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Name *</label>
                            <input type="text" x-model="form.medicine_name" class="inp" style="font-size:.82rem" placeholder="Medicine name">
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Generic</label>
                            <input type="text" x-model="form.generic_name" class="inp" style="font-size:.82rem" placeholder="Generic name">
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Form</label>
                            <select x-model="form.form" class="inp" style="font-size:.82rem">
                                <option value="">—</option>
                                <option>Tablet</option><option>Capsule</option><option>Syrup</option>
                                <option>Drops</option><option>Injection</option><option>Cream</option>
                                <option>Ointment</option><option>Inhaler</option><option>Sachet</option>
                                <option>Suppository</option><option>Patch</option><option>Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Dosage</label>
                            <input type="text" x-model="form.dosage" class="inp" style="font-size:.82rem" placeholder="500mg">
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Frequency</label>
                            <select x-model="form.frequency" class="inp" style="font-size:.82rem">
                                <option value="">—</option>
                                <option>Once daily</option><option>Twice daily</option>
                                <option>Three times daily</option><option>Four times daily</option>
                                <option>Every 6 hours</option><option>Every 8 hours</option>
                                <option>Every 12 hours</option><option>Once weekly</option>
                                <option>As needed (SOS)</option><option>At bedtime</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Timing</label>
                            <select x-model="form.timing" class="inp" style="font-size:.82rem">
                                <option value="">—</option>
                                <option value="before_food">Before food</option>
                                <option value="after_food">After food</option>
                                <option value="with_food">With food</option>
                                <option value="empty_stomach">Empty stomach</option>
                                <option value="bed_time">Bedtime</option>
                                <option value="any_time">Anytime</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Days</label>
                            <input type="number" x-model="form.duration_days" class="inp" style="font-size:.82rem" placeholder="5" min="1" max="365">
                        </div>
                    </div>
                    <div style="margin-bottom:10px">
                        <label style="display:block;font-size:.65rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--txt-lt);margin-bottom:3px">Special Instructions</label>
                        <input type="text" x-model="form.special_instructions" class="inp" style="font-size:.82rem" placeholder="e.g. Avoid alcohol">
                    </div>
                    <div style="display:flex;gap:8px">
                        <button type="button" @click="saveEdit({{ $med->id }})" :disabled="saving"
                                style="padding:6px 16px;background:var(--leaf,#3d7a5c);color:#fff;border:none;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;display:flex;align-items:center;gap:5px">
                            <span x-show="saving" style="width:11px;height:11px;border:1.5px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                            <span x-text="saving?'Saving…':'Save'"></span>
                        </button>
                        <button type="button" @click="editing=false;form={...original}"
                                style="padding:6px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;font-size:.8rem;color:var(--txt-md);cursor:pointer;font-family:'Outfit',sans-serif">
                            Cancel
                        </button>
                        <span x-show="saveMsg" x-text="saveMsg" style="font-size:.78rem;color:var(--leaf);align-self:center"></span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    @if($medicines->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--warm-bd)">
        {{ $medicines->links() }}
    </div>
    @endif
    @endif
</div>

{{-- Delete confirmation modal --}}
<div x-show="deleteConfirm.show"
     style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;display:flex;align-items:center;justify-content:center;padding:20px"
     @click.self="deleteConfirm.show=false">
    <div style="background:#fff;border-radius:14px;padding:26px 28px;max-width:380px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.18)">
        <div style="font-family:'Cormorant Garamond',serif;font-size:1.15rem;color:var(--txt);margin-bottom:8px">Remove medicine?</div>
        <p style="font-size:.875rem;color:var(--txt-md);margin-bottom:20px">
            "<strong x-text="deleteConfirm.name"></strong>" will be removed from your list. Existing prescriptions are not affected.
        </p>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button type="button" @click="deleteConfirm.show=false"
                    style="padding:8px 16px;border:1.5px solid var(--warm-bd);border-radius:9px;background:transparent;font-size:.875rem;color:var(--txt-md);cursor:pointer;font-family:'Outfit',sans-serif">
                Cancel
            </button>
            <button type="button" @click="doDelete()" :disabled="deleteConfirm.loading"
                    style="padding:8px 18px;border:none;border-radius:9px;background:#dc2626;color:#fff;font-size:.875rem;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;display:flex;align-items:center;gap:6px"
                    :style="deleteConfirm.loading?'opacity:.6':''">
                <span x-show="deleteConfirm.loading" style="width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite"></span>
                <span x-text="deleteConfirm.loading?'Removing…':'Remove'"></span>
            </button>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}
@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const STORE_URL   = '{{ route("doctor.medicines.store") }}';
const UPDATE_BASE = '{{ url("doctor/medicines") }}';

function medicineManager() {
    return {
        showAdd: @json($medicines->isEmpty() && !request('q') && !request('form')),
        saving: false,
        addForm: emptyMedForm(),
        addErr: {},
        deleteConfirm: { show: false, id: null, name: '', loading: false },

        init() {},

        resetAdd() {
            this.addForm = emptyMedForm();
            this.addErr  = {};
        },

        async submitAdd() {
            this.addErr = {};
            if (!this.addForm.medicine_name.trim()) {
                this.addErr.medicine_name = 'Name is required.';
                return;
            }
            this.saving = true;
            try {
                const res  = await fetch(STORE_URL, {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body:    JSON.stringify(this.addForm),
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    window.location.reload();
                } else if (json.errors) {
                    this.addErr = Object.fromEntries(Object.entries(json.errors).map(([k,v]) => [k, v[0]]));
                }
            } catch { alert('Save failed. Please try again.'); }
            finally   { this.saving = false; }
        },

        confirmDelete(id, name) {
            this.deleteConfirm = { show: true, id, name, loading: false };
        },

        async doDelete() {
            this.deleteConfirm.loading = true;
            try {
                const res = await fetch(`${UPDATE_BASE}/${this.deleteConfirm.id}`, {
                    method:  'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const json = await res.json();
                if (json.success) window.location.reload();
            } catch { alert('Delete failed.'); }
            finally { this.deleteConfirm.loading = false; }
        },
    };
}

function editRow(id, original) {
    return {
        editing: false,
        saving:  false,
        saveMsg: '',
        original: { ...original },
        form: { ...original },

        async saveEdit(id) {
            if (!this.form.medicine_name?.trim()) { alert('Medicine name is required.'); return; }
            this.saving = true;
            try {
                const res  = await fetch(`${UPDATE_BASE}/${id}`, {
                    method:  'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body:    JSON.stringify(this.form),
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    this.original = { ...this.form };
                    this.editing  = false;
                    this.saveMsg  = '✓ Saved';
                    setTimeout(() => this.saveMsg = '', 2500);
                }
            } catch { alert('Save failed.'); }
            finally { this.saving = false; }
        },
    };
}

function emptyMedForm() {
    return { medicine_name:'', generic_name:'', form:'', dosage:'', frequency:'', timing:'', duration_days:'', special_instructions:'' };
}
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endpush
