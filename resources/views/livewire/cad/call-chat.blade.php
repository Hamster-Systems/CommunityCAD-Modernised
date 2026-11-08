<div wire:poll.3s class="text-white">
    <div class="mb-4 border-b border-slate-600 pb-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-bold uppercase tracking-wide">Incident activity</h2>
                <p class="mt-1 text-xs text-slate-400">Notes, unit changes and linked records</p>
            </div>
            <span class="rounded border border-slate-600 px-2 py-1 text-xs text-slate-300">Newest first</span>
        </div>
        <form wire:submit.prevent="postNote" class="space-y-2">
            <label for="call_activity_note_{{ $call->id }}" class="block text-xs text-slate-300">Add an operational note</label>
            <textarea id="call_activity_note_{{ $call->id }}" wire:model.defer="note" maxlength="2000" rows="3" required
                class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2 text-sm text-white"
                placeholder="Describe an update, witness statement or observation…"></textarea>
            @error('note') <p class="text-xs text-rose-300">{{ $message }}</p> @enderror
            <div class="flex justify-end">
                <button wire:loading.attr="disabled" wire:target="postNote" class="rounded-md bg-indigo-700 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-600 disabled:opacity-50" type="submit">
                    <span wire:loading.remove wire:target="postNote">Post note</span>
                    <span wire:loading wire:target="postNote">Posting…</span>
                </button>
            </div>
        </form>
    </div>
    <div class="mb-3 flex flex-wrap gap-1" aria-label="Filter call activity">
        @foreach (['ALL' => 'All', 'CALL' => 'Call', 'UNIT' => 'Units', 'PERSON' => 'People', 'VEHICLE' => 'Vehicles', 'REPORT' => 'Reports', 'NOTE' => 'Notes'] as $category => $label)
            <button type="button" wire:click="setFilter('{{ $category }}')"
                aria-pressed="{{ $filter === $category ? 'true' : 'false' }}"
                class="rounded-md border px-2 py-1.5 text-xs font-semibold {{ $filter === $category ? 'border-indigo-400 bg-indigo-900 text-white' : 'border-slate-600 bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
    <div class="max-h-[68vh] space-y-3 overflow-y-auto overflow-x-hidden pr-1" aria-live="polite">
        @forelse ($call_chat as $chat)
            @php
                $text = $chat->text ?? '';
                $type = 'NOTE';
                $message = $text;
                if (preg_match('/^\\[(CALL|UNIT|PERSON|VEHICLE|REPORT|NOTE|SYSTEM)\\]\\s*(.*)$/s', $text, $matched)) {
                    $type = $matched[1];
                    $message = $matched[2];
                } elseif (str_contains(strtoupper($text), 'ASSIGN') || str_contains(strtoupper($text), 'STATUS SET TO')) {
                    $type = 'UNIT';
                } elseif (str_contains(strtoupper($text), 'CALL STATUS') || str_contains(strtoupper($text), 'CALL PRIORITY') || str_contains(strtoupper($text), 'CALL NATURE')) {
                    $type = 'CALL';
                } elseif ($chat->from === 'SYSTEM') {
                    $type = 'SYSTEM';
                }
                $colours = [
                    'CALL' => 'border-sky-700 text-sky-200',
                    'UNIT' => 'border-emerald-700 text-emerald-200',
                    'PERSON' => 'border-amber-700 text-amber-200',
                    'VEHICLE' => 'border-violet-700 text-violet-200',
                    'REPORT' => 'border-indigo-600 text-indigo-200',
                    'NOTE' => 'border-slate-500 text-slate-200',
                    'SYSTEM' => 'border-slate-600 text-slate-300',
                ];
                $colour = $colours[$type] ?? $colours['SYSTEM'];
            @endphp
            <article wire:key="activity-{{ $chat->id }}" class="relative rounded-lg border border-slate-600/70 bg-[#142236] p-3.5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="rounded-md border px-2 py-1 text-xs font-bold tracking-wide {{ $colour }}">{{ $type }}</span>
                    <time class="text-xs text-slate-400" datetime="{{ $chat->created_at?->toIso8601String() }}"
                        title="{{ $chat->created_at?->format('d M Y H:i:s') }}">
                        {{ $chat->created_at?->format('H:i:s · d M') }}
                    </time>
                </div>
                <p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-slate-100">{{ $message }}</p>
                <p class="mt-3 flex items-center gap-2 border-t border-slate-700 pt-2 text-xs text-slate-300">
                    {{ $chat->from ?: 'SYSTEM' }}
                </p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-600 p-5 text-sm text-slate-400">No matching activity yet.</p>
        @endforelse
        @if ($hasMore)
            <button type="button" wire:click="showMore" wire:loading.attr="disabled" wire:target="showMore"
                class="w-full rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm font-semibold hover:bg-slate-700">Load older activity</button>
        @endif
    </div>
</div>
