<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MedTech') — India's Health Platform</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{
            font-family:'DM Sans',sans-serif;
            background-color:#eef4f7;
            background-image:
                radial-gradient(ellipse at 15% 55%,rgba(13,92,110,.08) 0%,transparent 55%),
                radial-gradient(ellipse at 85% 15%,rgba(20,184,184,.06) 0%,transparent 45%);
            min-height:100vh;
        }
        .font-display{font-family:'Playfair Display',serif}
        :root{
            --p:#0d5c6e; --p-lt:#0d9e9e; --p-bg:#f0f8fa; --p-bd:#c7e8ee;
            --tx:#1a2e35; --mt:#6b7f86; --bd:#dde8ec;
            --er:#dc2626; --er-bg:#fef2f2; --ok:#059669; --ok-bg:#ecfdf5;
            --sd:#f5f1eb;
        }
        /* Inputs */
        .inp{
            display:block;width:100%;padding:.65rem .85rem;
            border:1.5px solid var(--bd);border-radius:10px;
            font-size:.9375rem;color:var(--tx);background:#fff;
            transition:border-color .2s,box-shadow .2s;outline:none;
            font-family:'DM Sans',sans-serif;
        }
        .inp:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(13,92,110,.08)}
        .inp.err{border-color:var(--er);background:var(--er-bg)}
        .inp::placeholder{color:#a8b8bd}
        /* Mobile combo */
        .mob-wrap{
            display:flex;border:1.5px solid var(--bd);border-radius:10px;
            overflow:hidden;background:#fff;
            transition:border-color .2s,box-shadow .2s;
        }
        .mob-wrap:focus-within{border-color:var(--p);box-shadow:0 0 0 3px rgba(13,92,110,.08)}
        .mob-wrap.err{border-color:var(--er)}
        .cc-sel{
            padding:.65rem .6rem;border:none;border-right:1.5px solid var(--bd);
            background:#f7fafc;font-size:.875rem;color:var(--tx);
            cursor:pointer;outline:none;font-family:'DM Sans',sans-serif;min-width:90px;
        }
        .mob-inp{
            flex:1;border:none;background:transparent;
            padding:.65rem .85rem;font-size:.9375rem;color:var(--tx);
            outline:none;font-family:'DM Sans',sans-serif;
        }
        .mob-inp::placeholder{color:#a8b8bd}
        /* OTP boxes */
        .otp-box{
            width:50px;height:58px;text-align:center;
            font-size:1.5rem;font-weight:600;
            border:1.5px solid var(--bd);border-radius:12px;
            background:#fff;outline:none;color:var(--tx);
            transition:border-color .15s,box-shadow .15s,background .15s;
            font-family:'DM Sans',sans-serif;caret-color:transparent;
        }
        .otp-box:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(13,92,110,.10);background:#f7fbfc}
        .otp-box.filled{border-color:var(--p-lt);background:var(--p-bg)}
        .otp-box.oerr{border-color:var(--er);background:var(--er-bg);animation:shake .3s}
        @keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-4px)}40%,80%{transform:translateX(4px)}}
        /* Buttons */
        .btn-p{
            display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
            padding:.75rem 1.25rem;background:var(--p);color:#fff;border:none;
            border-radius:11px;font-size:.9375rem;font-weight:600;cursor:pointer;
            transition:background .2s,transform .1s,box-shadow .2s;
            font-family:'DM Sans',sans-serif;box-shadow:0 2px 8px rgba(13,92,110,.25);
        }
        .btn-p:hover:not(:disabled){background:#0a4f60;box-shadow:0 4px 16px rgba(13,92,110,.30);transform:translateY(-1px)}
        .btn-p:active:not(:disabled){transform:translateY(0)}
        .btn-p:disabled{opacity:.55;cursor:not-allowed;transform:none}
        .btn-o{
            display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
            padding:.7rem 1.25rem;background:transparent;color:var(--p);
            border:1.5px solid var(--p-bd);border-radius:11px;font-size:.9375rem;
            font-weight:500;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;
            text-decoration:none;
        }
        .btn-o:hover{background:var(--p-bg);border-color:var(--p)}
        .btn-g{
            display:flex;align-items:center;justify-content:center;gap:6px;width:100%;
            padding:.7rem 1.25rem;background:var(--sd);color:var(--mt);border:none;
            border-radius:11px;font-size:.875rem;font-weight:500;cursor:pointer;
            transition:all .2s;font-family:'DM Sans',sans-serif;text-decoration:none;
        }
        .btn-g:hover{background:#ece7df;color:var(--tx)}
        /* Misc */
        .lbl{display:block;font-size:.72rem;font-weight:600;color:var(--p);letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px}
        .ferr{font-size:.75rem;color:var(--er);margin-top:4px}
        .tab-bar{display:flex;background:#edf4f7;border-radius:12px;padding:4px;gap:4px;margin-bottom:1.5rem}
        .tab-btn{flex:1;padding:8px;border-radius:9px;border:none;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;background:transparent;color:var(--mt)}
        .tab-btn.on{background:#fff;color:var(--p);font-weight:600;box-shadow:0 1px 6px rgba(13,92,110,.12)}
        .tab-btn:not(.on):hover{background:rgba(255,255,255,.5);color:var(--p)}
        .alert{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;font-size:.875rem;margin-bottom:1.25rem}
        .a-ok{background:var(--ok-bg);color:#065f46;border:1px solid #a7f3d0}
        .a-er{background:var(--er-bg);color:#991b1b;border:1px solid #fecaca}
        .a-in{background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe}
        .a-ic{width:16px;height:16px;margin-top:1px;flex-shrink:0}
        .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .su{animation:slideUp .38s cubic-bezier(.22,.68,0,1.2) both}
        .role-card{border:2px solid var(--bd);border-radius:14px;padding:1.25rem;cursor:pointer;transition:all .2s;background:#fff}
        .role-card:hover{border-color:var(--p-lt);background:var(--p-bg)}
        .role-card.sel{border-color:var(--p);background:var(--p-bg);box-shadow:0 0 0 3px rgba(13,92,110,.08)}
        .divider{display:flex;align-items:center;gap:12px;margin:1.25rem 0;color:#94a3b8;font-size:.72rem;font-weight:500;letter-spacing:.06em;text-transform:uppercase}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--bd)}
        select.inp{cursor:pointer}
    </style>
</head>
<body>
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-10">

    <!-- Logo -->
    <div class="su mb-7 text-center">
        <a href="{{ route('home') }}" style="display:inline-flex;flex-direction:column;align-items:center;gap:6px;text-decoration:none">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="background:var(--p);width:38px;height:38px;border-radius:11px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(13,92,110,.3)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <span class="font-display" style="font-size:1.6rem;color:var(--p);letter-spacing:-.02em">MedTech</span>
            </div>
            <span style="font-size:.68rem;color:#94a3b8;letter-spacing:.12em;text-transform:uppercase;font-weight:500">India's Health Platform</span>
        </a>
    </div>

    <!-- Card -->
    <div class="w-full max-w-md su" style="animation-delay:.08s;background:#fff;border-radius:20px;border:1px solid var(--bd);box-shadow:0 8px 40px rgba(13,92,110,.08),0 1px 4px rgba(13,92,110,.04);padding:2rem">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert a-ok">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert a-er">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @if(session('info'))
        <div class="alert a-in">
            <svg class="a-ic" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            {{ session('info') }}
        </div>
        @endif

        @yield('content')
    </div>

    <div class="mt-6 text-center su" style="animation-delay:.16s;font-size:.72rem;color:#94a3b8">
        <a href="#" style="color:inherit;text-decoration:none">Privacy</a>
        <span style="margin:0 8px">·</span>
        <a href="#" style="color:inherit;text-decoration:none">Terms</a>
        <span style="margin:0 8px">·</span>
        <a href="#" style="color:inherit;text-decoration:none">Support</a>
        <p style="margin-top:6px">© {{ date('Y') }} MedTech India</p>
    </div>
</div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
