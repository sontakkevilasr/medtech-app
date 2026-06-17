@props([
    'name'     => '',
    'photo'    => null,
    'size'     => null,
    'radius'   => null,
    'bg'       => null,
    'color'    => null,
    'fontSize' => null,
])

@php
    $initials = strtoupper(implode('', array_map(
        fn($w) => $w[0] ?? '',
        array_slice(explode(' ', trim($name)), 0, 2)
    )));
    $photoUrl = $photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($photo) : null;
    $inline   = $size !== null || $bg !== null;

    $boxStyles = [];
    if ($size)   { $boxStyles[] = "width:{$size}px"; $boxStyles[] = "height:{$size}px"; }
    if ($radius !== null) { $boxStyles[] = "border-radius:{$radius}px"; }
    elseif ($size) { $boxStyles[] = 'border-radius:' . round($size / 4) . 'px'; }
@endphp

@if($photoUrl)
    <img src="{{ $photoUrl }}" alt="{{ $name }}"
         {{ $attributes->merge(['style' => implode(';', array_merge($boxStyles, ['object-fit:cover', 'flex-shrink:0'])) . ';']) }}>
@elseif($inline)
    @php
        if ($fontSize) { $boxStyles[] = "font-size:{$fontSize}"; }
        $boxStyles[] = 'background:' . ($bg ?? '#3d7a6e');
        $boxStyles[] = 'display:flex';
        $boxStyles[] = 'align-items:center';
        $boxStyles[] = 'justify-content:center';
        $boxStyles[] = 'font-weight:700';
        $boxStyles[] = 'color:' . ($color ?? '#fff');
        $boxStyles[] = 'flex-shrink:0';
    @endphp
    <div {{ $attributes->merge(['style' => implode(';', $boxStyles) . ';']) }}>{{ $initials }}</div>
@else
    <div {{ $attributes }}>{{ $initials }}</div>
@endif
