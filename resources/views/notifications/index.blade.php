{{--
    resources/views/notifications/index.blade.php
    Works with BOTH patient and doctor layouts — extend whichever applies.
    The controller is shared (Common\NotificationController).
--}}
@php $role = auth()->user()->role; @endphp
@extends($role === 'doctor' ? 'layouts.doctor' : 'layouts.patient')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
@php
$typeIcon = fn(string $t) => match(true) {
    str_starts_with($t, 'appointment') => '📅',
    str_starts_with($t, 'prescription')=> '💊',
    str_starts_with($t, 'access')      => '🔒',
    str_starts_with($t, 'payment')     => '💳',
    str_starts_with($t, 'timeline')    => '📋',
    str_starts_with($t, 'doctor')      => '✅',
    default                            => '🔔',
};
$typeColor = fn(string $t) => match(true) {
    str_starts_with($t, 'appointment') => ['#3d7a6e','#eef5f3'],
    str_starts_with($t, 'prescription')=> ['#4a3760','#f4f0fa'],
    str_starts_with($t, 'access')      => ['#c0737a','#fce7ef'],
    str_starts_with($t, 'payment')     => ['#3d5e7a','#e8f0f9'],
    str_starts_with($t, 'timeline')    => ['#c98a3a','#fdf5e8'],
    str_starts_with($t, 'doctor')      => ['#10b981','#d1fae5'],
    default                            => ['#6b7280','#f3f4f6'],
};
@endphp

<div class="fade-in" style="max-width:680px">

{{-- Header strip --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:10px">

    {{-- Filter tabs --}}
    <div style="display:flex;gap:5px">
        @foreach(['all'=>'All', 'unread'=>'Unread ('.$unreadCount.')'] as $val => $lbl)
        <a href="{{ route('notifications.index', ['filter'=>$val]) }}"
           style="padding:6px 14px;border-radius:8px;font-size:.82rem;font-weight:500;text-decoration:none;border:1.5px solid {{ $filter===$val ? ($role==='doctor' ? 'var(--leaf)' : 'var(--plum)') : 'var(--warm-bd)' }};background:{{ $filter===$val ? ($role==='doctor' ? 'var(--leaf)' : 'var(--plum)') : 'transparent' }};color:{{ $filter===$val ? '#fff' : 'var(--txt-md)' }};transition:all .12s">
            {{ $lbl }}
        </a>
        @endforeach
    </div>

    {{-- Mark all read --}}
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit"
                style="font-size:.8rem;padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;background:transparent;cursor:pointer;color:var(--txt-md);font-family:inherit;transition:background .12s"
                onmouseover="this.style.background='var(--sand,#f4efe8)'" onmouseout="this.style.background='transparent'">
            ✓ Mark all read
        </button>
    </form>
    @endif
</div>

{{-- Notifications list --}}
@if($query->isEmpty())
<div style="padding:52px 24px;text-align:center">
    <div style="font-size:3rem;margin-bottom:12px">🔔</div>
    <div style="font-size:1rem;font-weight:500;color:var(--txt-md);margin-bottom:5px">
        {{ $filter === 'unread' ? 'No unread notifications' : 'No notifications yet' }}
    </div>
    <p style="font-size:.82rem;color:var(--txt-lt)">
        Notifications for appointments, prescriptions, access requests and payments will appear here.
    </p>
</div>
@else

<div style="display:flex;flex-direction:column;gap:8px">
@foreach($query as $notif)
@php [$color, $bg] = $typeColor($notif->type); @endphp
<div style="display:flex;align-items:flex-start;gap:12px;padding:14px 16px;border-radius:12px;border:1.5px solid {{ $notif->is_read ? 'var(--warm-bd)' : $color.'44' }};background:{{ $notif->is_read ? '#fff' : $bg }};transition:all .15s"
     onmouseover="this.style.boxShadow='0 2px 12px rgba(0,0,0,.07)'" onmouseout="this.style.boxShadow='none'">

    {{-- Icon circle --}}
    <div style="width:40px;height:40px;border-radius:50%;background:{{ $bg }};border:1.5px solid {{ $color }}33;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
        {{ $typeIcon($notif->type) }}
    </div>

    {{-- Content --}}
    <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
            <div style="font-weight:{{ $notif->is_read ? '500' : '700' }};font-size:.9rem;color:var(--txt)">
                {{ $notif->title }}
            </div>
            <span style="font-size:.7rem;color:var(--txt-lt);flex-shrink:0;white-space:nowrap">
                {{ $notif->created_at->diffForHumans() }}
            </span>
        </div>
        <p style="font-size:.82rem;color:var(--txt-md);margin-top:3px;line-height:1.5">
            {{ $notif->body }}
        </p>

        {{-- Actions --}}
        <div style="display:flex;gap:8px;margin-top:8px">
            @if(!$notif->is_read)
            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                @csrf
                <button type="submit"
                        style="font-size:.72rem;padding:3px 10px;border:1px solid {{ $color }};border-radius:6px;background:transparent;cursor:pointer;color:{{ $color }};font-family:inherit;transition:background .12s"
                        onmouseover="this.style.background='{{ $bg }}'" onmouseout="this.style.background='transparent'">
                    Mark read
                </button>
            </form>
            @endif
            <form method="POST" action="{{ route('notifications.destroy', $notif->id) }}"
                  onsubmit="return confirm('Delete this notification?')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="font-size:.72rem;padding:3px 10px;border:1px solid #fecaca;border-radius:6px;background:transparent;cursor:pointer;color:#dc2626;font-family:inherit;transition:background .12s"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    Delete
                </button>
            </form>
        </div>
    </div>

    {{-- Unread dot --}}
    @if(!$notif->is_read)
    <div style="width:9px;height:9px;border-radius:50%;background:{{ $color }};flex-shrink:0;margin-top:4px"></div>
    @endif
</div>
@endforeach
</div>

{{-- Pagination --}}
@if($query->hasPages())
<div style="display:flex;justify-content:center;gap:6px;margin-top:20px">
    @if(!$query->onFirstPage())
    <a href="{{ $query->previousPageUrl() }}" style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">← Prev</a>
    @endif
    <span style="padding:6px 14px;font-size:.78rem;color:var(--txt-lt)">{{ $query->currentPage() }} / {{ $query->lastPage() }}</span>
    @if($query->hasMorePages())
    <a href="{{ $query->nextPageUrl() }}" style="padding:6px 14px;border:1.5px solid var(--warm-bd);border-radius:8px;font-size:.8rem;text-decoration:none;color:var(--txt-md)">Next →</a>
    @endif
</div>
@endif
@endif

</div>
@endsection
