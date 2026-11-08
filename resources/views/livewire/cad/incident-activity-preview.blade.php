<aside wire:poll.5s class="text-white normal-case" aria-label="Recent incident activity">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-700 pb-3">
        <div>
            <h2 class="text-sm font-bold uppercase tracking-wide">Recent Activity</h2>
            <p class="mt-1 text-xs text-slate-400">Latest updates · Refreshes automatically</p>
        </div>
        <span class="rounded border border-sky-800 px-2 py-1 text-xs text-sky-300">LIVE</span>
    </div>
    <div class="mt-3 space-y-2">
        @forelse ($events as $event)
            @php
                $raw = $event->text ?? '';
                $type = 'NOTE';
                $message = $raw;
                if (preg_match('/^\\[(CALL|UNIT|PERSON|VEHICLE|REPORT|NOTE|SYSTEM|SITUATION|DISPATCH|WITNESS)\\]\\s*(.*)$/s', $raw, $parts)) {
                    $type = $parts[1];
                    $message = $parts[2];
                } elseif (str_contains(strtoupper($raw), 'ASSIGN') || str_contains(strtoupper($raw), 'STATUS SET TO')) {
                    $type = 'UNIT';
                } elseif (str_contains(strtoupper($raw), 'CALL STATUS') || str_contains(strtoupper($raw), 'CALL PRIORITY')) {
                    $type = 'CALL';
                }
                $badges = [
                    'CALL' => 'border-sky-700 text-sky-200',
                    'UNIT' => 'border-emerald-700 text-emerald-200',
                    'PERSON' => 'border-amber-700 text-amber-200',
                    'VEHICLE' => 'border-violet-700 text-violet-200',
                    'REPORT' => 'border-indigo-600 text-indigo-200',
                    'SITUATION' => 'border-orange-600 text-orange-200',
                    'DISPATCH' => 'border-blue-600 text-blue-200',
                    'WITNESS' => 'border-teal-600 text-teal-200',
                    'NOTE' => 'border-slate-600 text-slate-200',
                    'SYSTEM' => 'border-slate-600 text-slate-300',
                ];
            @endphp
            <article wire:key="recent-event-{{ $event->id }}" class="rounded-lg border border-slate-700 bg-[#1b293b] p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="rounded border px-2 py-0.5 text-xs font-bold {{ $badges[$type] ?? $badges['NOTE'] }}">{{ $type }}</span>
                    <time class="text-xs text-slate-400" title="{{ $event->created_at?->format('d M Y H:i:s') }}">{{ $event->created_at?->format('H:i:s') }}</time>
                </div>
                <p class="mt-2 break-words text-sm leading-relaxed text-slate-100">{{ \Illuminate\Support\Str::limit($message, 170) }}</p>
                <p class="mt-2 truncate border-t border-slate-700 pt-2 text-xs text-slate-400" title="{{ $event->from ?: 'SYSTEM' }}">{{ $event->from ?: 'SYSTEM' }}</p>
            </article>
        @empty
            <p class="rounded-lg border border-dashed border-slate-600 p-4 text-sm text-slate-400">No activity recorded for this incident.</p>
        @endforelse
    </div>
</aside>
