<section wire:poll.4s class="space-y-2">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <p class="text-sm text-slate-400">Live event history · Most recent updates first</p>
        <span class="rounded border border-slate-600 px-2 py-1 text-xs text-slate-400">Updates automatically</span>
    </div>
    @forelse ($entries as $entry)
        @php
            $text = $entry->text ?? '';
            $type = 'NOTE';
            $message = $text;
            if (preg_match('/^\\[(CALL|UNIT|PERSON|VEHICLE|REPORT|NOTE|SYSTEM)\\]\\s*(.*)$/s', $text, $matches)) {
                $type = $matches[1];
                $message = $matches[2];
            }
        @endphp
        <article wire:key="history-{{ $entry->id }}" class="rounded-lg border border-slate-700 bg-[#19283b] px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-bold tracking-wide text-sky-300">{{ $type }}</span>
                <time class="text-xs text-slate-400">{{ $entry->created_at?->format('H:i:s · d M Y') }}</time>
            </div>
            <p class="mt-2 whitespace-pre-wrap break-words text-sm leading-6 text-slate-100">{{ $message }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ $entry->from ?: 'SYSTEM' }}</p>
        </article>
    @empty
        <p class="rounded-lg border border-dashed border-slate-600 p-5 text-sm text-slate-400">No incident activity yet.</p>
    @endforelse
</section>
