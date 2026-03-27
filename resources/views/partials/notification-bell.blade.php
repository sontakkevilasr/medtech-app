<div x-data="notifBell()" x-init="init()" class="nb-wrap">
    <button
        type="button"
        @click="toggle()"
        :class="open ? 'nb-btn nb-btn-open' : 'nb-btn'"
        title="Notifications"
        aria-label="Notifications"
    >
        <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <span
            x-cloak
            x-show="unread > 0"
            x-text="unread > 9 ? '9+' : unread"
            class="nb-badge"
        ></span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="nb-enter"
        x-transition:enter-start="nb-enter-start"
        x-transition:enter-end="nb-enter-end"
        x-transition:leave="nb-leave"
        x-transition:leave-start="nb-enter-end"
        x-transition:leave-end="nb-enter-start"
        @click.outside="open = false"
        class="nb-dropdown"
    >
        <div class="nb-header">
            <div class="nb-title">
                Notifications
                <span
                    x-cloak
                    x-show="unread > 0"
                    x-text="'(' + unread + ' unread)'"
                    class="nb-unread-label"
                ></span>
            </div>

            <button
                x-cloak
                x-show="unread > 0"
                @click.stop="markAllRead()"
                type="button"
                class="nb-mark-all"
            >
                Mark all read
            </button>
        </div>

        <div x-cloak x-show="loading" class="nb-loading">
            <div class="nb-spinner"></div>
        </div>

        <div x-cloak x-show="!loading && notifications.length === 0" class="nb-empty">
            <div class="nb-empty-icon">Notifications</div>
            <div class="nb-empty-text">No notifications yet</div>
        </div>

        <div x-cloak x-show="!loading && notifications.length > 0" class="nb-list">
            <template x-for="n in notifications" :key="n.id">
                <button
                    type="button"
                    class="nb-item"
                    :class="n.is_read ? '' : 'nb-item-unread'"
                    @click="readAndGo(n)"
                >
                    <div class="nb-icon" x-text="n.icon || '!'" aria-hidden="true"></div>

                    <div class="nb-content">
                        <div
                            class="nb-item-title"
                            :style="n.is_read ? 'font-weight:500' : 'font-weight:700'"
                            x-text="n.title"
                        ></div>
                        <div class="nb-item-body" x-text="n.body"></div>
                        <div class="nb-item-ago" x-text="n.ago"></div>
                    </div>

                    <div x-cloak x-show="!n.is_read" class="nb-dot"></div>
                </button>
            </template>
        </div>

        <div class="nb-footer">
            <a href="{{ route('notifications.index') }}" class="nb-view-all">
                View all notifications
            </a>
        </div>
    </div>
</div>

@once
<style>
[x-cloak] {
    display: none !important;
}

.nb-wrap {
    position: relative;
}

.nb-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1.5px solid var(--warm-bd);
    border-radius: 9px;
    background: transparent;
    color: var(--txt-md);
    cursor: pointer;
    transition: background .15s, border-color .15s, color .15s;
    outline: none;
}

.nb-btn:hover,
.nb-btn-open {
    background: var(--sand, #f4efe8);
    border-color: var(--txt-lt);
    color: var(--txt);
}

.nb-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border: 2px solid #fff;
    border-radius: 999px;
    background: #ef4444;
    color: #fff;
    font-size: .62rem;
    font-weight: 700;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.nb-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: min(340px, calc(100vw - 24px));
    max-height: min(420px, calc(100vh - 100px));
    display: flex;
    flex-direction: column;
    background: #fff;
    border: 1.5px solid var(--warm-bd);
    border-radius: 14px;
    box-shadow: 0 10px 32px rgba(0, 0, 0, .13);
    z-index: 9999;
    overflow: hidden;
}

.nb-enter {
    transition: opacity .15s ease, transform .15s ease;
}

.nb-enter-start {
    opacity: 0;
    transform: translateY(-6px);
}

.nb-enter-end {
    opacity: 1;
    transform: translateY(0);
}

.nb-leave {
    transition: opacity .1s ease, transform .1s ease;
}

.nb-header,
.nb-footer {
    flex-shrink: 0;
}

.nb-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid var(--warm-bd);
}

.nb-title {
    font-size: .9rem;
    font-weight: 600;
    color: var(--txt);
}

