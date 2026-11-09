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

    {{-- Scoped to the public sign-in and signup pages; operational CAD styling is unaffected. --}}
    <style>
        .cc-auth-page{min-height:calc(100vh - 100px);display:flex;align-items:center;justify-content:center;padding:36px 16px;color:#f8fafc;background:radial-gradient(ellipse at 18% 30%,rgba(17,91,131,.48),transparent 48%),radial-gradient(ellipse at 86% 76%,rgba(29,65,107,.6),transparent 45%),linear-gradient(135deg,#061321,#142a42 55%,#07111d)}
        .cc-auth-card{width:100%;max-width:440px;padding:30px;border:1px solid rgba(203,213,225,.28);border-radius:22px;background:rgba(30,45,64,.9);box-shadow:0 20px 75px rgba(0,0,0,.35);backdrop-filter:blur(18px)}
        .cc-auth-card-wide{max-width:580px}
        .cc-auth-brand{display:flex;align-items:center;gap:12px;margin-bottom:24px;font-size:13px;line-height:1.35}
        .cc-auth-brand strong,.cc-auth-brand span{display:block}
        .cc-auth-brand span{color:#cbd5e1}
        .cc-auth-brand-mark{display:grid;place-items:center;width:43px;height:43px;border-radius:12px;background:#285a88;color:white;font-size:22px;font-weight:800}
        .cc-auth-card h1{font-size:30px;line-height:1.2;font-weight:750;letter-spacing:-.03em;margin:0 0 12px}
        .cc-auth-muted{color:#cbd5e1;line-height:1.6;margin-bottom:25px;font-size:14px}
        .cc-auth-btn{display:flex;justify-content:center;align-items:center;width:100%;min-height:46px;border-radius:10px;padding:12px 18px;font-size:14px;font-weight:700;text-decoration:none;transition:background .2s}
        .cc-auth-btn-primary{background:#2563eb;color:#fff}
        .cc-auth-btn-primary:hover{background:#1d4ed8}
        .cc-auth-btn-secondary{background:rgba(148,163,184,.16);border:1px solid #64748b;color:white}
        .cc-auth-btn-secondary:hover{background:rgba(148,163,184,.3)}
        .cc-auth-btn:focus-visible,.cc-auth-input:focus-visible{outline:3px solid #7dd3fc;outline-offset:2px}
        .cc-auth-help,.cc-auth-footer{font-size:12px;color:#cbd5e1;line-height:1.6;margin-top:14px}
        .cc-auth-footer{text-align:center;margin-top:24px}
        .cc-auth-divider{display:flex;align-items:center;gap:12px;text-align:center;color:#cbd5e1;font-size:12px;margin:22px 0}
        .cc-auth-divider:before,.cc-auth-divider:after{content:"";height:1px;flex:1;background:#64748b}
        .cc-auth-field{display:block;margin-top:16px;font-weight:600;font-size:13px}
        .cc-auth-input{display:block;width:100%;min-height:44px;margin-top:7px;padding:10px 12px;border:1px solid #64748b;border-radius:9px;background:rgba(255,255,255,.12);color:white}
        .cc-auth-input::placeholder{color:#cbd5e1}
        .cc-auth-alert{padding:12px;border:1px solid #fca5a5;border-radius:9px;background:rgba(127,29,29,.4);color:#fee2e2;margin-bottom:15px}
        .cc-auth-details{background:rgba(15,23,42,.38);padding:16px;border:1px solid rgba(148,163,184,.35);border-radius:12px;margin:16px 0}
        .cc-auth-details p{color:#cbd5e1;font-size:13px;line-height:1.6}
        @media(max-width:480px){.cc-auth-card{padding:22px}.cc-auth-card h1{font-size:26px}}
    </style>
    @livewireStyles
</head>

<body class="bg-[#01161e]">

    @include('inc.public.navbar')

    @yield('content')

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
