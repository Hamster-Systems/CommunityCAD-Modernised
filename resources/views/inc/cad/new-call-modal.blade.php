    {{-- Same call-creation form/controller as /cad/call; the MDT phone action
         is available throughout the CAD navigation. --}}
    <div x-data="cadNewCallModal()"
         @cad-open-new-call.window="showModal()"
         @cad-new-call-cancel.window="closeModal()"
         @keydown.escape.window="closeModal()">
        <div x-show="createdUrl" x-cloak role="status"
            class="mx-auto my-3 flex max-w-7xl flex-wrap items-center justify-between gap-2 rounded-lg border border-emerald-700 bg-emerald-950 p-3 text-emerald-100">
            <span x-text="successMessage"></span>
            <a :href="createdUrl" class="rounded border border-emerald-500 px-3 py-1.5 text-sm font-semibold hover:bg-emerald-800">Open created call</a>
        </div>
        <div x-show="open" x-cloak class="cad-mdt-new-call-backdrop fixed inset-0 z-[120] flex items-center justify-center bg-black/80 p-2 sm:p-5"
            @click.self="closeModal()" aria-hidden="false">
            <section role="dialog" aria-modal="true" aria-labelledby="cad-new-call-title" aria-describedby="cad-new-call-desc"
                x-ref="dialog" @keydown.tab="trapFocus($event)" @submit.capture.prevent="submitCall($event)"
                @input.capture="dirty = true" @change.capture="dirty = true"
                class="cad-new-call-dialog border border-slate-500 bg-[#162235] text-white shadow-2xl">
                <header class="flex flex-none items-start justify-between gap-3 border-b border-slate-600 bg-[#1c3046] px-4 py-3 sm:px-6">
                    <div>
                        <h2 id="cad-new-call-title" class="text-lg font-bold">Create 911 Call</h2>
                        <p id="cad-new-call-desc" class="mt-1 text-xs text-slate-300">Record the essential incident information, then open the call to add details and dispatch units.</p>
                    </div>
                    <button type="button" @click="closeModal()" aria-label="Close new call dialog"
                        class="rounded-md border border-slate-500 p-2 text-white hover:bg-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-sky-400">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </header>
                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 sm:px-6 cad-new-call-modal">
                    <div x-show="errorMessage" x-cloak role="alert" class="mb-4 rounded-md border border-red-600 bg-red-950 px-3 py-3 text-sm text-red-100">
                        <p class="font-bold" x-text="errorMessage"></p>
                        <ul class="mt-2 list-disc pl-5" x-show="errors.length">
                            <template x-for="message in errors" :key="message"><li x-text="message"></li></template>
                        </ul>
                    </div>
                    @livewire('cad.call-create-screen', ['modal' => true], key('call-create-modal'))
                </div>
            </section>
        </div>
        <style>
            [x-cloak] { display: none !important; }
            .cad-mdt-new-call-backdrop {
                position: fixed; inset: 0; z-index: 120;
                display: flex; align-items: center; justify-content: center;
                padding: 16px; background: rgba(0,0,0,.82);
            }
            .cad-new-call-dialog {
                display: flex; flex-direction: column; width: min(100%, 850px);
                max-height: calc(100dvh - 32px); min-height: 0;
                overflow: hidden; border-radius: 12px;
            }
            .cad-new-call-modal { min-height: 0; overflow-y: auto; overscroll-behavior: contain; }
            .cad-new-call-modal .cad-modal-call-fields,
            .cad-new-call-modal .cad-modal-call-fields form { width: 100%; min-width: 0; }
            .cad-new-call-modal .cad-modal-call-fields form {
                display: flex; flex-direction: column; gap: 14px;
            }
            .cad-new-call-modal .cad-call-address-grid,
            .cad-new-call-modal .cad-call-detail-grid,
            .cad-new-call-modal .cad-call-status-grid {
                display: grid; gap: 14px; align-items: start; width: 100%;
            }
            .cad-new-call-modal .cad-call-address-grid {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1.3fr) minmax(0, 1fr);
            }
            .cad-new-call-modal .cad-call-detail-grid {
                grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
            }
            .cad-new-call-modal .cad-call-status-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .cad-new-call-modal .cad-call-field,
            .cad-new-call-modal .cad-call-address-grid > *,
            .cad-new-call-modal .cad-call-detail-grid > *,
            .cad-new-call-modal .cad-call-status-grid > * {
                min-width: 0; width: 100%; margin: 0;
            }
            .cad-new-call-modal .cad-modal-call-fields label {
                display: block; font-size: 12px; font-weight: 600;
                line-height: 1.5; margin: 0 0 5px; color: #cbd5e1;
            }
            .cad-new-call-modal .text-input-sm,
            .cad-new-call-modal .select-input-sm,
            .cad-new-call-modal .textarea-input-sm {
                display: block; width: 100%; min-width: 0;
                box-sizing: border-box; padding: 10px 12px;
                border: 1px solid #64748b; border-radius: 7px;
                background: #24364d; color: #fff; font-size: 14px;
                height: auto; min-height: 41px;
            }
            .cad-new-call-modal .cad-address-picker { position: relative; min-width: 0; }
            .cad-new-call-modal .cad-address-options {
                position: absolute; z-index: 300; top: calc(100% + 4px);
                left: 0; right: 0; max-height: 225px; overflow-y: auto;
                border: 1px solid #5b7694; border-radius: 8px;
                background: #14253a; box-shadow: 0 12px 25px rgba(0,0,0,.6);
                overscroll-behavior: contain;
            }
            .cad-new-call-modal .cad-address-option {
                display: flex; flex-direction: column; gap: 3px;
                width: 100%; min-height: 45px; padding: 9px 12px;
                text-align: left; border-bottom: 1px solid #334b66;
                color: white; cursor: pointer; background: transparent;
            }
            .cad-new-call-modal .cad-address-option:last-child { border-bottom: 0; }
            .cad-new-call-modal .cad-address-option:hover,
            .cad-new-call-modal .cad-address-option-active { background: #24567a; }
            .cad-new-call-modal .cad-address-option-street { font-weight: 700; font-size: 13px; }
            .cad-new-call-modal .cad-address-option-details { font-size: 11px; color: #cbd5e1; }
            .cad-new-call-modal textarea.textarea-input-sm { min-height: 92px; resize: vertical; }
            .cad-new-call-modal .cad-modal-call-fields h1 {
                margin: 1px 0 3px; font-size: 13px; line-height: 1.5;
                color: #93c5fd; letter-spacing: .05em;
                text-transform: uppercase; font-weight: 700;
            }
            .cad-new-call-modal .cad-call-caller-section {
                display: flex; flex-direction: column; gap: 10px;
            }
            .cad-new-call-modal .cad-call-section-heading {
                display:flex;justify-content:space-between;align-items:center;
            }
            .cad-new-call-modal .cad-call-linked-person {
                display:flex;align-items:center;gap:10px;flex-wrap:wrap;
            }
            .cad-new-call-modal .cad-call-linked-person select { flex:1;min-width:130px; }
            .cad-new-call-modal .cad-call-search-results { display:flex;flex-wrap:wrap;gap:8px; }
            .cad-new-call-modal input:focus,
            .cad-new-call-modal select:focus,
            .cad-new-call-modal textarea:focus {
                outline: 2px solid #38bdf8; outline-offset: 1px;
            }
            @media (max-width: 680px) {
                .cad-mdt-new-call-backdrop { padding: 8px; }
                .cad-new-call-dialog { max-height: calc(100dvh - 16px); }
                .cad-new-call-modal .cad-call-address-grid,
                .cad-new-call-modal .cad-call-detail-grid,
                .cad-new-call-modal .cad-call-status-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
            }
        </style>
    </div>
    <script>
        function cadNewCallModal() {
            return {
                open: false, dirty: false, submitting: false, errorMessage: '', errors: [],
                createdUrl: '', successMessage: '', trigger: null,
                showModal() {
                    this.trigger = document.activeElement;
                    this.errorMessage = ''; this.errors = []; this.dirty = false;
                    this.open = true;
                    this.$nextTick(() => {
                        const input = this.$refs.dialog?.querySelector('input[name="location"]');
                        if (input) input.focus();
                    });
                },
                closeModal() {
                    if (!this.open || this.submitting) return;
                    if (this.dirty && !window.confirm('Discard your unsaved call details?')) return;
                    this.open = false;
                    this.dirty = false;
                    const form = this.$refs.dialog?.querySelector('form');
                    if (form) form.reset();
                    if (window.Livewire && typeof window.Livewire.emit === 'function') {
                        window.Livewire.emit('resetCallComposer');
                    }
                    this.$nextTick(() => this.trigger?.focus());
                },
                trapFocus(event) {
                    const elements = [...this.$refs.dialog.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]')]
                        .filter(el => el.getClientRects().length && el.offsetParent !== null);
                    if (!elements.length) { event.preventDefault(); return; }
                    const first = elements[0], last = elements[elements.length-1];
                    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                    else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
                },
                async refreshCallLists() {
                    // Both MDT and dispatcher screens poll already. Trigger an
                    // immediate refresh without navigating away or replaying sound.
                    if (window.Livewire && typeof window.Livewire.emit === 'function') {
                        window.Livewire.emit('updated-page');
                        window.Livewire.emit('incidentUpdated');
                    }
                },
                async submitCall(event) {
                    const form = event.target;
                    if (this.submitting || !form.matches('form')) return;
                    this.submitting = true; this.errorMessage = ''; this.errors = [];
                    const button = form.querySelector('button[type="submit"]');
                    if (button) { button.disabled = true; button.textContent = 'Creating…'; }
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST', credentials: 'same-origin',
                            headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' },
                            body: new FormData(form)
                        });
                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            this.errors = Object.values(result.errors || {}).flat();
                            throw new Error(result.message || 'Unable to create the call. Please check the fields.');
                        }
                        this.dirty = false; this.open = false;
                        form.reset();
                        if (window.Livewire && typeof window.Livewire.emit === 'function') {
                            window.Livewire.emit('resetCallComposer');
                        }
                        this.createdUrl = result.url || '';
                        this.successMessage = result.message || 'Call created successfully.';
                        // Initial intake ends here; dispatch continues on the
                        // full incident workspace where units/people can be added.
                        if (result.url) {
                            window.location.assign(result.url);
                            return;
                        }
                        this.$nextTick(() => this.trigger?.focus());
                        try { await this.refreshCallLists(); } catch (_) { /* Success link remains available. */ }
                        if (window.Livewire && typeof window.Livewire.emit === 'function') {
                            window.Livewire.emit('incidentUpdated');
                        }
                    } catch (error) {
                        this.errorMessage = error.message || 'Could not create the call.';
                    } finally {
                        this.submitting = false;
                        if (button) { button.disabled = false; button.textContent = 'Create & open call'; }
                    }
                }
            };
        }
    </script>

