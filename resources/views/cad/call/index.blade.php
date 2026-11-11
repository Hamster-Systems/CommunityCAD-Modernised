@extends('layouts.cad')

@section('content')
    <div class="sticky top-0 z-50">
        @include('inc.cad.mdt-nav')
    </div>

    <div class="relative max-w-7xl mx-auto" x-data="cadNewCallModal()" @keydown.escape.window="closeModal()" @cad-new-call-cancel.window="closeModal()" @cad-open-new-call.window="showModal()">
        <div :inert="open ? true : null">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-white text-xl">Recent Calls <span class="text-base">(Last 2 days)</span></h1>

        </div>
        <div x-show="createdUrl" x-cloak role="status" class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-lg border border-emerald-700 bg-emerald-950 p-4 text-emerald-100">
            <span x-text="successMessage"></span>
            <a :href="createdUrl" class="rounded border border-emerald-500 px-3 py-1.5 text-sm font-bold hover:bg-emerald-800">Open created call</a>
        </div>
        <div id="recent-call-list" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($recent_calls as $call)
                <div class="bg-gray-200 rounded-lg p-2 text-black text-sm">
                    <a href="{{ route('cad.call.show', $call->id) }}">
                        <div class="flex justify-between items-center border-b-2 border-blue-600">
                            <p class="text-lg">{{ $call->id }}</p>
                            <p class="text-sm">{{ $call->status }} - {{ $call->status_info['name'] }}</p>
                        </div>
                        <div class="flex mt-1">
                            <div class="h-20 w-20">
                                <svg class="w-20 h-20 mx-auto" fill="none" stroke-width="1.5" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p><span class="text-blue-500 text-xs">Type</span>
                                    {{ $call->type_name }}</p>
                                <p><span class="text-blue-500 text-xs">Created At</span>
                                    {{ $call->created_at->format('m/d/y H:i:s') }}</p>
                                <p><span class="text-blue-500 text-xs">Nature</span>
                                    {{ $call->nature }} - {{ $call->nature_info['name'] }}</p>
                            </div>
                        </div>
                        <div class="border-t-2 border-black flex justify-between">

                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <hr class="mt-10 mb-5">
        <h1 class="text-white text-xl">All Calls</h1>
        <div id="older-call-list" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($all_calls as $call)
                <div class="bg-gray-200 rounded-lg p-2 text-black text-sm">
                    <a href="{{ route('cad.call.show', $call->id) }}">
                        <div class="flex justify-between items-center border-b-2 border-blue-600">
                            <p class="text-lg">{{ $call->id }}</p>
                            <p class="text-sm">{{ $call->status }} - {{ $call->status_info['name'] }}</p>
                        </div>
                        <div class="flex mt-1">
                            <div class="h-20 w-20">
                                <svg class="w-20 h-20 mx-auto" fill="none" stroke-width="1.5" stroke="currentColor"
                                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p><span class="text-blue-500 text-xs">Type</span>
                                    {{ $call->type_name }}</p>
                                <p><span class="text-blue-500 text-xs">Created At</span>
                                    {{ $call->created_at->format('m/d/y H:i:s') }}</p>
                                <p><span class="text-blue-500 text-xs">Nature</span>
                                    {{ $call->nature }} - {{ $call->nature_info['name'] }}</p>
                            </div>
                        </div>
                        <div class="border-t-2 border-black flex justify-between">

                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        </div>
        <div x-show="open" x-cloak class="fixed inset-0 z-[120] flex items-center justify-center bg-black/80 p-2 sm:p-5"
            @click.self="closeModal()" aria-hidden="false">
            <section role="dialog" aria-modal="true" aria-labelledby="cad-new-call-title" aria-describedby="cad-new-call-desc"
                x-ref="dialog" @keydown.tab="trapFocus($event)" @submit.capture.prevent="submitCall($event)"
                @input.capture="dirty = true" @change.capture="dirty = true"
                class="flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-xl border border-slate-500 bg-[#162235] text-white shadow-2xl">
                <header class="flex flex-none items-start justify-between gap-3 border-b border-slate-600 bg-[#1c3046] px-4 py-3 sm:px-6">
                    <div>
                        <h2 id="cad-new-call-title" class="text-lg font-bold">New Call</h2>
                        <p id="cad-new-call-desc" class="mt-1 text-xs text-slate-300">Create a dispatch incident without leaving the call queue.</p>
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
            [x-cloak] { display:none !important; }
            .cad-new-call-modal .text-input-sm,
            .cad-new-call-modal .select-input-sm,
            .cad-new-call-modal .textarea-input-sm {
                display:block;width:100%;min-width:0;box-sizing:border-box;
                border:1px solid #64748b;border-radius:7px;background:#24364d;
                color:white;padding:10px 12px;font-size:14px;
            }
            .cad-new-call-modal textarea { min-height:90px; }
            .cad-new-call-modal .cad-modal-call-fields h1 {font-size:15px; margin:4px 0;color:#93c5fd;}
            .cad-new-call-modal .cad-modal-call-fields form > .flex {gap:12px;}
            .cad-new-call-modal .cad-modal-call-fields form > .flex > div {min-width:0;}
            .cad-new-call-modal .cad-modal-call-fields .ml-3 {margin-left:0;}
            @media (max-width:640px) {
                .cad-new-call-modal .cad-modal-call-fields form > .flex {flex-direction:column;}
                .cad-new-call-modal .cad-modal-call-fields form > .flex > div {width:100%;}
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
                    const response = await fetch('{{ route('cad.call.index') }}', {
                        credentials: 'same-origin', headers: { 'Accept': 'text/html' }
                    });
                    if (!response.ok) return;
                    const html = new DOMParser().parseFromString(await response.text(), 'text/html');
                    for (const id of ['recent-call-list', 'older-call-list']) {
                        const current = document.getElementById(id);
                        const updated = html.getElementById(id);
                        if (current && updated) current.innerHTML = updated.innerHTML;
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
                        this.$nextTick(() => this.trigger?.focus());
                        try { await this.refreshCallLists(); } catch (_) { /* Success link remains available. */ }
                        if (window.Livewire && typeof window.Livewire.emit === 'function') {
                            window.Livewire.emit('incidentUpdated');
                        }
                    } catch (error) {
                        this.errorMessage = error.message || 'Could not create the call.';
                    } finally {
                        this.submitting = false;
                        if (button) { button.disabled = false; button.textContent = 'Create call'; }
                    }
                }
            };
        }
    </script>
@endsection