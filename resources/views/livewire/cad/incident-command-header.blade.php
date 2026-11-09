<div wire:poll.4s class="mx-auto max-w-[1560px] px-4 pt-4 text-white">
    <div class="mb-3 overflow-hidden rounded-lg border border-slate-600 bg-[#15202e]">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-700 px-5 py-4">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <a href="{{ route('cad.mdt') }}" class="hover:text-white">← MDT / CALL QUEUE</a>
                    <span>/</span><span>INCIDENT {{ $call->id }}</span>
                </div>
                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <h1 class="text-xl font-bold md:text-2xl">#{{ $call->id }} · {{ $natureLabel }}</h1>
                    <span class="rounded border border-amber-600/70 bg-amber-950 px-2 py-1 text-xs font-bold text-amber-200">PRIORITY {{ $call->priority }}</span>
                    <span class="rounded border border-slate-500 px-2 py-1 text-xs font-bold">{{ $statusLabel }}</span>
                </div>
                <p class="mt-2 text-sm text-slate-300">{{ $call->location ?: 'Location not supplied' }} · {{ $call->city ?: 'City unknown' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-md border px-3 py-2 text-xs font-bold {{ $attached ? 'border-emerald-600 bg-emerald-950 text-emerald-200' : 'border-slate-500 bg-slate-800 text-slate-300' }}">
                    {{ $attached ? 'YOUR UNIT ASSIGNED' : 'VIEW ONLY · NOT ASSIGNED' }}
                </span>
                @if ($unit && (int) $unit->department_type !== 2)
                    <button wire:click="toggleAssignment" wire:loading.attr="disabled" wire:target="toggleAssignment"
                        class="rounded-md px-4 py-2 text-sm font-semibold disabled:opacity-50 {{ $attached ? 'bg-rose-700 hover:bg-rose-600' : 'bg-emerald-700 hover:bg-emerald-600' }}"
                        type="button">{{ $attached ? 'Detach unit' : 'Attach my unit' }}</button>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 divide-x divide-y divide-slate-700 text-sm md:grid-cols-4 md:divide-y-0">
            <div class="px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-slate-400">Incident type</div>
                <div class="mt-1 font-semibold">{{ $call->type_name }} · {{ $call->source }}</div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-slate-400">Assigned units · {{ $call->attached_units->count() }}</div>
                <div class="mt-1 flex flex-wrap gap-1">
                    @forelse ($call->attached_units as $assigned)
                        <span class="rounded border border-sky-700 bg-sky-950 px-2 py-0.5 font-semibold">{{ $assigned->unit_callsign }} <span class="font-normal text-sky-200">{{ $assigned->status }}</span></span>
                    @empty
                        <span class="text-slate-400">No units assigned</span>
                    @endforelse
                </div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-slate-400">Call received</div>
                <div class="mt-1 font-semibold">{{ $call->created_at->format('d M Y · H:i') }}</div>
            </div>
            <div class="px-4 py-3">
                <div class="text-xs uppercase tracking-wide text-slate-400">Last updated</div>
                <div class="mt-1 font-semibold">{{ $call->updated_at->format('d M Y · H:i') }}</div>
            </div>
        </div>
    </div>
</div>
