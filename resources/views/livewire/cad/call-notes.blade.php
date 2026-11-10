<section wire:poll.5s class="space-y-4 text-white normal-case" aria-label="Call notes">
    <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-700 pb-3">
        <div>
            <h2 class="{{ $compact ? 'text-sm' : 'text-xl' }} font-bold">Call Notes</h2>
            <p class="mt-1 text-xs text-slate-400">
                {{ $compact ? 'Latest operational information' : 'Dispatch information, situation updates and witness notes for this incident' }}
            </p>
        </div>
        <span class="rounded border border-sky-800 bg-sky-950 px-2 py-1 text-xs font-semibold text-sky-200">LIVE</span>
    </header>

    @if ($canPost)
        <form wire:submit.prevent="addDispatchNote" class="rounded-lg border border-sky-800 bg-[#192d41] p-4">
            <label class="mb-2 block text-sm font-semibold text-sky-100" for="call-dispatch-note-{{ $callId }}">
                Dispatch Call Note
            </label>
            <textarea id="call-dispatch-note-{{ $callId }}" rows="3" maxlength="2000" required
                wire:model.defer="message" placeholder="Important information for units responding to this incident…"
                class="w-full rounded-md border border-slate-500 bg-slate-900 p-3 text-sm text-white"></textarea>
            @error('message') <p class="mt-1 text-xs text-red-300">{{ $message }}</p> @enderror
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-400">Also recorded in Activity · Attached units receive their Call Updated notification</p>
                <button type="submit" wire:loading.attr="disabled" wire:target="addDispatchNote"
                    class="rounded-md bg-sky-700 px-4 py-2.5 text-sm font-semibold hover:bg-sky-600 disabled:opacity-50">
                    Post Call Note
                </button>
            </div>
        </form>
    @endif

    <div class="space-y-3" aria-live="polite">
        @forelse ($notes as $note)
            @php
                $raw = (string) $note->text;
                $category = 'NOTE';
                $body = $raw;
                if (preg_match('/^\\[(DISPATCH|NOTE|SITUATION|WITNESS)\\]\\s*(.*)$/s', $raw, $match)) {
                    $category = $match[1];
                    $body = $match[2];
                }
                $isDispatch = $category === 'DISPATCH';
            @endphp
            <article wire:key="call-note-{{ $compact ? 'preview-' : 'full-' }}{{ $note->id }}"
                class="rounded-lg border {{ $isDispatch ? 'border-sky-700 bg-[#19324b]' : 'border-slate-600 bg-[#19283b]' }} p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-bold {{ $isDispatch ? 'text-sky-200' : 'text-slate-300' }}">
                        {{ $category === 'DISPATCH' ? 'DISPATCH UPDATE' : ($category === 'SITUATION' ? 'SITUATION UPDATE' : ($category === 'WITNESS' ? 'WITNESS NOTE' : 'CALL NOTE')) }}
                    </span>
                    <time class="text-xs text-slate-300" datetime="{{ $note->created_at?->toIso8601String() }}"
                        title="{{ $note->created_at?->format('d M Y H:i:s') }}">
                        {{ $note->created_at?->format('d M Y · H:i') }}
                    </time>
                </div>
                <p class="mt-2 whitespace-pre-wrap break-words text-sm leading-relaxed text-white">{{ $compact ? \\Illuminate\\Support\\Str::limit($body, 150) : $body }}</p>
                <p class="mt-2 text-xs text-slate-300">From {{ $note->from ?: 'CommunityCAD System' }}</p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-600 p-4 text-sm text-slate-300">
                No operational call notes yet. Status changes and assignments remain available in Activity.
            </p>
        @endforelse
    </div>

    @unless ($compact)
        @if ($notes->count() >= 40)
            <p class="text-xs text-slate-400">Showing the latest 40 notes. Older entries remain available in the Activity tab.</p>
        @endif
    @endunless
</section>
