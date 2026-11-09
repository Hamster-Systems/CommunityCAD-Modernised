<div class="space-y-2" wire:poll.5s>
    @if ($pendingEnrouteCallId)
        {{-- Original addedtocall.mp3 announcement; play once per assignment, not every 5s Livewire poll. --}}
        <section class="mx-auto my-2 max-w-7xl rounded-lg border border-sky-500/70 bg-[#16283b] p-4 text-white shadow-lg normal-case"
            role="region" aria-label="New call assignment"
            x-data
            x-init="(() => {
                const key = 'cad-assignment-sound:{{ (int) auth()->user()->active_unit->id }}:{{ $pendingEnrouteCallId }}:{{ auth()->user()->active_unit->updated_at?->getTimestamp() ?? 0 }}';
                try {
                    if (!sessionStorage.getItem(key)) {
                        sessionStorage.setItem(key, 'played');
                        $refs.assignmentAudio.play().catch(() => {});
                    }
                } catch (error) {
                    $refs.assignmentAudio.play().catch(() => {});
                }
            })()">
            <audio x-ref="assignmentAudio" preload="auto">
                <source src="{{ secure_asset('audio/addedtocall.mp3') }}" type="audio/mpeg">
            </audio>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-sky-300">New call assignment · #{{ $pendingEnrouteCallId }}</p>
                    <p class="mt-1 text-sm">Your unit has been assigned to this call. Would you like to mark yourself <strong>En Route</strong>?</p>
                    <p class="mt-1 text-xs text-slate-300">If you keep your current status, the call remains assigned to your unit.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button"
                        wire:click="answerAssignment({{ $pendingEnrouteCallId }}, true)"
                        wire:loading.attr="disabled" wire:target="answerAssignment"
                        class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold hover:bg-sky-600 disabled:opacity-50">
                        Mark En Route
                    </button>
                    <button type="button"
                        wire:click="answerAssignment({{ $pendingEnrouteCallId }}, false)"
                        wire:loading.attr="disabled" wire:target="answerAssignment"
                        class="rounded-md border border-slate-500 bg-slate-800 px-4 py-2 text-sm hover:bg-slate-700 disabled:opacity-50">
                        Keep current status
                    </button>
                    <a class="rounded-md border border-slate-500 px-4 py-2 text-sm hover:bg-slate-800"
                        href="{{ route('cad.call.show', $pendingEnrouteCallId) }}">View call</a>
                    <button type="button" @click="$refs.assignmentAudio.currentTime = 0; $refs.assignmentAudio.play().catch(() => {})"
                        class="rounded-md border border-slate-500 px-3 py-2 text-sm hover:bg-slate-800"
                        aria-label="Replay call assignment announcement" title="Replay assignment sound">
                        🔊
                    </button>
                </div>
            </div>
        </section>
    @endif
    @foreach ($active_alerts as $alert)
        @if ($alert['color'] == 'red')
            <div class="bg-red-600 text-white animate-pulse py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ route($alert['link']) }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif

                @if ($alert['audio'] != null)
                    <audio autoplay id="panicButton" volume="1">
                        <source src="{{ secure_asset($alert['audio']) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                @endif
            </div>
        @elseif($alert['color'] == 'yellow')
            <div class="bg-yellow-600 text-white py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ route($alert['link']) }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif

                @if ($alert['audio'] != null)
                    <audio autoplay id="panicButton" volume="1">
                        <source src="{{ secure_asset($alert['audio']) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                @endif
            </div>
        @elseif($alert['color'] == 'green')
            <div class="bg-green-600 text-white py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ $alert['link'] }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif

                @if ($alert['audio'] != null)
                    <audio autoplay id="panicButton" volume="1">
                        <source src="{{ secure_asset($alert['audio']) }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                @endif
            </div>
        @endif
    @endforeach
</div>
