@extends('layouts.cad')

@section('content')
    <div>
        @include('inc.cad.mdt-nav')
    </div>

    <div class="container p-4 mx-auto bg-[#2e3547] text-white cursor-default rounded-2xl mt-5">
        <header>
            <div class="flex justify-between">
                <h1 class="text-2xl font-semibold">Welcome
                    {{ auth()->user()->active_unit->officer_name }}
                </h1>
            </div>
        </header>

        <main class="my-5"></main>

        <main class="my-5">
            <div class="flex p-4">
                <div class="w-1/2">
                    <p class="text-lg font-semibold">Message Center</p>
                    <ul class="ml-8 list-disc">
                        <li>Alerts: <span class="text-sm text-gray-300">Notifications coming later</span></li>
                        <li>Messages: <a class="text-sm underline" href="{{ route('cad.message_center') }}">Open message centre</a></li>
                        
                        
                    </ul>

                    <p class="mt-3 text-lg font-semibold">System</p>
                    <ul class="ml-8 list-disc">
                        <li class="">Username: <span
                                class="text-sm !lowercase">{{ str_replace(' ', '_', strtolower(auth()->user()->active_unit->officer_name)) }}</span>
                        </li>
                        <li>Rank: <span class="text-sm">{{ auth()->user()->active_unit->mdtIdentity?->rank ?? auth()->user()->active_unit->user_department->rank }}</span></li>
                        
                    </ul>
                </div>
                <div class="w-1/2">
                    <p class="text-lg font-semibold">CAD</p>
                    <ul class="ml-8 list-disc">
                        <li>Calls: <a class="text-sm underline" href="{{ route('cad.call.index') }}">{{ $call_count }}
                                active</a>
                        </li>
                        <li>Unit: <a class="text-sm underline"
                                href="{{ route('cad.mdt') }}">{{ auth()->user()->active_unit->mdtIdentity?->callsign ?? auth()->user()->active_unit->user_department->badge_number }} -
                                {{ auth()->user()->active_unit->status }}</a></li>
                        <li>Subdivision: <span class="text-sm">{{ implode(', ', auth()->user()->active_unit->mdtIdentity?->subdivisions ?? []) ?: 'General' }}</span></li>
                        <li>My Active Calls:
                            @forelse ($myCalls as $call)
                                <a class="text-sm underline" href="{{ route('cad.call.show', $call->id) }}">
                                    #{{ $call->id }} — {{ $call->nature_info['name'] ?? $call->nature }}
                                </a>@if (!$loop->last), @endif
                            @empty
                                <span class="text-sm">None assigned</span>
                            @endforelse
                        </li>
                    </ul>

                    <p class="mt-3 text-lg font-semibold">Reports</p>
                    <ul class="ml-8 list-disc">
                        <li><a class="text-sm underline" href="{{ route('cad.report.create') }}">Create a report</a></li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
@endsection
