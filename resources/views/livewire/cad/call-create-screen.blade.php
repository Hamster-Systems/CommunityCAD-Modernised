<div class="{{ $modal ? 'cad-modal-call-fields w-full' : 'max-w-7xl mx-auto p-2' }}">
    @unless ($modal)
        <h1 class="text-xl font-bold text-white">Create Call</h1>
    @endunless
    <form action="{{ route('cad.call.store') }}"
        class="{{ $modal ? 'space-y-4 text-white cursor-default' : 'p-2 mt-5 space-y-2 text-white border border-white rounded cursor-default' }}" method="POST">
        @csrf
        <h1 class="text-xl font-semibold">Incident Location</h1>
        <div class="flex">
            <div class="w-1/2">
                <label class="block mr-2" for="cad-call-location-{{ $modal ? 'modal' : 'page' }}">Incident Address <span class="text-red-300">*</span></label>
                <input id="cad-call-location-{{ $modal ? 'modal' : 'page' }}" required @unless($modal) autofocus @endunless
                    class="text-input-sm uppercase" name="location" type="text" maxlength="255"
                    placeholder="Street address or nearest landmark" value="{{ old('location') }}">
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>
            <div class="w-1/3 ml-3">
                <label class="block mr-2" for="cad-call-city-{{ $modal ? 'modal' : 'page' }}">City <span class="text-red-300">*</span></label>
                <input id="cad-call-city-{{ $modal ? 'modal' : 'page' }}" required class="text-input-sm uppercase"
                    name="city" type="text" maxlength="255" placeholder="City / area" value="{{ old('city') }}">
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div class="w-1/5 ml-3">
                <label class="block mr-2" for="cad-call-postal-{{ $modal ? 'modal' : 'page' }}">Postal</label>
                <input id="cad-call-postal-{{ $modal ? 'modal' : 'page' }}" class="text-input-sm uppercase"
                    name="postal" type="text" maxlength="64" placeholder="Optional" value="{{ old('postal') }}">
                <x-input-error :messages="$errors->get('postal')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between gap-2">
            <h1 class="text-xl font-semibold">Caller Information</h1>
            <span class="text-xs text-slate-400">Optional</span>
        </div>
        <div class="flex">
            <div class="w-full text-white space-y-2">
                @forelse ($linked_civilians as $id => $name)
                    <div>
                        <input checked class="hidden" name="linked_civilians[]" type="checkbox"
                            value="{{ $id }}">
                        <label for="">{{ $name }} | <a class="text-red-600 hover:underline"
                                href="#" wire:click='remove_civilian_from_call("{{ $id }}")'>Remove</a>
                        </label>
                        <select class="select-input-sm" id="" name="linked_civilians_types[]">
                            <option value="RP">REPORTING PARTY</option>
                            <option value="SUSPECT">SUSPECT</option>
                            <option value="VICTIM">VICTIM</option>
                            <option value="WITNESS">WITNESS</option>
                            <option value="OTHER">OTHER</option>
                        </select>
                    </div>
                @empty
                    {{-- No linked callers yet. Keep this area compact. --}}
                @endforelse
            </div>
        </div>
        <div class="flex justify-between items-baseline">
            <div class="w-full">
                <label class="block mr-2">Find and link a person:</label>
                <input class="text-input-sm uppercase" type="search" placeholder="Search by name (optional)" wire:model.debounce.400ms='civilian_search'>
            </div>
        </div>
        <div class="flex">
            <div class="w-full text-white flex flex-wrap gap-2">
                @forelse ($civilians as $civilian)
                    <a class="flex py-2 px-4 shadow-md no-underline rounded-full bg-green-600 text-white font-sans font-semibold text-sm border-green-600 btn-primary hover:text-white hover:bg-green-500 focus:outline-none active:shadow-none mr-2"
                        href="#"
                        wire:click='add_civilian_to_call("{{ $civilian->id }}",
                    "{{ $civilian->name }}")'>
                        <svg class="w-4 h-4 mr-3" fill="none" stroke-width="1.5" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        {{ $civilian->name }}
                    </a>
                @empty
                    {{-- No matching caller results. --}}
                @endforelse
            </div>
        </div>

        <h1 class="text-xl font-semibold">Incident Details</h1>
        <div class="flex">
            <div class="w-3/5">
                <label class="block mr-2 text-lg">Nature:</label>
                <select class="select-input-sm" name="nature">
                    @foreach ($call_natures as $code => $props)
                        <option value="{{ $code }}">{{ $code }} - {{ $props['name'] }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('nature')" class="mt-2" />
            </div>
            <div class="w-2/5 ml-3">
                <label class="block mr-2 text-lg">Received via:</label>
                <select class="select-input-sm" name="source">
                    <option value="911 CALL">911 CALL</option>
                    <option value="NON-EMERGENCY">NON-EMERGENCY</option>
                    <option value="OFFICER">OFFICER</option>
                    <option value="FIRE">FIRE</option>
                    <option value="FLAG DOWN">FLAG DOWN</option>
                    <option value="OTHER">OTHER</option>
                </select>
                <x-input-error :messages="$errors->get('source')" class="mt-2" />
            </div>
        </div>

        <div class="flex">
            <div class="w-1/3">
                <label class="block mr-2 text-lg">Priority:</label>
                <select class="select-input-sm" name="priority">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option selected value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="w-1/3 ml-3">
                <label class="block mr-2 text-lg">Type:</label>
                <select class="select-input-sm" name="type">
                    <option value="1">Police</option>
                    <option value="2">Fire</option>
                    <option value="3">EMS</option>
                </select>
            </div>
            <div class="w-1/3 ml-3">
                <label class="block mr-2 text-lg">Status:</label>
                <select class="select-input-sm" name="status">
                    <option value="RCVD">RCVD - RECEIVED</option>
                    <option value="HLD">HLD - HOLD</option>
                </select>
            </div>
        </div>

        <div class="flex">
            <div class="w-full">
                <label class="block mr-2 text-lg">Narrative:</label>
                <textarea class="textarea-input-sm uppercase" name="narrative" placeholder="Briefly describe the situation and information reported by the caller…">{{ old('narrative') }}</textarea>
                <x-input-error :messages="$errors->get('narrative')" class="mt-2" />
            </div>
        </div>
        @unless ($modal)
            <button class="button-md bg-gray-600 hover:bg-gray-500 text-white" type="submit">CREATE CALL</button>
        @else
            <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 border-t border-slate-600 bg-[#162235] px-2 py-3">
                <button type="button" @click="$dispatch('cad-new-call-cancel')" class="rounded-lg border border-slate-500 px-4 py-2 font-semibold text-white hover:bg-slate-700">Cancel</button>
                <button type="submit" class="rounded-lg bg-sky-700 px-5 py-2 font-semibold text-white hover:bg-sky-600">Create call</button>
            </div>
        @endunless
    </form>
</div>
