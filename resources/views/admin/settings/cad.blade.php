@extends('layouts.admin_settings')

@section('content')
    <header class="w-full my-3">
        <h1 class="text-2xl font-bold text-white">CAD Settings</h1>
        <p class="text-sm"><a class="flex text-sm items-center text-blue-600 underline"
                href="https://communitycad.app/docs/settings-page">Learn More
                <svg class="w-4 h-4 ml-2" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a></p>
    </header>
    <div class="card">
        <form action="{{ route('admin.settings.update') }}" id="mdeditor" method="POST">
            @csrf
            <div class="pill p-3">
                <p class="text-lg font-semibold">On duty Button</p>
                <p>Text for on duty button.</p>
                <input class="text-input" name="on_duty_button_text" type="text"
                    value="{{ old('on_duty_button_text', get_setting('on_duty_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">Available Button</p>
                <p>Text for available button.</p>
                <input class="text-input" name="available_button_text" type="text"
                    value="{{ old('available_button_text', get_setting('available_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">Enroute Button</p>
                <p>Text for enroute button.</p>
                <input class="text-input" name="enroute_button_text" type="text"
                    value="{{ old('enroute_button_text', get_setting('enroute_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">On Scene Button</p>
                <p>Text for on scene button.</p>
                <input class="text-input" name="on_scene_button_text" type="text"
                    value="{{ old('on_scene_button_text', get_setting('on_scene_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">Break Button</p>
                <p>Text for break button.</p>
                <input class="text-input" name="break_button_text" type="text"
                    value="{{ old('break_button_text', get_setting('break_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">Off duty Button</p>
                <p>Text for off duty button.</p>
                <input class="text-input" name="off_duty_button_text" type="text"
                    value="{{ old('off_duty_button_text', get_setting('off_duty_button_text')) }}">
            </div>

            <div class="pill p-3">
                <p class="text-lg font-semibold">Dispatch 911 Call (Busy) Button</p>
                <p>Text for dispatch 911 call busy button.</p>
                <input class="text-input" name="911_call_busy_button_text" type="text"
                    value="{{ old('911_call_busy_button_text', get_setting('911_call_busy_button_text')) }}">
            </div>

            <div class="flex justify-end">
                <button class="inline-block secondary-button-md">Save</button>
            </div>
        </form>
    </div>
    <section class="card mt-6 rounded-lg border border-slate-600 bg-[#152234] p-5 text-white">
        <h2 class="text-xl font-bold">CAD Address Directory</h2>
        <p class="mt-2 text-sm text-slate-300">Import streets and locations for the 911 Call address suggestions. These are locally stored entries, not live geocoding. Dispatch can still type an address manually.</p>
        <p class="mt-2 text-sm text-sky-300">CSV columns: <code>street,city,postal</code> (also accepts <code>address</code> or <code>location</code> instead of street). City and postal are optional.</p>
        @if (session('address_import_success'))
            <p class="mt-3 rounded border border-emerald-600 bg-emerald-950 p-3 text-sm" role="status">{{ session('address_import_success') }}</p>
        @endif
        @error('address_file')
            <p class="mt-3 text-sm text-red-300" role="alert">{{ $message }}</p>
        @enderror
        @error('import_mode')
            <p class="mt-3 text-sm text-red-300" role="alert">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-sm text-slate-300">Stored addresses: <strong>{{ \App\Models\Cad\AddressDirectory::count() }}</strong></p>
        <form method="POST" action="{{ route('admin.settings.cad.addresses.import') }}" enctype="multipart/form-data"
            class="mt-4 flex flex-col gap-3">
            @csrf
            <label class="text-sm font-semibold" for="cad-address-file">Address CSV file (up to 2 MB / 10,000 entries)</label>
            <input id="cad-address-file" type="file" name="address_file" required accept=".csv,.txt,text/csv,text/plain"
                class="block w-full rounded border border-slate-500 bg-[#24344a] p-2 text-sm">
            <label class="text-sm font-semibold" for="cad-address-import-mode">Import behaviour</label>
            <select id="cad-address-import-mode" name="import_mode" class="rounded border border-slate-500 bg-[#24344a] p-2 text-sm">
                <option value="append">Add or update imported entries</option>
                <option value="replace">Replace the entire directory with this file</option>
            </select>
            <p class="text-xs text-amber-200">Replace removes previously imported entries. Export or back up the existing directory before replacing it.</p>
            <button type="submit" class="self-start rounded bg-sky-700 px-4 py-2 text-sm font-semibold hover:bg-sky-600">Import addresses</button>
        </form>
    </section>
@endsection
