<div class="{{ $modal ? 'cad-modal-call-fields w-full' : 'max-w-7xl mx-auto p-2' }}">
    @unless ($modal)
        <h1 class="text-xl font-bold text-white">Create Call</h1>
        <style>
            /* Keep the legacy standalone create page usable too. */
            .cad-call-address-grid, .cad-call-detail-grid, .cad-call-status-grid {
                display: grid; gap: 12px; width: 100%; align-items: start;
            }
            .cad-call-address-grid { grid-template-columns: 2fr 1.3fr 1fr; }
            .cad-call-detail-grid { grid-template-columns: 1.5fr 1fr; }
            .cad-call-status-grid { grid-template-columns: repeat(3, minmax(0,1fr)); }
            .cad-call-field { min-width: 0; }
            .cad-address-picker { position: relative; min-width: 0; }
            .cad-address-options {
                position: absolute; top: calc(100% + 4px); left: 0; right: 0;
                z-index: 300; max-height: 225px; overflow-y: auto;
                border: 1px solid #64748b; border-radius: 8px;
                background: #14253a; box-shadow: 0 12px 25px rgba(0,0,0,.6);
            }
            .cad-address-option {
                display: flex; flex-direction: column; gap: 3px;
                width: 100%; min-height: 45px; padding: 9px 12px;
                background: transparent; border-bottom: 1px solid #334b66;
                color: white; text-align: left;
            }
            .cad-address-option:hover,
            .cad-address-option-active { background: #24567a; }
            .cad-address-option-street { font-weight: 700; font-size: 13px; }
            .cad-address-option-details { color: #cbd5e1; font-size: 11px; }

            .cad-call-field input, .cad-call-field select, .cad-call-field textarea {
                width:100%; min-width:0; box-sizing:border-box;
            }
            .cad-call-caller-section { display:flex; flex-direction:column; gap:10px; }
            .cad-call-section-heading { display:flex; align-items:center; justify-content:space-between; }
            .cad-call-linked-person, .cad-call-search-results { display:flex; flex-wrap:wrap; gap:10px; align-items:center; }
            @media (max-width:680px) {
                .cad-call-address-grid, .cad-call-detail-grid, .cad-call-status-grid { grid-template-columns:1fr; }
            }
        </style>
    @endunless
    <form action="{{ route('cad.call.store') }}"
        class="{{ $modal ? 'space-y-4 text-white cursor-default' : 'p-2 mt-5 space-y-2 text-white border border-white rounded cursor-default' }}" method="POST">
        @csrf
        <h1 class="text-xl font-semibold">Incident Location</h1>
        <div class="cad-call-address-grid">
            <div class="cad-call-field">
                <label class="block mr-2" for="cad-call-location-{{ $modal ? 'modal' : 'page' }}">Incident Address <span class="text-red-300">*</span></label>
                <div class="cad-address-picker" x-data="cadAddressSuggestions('{{ route('cad.addresses.suggest') }}')"
                    @click.outside="visible = false">
                    <input id="cad-call-location-{{ $modal ? 'modal' : 'page' }}" required @unless($modal) autofocus @endunless
                        class="text-input-sm uppercase" name="location" type="text" maxlength="255" autocomplete="off"
                        role="combobox" aria-autocomplete="list" :aria-expanded="visible && results.length > 0"
                        aria-controls="cad-address-options-{{ $modal ? 'modal' : 'page' }}"
                        x-model="query" @input.debounce.250ms="lookup()"
                        @focus="if (results.length) visible = true"
                        @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)"
                        @keydown.enter="chooseActive($event)" @keydown.escape="visible = false"
                        placeholder="Street address or nearest landmark" value="{{ old('location') }}">
                    <div id="cad-address-options-{{ $modal ? 'modal' : 'page' }}" x-show="visible && results.length"
                        x-cloak role="listbox" class="cad-address-options">
                        <template x-for="(item, index) in results" :key="item.id">
                            <button type="button" role="option" class="cad-address-option"
                                :aria-selected="active === index"
                                :class="{ 'cad-address-option-active': active === index }"
                                @click="choose(item)">
                                <span x-text="item.street" class="cad-address-option-street"></span>
                                <span x-text="[item.city, item.postal].filter(Boolean).join(' · ')" class="cad-address-option-details"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('location')" class="mt-2" />
            </div>
            <div class="cad-call-field">
                <label class="block mr-2" for="cad-call-city-{{ $modal ? 'modal' : 'page' }}">City <span class="text-red-300">*</span></label>
                <input id="cad-call-city-{{ $modal ? 'modal' : 'page' }}" required class="text-input-sm uppercase"
                    name="city" type="text" maxlength="255" placeholder="City / area" value="{{ old('city') }}">
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div class="cad-call-field">
                <label class="block mr-2" for="cad-call-postal-{{ $modal ? 'modal' : 'page' }}">Postal</label>
                <input id="cad-call-postal-{{ $modal ? 'modal' : 'page' }}" class="text-input-sm uppercase"
                    name="postal" type="text" maxlength="64" placeholder="Optional" value="{{ old('postal') }}">
                <x-input-error :messages="$errors->get('postal')" class="mt-2" />
            </div>
        </div>

        {{-- Keep civilian linking available on the standalone form. The quick
             911 call modal records the incident first; people can be added
             later on the full incident workspace. --}}
        @unless ($modal)
        <section class="cad-call-caller-section">
            <div class="cad-call-section-heading">
                <h1 class="text-xl font-semibold">Caller Information</h1>
                <span class="text-xs text-slate-400">Optional</span>
            </div>
            @if (count($linked_civilians))
                <div class="cad-call-linked-people">
                    @foreach ($linked_civilians as $id => $name)
                        <div class="cad-call-linked-person" wire:key="cad-linked-person-{{ $id }}">
                            <input checked class="hidden" name="linked_civilians[]" type="checkbox" value="{{ $id }}">
                            <span>{{ $name }}</span>
                            <select class="select-input-sm" name="linked_civilians_types[]" aria-label="Role for {{ $name }}">
                                <option value="RP">REPORTING PARTY</option>
                                <option value="SUSPECT">SUSPECT</option>
                                <option value="VICTIM">VICTIM</option>
                                <option value="WITNESS">WITNESS</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                            <button type="button" class="text-red-300 hover:text-red-100"
                                wire:click='remove_civilian_from_call("{{ $id }}")'>Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="cad-call-field">
                <label for="cad-caller-search-{{ $modal ? 'modal' : 'page' }}">Find and link a person</label>
                <input id="cad-caller-search-{{ $modal ? 'modal' : 'page' }}"
                    class="text-input-sm uppercase" type="search"
                    placeholder="Search by name (optional)" wire:model.debounce.400ms="civilian_search">
            </div>
            @if ($civilians && $civilians->isNotEmpty())
                <div class="cad-call-search-results" aria-label="Matching people">
                    @foreach ($civilians as $civilian)
                        <button type="button" class="rounded-md border border-emerald-600 bg-emerald-900 px-3 py-2 text-sm text-white hover:bg-emerald-800"
                            wire:key="cad-civilian-result-{{ $civilian->id }}"
                            wire:click='add_civilian_to_call("{{ $civilian->id }}", "{{ $civilian->name }}")'>
                            + {{ $civilian->name }}
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        @endunless

        <h1 class="text-xl font-semibold">Incident Details</h1>
        <div class="cad-call-detail-grid">
            <div class="cad-call-field">
                <label class="block mr-2 text-lg">Nature:</label>
                <select class="select-input-sm" name="nature">
                    @foreach ($call_natures as $code => $props)
                        <option value="{{ $code }}">{{ $code }} - {{ $props['name'] }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('nature')" class="mt-2" />
            </div>
            <div class="cad-call-field">
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

        <div class="cad-call-status-grid">
            <div class="cad-call-field">
                <label class="block mr-2 text-lg">Priority:</label>
                <select class="select-input-sm" name="priority">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option selected value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div class="cad-call-field">
                <label class="block mr-2 text-lg">Type:</label>
                <select class="select-input-sm" name="type">
                    <option value="1">Police</option>
                    <option value="2">Fire</option>
                    <option value="3">EMS</option>
                </select>
            </div>
            <div class="cad-call-field">
                <label class="block mr-2 text-lg">Initial status:</label>
                <select class="select-input-sm" name="status">
                    <option value="RCVD">RCVD - RECEIVED</option>
                    <option value="HLD">HLD - HOLD</option>
                </select>
            </div>
        </div>

        <div class="cad-call-field">
            <div>
                <label class="block mr-2 text-lg">Narrative:</label>
                <textarea class="textarea-input-sm uppercase" name="narrative" required placeholder="Briefly describe the situation and information reported by the caller…">{{ old('narrative') }}</textarea>
                <x-input-error :messages="$errors->get('narrative')" class="mt-2" />
            </div>
        </div>
        @unless ($modal)
            <button class="button-md bg-gray-600 hover:bg-gray-500 text-white" type="submit">CREATE CALL</button>
        @else
            <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 border-t border-slate-600 bg-[#162235] px-2 py-3">
                <button type="button" @click="$dispatch('cad-new-call-cancel')" class="rounded-lg border border-slate-500 px-4 py-2 font-semibold text-white hover:bg-slate-700">Cancel</button>
                <button type="submit" class="rounded-lg bg-sky-700 px-5 py-2 font-semibold text-white hover:bg-sky-600">Create &amp; open call</button>
            </div>
        @endunless
    </form>

@once
<script>
    function cadAddressSuggestions(endpoint) {
        return {
            query: '', results: [], visible: false, active: -1, sequence: 0,
            init() {
                const input = this.$el.querySelector('input[name="location"]');
                this.query = input ? input.value : '';
            },
            async lookup() {
                const term = this.query.trim();
                const token = ++this.sequence;
                this.active = -1;
                if (term.length < 2) { this.results = []; this.visible = false; return; }
                try {
                    const response = await fetch(endpoint + '?q=' + encodeURIComponent(term), {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) throw new Error('Lookup unavailable');
                    const data = await response.json();
                    if (token !== this.sequence) return;
                    this.results = Array.isArray(data.results) ? data.results : [];
                    this.visible = this.results.length > 0;
                } catch (_) {
                    if (token === this.sequence) { this.results = []; this.visible = false; }
                }
            },
            move(direction) {
                if (!this.results.length) return;
                this.visible = true;
                this.active = (this.active + direction + this.results.length) % this.results.length;
                this.$nextTick(() => {
                    const option = this.$el.querySelectorAll('.cad-address-option')[this.active];
                    if (option) option.scrollIntoView({block: 'nearest'});
                });
            },
            chooseActive(event) {
                if (this.visible && this.active >= 0 && this.results[this.active]) {
                    event.preventDefault();
                    this.choose(this.results[this.active]);
                }
            },
            choose(item) {
                this.sequence++;
                this.query = item.street;
                this.results = [];
                this.visible = false;
                const form = this.$el.closest('form');
                if (form) {
                    for (const key of ['city', 'postal']) {
                        if (item[key]) {
                            const input = form.querySelector('[name="' + key + '"]');
                            if (input) {
                                input.value = item[key];
                                input.dispatchEvent(new Event('input', {bubbles:true}));
                            }
                        }
                    }
                }
                const input = this.$el.querySelector('input[name="location"]');
                if (input) input.dispatchEvent(new Event('change', {bubbles:true}));
            }
        };
    }
</script>
@endonce

</div>
