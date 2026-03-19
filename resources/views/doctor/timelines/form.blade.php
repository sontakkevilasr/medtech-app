@extends('layouts.doctor')
@section('title', isset($template) ? 'Edit Template' : 'New Timeline Template')
@section('page-title')
    <a href="{{ route('doctor.timelines.index') }}" style="font-size:.85rem;font-weight:400;color:var(--txt-lt);text-decoration:none">Timelines</a>
    <span style="color:var(--txt-lt);margin:0 6px">/</span>
    {{ isset($template) ? 'Edit '.$template->title : 'New Template' }}
@endsection

@section('content')
@php $editing = isset($template); @endphp
<div class="fade-in" style="max-width:600px">
<div style="background:#fff;border:1.5px solid var(--warm-bd);border-radius:14px;padding:24px 28px">
    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;color:var(--txt);margin-bottom:4px">
        {{ $editing ? 'Edit Template' : 'Create Timeline Template' }}
    </div>
    <p style="font-size:.8rem;color:var(--txt-lt);margin-bottom:22px">
        {{ $editing ? 'Update template details.' : 'Build a reusable care timeline for your patients.' }}
    </p>

    <form method="POST" action="{{ $editing ? route('doctor.timelines.update', $template) : route('doctor.timelines.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        @if($errors->any())
        <div style="padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:9px;margin-bottom:16px">
            @foreach($errors->all() as $e)<div style="font-size:.78rem;color:#dc2626">• {{ $e }}</div>@endforeach
        </div>
        @endif

        <div style="margin-bottom:14px">
            <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Template Title *</label>
            <input type="text" name="title" value="{{ old('title', $template?->title) }}"
                   placeholder="e.g. Post-Cardiac Surgery Recovery Plan"
                   style="width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.9rem;color:var(--txt);background:#fff;outline:none;font-family:inherit"
                   onfocus="this.style.borderColor='var(--leaf)'" onblur="this.style.borderColor='var(--warm-bd)'"
                   required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Specialty *</label>
                <input type="text" name="specialty_type" value="{{ old('specialty_type', $template?->specialty_type) }}"
                       placeholder="e.g. cardiology"
                       style="width:100%;padding:.55rem .8rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit"
                       required>
            </div>
            <div>
                <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Duration</label>
                <div style="display:flex;gap:6px">
                    <input type="number" name="total_duration_days" value="{{ old('total_duration_days', $template?->total_duration_days ?? 30) }}"
                           min="1" style="flex:1;padding:.55rem .7rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit">
                    <select name="duration_unit" style="padding:.55rem .7rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit">
                        @foreach(['day','week','month'] as $u)
                        <option value="{{ $u }}" {{ old('duration_unit', $template?->duration_unit) === $u ? 'selected':'' }}>{{ ucfirst($u) }}s</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div style="margin-bottom:22px">
            <label style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:5px">Description</label>
            <textarea name="description" rows="3"
                      placeholder="Describe what this timeline covers..."
                      style="width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit;resize:none">{{ old('description', $template?->description) }}</textarea>
        </div>

        <div style="display:flex;gap:10px">
            <button type="submit"
                    style="flex:1;padding:.75rem;background:var(--leaf);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:inherit;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                {{ $editing ? 'Save Changes' : 'Create Template' }}
            </button>
            <a href="{{ $editing ? route('doctor.timelines.show', $template) : route('doctor.timelines.index') }}"
               style="padding:.75rem 20px;border:1.5px solid var(--warm-bd);border-radius:11px;font-size:.9rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:background .12s"
               onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>
@endsection
