@extends('layouts.admin')
@section('title', $role ? ucfirst($role).'s' : 'All Users')
@section('page-title', $role === 'doctor' ? 'Doctors' : ($role === 'patient' ? 'Patients' : 'All Users'))

@section('content')
<div class="fade-in" x-data="{ processing: null }">

{{-- Sub-nav --}}
<div style="display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap;align-items:center">
    <a href="{{ route('admin.users.index') }}"
       class="btn {{ !$role ? 'btn-primary' : 'btn-ghost' }}" style="font-size:.8rem">All Users</a>
    <a href="{{ route('admin.users.doctors') }}"
       class="btn {{ $role==='doctor' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:.8rem">Doctors</a>
    <a href="{{ route('admin.users.patients') }}"
       class="btn {{ $role==='patient' ? 'btn-primary' : 'btn-ghost' }}" style="font-size:.8rem">Patients</a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ request()->url() }}"
      style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;align-items:center">
    <input type="text" name="q" value="{{ $search }}" placeholder="Search name or mobile…"
           class="inp" style="min-width:200px">

    <select name="status" class="inp" style="min-width:130px">
        <option value="">All Status</option>
        <option value="active"    {{ $status==='active'    ? 'selected' : '' }}>Active</option>
        <option value="suspended" {{ $status==='suspended' ? 'selected' : '' }}>Suspended</option>
    </select>

    @if($role === 'doctor' || !$role)
    <select name="verified" class="inp" style="min-width:140px">
        <option value="">All Verified</option>
        <option value="yes" {{ $verified==='yes' ? 'selected' : '' }}>Verified</option>
        <option value="no"  {{ $verified==='no'  ? 'selected' : '' }}>Unverified</option>
    </select>
    <select name="premium" class="inp" style="min-width:130px">
        <option value="">All Plans</option>
        <option value="yes" {{ $premium==='yes' ? 'selected' : '' }}>Premium</option>
        <option value="no"  {{ $premium==='no'  ? 'selected' : '' }}>Free</option>
    </select>
    @endif

    <button type="submit" class="btn btn-primary" style="font-size:.8rem">Filter</button>
    @if($search || $status || $verified || $premium)
    <a href="{{ request()->url() }}" class="btn btn-ghost" style="font-size:.8rem">✕ Clear</a>
    @endif

    <div style="margin-left:auto;font-size:.78rem;color:var(--txt-lt)">
        {{ $users->total() }} user{{ $users->total() !== 1 ? 's' : '' }}
    </div>
</form>

