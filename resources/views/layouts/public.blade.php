<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ get_setting('community_name') }}</title>
    <link href="{{ get_setting('community_logo', 'https://communitycad.app/images/default_images/communitycad.png') }}"
        rel="icon" type="image/x-icon">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <script src="{{ mix('js/app.js') }}" defer></script>

    <script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- Auth-only styles: no changes to operational CAD layouts. --}}
    <style>
        .cc-auth-page{
            min-height:100vh;display:flex;align-items:center;justify-content:center;padding:28px 16px;
            color:#f8fafc;
            background:
                linear-gradient(105deg,rgba(5,17,33,.77),rgba(9,25,49,.51) 52%,rgba(6,20,37,.75)),
                url('https://upload.wikimedia.org/wikipedia/commons/f/f0/Sutton_Police_Station_by_night.jpg') center center/cover no-repeat,
                #0b1f33;
        }
        .cc-auth-card{
            width:100%;max-width:400px;padding:30px;border:1px solid rgba(208,222,239,.30);
            border-radius:20px;background:rgba(43,60,78,.84);
            box-shadow:0 20px 65px rgba(0,0,0,.40);
            -webkit-backdrop-filter:blur(15px);backdrop-filter:blur(15px);
        }
        .cc-auth-card-wide{max-width:480px;}
        .cc-auth-brand{display:flex;align-items:center;gap:12px;margin-bottom:24px;font-size:13px;line-height:1.3;}
        .cc-auth-brand strong,.cc-auth-brand span{display:block;}
        .cc-auth-brand strong{font-size:14px;color:#fff}
        .cc-auth-brand span,.cc-auth-muted{color:#d9e1ec;}
        .cc-auth-brand-mark{display:grid;place-items:center;width:42px;height:42px;flex-shrink:0;border:1px solid rgba(255,255,255,.18);border-radius:12px;background:linear-gradient(135deg,#3971a8,#26527c);color:white;font-size:20px;font-weight:800;}
        .cc-auth-card h1{font-size:30px;line-height:1.2;font-weight:750;letter-spacing:-.035em;margin:0 0 10px;}
        .cc-auth-muted{line-height:1.55;margin-bottom:22px;font-size:14px;}
        .cc-auth-btn{display:flex;justify-content:center;align-items:center;text-align:center;width:100%;min-height:43px;border-radius:10px;padding:11px 16px;font-size:13px;font-weight:750;text-decoration:none;transition:background .18s,transform .18s;}
        .cc-auth-btn:hover{transform:translateY(-1px)}
        .cc-auth-btn-primary{background:linear-gradient(105deg,#0ea5e9,#2563eb);color:#fff;}
        .cc-auth-btn-primary:hover{background:linear-gradient(105deg,#0284c7,#1d4ed8)}
        .cc-auth-btn-discord{margin-top:9px;background:#5865f2;color:#fff;}
        .cc-auth-btn-discord:hover{background:#4752c4}
        .cc-auth-btn-secondary{background:rgba(148,163,184,.22);border:1px solid rgba(203,213,225,.32);color:white;}
        .cc-auth-btn-secondary:hover{background:rgba(148,163,184,.36)}
        .cc-auth-btn:focus-visible,.cc-auth-input:focus-visible,.cc-auth-options a:focus-visible{outline:3px solid #7dd3fc;outline-offset:2px}
        .cc-auth-help,.cc-auth-footer{font-size:11px;color:#d1deec;line-height:1.5;margin-top:12px;}
        .cc-auth-footer{text-align:center;margin-top:22px;}
        .cc-auth-divider{display:flex;align-items:center;gap:12px;text-align:center;color:#d1deec;font-size:12px;margin:21px 0;}
        .cc-auth-divider:before,.cc-auth-divider:after{content:"";height:1px;flex:1;background:rgba(203,213,225,.28);}
        .cc-auth-field{display:block;margin-top:15px;font-weight:650;font-size:12px;color:#f5f8fd;}
        .cc-auth-input{display:block;width:100%;min-height:43px;margin-top:7px;padding:10px 12px;border:1px solid rgba(203,213,225,.35);border-radius:10px;background:rgba(226,232,240,.14);color:#fff;font-size:13px;}
        .cc-auth-input::placeholder{color:#c5d0dd;}
        .cc-auth-options{display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin:14px 0 20px;font-size:12px;}
        .cc-auth-options a{color:#a9dafa;text-decoration:none;}
        .cc-auth-options a:hover{text-decoration:underline;}
        .cc-auth-remember{display:inline-flex;align-items:center;gap:8px;color:#e2e8f0;}
        .cc-auth-submit{margin-top:3px;}
        .cc-auth-alert{padding:12px;border:1px solid #fca5a5;border-radius:9px;background:rgba(127,29,29,.45);color:#fee2e2;margin-bottom:15px;font-size:12px;}
        .cc-auth-details{background:rgba(15,23,42,.38);padding:13px;border:1px solid rgba(148,163,184,.35);border-radius:12px;margin:13px 0;}
        .cc-auth-details p{color:#d1deec;font-size:12px;line-height:1.6;}
        .cc-auth-photo-credit{position:fixed;bottom:8px;right:12px;color:#e2e8f0;font-size:10px;text-shadow:0 1px 6px #000;}
        .cc-auth-photo-credit a{color:inherit;text-decoration:underline}
        @media(max-width:480px){.cc-auth-page{padding:16px 12px}.cc-auth-card{padding:23px}.cc-auth-card h1{font-size:27px}.cc-auth-photo-credit{position:static;text-align:center;padding:8px;background:#0b1f33}}
        @media(prefers-reduced-motion:reduce){.cc-auth-btn{transition:none}.cc-auth-btn:hover{transform:none}}
    </style>
    @livewireStyles
</head>

<body class="bg-[#01161e]">

    @if (auth()->check() || !request()->routeIs('home', 'auth.login', 'auth.register', 'auth.local.verification-help', 'account.create'))
        @include('inc.public.navbar')
    @endif

    @yield('content')

    @if (!auth()->check() && request()->routeIs('home', 'auth.login', 'auth.register', 'auth.local.verification-help', 'account.create'))
        <div class="cc-auth-photo-credit">
            Background: <a href="https://commons.wikimedia.org/wiki/File:Sutton_Police_Station_by_night.jpg" target="_blank" rel="noopener noreferrer">Simon Lee / Wikimedia Commons</a>
            · <a href="https://creativecommons.org/licenses/by-sa/2.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 2.0</a>
            · tinted and cropped for display
        </div>
    @endif

    @if (session('alerts'))
        <div class="">
            <div class="absolute right-0 z-50 top-9">
                <x-alert />
            </div>
        </div>
    @endif

    @livewireScripts

</body>

</html>
