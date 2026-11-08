<section wire:poll.3s.keep-alive class="space-y-5 text-white normal-case">
    <header class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-700 pb-4">
        <div>
            <h2 class="text-xl font-bold">Incident activity</h2>
            <p class="mt-1 text-sm text-slate-400">Call #{{ $call->id }} · Unit, incident and operational records</p>
        </div>
        <span class="rounded-md border border-sky-800 bg-sky-950 px-3 py-2 text-xs font-semibold text-sky-200">
            Auto-refresh · ~3 seconds
        </span>
    </header>

    <div class="rounded-lg border border-slate-700 bg-[#152235] p-4">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-0 flex-1">
                <label for="cad_activity_search_{{ $call->id }}" class="mb-2 block text-xs font-semibold uppercase text-slate-300">Search incident activity</label>
                <input id="cad_activity_search_{{ $call->id }}" type="search" wire:model.debounce.400ms="search"
                    maxlength="100" placeholder="Search messages, callsigns or people…"
                    class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-sm text-white">
            </div>
            <span class="pb-2 text-xs text-slate-400">
                @if ($entries->total())
                    Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ $entries->total() }}
                @else
                    No matching activity
                @endif
            </span>
        </div>
        <div class="mt-3 flex flex-wrap gap-2" aria-label="Filter incident events">
            @foreach (['ALL' => 'All', 'CALL' => 'Calls', 'UNIT' => 'Units', 'PERSON' => 'People', 'VEHICLE' => 'Vehicles', 'REPORT' => 'Reports', 'NOTE' => 'Notes', 'SITUATION' => 'Situation', 'DISPATCH' => 'Dispatch', 'WITNESS' => 'Witness'] as $code => $label)
                <button type="button" wire:click="setFilter('{{ $code }}')"
                    aria-pressed="{{ $filter === $code ? 'true' : 'false' }}"
                    class="rounded-md border px-3 py-2 text-xs font-semibold transition
                        {{ $filter === $code ? 'border-indigo-400 bg-indigo-800 text-white' : 'border-slate-600 bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>


    <form wire:submit.prevent="postNote" class="rounded-lg border border-slate-700 bg-[#19283b] p-4">
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-sky-200">Submit an update</h3>
        <div class="grid gap-3 md:grid-cols-4">
            <div>
                <label for="cad_activity_type_{{ $call->id }}" class="mb-2 block text-xs font-semibold uppercase text-slate-300">Update type</label>
                <select id="cad_activity_type_{{ $call->id }}" wire:model="entryType"
                    class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-sm text-white">
                    <option value="NOTE">General Note</option>
                    <option value="SITUATION">Situation Update</option>
                    <option value="DISPATCH">Dispatch Update</option>
                    <option value="WITNESS">Witness Statement</option>
                </select>
                @error('entryType') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-3">
                <label for="cad_activity_text_{{ $call->id }}" class="mb-2 block text-xs font-semibold uppercase text-slate-300">Message</label>
                <textarea id="cad_activity_text_{{ $call->id }}" wire:model.defer="note" rows="3" maxlength="2000" required
                    class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-sm text-white"
                    placeholder="Describe the incident update, action taken, or information received…"></textarea>
                @error('note') <p class="mt-1 text-xs text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-slate-400">Your selected MDT identifier is recorded with each submission.</p>
            <button type="submit" wire:loading.attr="disabled" wire:target="postNote"
                class="rounded-md bg-indigo-700 px-5 py-2.5 text-sm font-semibold hover:bg-indigo-600 disabled:opacity-50">
                <span wire:loading.remove wire:target="postNote">Submit update</span>
                <span wire:loading wire:target="postNote">Submitting…</span>
            </button>
        </div>
    </form>

    <div wire:loading.delay wire:target="search,setFilter" class="text-sm text-sky-300">Updating results…</div>
    <div class="space-y-3" aria-live="polite" aria-label="Incident events">
        @forelse ($entries as $entry)
            @php
                $raw = $entry->text ?? '';
                $category = 'NOTE';
                $message = $raw;
                if (preg_match('/^\\[(CALL|UNIT|PERSON|VEHICLE|REPORT|NOTE|SYSTEM|SITUATION|DISPATCH|WITNESS)\\]\\s*(.*)$/s', $raw, $parts)) {
                    $category = $parts[1];
                    $message = $parts[2];
                } elseif (str_contains(strtoupper($raw), 'ASSIGN') || str_contains(strtoupper($raw), 'STATUS SET TO')) {
                    $category = 'UNIT';
                } elseif (str_contains(strtoupper($raw), 'CALL STATUS') || str_contains(strtoupper($raw), 'CALL PRIORITY')) {
                    $category = 'CALL';
                }
                $styles = [
                    'CALL' => 'border-sky-700 bg-sky-950 text-sky-200',
                    'UNIT' => 'border-emerald-700 bg-emerald-950 text-emerald-200',
                    'PERSON' => 'border-amber-700 bg-amber-950 text-amber-200',
                    'VEHICLE' => 'border-violet-700 bg-violet-950 text-violet-200',
                    'REPORT' => 'border-indigo-600 bg-indigo-950 text-indigo-200',
                    'SITUATION' => 'border-orange-600 bg-orange-950 text-orange-200',
                    'DISPATCH' => 'border-blue-600 bg-blue-950 text-blue-200',
                    'WITNESS' => 'border-teal-600 bg-teal-950 text-teal-200',
                    'NOTE' => 'border-slate-600 bg-slate-800 text-slate-200',
                    'SYSTEM' => 'border-slate-600 bg-slate-800 text-slate-200',
                ];
            @endphp
            <article wire:key="incident-event-{{ $entry->id }}" class="rounded-lg border border-slate-700 bg-[#182639] px-4 py-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-md border px-2.5 py-1 text-xs font-bold tracking-wide {{ $styles[$category] ?? $styles['NOTE'] }}">
                        {{ $category === 'SITUATION' ? 'SITUATION UPDATE' : ($category === 'WITNESS' ? 'WITNESS STATEMENT' : ($category === 'DISPATCH' ? 'DISPATCH UPDATE' : $category)) }}
                    </span>
                    <time class="text-xs text-slate-400" datetime="{{ $entry->created_at?->toIso8601String() }}"
                        title="{{ $entry->created_at?->format('d M Y H:i:s') }}">
                        {{ $entry->created_at?->format('d M Y · H:i:s') }}
                    </time>
                </div>
                <p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6 text-white">{{ $message }}</p>
                <p class="mt-3 border-t border-slate-700 pt-2 text-xs font-medium text-slate-300">
                    Recorded by {{ $entry->from ?: 'SYSTEM' }}
                </p>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-slate-600 px-5 py-10 text-center text-sm text-slate-400">
                Nothing matched your search. Try a different keyword or category.
            </div>
        @endforelse
    </div>

    @if ($entries->hasPages())
        <nav class="rounded-lg border border-slate-700 bg-[#152235] p-4" aria-label="Activity pagination">
            {{ $entries->links() }}
        </nav>
    @endif
</section>
