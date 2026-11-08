<div wire:poll.4s class="space-y-2">

    @forelse ($units as $unit)
        <article wire:key="incident-unit-{{ $unit->id }}" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-700 bg-slate-900 p-4">
            <div>
                <p class="font-bold text-sky-300">{{ $unit->unit_callsign }}</p>
                <p class="mt-1 text-sm text-slate-200">{{ $unit->officer_name }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $unit->user_department?->department?->name ?? 'Agency' }}</p>
            </div>
            <span class="rounded border border-slate-500 bg-slate-800 px-3 py-2 text-sm font-semibold">{{ $unit->status }}</span>
        </article>
    @empty
        <div class="rounded-lg border border-dashed border-slate-600 p-5 text-sm text-slate-400">No units have been assigned to this call.</div>
    @endforelse
</div>
