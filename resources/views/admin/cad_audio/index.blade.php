@extends('layouts.admin')

@section('content')
    <div class="admin-overview" style="max-width: 1100px;">
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

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach ($events as $event => $settings)
                <section class="rounded-xl border border-slate-700 bg-[#152234] p-5 shadow-md" aria-labelledby="audio-title-{{ $event }}">
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
                            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                                Save audio
                            </button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.cad_audio.reset', $event) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700"
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
