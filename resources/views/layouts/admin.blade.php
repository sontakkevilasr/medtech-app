<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Admin') — MedTech Admin</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
:root {
    --ink:      #1a1f2e;
    --ink2:     #252c3f;
    --accent:   #6366f1; /* indigo */
    --accent-lt:#e0e7ff;
    --danger:   #ef4444;
    --warn:     #f59e0b;
    --success:  #10b981;
    --bg:       #f4f6fb;
    --card:     #ffffff;
    --bd:       #e8eaf0;
    --txt:      #1a1f2e;
    --txt-md:   #4b5568;
    --txt-lt:   #9ca3af;
    --sidebar-w:240px;
    --topbar-h: 58px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--txt); min-height: 100vh; }

/* ── Sidebar ──────────────────────────────────────────────── */
.sidebar {
    position: fixed; left: 0; top: 0; bottom: 0;
    width: var(--sidebar-w);
    background: var(--ink);
    display: flex; flex-direction: column;
    z-index: 50; overflow-y: auto;
}
.sidebar-logo {
    padding: 20px 20px 16px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    display: flex; align-items: center; gap: 10px;
}
.logo-badge {
    width: 34px; height: 34px; border-radius: 9px;
    background: var(--accent);
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 800; color: #fff;
}
.logo-text { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 600; color: #fff; }
.logo-sub  { font-size: .65rem; color: rgba(255,255,255,.4); letter-spacing: .08em; text-transform: uppercase; }

.sidebar-section { padding: 12px 0 4px; }
.sidebar-label {
    font-size: .6rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
    color: rgba(255,255,255,.3); padding: 0 20px 6px;
}
.nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 20px; font-size: .85rem; font-weight: 500; color: rgba(255,255,255,.6);
    text-decoration: none; border-radius: 0; transition: all .15s; position: relative;
}
.nav-item:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.9); }
.nav-item.active {
    background: rgba(99,102,241,.18); color: #fff;
    border-right: 3px solid var(--accent);
}
.nav-item .icon { width: 16px; height: 16px; flex-shrink: 0; opacity: .7; }
.nav-item.active .icon { opacity: 1; }
.nav-badge {
    margin-left: auto; font-size: .65rem; font-weight: 700;
    background: var(--danger); color: #fff;
    padding: 2px 7px; border-radius: 20px; min-width: 20px; text-align: center;
}

.sidebar-bottom {
    margin-top: auto; padding: 16px 20px;
    border-top: 1px solid rgba(255,255,255,.06);
}
.admin-chip {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 12px; background: rgba(255,255,255,.06);
    border-radius: 10px;
}
.admin-avatar {
    width: 30px; height: 30px; border-radius: 8px;
    background: var(--accent); display: flex; align-items: center;
    justify-content: center; font-size: .75rem; font-weight: 700; color: #fff;
}

/* ── Topbar ───────────────────────────────────────────────── */
.topbar {
    position: fixed; top: 0; left: var(--sidebar-w); right: 0;
    height: var(--topbar-h);
    background: var(--card); border-bottom: 1px solid var(--bd);
    display: flex; align-items: center;
    padding: 0 28px; gap: 14px; z-index: 40;
}
.page-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem; font-weight: 500; color: var(--txt);
    flex: 1;
}

/* ── Main content ─────────────────────────────────────────── */
.main-content {
    margin-left: var(--sidebar-w);
    margin-top: var(--topbar-h);
    padding: 28px;
    min-height: calc(100vh - var(--topbar-h));
}

/* ── Cards ────────────────────────────────────────────────── */
.card {
    background: var(--card);
    border: 1px solid var(--bd);
    border-radius: 14px;
}

/* ── Stat card ────────────────────────────────────────────── */
.stat-card {
    background: var(--card);
    border: 1px solid var(--bd);
    border-radius: 14px;
    padding: 20px 22px;
}

