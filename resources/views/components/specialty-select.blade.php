@php
    use App\Models\TimelineTemplate;
    $specialties = TimelineTemplate::SPECIALTIES;
    $fieldName   = $name ?? 'specialty_type';
    $current     = old($fieldName, $selected ?? '');
    $required    = $required ?? true;
@endphp

<select name="{{ $fieldName }}"
        {{ $required ? 'required' : '' }}
        style="width:100%;padding:.55rem .8rem;border:1.5px solid var(--warm-bd);border-radius:9px;font-size:.875rem;color:var(--txt);background:#fff;outline:none;font-family:inherit;transition:border-color .15s"
        onfocus="this.style.borderColor='var(--leaf)'" onblur="this.style.borderColor='var(--warm-bd)'">
    <option value="" disabled {{ $current === '' ? 'selected' : '' }}>— Select specialty —</option>
    @foreach($specialties as $value => $meta)
    <option value="{{ $value }}" {{ $current === $value ? 'selected' : '' }}>
        {{ $meta['icon'] }}  {{ $meta['label'] }}
    </option>
    @endforeach
</select>
