@extends('layouts.cad_simple')

@section('content')
    <section class="min-h-screen flex flex-col justify-center items-center" x-data="{ modalOpen: true }">
        <div class="fixed top-0 left-0 flex items-center justify-center w-full h-full min-h-screen px-4 py-5 bg-black bg-opacity-90"
            x-show="modalOpen" x-transition>
            <div class="w-full max-w-[570px] rounded-[20px] bg-gray-400 py-10 px-6 md:py-[40px] md:px-[50px]">
                <h3 class="pb-2 text-xl font-bold text-center sm:text-2xl">
                    System Use Notification
                </h3>
                <p class="mb-5 leading-relaxed text-justify">
                    Users of this system are advised they are accessing a restricted information system that is governed by
                    applicable agency policies, local laws, state laws, federal laws, and other regulations. Usage may be
                    monitored, recorded, and subject to audit.
                </p>
                <p class="mb-5 leading-relaxed text-justify">
                    Unauthorized use of this system is prohibited. Unauthorized users may be subject to criminal and/or
                    civil penalties. Continuing to use this system indicates the user's consent to the monitoring and
                    recording of all actions within the system.
                </p>
                <p class="mb-5 leading-relaxed">
                    Must be on desktop computer.
                </p>
                <div class="flex flex-wrap -mx-3 text-center">
                    <div class="w-1/2 px-3">
                        <a @click="modalOpen = false" class="secondary-button-md" href="#">
                            Accept and Continue
                        </a>
                    </div>
                    <div class="w-1/2 px-3">
                        <a class="delete-button-md" href="{{ route('portal.dashboard') }}">
                            Decline and Exit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (empty($available_departments))
            <div class="fixed top-0 left-0 flex items-center justify-center w-full h-full min-h-screen px-4 py-5 bg-black bg-opacity-90"
                x-transition>
                <div class="w-full max-w-[570px] rounded-[20px] bg-gray-400 py-10 px-6 md:py-[40px] md:px-[50px]">
                    <h3 class="pb-2 text-xl font-bold text-center sm:text-2xl">
                        System Use Notification
                    </h3>
                    <p class="mb-5 leading-relaxed text-justify">
                        You do not have access to this system. Contact your supervisor to gain access.
                    </p>
                    <div class="flex flex-wrap -mx-3 text-center">
                        <div class="w-1/2 px-3">
                            <a class="delete-button-md" href="{{ route('portal.dashboard') }}">
                                Exit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="w-full max-w-3xl px-4 py-10 space-y-5">
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