{{-- Table --}}
<div class="card" style="overflow:hidden">
    <table class="admin-table">
        <thead><tr>
            <th style="width:40%">User</th>
            <th>Mobile</th>
            @if($role === 'doctor' || !$role)<th>Specialty / City</th>@endif
            <th>Joined</th>
            <th>Status</th>
            <th style="text-align:right">Actions</th>
        </tr></thead>
        <tbody>
        @forelse($users as $user)
        @php
            $name     = $user->profile?->full_name ?? 'Unknown';
            $initials = strtoupper(implode('', array_map(fn($x) => $x[0], array_slice(explode(' ', $name), 0, 2))));
            $palette  = ['#6366f1','#10b981','#f59e0b','#ef4444','#3b82f6','#8b5cf6'];
            $color    = $palette[$user->id % count($palette)];
            $dp       = $user->doctorProfile;
        @endphp
        <tr id="row-{{ $user->id }}">
            {{-- Name + role --}}
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:9px;background:{{ $color }};display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;color:#fff;flex-shrink:0">
                        {{ $initials }}
                    </div>
                    <div>
                        <div style="font-weight:500;color:var(--txt)">
                            {{ $user->isDoctor() ? 'Dr. ' : '' }}{{ $name }}
                        </div>
                        <div style="display:flex;gap:5px;margin-top:2px">
                            <span class="badge {{ $user->isDoctor() ? 'badge-purple' : 'badge-blue' }}" style="font-size:.62rem">
                                {{ ucfirst($user->role) }}
                            </span>
                            @if($dp?->is_verified)
                            <span class="badge badge-green" style="font-size:.62rem">✓ Verified</span>
                            @elseif($user->isDoctor())
                            <span class="badge badge-yellow" style="font-size:.62rem">Unverified</span>
                            @endif
                            @if($dp?->is_premium)
                            <span class="badge badge-purple" style="font-size:.62rem">⭐ Premium</span>
                            @endif
                        </div>
                    </div>
                </div>
            </td>

            {{-- Mobile --}}
            <td style="font-family:monospace;font-size:.82rem;color:var(--txt-md)">
                {{ $user->country_code }} {{ $user->mobile_number }}
            </td>

            {{-- Specialty / city --}}
            @if($role === 'doctor' || !$role)
            <td style="font-size:.8rem;color:var(--txt-md)">
                @if($user->isDoctor())
                    {{ $dp?->specialization ?? '—' }}
                    @if($dp?->clinic_city)<div style="font-size:.72rem;color:var(--txt-lt)">{{ $dp->clinic_city }}</div>@endif
                @else
                    {{ $user->profile?->city ?? '—' }}
                @endif
            </td>
            @endif

            {{-- Joined --}}
            <td style="font-size:.78rem;color:var(--txt-lt)">{{ $user->created_at->format('d M Y') }}</td>

            {{-- Status with live toggle --}}
            <td>
                <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}"
                      id="status-badge-{{ $user->id }}">
                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                </span>
            </td>

            {{-- Actions --}}
            <td style="text-align:right">
                <div style="display:flex;gap:5px;justify-content:flex-end;align-items:center">
                    {{-- Activate / Suspend toggle (AJAX) --}}
                    @if($user->is_active)
                    <button type="button"
                            @click="toggleUser({{ $user->id }}, 'suspend', '{{ route('admin.users.suspend', $user) }}')"
                            :disabled="processing === {{ $user->id }}"
                            class="btn btn-danger" style="font-size:.72rem;padding:4px 10px"
                            :style="processing===={{ $user->id }} ? 'opacity:.5' : ''">
                        <span x-show="processing==={{ $user->id }}" style="width:11px;height:11px;border:1.5px solid rgba(239,68,68,.3);border-top-color:var(--danger);border-radius:50%;animation:spin .6s linear infinite"></span>
                        Suspend
                    </button>
                    @else
                    <button type="button"
                            @click="toggleUser({{ $user->id }}, 'activate', '{{ route('admin.users.activate', $user) }}')"
                            :disabled="processing === {{ $user->id }}"
                            class="btn btn-success" style="font-size:.72rem;padding:4px 10px"
                            :style="processing==={{ $user->id }} ? 'opacity:.5' : ''">
                        Activate
                    </button>
                    @endif

                    @if($user->isDoctor() && !$dp?->is_premium)
                    <form method="POST" action="{{ route('admin.users.grant-premium', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-ghost" style="font-size:.72rem;padding:4px 10px"
                                onclick="return confirm('Grant 1-year premium to Dr. {{ $name }}?')">⭐</button>
                    </form>
                    @endif

                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost" style="font-size:.72rem;padding:4px 10px">View</a>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--txt-lt)">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="pager">
        <span>Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</span>
        <div style="display:flex;gap:4px">
            @if(!$users->onFirstPage())
                <a href="{{ $users->previousPageUrl() }}">← Prev</a>
            @endif
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
function toggleUser(id, action, url) {
    return {
        async toggleUser(id, action, url) {
            this.processing = id;
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const d = await r.json();
                if (d.success) {
                    const badge = document.getElementById('status-badge-' + id);
                    if (badge) {
                        badge.className = d.status === 'active' ? 'badge badge-green' : 'badge badge-red';
                        badge.textContent = d.status === 'active' ? 'Active' : 'Suspended';
                    }
                    location.reload();
                }
            } finally { this.processing = null; }
        }
    }
}
// Extend the parent x-data
document.addEventListener('alpine:init', () => {
    window.Alpine.data('userActions', () => ({
        processing: null,
        async toggleUser(id, action, url) {
            this.processing = id;
            try {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const d = await r.json();
                if (d.success) location.reload();
            } finally { this.processing = null; }
        }
    }));
});
</script>
@endpush
