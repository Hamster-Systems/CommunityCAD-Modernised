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
        /* Panic highlighting is a CELL BACKGROUND animation, never opacity
           on a cell or parent. Opacity would dim popup menus, buttons,
           tooltips and other content nested inside the affected cell. */
        @keyframes cad-panic-cell-pulse {
            0%, 47% { background-color: rgba(180, 20, 24, .36); }
            48%, 100% { background-color: rgba(180, 20, 24, .07); }
        }
        .cad-panic-row > td {
            color: #ff6969 !important;
            font-weight: 800 !important;
            animation: cad-panic-cell-pulse 1480ms steps(1, end) infinite;
            animation-delay: var(--cad-panic-offset, 0ms);
        }
        .cad-panic-row > td > a,
        .cad-panic-row > td > span {
            color: inherit;
        }
        /* Standalone panic labels remain red, but not animated as an entire
           container: interactive children must stay legible and stable. */
        .cad-panic-call {
            color: #ff6969 !important;
            font-weight: 800 !important;
        }
        /* Menus stay visually independent of the state of their parent row. */
        .cad-panic-row [role="menu"],
        .cad-panic-row [data-cad-popup] {
            color: #fff !important;
            font-weight: normal !important;
            animation: none !important;
            opacity: 1 !important;
        }
        @media (prefers-reduced-motion: reduce) {
            .cad-panic-row > td {
                animation: none !important;
                background-color: rgba(180, 20, 24, .25);
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
