@extends('layouts.admin')

@section('content')
    <style>
        /* Scoped styles: stable even if the existing Tailwind bundle misses
           modern utility classes. Never affect other Admin Portal pages. */
        .cad-audio-page { width:100%; max-width:1150px; margin:0 auto; }
        .cad-audio-grid { display:grid; grid-template-columns:minmax(0,1fr); gap:18px; }
        @media (min-width:950px) {
            .cad-audio-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        .cad-audio-card { min-width:0; border:1px solid #334155; border-radius:12px; background:#152234; padding:20px; }
        .cad-audio-card select,
        .cad-audio-card input[type=file] {
            display:block; width:100%; max-width:100%; box-sizing:border-box;
            padding:10px 12px; border:1px solid #52657e; border-radius:7px;
            background:#26374e !important; color:#f8fafc !important;
            font:inherit; font-size:14px;
        }
        .cad-audio-card select option { background:#26374e; color:#f8fafc; }
        .cad-audio-card input[type=file]::file-selector-button {
            background:#374a65; border:0; border-radius:5px; color:#fff;
            padding:6px 10px; margin-right:12px; cursor:pointer;
        }
        .cad-audio-card input[type=range] { width:100%; accent-color:#6366f1; cursor:pointer; }
        .cad-audio-card audio { display:block; width:100%; max-width:100%; min-width:0; }
        .cad-audio-card button { font:600 13px system-ui,sans-serif; border-radius:7px; padding:10px 15px; cursor:pointer; }
        .cad-audio-card .cad-audio-save { background:#4f46e5 !important; color:#fff !important; border:1px solid #6366f1; }
        .cad-audio-card .cad-audio-save:hover { background:#6366f1 !important; }
        .cad-audio-card .cad-audio-reset { background:#19293b !important; color:#e2e8f0 !important; border:1px solid #64748b; }
        .cad-audio-card .cad-audio-reset:hover { background:#334155 !important; }
        .cad-audio-card :is(button,input,select,a):focus-visible { outline:2px solid #38bdf8; outline-offset:3px; }

        /* Three even columns on wide admin pages: nine voice cards form
           three complete rows instead of an orphan and a blank half-row. */
        .cad-audio-page { max-width:1280px; }
        .cad-audio-grid { grid-template-columns:minmax(0,1fr); align-items:stretch; }
        @media (min-width:740px) {
            .cad-audio-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .cad-audio-grid > .cad-audio-card:last-child:nth-child(odd) { grid-column:1/-1; }
        }
        @media (min-width:960px) {
            .cad-audio-grid { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .cad-audio-grid > .cad-audio-card:last-child:nth-child(odd) { grid-column:auto; }
        }
        .cad-audio-card { display:flex; flex-direction:column; gap:14px; padding:18px; }
        .cad-audio-card > header { margin:0; min-height:85px; }
        .cad-audio-card > form:first-of-type { display:flex; flex-direction:column; gap:13px; flex:1; }
        .cad-audio-card > form:last-of-type { margin:0; }
        .cad-audio-card .cad-audio-save { width:100%; }
        .cad-audio-card .cad-audio-reset { width:100%; }
        .cad-audio-card .cad-audio-source { border:1px solid #455873; border-radius:8px; padding:10px 12px; background:#0e1d2c; }
        .cad-audio-card .cad-audio-pill { display:inline-flex; align-items:center; padding:4px 9px; border-radius:999px; border:1px solid; font:bold 11px system-ui,sans-serif; letter-spacing:.03em; }
        .cad-audio-card .cad-audio-pill--uploaded { border-color:#2c9d84; background:#123c38; color:#8ef0d1; }
        .cad-audio-card .cad-audio-pill--bundled { border-color:#64748b; background:#263548; color:#dbe6f5; }
        .cad-audio-card .cad-audio-pill--muted { border-color:#b98c48; background:#40301b; color:#ffe1a4; }
        .cad-audio-stat-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-bottom:18px; }
        @media (min-width:780px) { .cad-audio-stat-grid {grid-template-columns:repeat(4,minmax(0,1fr));} }
        .cad-audio-stat { background:#152234; border:1px solid #34465b; border-radius:10px; padding:12px 15px; }
        .cad-audio-stat strong {display:block;color:white;font-size:22px;font-weight:750;font-variant-numeric:tabular-nums;}
        .cad-audio-stat span {display:block;color:#cbd5e1;font-size:12px;}
        .cad-audio-card .cad-audio-preview { margin-top:auto; }
        @media (max-width:959px) { .cad-audio-card > header {min-height:0;} }
    </style>
    <div class="admin-overview cad-audio-page">
        <header class="admin-overview-header">
            <div>
                <p class="admin-overview-eyebrow">Community administration / CAD</p>
                <h1>CAD Audio</h1>
                <p>Manage the sounds used by dispatch, unit status changes and emergency notifications.</p>
            </div>
            <a href="{{ route('admin.index') }}" class="admin-overview-primary-link">Back to overview &rarr;</a>
        </header>

        @if (session('audio_success'))
            <div role="status" class="mb-5 rounded-lg border border-emerald-700 bg-emerald-950 px-4 py-3 text-emerald-100">
                {{ session('audio_success') }}
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-lg border border-red-600 bg-red-950 px-4 py-3 text-red-100">
                <strong>Unable to save audio settings:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-5 rounded-lg border border-sky-800 bg-[#142639] p-4 text-sm text-slate-200">
            <strong class="text-white">Notification audio</strong>
            <p class="mt-1">Every notification uses its bundled audio until you upload a replacement. Upload an MP3, OGG or WAV directly without changing modes, or select Muted to disable playback. Uploaded sounds stay saved when muted.</p>
        </div>

        @php
            $audioTotal = count($events);
            $audioUploaded = collect($events)->filter(fn ($sound) => $sound['has_custom'])->count();
            $audioMuted = collect($events)->filter(fn ($sound) => $sound['mode'] === 'muted')->count();
            $audioBundled = $audioTotal - $audioUploaded;
        @endphp
        <div class="cad-audio-stat-grid" aria-label="Audio library overview">
            <div class="cad-audio-stat"><strong>{{ $audioTotal }}</strong><span>Notification sounds</span></div>
            <div class="cad-audio-stat"><strong>{{ $audioUploaded }}</strong><span>Uploaded recordings</span></div>
            <div class="cad-audio-stat"><strong>{{ $audioBundled }}</strong><span>Bundled recordings</span></div>
            <div class="cad-audio-stat"><strong>{{ $audioMuted }}</strong><span>Muted notifications</span></div>
        </div>
        <h2 class="mb-2 text-lg font-bold text-white">CAD notification sounds</h2>
        <p class="mb-4 text-sm text-slate-300">Audio source shows which recording is saved. Playback shows whether it is enabled or muted. A muted notification still keeps its uploaded recording.</p>
        <div class="cad-audio-grid">
            @foreach ($events as $event => $settings)
                <section class="cad-audio-card" aria-labelledby="audio-title-{{ $event }}">
                    <header class="border-b border-slate-700 pb-3">
                        <h3 id="audio-title-{{ $event }}" class="text-base font-bold leading-snug text-white">{{ $settings['title'] }}</h3>
                        <p class="mt-2 text-xs leading-relaxed text-slate-300">{{ $settings['description'] }}</p>
                    </header>
                    <div class="cad-audio-source" aria-label="Recording and playback status for {{ $settings['title'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-300">Audio source</span>
                            <span class="cad-audio-pill {{ $settings['has_custom'] ? 'cad-audio-pill--uploaded' : 'cad-audio-pill--bundled' }}">
                                {{ $settings['has_custom'] ? 'UPLOADED AUDIO' : 'BUNDLED AUDIO' }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-semibold text-slate-300">Playback</span>
                            @if ($settings['mode'] === 'muted')
                                <span class="cad-audio-pill cad-audio-pill--muted">MUTED</span>
                            @else
                                <span class="text-xs font-semibold text-emerald-300">Enabled</span>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.cad_audio.update', $event) }}" enctype="multipart/form-data">
                        @csrf
                        <label class="block text-sm font-semibold text-slate-200" for="mode-{{ $event }}">
                            Playback mode
                        </label>
                        <select id="mode-{{ $event }}" name="mode" required
                            class="w-full rounded-md border border-slate-500 bg-[#24344a] px-3 py-2 text-white">
                            <option value="custom" @selected($settings['mode'] === 'custom')>Enabled (use saved recording)</option>
                            <option value="muted" @selected($settings['mode'] === 'muted')>Muted</option>
                        </select>

                        <div>
                            <label for="sound-{{ $event }}" class="block text-sm font-semibold text-slate-200">
                                Replace recording
                            </label>
                            <input type="file" id="sound-{{ $event }}" name="sound" accept=".mp3,.ogg,.wav,audio/mpeg,audio/ogg,audio/wav"
                                class="mt-2 block w-full rounded-md border border-slate-500 bg-[#24344a] p-2 text-sm text-white file:mr-3 file:rounded file:border-0 file:bg-indigo-600 file:px-3 file:py-1 file:text-white">
                            <p class="mt-1 text-xs text-slate-400">
                                MP3, OGG or WAV · maximum 2 MB.
                                {{ $settings['has_custom'] ? 'Your uploaded recording is saved. You can replace it here.' : 'Currently using the included recording. Upload a file to replace it.' }}
                            </p>
                        </div>

                        <div>
                            <label for="volume-{{ $event }}" class="flex justify-between text-sm font-semibold text-slate-200">
                                <span>Notification volume</span>
                                <output id="volume-label-{{ $event }}">{{ $settings['volume'] }}%</output>
                            </label>
                            <input id="volume-{{ $event }}" name="volume" type="range" min="0" max="100" step="5"
                                value="{{ $settings['volume'] }}"
                                oninput="document.getElementById('volume-label-{{ $event }}').textContent = this.value + '%'"
                                class="mt-2 w-full accent-indigo-500">
                        </div>

                        <div class="cad-audio-preview rounded-lg border border-slate-700 bg-[#0f1826] p-3">
                            <p class="mb-2 text-xs font-bold uppercase text-slate-400">Preview current recording</p>
                            @if ($settings['mode'] === 'muted')
                                <p class="text-sm text-slate-400">Playback is muted. Change to Enabled and save to listen again. Your recording is retained.</p>
                            @else
                                <audio controls preload="none" class="w-full" aria-label="Preview {{ $settings['title'] }} sound"
                                    onplay="this.volume = {{ $settings['volume'] / 100 }}">
                                    <source src="{{ route('admin.cad_audio.preview', $event) }}">
                                    Your browser does not support audio preview.
                                </audio>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="submit" class="cad-audio-save">
                                Save audio
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.cad_audio.reset', $event) }}">
                        @csrf
                        <button type="submit" class="cad-audio-reset"
                            onclick="return confirm('Remove the uploaded file and restore the bundled sound for {{ $settings['title'] }}?')">
                            Restore bundled audio
                        </button>
                    </form>
                </section>
            @endforeach
        </div>
        <section class="mt-8 rounded-xl border border-slate-700 bg-[#152234] p-5">
            <h2 class="text-lg font-bold text-white">Department &amp; alert tones</h2>
            <p class="mt-2 text-sm text-slate-300">Fire station tones, dispatch signalling, timed priority alerts and department-specific alert patterns will be configured in a separate tones table. They are not part of the voice notification settings above.</p>
        </section>
        <p class="mt-6 text-sm text-slate-400">Settings apply community-wide. Audio playback depends on browser autoplay permission; changes to sounds do not alter the associated CAD status or call history.</p>
    </div>
@endsection
