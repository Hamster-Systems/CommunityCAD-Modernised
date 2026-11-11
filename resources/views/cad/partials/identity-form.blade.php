@php
    $editing = (bool) ($editing ?? false);
    $departmentSubdivisions = collect($available_departments)->mapWithKeys(fn ($membership) =>
        [(string) $membership->id => ($membership->department?->mdt_subdivisions ?? [])])->all();
    $subdivisions = collect($departmentSubdivisions)->flatMap(fn ($values) => $values)->unique()->values()->all();
    $selectedSubdivisions = old('subdivisions', $editing ? ($identity->subdivisions ?? ($identity->subdivision ? [$identity->subdivision] : [])) : []);
    $selectedCivilian = old('civilian_id', $editing ? $identity->civilian_id : null);
    $manualName = $editing && $identity->civilian
        ? trim($identity->civilian->first_name . ' ' . $identity->civilian->last_name)
        : ($editing ? $identity->display_name : '');
@endphp
<form action="{{ $editing ? route('cad.identities.update', $identity, false) : route('cad.identities.store', [], false) }}"
      method="POST" class="space-y-4" data-identifier-form
      data-department-subdivisions='@json($departmentSubdivisions)'>
    @csrf
    @if ($editing) @method('PUT') @endif
    <div>
        <label for="identity_department" class="block text-sm mb-1">Department</label>
        <select id="identity_department" name="user_department_id" class="text-input w-full" required>
            @foreach ($available_departments as $membership)
                <option value="{{ $membership->id }}" @selected((string) old('user_department_id', $editing ? $identity->user_department_id : '') === (string) $membership->id)>{{ $membership->department->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="identity_name" class="block text-sm mb-1">First Last Name</label>
        <input id="identity_name" name="display_name" maxlength="100"
               value="{{ old('display_name', $manualName) }}" class="text-input w-full"
               placeholder="John Smith" autocomplete="name">
        <p class="mt-1 text-xs text-gray-400" data-name-hint>
            Enter a first and last name, or link a civilian to use their name automatically.
        </p>
    </div>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="identity_callsign" class="block text-sm mb-1">Callsign</label>
            <input id="identity_callsign" name="callsign" maxlength="32" required
                   value="{{ old('callsign', $editing ? $identity->callsign : '') }}"
                   class="text-input w-full" placeholder="2-L-12">
        </div>
        <div>
            <label for="identity_rank" class="block text-sm mb-1">Rank</label>
            <input id="identity_rank" name="rank" maxlength="80" required
                   value="{{ old('rank', $editing ? $identity->rank : '') }}"
                   class="text-input w-full" placeholder="Officer">
        </div>
    </div>
    <fieldset>
        <legend class="block text-sm mb-2">Subdivisions (optional)</legend>
        <details class="relative w-full" data-subdivision-dropdown>
            <summary class="flex cursor-pointer select-none items-center justify-between gap-3 rounded-md border border-gray-400 bg-gray-200 px-3 py-2 text-black focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400"
                     aria-label="Choose one or more subdivisions">
                <span data-subdivision-summary>{{ count((array) $selectedSubdivisions) ? implode(', ', (array) $selectedSubdivisions) : 'None / General' }}</span>
                <span aria-hidden="true" class="text-xs">&#9662;</span>
            </summary>
            <div class="absolute left-0 right-0 z-30 mt-1 max-h-64 overflow-y-auto rounded-md border border-gray-400 bg-gray-100 p-2 text-black shadow-xl">
                @foreach ($subdivisions as $subdivision)
                    <label class="flex cursor-pointer items-center gap-3 rounded px-3 py-2 hover:bg-gray-300"
                        data-subdivision-option="{{ $subdivision }}">
                        <input type="checkbox" name="subdivisions[]" value="{{ $subdivision }}"
                               @checked(in_array($subdivision, (array) $selectedSubdivisions, true))
                               class="h-4 w-4">
                        <span>{{ $subdivision }}</span>
                    </label>
                @endforeach
            </div>
        </details>
        <p class="mt-1 text-xs text-gray-400">Options belong to the selected department. You can select multiple or leave them all unchecked for General.</p>
    </fieldset>
    <div>
        <label for="identity_civilian" class="block text-sm mb-1">Link civilian (optional)</label>
        <select id="identity_civilian" name="civilian_id" class="text-input w-full">
            <option value="">None — MDT identity only</option>
            @foreach ($civilians as $civilian)
                <option value="{{ $civilian->id }}"
                        @selected((string) $selectedCivilian === (string) $civilian->id)
                        data-full-name="{{ $civilian->first_name }} {{ $civilian->last_name }}"
                        data-short-name="{{ mb_substr($civilian->first_name, 0, 1) }}. {{ $civilian->last_name }}">
                    {{ $civilian->first_name }} {{ $civilian->last_name }}
                </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="new-button-md">{{ $editing ? 'Save identifier' : 'Create identifier' }}</button>
</form>
