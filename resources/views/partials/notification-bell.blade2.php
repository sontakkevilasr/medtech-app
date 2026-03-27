{{--
    resources/views/partials/notification-bell.blade.php
    Drop-in for any layout topbar: @include('partials.notification-bell')
    Requires: Alpine.js loaded, notifications routes registered
--}}
<div x-data="notifBell()" x-init="init()" style="position:relative">

    {{-- ── Bell button ──────────────────────────────────────────────────────── --}}
    <button type="button"
            @click="toggle()"
            :class="open ? 'nb-btn nb-btn-open' : 'nb-btn'"
            title="Notifications">

        {{-- Bell icon --}}
        <svg width="17" height="17" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11
                     a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341
                     C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436
                     L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        {{-- Unread badge — only rendered when count > 0 --}}
        <span x-show="unread > 0"
              x-text="unread > 9 ? '9+' : unread"
              class="nb-badge">
        </span>
    </button>

    {{-- ── Dropdown ─────────────────────────────────────────────────────────── --}}
    {{-- FIX 1: NO display:none here — x-show handles visibility by itself     --}}
    {{-- FIX 2: use x-transition with CSS vars, not Tailwind class names        --}}
    <div x-show="open"
         x-transition:enter="nb-enter"
         x-transition:enter-start="nb-enter-start"
         x-transition:enter-end="nb-enter-end"
         x-transition:leave="nb-leave"
         x-transition:leave-start="nb-enter-end"
         x-transition:leave-end="nb-enter-start"
         @click.outside="open = false"
         class="nb-dropdown">

        {{-- Header --}}
        <div class="nb-header">
            <span class="nb-title">
                Notifications
                <span x-show="unread > 0"
                      x-text="'(' + unread + ' unread)'"
                      class="nb-unread-label"></span>
            </span>
            <button x-show="unread > 0"
                    @click.stop="markAllRead()"
                    class="nb-mark-all">
                ✓ Mark all read
            </button>
        </div>

        {{-- Loading spinner --}}
        <div x-show="loading" class="nb-loading">
            <div class="nb-spinner"></div>
        </div>

        {{-- Empty state --}}
        <div x-show="!loading && notifications.length === 0" class="nb-empty">
            <div style="font-size:2rem;margin-bottom:8px">🔔</div>
            <div style="font-size:.85rem">No notifications yet</div>
        </div>

        {{-- Notification list --}}
        <div x-show="!loading && notifications.length > 0" class="nb-list">
            <template x-for="n in notifications" :key="n.id">
                <div class="nb-item"
                     :class="n.is_read ? '' : 'nb-item-unread'"
                     @click="readAndGo(n)">

                    {{-- Icon circle --}}
                    <div class="nb-icon" x-text="n.icon"></div>

                    {{-- Text content --}}
                    <div class="nb-content">
                        <div class="nb-item-title"
                             :style="n.is_read ? 'font-weight:500' : 'font-weight:700'"
                             x-text="n.title"></div>
                        <div class="nb-item-body" x-text="n.body"></div>
                        <div class="nb-item-ago"  x-text="n.ago"></div>
                    </div>

                    {{-- Unread dot --}}
                    <div x-show="!n.is_read" class="nb-dot"></div>
                </div>
            </template>
        </div>

        {{-- Footer --}}
        <div class="nb-footer">
            <a href="{{ route('notifications.index') }}" class="nb-view-all">
                View all notifications →
            </a>
        </div>
    </div>
</div>

{{-- ── Styles (scoped with nb- prefix) ─────────────────────────────────────── --}}
@once
@push('styles')
<style>
/* Bell button */
.nb-btn {
    position: relative;
    width: 36px; height: 36px;
    border-radius: 9px;
    border: 1.5px solid var(--warm-bd);
    background: transparent;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: var(--txt-md);
    transition: background .15s, border-color .15s;
    outline: none;
}
.nb-btn:hover, .nb-btn-open {
    background: var(--sand, #f4efe8) !important;
    border-color: var(--txt-lt) !important;
}

/* Unread badge */
.nb-badge {
    position: absolute;
    top: -5px; right: -5px;
    min-width: 18px; height: 18px;
    border-radius: 9px;
    background: #ef4444;
    color: #fff;
    font-size: .6rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    padding: 0 4px;
    border: 2px solid #fff;
    line-height: 1;
    pointer-events: none;
}

/* Dropdown panel */
.nb-dropdown {
    position: absolute;
    right: 0; top: calc(100% + 8px);
    width: 340px;
    background: #fff;
    border: 1.5px solid var(--warm-bd);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,.13);
    z-index: 9999;
    overflow: hidden;
}

