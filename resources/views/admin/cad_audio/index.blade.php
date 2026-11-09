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
            <p class="mt-1">Choose a built-in sound, upload a custom file or mute each event. Audio is stored privately in persistent application storage. Browsers may require a click before allowing automatic sound playback.</p>
        </div>

        <div class="cad-audio-grid">
            @foreach ($events as $event => $settings)
                <section class="cad-audio-card" aria-labelledby="audio-title-{{ $event }}">
                    <header class="mb-4 flex items-start justify-between gap-3 border-b border-slate-700 pb-3">
                        <div>
                            <h2 id="audio-title-{{ $event }}" class="text-lg font-bold text-white">{{ $settings['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-300">{{ $settings['description'] }}</p>
                        </div>
                        <span class="rounded-md border border-slate-600 bg-slate-800 px-2 py-1 text-xs font-bold uppercase text-slate-200">
                            {{ $settings['mode'] }}
                        </span>
                    </header>

                    <form method="POST" action="{{ route('admin.cad_audio.update', $event) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <label class="block text-sm font-semibold text-slate-200" for="mode-{{ $event }}">
                            Audio mode
                        </label>
                        <select id="mode-{{ $event }}" name="mode" required
                            class="w-full rounded-md border border-slate-500 bg-[#24344a] px-3 py-2 text-white">
                            <option value="default" @selected($settings['mode'] === 'default')>Default sound</option>
                            <option value="custom" @selected($settings['mode'] === 'custom')>Custom uploaded sound</option>
                            <option value="muted" @selected($settings['mode'] === 'muted')>Muted</option>
                        </select>

                        <div>
                            <label for="sound-{{ $event }}" class="block text-sm font-semibold text-slate-200">
                                Upload custom sound
                            </label>
                            <input type="file" id="sound-{{ $event }}" name="sound" accept=".mp3,.ogg,.wav,audio/mpeg,audio/ogg,audio/wav"
                                class="mt-2 block w-full rounded-md border border-slate-500 bg-[#24344a] p-2 text-sm text-white file:mr-3 file:rounded file:border-0 file:bg-indigo-600 file:px-3 file:py-1 file:text-white">
                            <p class="mt-1 text-xs text-slate-400">
                                MP3, OGG or WAV · maximum 2 MB.
                                {{ $settings['has_custom'] ? 'A custom file is saved.' : 'No custom file uploaded.' }}
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

                        <div class="rounded-lg border border-slate-700 bg-[#0f1826] p-3">
                            <p class="mb-2 text-xs font-bold uppercase text-slate-400">Current sound preview</p>
                            @if ($settings['mode'] === 'muted')
                                <p class="text-sm text-slate-400">This event is muted. Select another mode and save to enable preview.</p>
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
                    <form method="POST" action="{{ route('admin.cad_audio.reset', $event) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="cad-audio-reset"
                            onclick="return confirm('Restore the default sound for {{ $settings['title'] }} and remove the saved custom file?')">
                            Restore default
                        </button>
                    </form>
                </section>
            @endforeach
        </div>
        <p class="mt-6 text-sm text-slate-400">Settings apply community-wide. Audio playback depends on browser autoplay permission; changes to sounds do not alter the associated CAD status or call history.</p>
    </div>
@endsection
