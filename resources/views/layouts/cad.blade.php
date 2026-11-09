<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ get_setting('community_name') }} | CAD</title>
    <link href="{{ get_setting('community_logo', 'https://communitycad.app/images/default_images/communitycad.png') }}"
        rel="icon" type="image/x-icon">

    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <script src="{{ mix('js/app.js') }}" defer></script>

    <script defer src="https://unpkg.com/alpinejs@3.2.4/dist/cdn.min.js"></script>

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400..900&display=swap" rel="stylesheet">

    {{-- DSEG uses a valid seven-segment typeface; the bundled DIGITALDREAM.ttf
         has malformed cmap/table metadata and Firefox rejects it. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dseg@0.46.0/css/dseg.css">
    <style>
        #running_clock_date,
        #running_clock_time {
            font-family: 'DSEG7-Classic', 'Orbitron', ui-monospace, monospace !important;
            font-variant-numeric: tabular-nums;
        }
        /* Live PANIC UNIT call alerts: animated red text, no Tailwind rebuild needed. */
        @keyframes cad-panic-text-flash {
            0%, 100% { color: #ff3434; text-shadow: 0 0 5px rgba(255, 40, 40, .75); }
            50% { color: #ff9b9b; text-shadow: none; }
        }
        .cad-panic-call {
            color: #ff3434 !important;
            font-weight: 800 !important;
            animation: cad-panic-text-flash 900ms ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .cad-panic-call { animation: none; }
        }
    </style>

    @livewireStyles
</head>

<body class="font-mono antialiased bg-black uppercase" onload="startTime()">

    <div class="">
        @yield('content')
    </div>

    @if (session('alerts'))
        <div class="">
            <div class="absolute right-0 z-50 top-9">
                <x-alert />
            </div>
        </div>
    @endif

    @livewireScripts

    @include('inc.cad.helperscripts')
</body>

</html>