/* ── Table ────────────────────────────────────────────────── */
.admin-table { width: 100%; border-collapse: collapse; }
.admin-table thead tr { border-bottom: 1.5px solid var(--bd); }
.admin-table thead th {
    padding: 10px 16px; font-size: .68rem; font-weight: 700;
    letter-spacing: .07em; text-transform: uppercase; color: var(--txt-lt);
    text-align: left;
}
.admin-table tbody tr {
    border-bottom: 1px solid var(--bd);
    transition: background .1s;
}
.admin-table tbody tr:hover { background: #f8f9fc; }
.admin-table tbody td { padding: 12px 16px; font-size: .875rem; color: var(--txt-md); }
.admin-table tbody tr:last-child { border-bottom: none; }

/* ── Badges ───────────────────────────────────────────────── */
.badge { display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; letter-spacing: .04em; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-yellow { background: #fef9c3; color: #854d0e; }
.badge-blue   { background: #dbeafe; color: #1e40af; }
.badge-purple { background: var(--accent-lt); color: #3730a3; }
.badge-gray   { background: #f3f4f6; color: #6b7280; }

/* ── Buttons ──────────────────────────────────────────────── */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 9px; font-size: .8125rem; font-weight: 600; cursor: pointer; font-family: 'Outfit', sans-serif; border: none; transition: all .15s; text-decoration: none; }
.btn-primary  { background: var(--accent); color: #fff; }
.btn-primary:hover { opacity: .88; }
.btn-danger   { background: #fef2f2; color: var(--danger); border: 1.5px solid #fecaca; }
.btn-danger:hover  { background: #fee2e2; }
.btn-success  { background: #f0fdf4; color: #15803d; border: 1.5px solid #bbf7d0; }
.btn-success:hover { background: #dcfce7; }
.btn-ghost    { background: transparent; color: var(--txt-md); border: 1.5px solid var(--bd); }
.btn-ghost:hover { background: var(--bg); }

/* ── Inputs ───────────────────────────────────────────────── */
.inp {
    padding: .5rem .8rem; border: 1.5px solid var(--bd); border-radius: 9px;
    font-size: .875rem; color: var(--txt); background: #fff;
    outline: none; font-family: 'Outfit', sans-serif; transition: border-color .15s;
}
.inp:focus { border-color: var(--accent); }

/* ── Utilities ────────────────────────────────────────────── */
.fade-in { animation: fadeIn .25s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Pagination ───────────────────────────────────────────── */
.pager { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; font-size: .8rem; color: var(--txt-md); border-top: 1px solid var(--bd); }
.pager a, .pager span { padding: 5px 12px; border-radius: 8px; border: 1px solid var(--bd); text-decoration: none; color: var(--txt-md); }
.pager a:hover { background: var(--bg); }
.pager .disabled { opacity: .4; cursor: not-allowed; }

@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .topbar, .main-content { left: 0; margin-left: 0; }
}
</style>
@stack('styles')
</head>
<body>

{{-- ── Sidebar ─────────────────────────────────────────────────────────────── -- --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-badge">M</div>
        <div>
            <div class="logo-text">MedTech</div>
            <div class="logo-sub">Admin Console</div>
        </div>
    </div>

    <nav style="flex:1">
        <div class="sidebar-section">
            <div class="sidebar-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-label">Users</div>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                All Users
            </a>
            <!-- <a href="{{ route('admin.users.doctors') }}" class="nav-item {{ request()->routeIs('admin.users.doctors') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Doctors
            </a> -->
            <a href="{{ route('admin.users.doctors') }}" class="nav-item {{ request()->routeIs('admin.users.doctors') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Doctors
            </a>
            <a href="{{ route('admin.doctors.create') }}" class="nav-item {{ request()->routeIs('admin.doctors.create') ? 'active' : '' }}" style="{{ request()->routeIs('admin.doctors.create') ? '' : 'color:rgba(255,255,255,.5)' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                + Add Doctor
            </a>
            <a href="{{ route('admin.users.patients') }}" class="nav-item {{ request()->routeIs('admin.users.patients') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Patients
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-label">Verification</div>
            <a href="{{ route('admin.verification.pending') }}" class="nav-item {{ request()->routeIs('admin.verification*') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Doctor Verification
                @php $pending = \App\Models\DoctorProfile::where('is_verified', false)->whereHas('user', fn($q)=>$q->where('is_active',true))->count(); @endphp
                @if($pending > 0)
                <span class="nav-badge">{{ $pending }}</span>
                @endif
            </a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-label">Analytics</div>
            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Reports
            </a>
            <a href="{{ route('admin.reports.export') }}" class="nav-item {{ request()->routeIs('admin.reports.export') ? 'active' : '' }}">
                <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Data
            </a>
        </div>
    </nav>

    <div class="sidebar-bottom">
        <div class="admin-chip">
            <div class="admin-avatar">{{ strtoupper(substr(auth()->user()->profile?->full_name ?? 'A', 0, 1)) }}</div>
            <div style="flex:1;min-width:0">
                <div style="font-size:.8rem;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    {{ auth()->user()->profile?->full_name ?? 'Admin' }}
                </div>
                <div style="font-size:.68rem;color:rgba(255,255,255,.4)">Administrator</div>
            </div>
        </div>
        <form method="POST" action="{{ route('auth.logout') }}" style="margin-top:8px">
            @csrf
            <button type="submit" style="width:100%;padding:8px;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:transparent;color:rgba(255,255,255,.5);font-size:.78rem;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .15s"
                    onmouseover="this.style.color='rgba(255,255,255,.8)';this.style.background='rgba(255,255,255,.06)'" onmouseout="this.style.color='rgba(255,255,255,.5)';this.style.background='transparent'">
                Sign out
            </button>
        </form>
    </div>
</aside>

{{-- ── Topbar ───────────────────────────────────────────────────────────────── -- --}}
<header class="topbar">
    <div class="page-title">@yield('page-title', 'Dashboard')</div>

    {{-- Search --}}
    <div style="position:relative">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
             style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--txt-lt)">
            <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <form method="GET" action="{{ route('admin.users.index') }}">
            <input type="text" name="q" placeholder="Search users…" class="inp" style="padding-left:32px;width:200px">
        </form>
    </div>

    {{-- Date --}}
    <div style="font-size:.8rem;color:var(--txt-lt)">{{ now()->format('D, d M Y') }}</div>
</header>

{{-- ── Flash messages ───────────────────────────────────────────────────────── -- --}}
@if(session('success') || session('error') || session('warning'))
<div style="position:fixed;top:calc(var(--topbar-h)+14px);right:20px;z-index:200;display:flex;flex-direction:column;gap:8px;min-width:280px;max-width:380px"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false, 4000)" x-transition>
    @foreach(['success'=>['bg'=>'#f0fdf4','bd'=>'#bbf7d0','color'=>'#15803d'],'error'=>['bg'=>'#fef2f2','bd'=>'#fecaca','color'=>'#dc2626'],'warning'=>['bg'=>'#fffbeb','bd'=>'#fde68a','color'=>'#b45309']] as $type => $cfg)
    @if(session($type))
    <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['bd'] }};color:{{ $cfg['color'] }};padding:12px 16px;border-radius:10px;font-size:.875rem;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,.08)">
        {{ session($type) }}
    </div>
    @endif
    @endforeach
</div>
@endif

{{-- ── Main ─────────────────────────────────────────────────────────────────── -- --}}
<main class="main-content fade-in">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