.nb-unread-label {
    margin-left: 4px;
    font-size: .73rem;
    font-weight: 400;
    color: var(--txt-lt);
}

.nb-mark-all {
    border: 0;
    background: none;
    padding: 0;
    color: var(--txt-lt);
    font: inherit;
    font-size: .72rem;
    cursor: pointer;
    transition: color .12s;
    white-space: nowrap;
}

.nb-mark-all:hover {
    color: var(--txt);
}

.nb-loading,
.nb-empty {
    padding: 24px 16px;
    text-align: center;
}

.nb-spinner {
    width: 20px;
    height: 20px;
    margin: 0 auto;
    border: 2px solid var(--warm-bd);
    border-top-color: var(--txt-lt);
    border-radius: 50%;
    animation: nb-spin .6s linear infinite;
}

@keyframes nb-spin {
    to {
        transform: rotate(360deg);
    }
}

.nb-empty {
    color: var(--txt-lt);
}

.nb-empty-icon {
    margin-bottom: 8px;
    font-size: .8rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.nb-empty-text {
    font-size: .85rem;
}

.nb-list {
    overflow-y: auto;
}

.nb-item {
    width: 100%;
    border: 0;
    border-bottom: 1px solid var(--warm-bd);
    background: #fff;
    padding: 11px 14px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    text-align: left;
    transition: background .12s;
}

.nb-item:last-child {
    border-bottom: 0;
}

.nb-item:hover {
    background: var(--sand, #f4efe8);
}

.nb-item-unread {
    background: #f6fff8;
}

.nb-item-unread:hover {
    background: var(--sand, #f4efe8);
}

.nb-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--sand, #f4efe8);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.nb-content {
    flex: 1;
    min-width: 0;
}

.nb-item-title {
    margin-bottom: 2px;
    color: var(--txt);
    font-size: .83rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.nb-item-body {
    color: var(--txt-md);
    font-size: .76rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
}

.nb-item-ago {
    margin-top: 3px;
    color: var(--txt-lt);
    font-size: .68rem;
}

.nb-dot {
    width: 8px;
    height: 8px;
    margin-top: 4px;
    border-radius: 50%;
    background: #ef4444;
    flex-shrink: 0;
}

.nb-footer {
    padding: 10px 14px;
    border-top: 1px solid var(--warm-bd);
    text-align: center;
}

.nb-view-all {
    color: var(--txt-lt);
    text-decoration: none;
    font-size: .8rem;
    font-weight: 500;
    transition: color .12s;
}

.nb-view-all:hover {
    color: var(--txt);
}

@media (max-width: 480px) {
    .nb-dropdown {
        right: -8px;
        width: min(340px, calc(100vw - 16px));
        max-height: min(420px, calc(100vh - 84px));
    }

    .nb-header {
        padding: 12px 14px;
    }

    .nb-item {
        padding: 11px 12px;
    }
}
</style>
@endonce

@push('scripts')
<script>
function notifBell() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unread: 0,
        pollInterval: null,

        init() {
            this.fetchUnreadCount();
            this.fetchLatest();
            this.pollInterval = setInterval(() => this.fetchUnreadCount(), 60000);
        },

        toggle() {
            this.open = !this.open;
        },

        async fetchUnreadCount() {
            try {
                const response = await fetch("{{ route('notifications.count') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                this.unread = data.count ?? 0;
            } catch (error) {}
        },

        async fetchLatest() {
            this.loading = true;

            try {
                const response = await fetch('{{ route('notifications.latest') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                this.notifications = data.notifications ?? [];
                this.unread = data.unread ?? 0;
            } catch (error) {
                this.notifications = [];
            } finally {
                this.loading = false;
            }
        },

        async readAndGo(notification) {
            if (!notification.is_read) {
                notification.is_read = true;
                this.unread = Math.max(0, this.unread - 1);

                try {
                    await fetch(notification.read_url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                } catch (error) {
                    notification.is_read = false;
                    this.unread += 1;
                }
            }

            this.open = false;
        },

        async markAllRead() {
            try {
                await fetch('{{ route('notifications.read-all') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
                this.unread = 0;
            } catch (error) {}
        }
    };
}
</script>
@endpush
