@extends('layouts.cad_simple')

@section('content')
    <section class="min-h-screen flex flex-col justify-center items-center" x-data="{ modalOpen: true }">        @if (empty($available_departments))
            {{-- Do not render the identifier form at all when access is denied.
                 A native select can appear above modal backdrops in some browsers. --}}
            <div class="fixed inset-0 z-50 flex min-h-screen items-center justify-center bg-black px-4 py-5">
                <div class="w-full max-w-[570px] rounded-[20px] bg-gray-400 px-6 py-10 md:px-[50px]">
                    <h3 class="pb-2 text-center text-xl font-bold sm:text-2xl">System Use Notification</h3>
                    <p class="mb-5 leading-relaxed">You do not have access to this system. Contact your supervisor to gain access.</p>
                    <a class="delete-button-md" href="{{ route('portal.dashboard') }}">Exit</a>
                </div>
            </div>
        @else
            <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/90 px-4 py-5"
                x-show="modalOpen" x-transition>
                <div class="w-full max-w-[570px] rounded-[20px] bg-gray-400 px-6 py-10 md:px-[50px]">
                    <h3 class="pb-2 text-center text-xl font-bold sm:text-2xl">System Use Notification</h3>
                    <p class="mb-5 leading-relaxed text-justify">
                        Users of this system are advised they are accessing a restricted information system governed by agency policies and applicable laws. Usage may be monitored and audited.
                    </p>
                    <p class="mb-5 leading-relaxed">Unauthorized use is prohibited. You must use a desktop computer.</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" @click="modalOpen = false" class="secondary-button-md">Accept and Continue</button>
                        <a class="delete-button-md" href="{{ route('portal.dashboard') }}">Decline and Exit</a>
                    </div>
                </div>
            </div>

        {{-- Keep ALL identifier controls, including native selects and <details>,
             hidden until the usage notice has been accepted. Inline display:none
             avoids a flash of controls before Alpine initialises. --}}
        <div class="w-full max-w-3xl px-4 py-10 space-y-5"
            x-show="!modalOpen" style="display: none;">
            @if ($errors->any())
                <div role="alert" class="mb-4 rounded-lg border border-red-500 bg-red-950/30 p-4 text-red-200">
                    <p class="font-semibold mb-2">Please review the identifier details:</p>
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            @if ($identities->isEmpty())
                {{-- First-time users go directly to creation, without an empty selector. --}}
            <div class="card">
                <h1 class="text-2xl font-semibold">Create an MDT identifier</h1>
                <p class="mt-2 mb-4 text-sm text-gray-300">Choose your department, callsign and rank. Linking a civilian is optional.</p>
                    @include('cad.partials.identity-form', ['editing' => false])
            </div>
            @else
                <div class="card">
                    <h1 class="text-2xl font-semibold">Choose your MDT identifier</h1>
                    <p class="mt-2 text-sm text-gray-300">Select an existing identifier to access the CAD.</p>
                    <form action="{{ route('cad.add_unit', [], false) }}" class="mt-5 space-y-3" method="POST">
                        @csrf
                        <label class="block text-sm font-semibold" for="mdt_identity_id">Your identifiers</label>
                        <select class="text-input w-full" name="mdt_identity_id" id="mdt_identity_id" required>
                            @foreach ($identities as $identity)
                                <option value="{{ $identity->id }}">
                                    {{ $identity->callsign }} — {{ $identity->rank }} {{ $identity->display_name }} ({{ $identity->userDepartment->department->name }})
                                </option>
                            @endforeach
                        </select>
                        <div class="flex flex-wrap gap-3 items-center">
                            <button type="submit" class="new-button-md">Access CAD</button>
                            <a href="{{ route('cad.identities.edit', ['mdt_identity_id' => $identities->first()->id], false) }}"
                               id="edit-current-identifier"
                               data-edit-base="{{ route('cad.identities.edit', [], false) }}"
                               class="rounded-lg border border-gray-500 px-4 py-3 text-sm text-white hover:bg-gray-700">
                                Edit Current Identifier
                            </a>
                        </div>
                    </form>

                    {{-- Native details works without JavaScript and keeps the creation form hidden until needed. --}}
                    <details class="mt-6 border-t border-gray-600 pt-4" @if ($errors->any()) open @endif>
                        <summary class="inline-block cursor-pointer rounded-lg border border-gray-500 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                            + Create another identifier
                        </summary>
                        <div class="mt-5 border-t border-gray-700 pt-5">
                            <h2 class="text-xl font-semibold mb-2">New MDT identifier</h2>
                            <p class="text-sm text-gray-300 mb-4">Create another identifier for an authorised department. A civilian is not required.</p>
                    @include('cad.partials.identity-form', ['editing' => false])
                        </div>
                    </details>
                </div>
            @endif
        </div>

        @endif

@include('cad.partials.identity-form-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('mdt_identity_id');
    const edit = document.getElementById('edit-current-identifier');
    if (!selector || !edit) return;
    function updateEditLink() {
        edit.href = edit.dataset.editBase + '?mdt_identity_id=' + encodeURIComponent(selector.value);
    }
    selector.addEventListener('change', updateEditLink);
    updateEditLink();
});
</script>

    </section>
@endsection
