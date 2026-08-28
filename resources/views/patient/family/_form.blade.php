@php $editing = isset($member); @endphp
<div class="fade-in" style="max-width:580px">

<div class="panel" style="padding:24px 28px">
    <div style="margin-bottom:22px">
        <h2 style="font-family:'Lora',serif;font-size:1.2rem;font-weight:500;color:var(--txt)">
            {{ $editing ? 'Edit Profile' : 'Add Family Member' }}
        </h2>
        <p style="font-size:.8rem;color:var(--txt-lt);margin-top:3px">
            {{ $editing
                ? 'Update '.$member->full_name.'\'s information.'
                : 'A unique Sub-ID will be automatically generated for this member.' }}
        </p>
    </div>

    <form method="POST"
          action="{{ $editing ? route('patient.family.update', $member->id) : route('patient.family.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif

        @if ($errors->any())
        <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;margin-bottom:18px">
            @foreach ($errors->all() as $error)
            <div style="font-size:.8rem;color:#dc2626;margin-bottom:2px">• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        {{-- Full Name --}}
        <div style="margin-bottom:16px">
            <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                Full Name <span style="color:var(--rose)">*</span>
            </label>
            <input type="text" name="full_name" value="{{ old('full_name', $member?->full_name) }}"
                   placeholder="As it appears on official documents"
                   style="width:100%;padding:.6rem .9rem;border:1.5px solid {{ $errors->has('full_name') ? '#fca5a5' : 'var(--warm-bd)' }};border-radius:10px;font-size:.9rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif"
                   onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'"
                   required>
        </div>

        {{-- Relation (only on create) --}}
        @if(!$editing)
        <div style="margin-bottom:16px">
            <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                Relation <span style="color:var(--rose)">*</span>
            </label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:7px">
                @foreach(['spouse'=>'Spouse','child'=>'Child','parent'=>'Parent','sibling'=>'Sibling','grandparent'=>'Grandparent','other'=>'Other'] as $val => $lbl)
                <label style="cursor:pointer">
                    <input type="radio" name="relation" value="{{ $val }}" {{ old('relation') === $val ? 'checked' : '' }}
                           style="display:none" class="rel-radio" id="rel-{{ $val }}">
                    <div class="rel-pill"
                         style="text-align:center;padding:8px 6px;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.8rem;font-weight:500;color:var(--txt-md);transition:all .15s;{{ old('relation') === $val ? 'background:var(--plum);color:#fff;border-color:var(--plum)' : '' }}"
                         onclick="document.querySelectorAll('.rel-pill').forEach(p=>{p.style.background='transparent';p.style.color='var(--txt-md)';p.style.borderColor='var(--warm-bd)'}); this.style.background='var(--plum)'; this.style.color='#fff'; this.style.borderColor='var(--plum)'">
                        {{ $lbl }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- DOB + Gender row --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            <div>
                <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                    Date of Birth
                </label>
                <input type="date" name="dob" value="{{ old('dob', $member?->dob?->format('Y-m-d')) }}"
                       max="{{ today()->format('Y-m-d') }}"
                       style="width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer"
                       onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'">
            </div>
            <div>
                <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                    Gender
                </label>
                <select name="gender"
                        style="width:100%;padding:.6rem .9rem;border:1.5px solid var(--warm-bd);border-radius:10px;font-size:.875rem;color:var(--txt);background:var(--cream);outline:none;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer"
                        onfocus="this.style.borderColor='var(--plum)'" onblur="this.style.borderColor='var(--warm-bd)'">
                    <option value="">— Select —</option>
                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('gender', $member?->gender) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Blood Group --}}
        <div style="margin-bottom:22px">
            <label style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--txt-lt);display:block;margin-bottom:6px">
                Blood Group
            </label>
            <div style="display:flex;gap:7px;flex-wrap:wrap">
                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                @php $sel = old('blood_group', $member?->blood_group) === $bg; @endphp
                <label style="cursor:pointer">
                    <input type="radio" name="blood_group" value="{{ $bg }}" {{ $sel ? 'checked':'' }}
                           style="display:none" class="bg-radio">
                    <div class="bg-pill"
                         style="padding:6px 12px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;font-weight:600;color:var(--txt-md);transition:all .15s;{{ $sel ? 'background:var(--rose-lt,#fce7ef);color:var(--rose);border-color:var(--rose)' : '' }}"
                         onclick="document.querySelectorAll('.bg-pill').forEach(p=>{p.style.background='transparent';p.style.color='var(--txt-md)';p.style.borderColor='var(--warm-bd)'}); this.style.background='var(--rose-lt,#fce7ef)'; this.style.color='var(--rose)'; this.style.borderColor='var(--rose)'">
                        {{ $bg }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Sub-ID info (create only) --}}
        @if(!$editing)
        <div style="padding:12px 14px;background:var(--parch);border-radius:10px;border:1px solid var(--warm-bd);margin-bottom:22px;display:flex;gap:10px;align-items:flex-start">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--txt-lt);flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4M12 8h.01"/></svg>
            <div style="font-size:.78rem;color:var(--txt-md);line-height:1.5">
                A unique <strong>Sub-ID</strong> (e.g. <code>MED-{{ str_pad(auth()->id(),5,'0',STR_PAD_LEFT) }}-B</code>) will be automatically assigned to this family member.
                You can share it with doctors for quick record lookup.
            </div>
        </div>
        @endif

        {{-- Actions --}}
        <div style="display:flex;gap:10px">
            <button type="submit"
                    style="flex:1;padding:.75rem;background:var(--plum);color:#fff;border:none;border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;transition:opacity .15s"
                    onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                {{ $editing ? 'Save Changes' : 'Add Member & Generate Sub-ID' }}
            </button>
            <a href="{{ $editing ? route('patient.family.show', $member->id) : route('patient.family.index') }}"
               style="padding:.75rem 20px;border:1.5px solid var(--warm-bd);border-radius:11px;font-size:.9375rem;font-weight:500;color:var(--txt-md);text-decoration:none;transition:all .15s"
               onmouseover="this.style.background='var(--parch)'" onmouseout="this.style.background='transparent'">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>