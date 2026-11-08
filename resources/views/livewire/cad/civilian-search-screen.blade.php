<section class="mx-auto mt-6 max-w-6xl px-4 text-white">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">CommunityCAD / Records</p>
            <h1 class="mt-1 text-2xl font-bold">Name Search</h1>
            <p class="mt-1 text-sm text-slate-400">Search by first and last name, identification number or status.</p>
        </div>
        <button wire:click="clearSearch" type="button" class="rounded-md border border-slate-600 px-4 py-2 text-sm hover:bg-slate-700">Clear search</button>
    </div>
    <div class="mb-5 grid gap-3 rounded-xl border border-slate-700 bg-[#152235] p-4 md:grid-cols-3">
        <div>
            <label for="cad_person_name" class="mb-2 block text-xs font-bold uppercase text-slate-300">Name</label>
            <input id="cad_person_name" type="search" wire:model.debounce.400ms="search_name" maxlength="100"
                placeholder="First and/or last name" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
        </div>
        <div>
            <label for="cad_person_id" class="mb-2 block text-xs font-bold uppercase text-slate-300">ID / SSN</label>
            <input id="cad_person_id" type="search" wire:model.debounce.400ms="search_ssn" maxlength="32"
                placeholder="Search by ID" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
        </div>
        <div>
            <label for="cad_person_status" class="mb-2 block text-xs font-bold uppercase text-slate-300">Status</label>
            <select id="cad_person_status" wire:model="search_status" class="w-full rounded-md border border-slate-500 bg-slate-800 px-3 py-2.5 text-white">
                <option value="">Any status</option>
                <option value="1">Alive</option>
                <option value="2">Wanted</option>
                <option value="3">Jailed</option>
                <option value="4">Deceased</option>
                <option value="5">Hospitalised</option>
            </select>
        </div>
    </div>
    <div wire:loading.delay class="mb-3 text-sm text-sky-300">Searching records…</div>
    @if (!$hasSearch)
        <p class="rounded-lg border border-dashed border-slate-600 bg-slate-900 px-5 py-10 text-center text-sm text-slate-300">
            Enter a name or ID, or select a status, to search the civilian database.
        </p>
    @elseif ($civilians->isEmpty())
        <p class="rounded-lg border border-dashed border-slate-600 bg-slate-900 px-5 py-10 text-center text-sm text-slate-300">
            No matching civilian records found. Try a broader search.
        </p>
    @else
        <p class="mb-3 text-xs text-slate-400">{{ $civilians->total() }} matching records · Showing {{ $civilians->firstItem() }}–{{ $civilians->lastItem() }}</p>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ($civilians as $civilian)
                <a wire:key="person-{{ $civilian->id }}" href="{{ route('cad.name.return', $civilian->id) }}"
                    class="block rounded-xl border border-slate-700 bg-[#142236] p-4 hover:border-sky-500 hover:bg-[#1c3048] focus:outline focus:outline-sky-400">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-white">{{ $civilian->name }}</h2>
                        <span class="rounded border px-2 py-1 text-xs font-bold {{ (int) $civilian->status === 2 ? 'border-rose-600 text-rose-300' : 'border-slate-500 text-slate-300' }}">
                            {{ $civilian->status_name ?? 'Unknown' }}
                        </span>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><dt class="text-xs uppercase text-slate-400">Identification</dt><dd class="mt-1 text-slate-200">{{ $civilian->s_n_n }}</dd></div>
                        <div><dt class="text-xs uppercase text-slate-400">Date of birth</dt><dd class="mt-1 text-slate-200">{{ $civilian->date_of_birth?->format('d M Y') ?? 'Not recorded' }}</dd></div>
                        <div class="col-span-2"><dt class="text-xs uppercase text-slate-400">Registered address</dt><dd class="mt-1 text-slate-200">{{ trim($civilian->address) ?: 'Not recorded' }}</dd></div>
                    </dl>
                    <p class="mt-3 border-t border-slate-700 pt-3 text-xs font-semibold text-sky-300">Open civilian record →</p>
                </a>
            @endforeach
        </div>
        <div class="mt-5">{{ $civilians->links() }}</div>
    @endif
</section>
