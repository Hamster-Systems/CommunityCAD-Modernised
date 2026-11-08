<section class="mx-auto mt-6 max-w-6xl px-4 text-white">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">CommunityCAD / Records</p>
            <h1 class="mt-1 text-2xl font-bold">Vehicle Search</h1>
            <p class="mt-1 text-sm text-slate-400">Search registration plates, vehicle models and registration status.</p>
        </div>
        <button type="button" wire:click="clearSearch" class="rounded-md border border-slate-600 px-4 py-2 text-sm hover:bg-slate-700">Clear search</button>
    </div>
    @if (auth()->user()->active_unit?->alpr)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-amber-700 bg-amber-950/40 px-4 py-3 text-sm">
            <span>Latest ALPR detection: <strong>{{ auth()->user()->active_unit->alpr }}</strong></span>
            <a href="{{ route('cad.vehicle.return', auth()->user()->active_unit->alpr) }}" class="font-semibold text-amber-200 underline">Open detection</a>
        </div>
    @endif
    <div class="mb-5 grid gap-3 rounded-xl border border-slate-700 bg-[#152235] p-4 md:grid-cols-3">
        <div>
            <label for="cad_vehicle_plate" class="mb-2 block text-xs font-bold uppercase text-slate-300">Registration plate</label>
            <input id="cad_vehicle_plate" wire:model.debounce.400ms="search_plate" type="search" maxlength="32"
                placeholder="ABC 123" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
        </div>
        <div>
            <label for="cad_vehicle_model" class="mb-2 block text-xs font-bold uppercase text-slate-300">Model</label>
            <input id="cad_vehicle_model" wire:model.debounce.400ms="search_model" type="search" maxlength="100"
                placeholder="Vehicle make or model" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
        </div>
        <div>
            <label for="cad_vehicle_status" class="mb-2 block text-xs font-bold uppercase text-slate-300">Status</label>
            <select id="cad_vehicle_status" wire:model="search_status" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
                <option value="">Any status</option>
                <option value="1">Valid</option>
                <option value="2">Stolen</option>
                <option value="3">Impounded</option>
                <option value="4">Booted</option>
                <option value="5">Deleted</option>
            </select>
        </div>
    </div>
    <div wire:loading.delay class="mb-3 text-sm text-sky-300">Searching vehicles…</div>
    @if (!$hasSearch)
        <p class="rounded-lg border border-dashed border-slate-600 bg-slate-900 px-5 py-10 text-center text-sm text-slate-300">Enter a plate or model, or select a status, to search registered vehicles.</p>
    @elseif ($vehicles->isEmpty())
        <p class="rounded-lg border border-dashed border-slate-600 bg-slate-900 px-5 py-10 text-center text-sm text-slate-300">No matching vehicles found.</p>
    @else
        <p class="mb-3 text-xs text-slate-400">{{ $vehicles->total() }} matching vehicles · Showing {{ $vehicles->firstItem() }}–{{ $vehicles->lastItem() }}</p>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($vehicles as $vehicle)
                <a wire:key="vehicle-{{ $vehicle->id }}" href="{{ route('cad.vehicle.return', $vehicle->plate) }}"
                    class="block rounded-xl border border-slate-700 bg-[#142236] p-4 hover:border-sky-500 hover:bg-[#1c3048] focus:outline focus:outline-sky-400">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-xl font-bold tracking-wide text-white">{{ $vehicle->plate }}</h2>
                        <span class="rounded border px-2 py-1 text-xs font-bold {{ (int) $vehicle->vehicle_status === 2 ? 'border-rose-600 text-rose-300' : 'border-slate-500 text-slate-300' }}">
                            {{ $vehicle->status_name ?? 'Unknown' }}
                        </span>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-400">Model</dt><dd class="mt-1">{{ $vehicle->model ?: 'Not recorded' }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-400">Colour</dt><dd class="mt-1">{{ $vehicle->color ?: 'Not recorded' }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs uppercase text-slate-400">Registered owner</dt><dd class="mt-1">{{ $vehicle->civilian?->name ?? $vehicle->business?->name ?? 'Unknown owner' }}</dd></div>
                    </dl>
                    <p class="mt-3 border-t border-slate-700 pt-3 text-xs font-semibold text-sky-300">Open vehicle record →</p>
                </a>
            @endforeach
        </div>
        <div class="mt-5">{{ $vehicles->links() }}</div>
    @endif
</section>