/* FIX: CSS-only transitions (no Tailwind) */
.nb-enter        { transition: opacity .15s ease, transform .15s ease; }
.nb-enter-start  { opacity: 0; transform: translateY(-6px); }
.nb-enter-end    { opacity: 1; transform: translateY(0); }
.nb-leave        { transition: opacity .1s ease, transform .1s ease; }

/* Header */
.nb-header {
    padding: 12px 16px;
    border-bottom: 1px solid var(--warm-bd);
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
}
.nb-title {
    font-weight: 600; font-size: .9rem; color: var(--txt);
}
.nb-unread-label {
    font-size: .73rem; font-weight: 400; color: var(--txt-lt); margin-left: 4px;
}
.nb-mark-all {
    font-size: .72rem; color: var(--txt-lt);
    background: none; border: none; cursor: pointer;
    font-family: inherit; padding: 0;
    transition: color .12s;
}
.nb-mark-all:hover { color: var(--txt); }

/* Loading */
.nb-loading {
    padding: 24px; text-align: center;
}
.nb-spinner {
    width: 20px; height: 20px;
    border: 2px solid var(--warm-bd);
    border-top-color: var(--txt-lt);
    border-radius: 50%;
    animation: nb-spin .6s linear infinite;
    margin: 0 auto;
}
@keyframes nb-spin { to { transform: rotate(360deg); } }

/* Empty */
.nb-empty {
    padding: 28px 16px; text-align: center; color: var(--txt-lt);
}

/* List */
.nb-list {
    max-height: 340px; overflow-y: auto;
}
.nb-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 11px 14px;
    border-bottom: 1px solid var(--warm-bd);
    cursor: pointer;
    transition: background .12s;
}
.nb-item:hover          { background: var(--sand, #f4efe8); }
.nb-item-unread         { background: #f6fff8; }
.nb-item-unread:hover   { background: var(--sand, #f4efe8); }

.nb-icon {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--sand, #f4efe8);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.nb-content {
    flex: 1; min-width: 0;
}
.nb-item-title {
    font-size: .83rem; color: var(--txt);
    margin-bottom: 2px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.nb-item-body {
    font-size: .76rem; color: var(--txt-md);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.nb-item-ago {
    font-size: .68rem; color: var(--txt-lt); margin-top: 3px;
}
.nb-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #ef4444;
    flex-shrink: 0; margin-top: 4px;
}

/* Footer */
.nb-footer {
    padding: 10px 14px;
    border-top: 1px solid var(--warm-bd);
    text-align: center;
}
.nb-view-all {
    font-size: .8rem; color: var(--txt-lt);
    text-decoration: none; font-weight: 500;
    transition: color .12s;
}
.nb-view-all:hover { color: var(--txt); }
</style>
@endpush
@endonce

{{-- ── JS ─────────────────────────────────────────────────────────────────── --}}
@push('scripts')
<script>
function notifBell() {
    return {
        open:          false,
        loading:       false,
        notifications: [],
        unread:        0,

        init() {
            this.fetchCount();
            // Poll every 60 s for badge updates
            setInterval(() => this.fetchCount(), 60000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) this.fetchLatest();
        },

        async fetchCount() {
            try {
                const r = await fetch('{{ route('notifications.count') }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const d = await r.json();
                this.unread = d.count ?? 0;
            } catch {}
        },

        async fetchLatest() {
            this.loading = true;
            try {
                const r = await fetch('{{ route('notifications.latest') }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const d = await r.json();
                this.notifications = d.notifications ?? [];
                this.unread        = d.unread ?? 0;
            } catch {
                this.notifications = [];
            } finally {
                this.loading = false;
            }
        },

        async readAndGo(notif) {
            // Optimistic update
            if (!notif.is_read) {
                notif.is_read = true;
                this.unread   = Math.max(0, this.unread - 1);
                fetch(notif.read_url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).catch(() => {});
            }
            this.open = false;
        },

        async markAllRead() {
            try {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                this.notifications.forEach(n => n.is_read = true);
                this.unread = 0;
            } catch {}
        },
    };
}
</script>
@endpush
