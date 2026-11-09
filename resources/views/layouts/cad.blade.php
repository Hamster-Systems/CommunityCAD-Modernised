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
        /* BUSY status only: readable amber against the existing CAD theme. */
        .cad-status-busy,
        .cad-status-busy > td,
        .cad-status-busy > td a,
        .cad-status-busy > td span {
            color: #ffb347 !important;
        }
        /* Animate the actual table cells/text, not a <tr> color that
           child elements may override. Reduced motion remains high-contrast. */
        @keyframes cad-panic-flash {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: .16; }
        }
        .cad-panic-row > td,
        .cad-panic-call {
            color: #ff4040 !important;
            font-weight: 800 !important;
            animation: cad-panic-flash 900ms steps(1, end) infinite;
        }
        .cad-panic-row > td a,
        .cad-panic-row > td span {
            color: inherit;
        }
        @media (prefers-reduced-motion: reduce) {
            .cad-panic-row > td, .cad-panic-call {
                animation: none !important;
                opacity: 1;
            }
        }
    </style>

    @livewireStyles
</head>

<body class="font-mono antialiased bg-black uppercase" onload="startTime()"
    data-cad-audio-feed="{{ route('cad.audio.events') }}"
    data-cad-audio-user="{{ (int) auth()->id() }}">

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

    <script src="{{ asset('js/cad-audio-live.js') }}" defer></script>

    @include('inc.cad.helperscripts')
</body>

</html>
