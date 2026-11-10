<div class="">
    <table class="w-full mt-4 uppercase border border-collapse table-auto border-slate-400">
        <tr class="text-lg font-bold text-white">
            <th class="p-1 border border-slate-400">Call #</th>
            <th class="p-1 relative border border-slate-400" x-data="{ open: false }">
                <div class="flex justify-between items-center">
                    Type
                    <a @click="open = !open" class="cursor-pointer">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke-width="1.5" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
                <div @click.outside="open = false"
                    class="absolute right-0 top-8 z-50 w-32 p-3 space-y-3 text-white bg-gray-800 rounded"
                    x-show="open">
                    <div class="flex items-center">
                        <input @checked(isset($type_filters['1']))
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            id="LEO" name="type_filters[]" type="checkbox" value="1"
                            wire:model="type_filters">
                        <label class="ms-2" for="LEO">LEO</label>
                    </div>
                    <div class="flex items-center">
                        <input @checked(isset($type_filters['2']))
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            id="FD" name="type_filters[]" type="checkbox" value="2"
                            wire:model="type_filters">
                        <label class="ms-2" for="FD">FD</label>
                    </div>
                    <div class="flex items-center">
                        <input @checked(isset($type_filters['3']))
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                            id="EMS" name="type_filters[]" type="checkbox" value="3"
                            wire:model="type_filters">
                        <label class="ms-2" for="EMS">EMS</label>
                    </div>
                </div>
            </th>
            <th class="p-1 relative border border-slate-400" x-data="{ open: false }">
                <div class="flex justify-between items-center">
                    Nature
                    <span class="text-xs text-yellow-500">
                        @if ($nature_filter)
                            (({{ $nature_filter }} - {{ $call_natures[$nature_filter]['name'] }}))
                        @else
                            ((ALL CALLS))
                        @endif
                    </span>
                    <a @click="open = !open" class="underline cursor-pointer">
                        <svg class="w-5 h-5 ml-2" fill="none" stroke-width="1.5" stroke="currentColor"
                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M6 13.5V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m12-3V3.75m0 9.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 3.75V16.5m-6-9V3.75m0 3.75a1.5 1.5 0 0 1 0 3m0-3a1.5 1.5 0 0 0 0 3m0 9.75V10.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
                <div @click.outside="open = false"
                    class="absolute right-0 top-8 z-50 w-80 p-3 space-y-3 text-white bg-gray-800 rounded"
                    x-show="open">
                    <select @change="open = false"class="select-input" id="" name=""
                        wire:model="nature_filter">
                        <option value="">ALL</option>
                        @foreach ($call_natures as $call_nature => $name)
                            <option value="{{ $call_nature }}">{{ $call_nature }} -
                                {{ $name['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </th>
            <th class="p-1 border border-slate-400">Location</th>
            <th class="p-1 border border-slate-400">City</th>
            <th class="p-1 border border-slate-400">Pri</th>
            <th class="p-1 border border-slate-400">Status</th>
            <th class="p-1 border border-slate-400">Time</th>
            <th class="p-1 border border-slate-400">Units</th>
        </tr>
        @foreach ($calls as $call)
            <tr wire:key="mdt-call-row-{{ $call->id }}"
                class="{{ $call->status === 'PANIC' ? 'cad-panic-row' : $call->status_info['color'] }} cursor-pointer hover:bg-slate-800/80"
                style="--cad-panic-offset: -{{ ((int) $call->id % 11) * 133 }}ms"
                x-data="{ contextOpen: false, contextX: 0, contextY: 0,
                    openContext(event) {
                        this.contextX = Math.max(8, Math.min(event.clientX, window.innerWidth - 258));
                        this.contextY = Math.max(8, Math.min(event.clientY, window.innerHeight - 190));
                        this.contextOpen = true;
                    }
                }"
                data-call-url="{{ route('cad.call.show', $call->id) }}"
                @contextmenu.prevent.stop="openContext($event)"
                @click="if (!$event.target.closest('a,button,input,select,[role=menu]')) window.location.href = $el.dataset.callUrl"
                @keydown.escape.window="contextOpen = false"
                title="Left-click to view details; right-click for call actions">
                <td class="p-1 border border-slate-400">
                    <div class="flex items-center justify-between gap-1">
                        <a class="hover:underline" href="{{ route('cad.call.show', $call->id) }}">{{ $call->id }}</a>
                        <button type="button" class="rounded-md border border-slate-500 bg-slate-800 px-2 py-1 text-sm font-bold text-white hover:bg-slate-600"
                            aria-label="Actions for call {{ $call->id }}" aria-haspopup="menu"
                            :aria-expanded="contextOpen.toString()"
                            @click.stop="openContext($event); contextX = Math.max(8, Math.min($event.clientX, window.innerWidth - 218)); contextY = Math.max(8, Math.min($event.clientY, window.innerHeight - 156))">
                            ⋯
                        </button>
                    </div>
                    {{-- Keep the menu within the Livewire component so wire:click actions work. --}}
                    <div x-show="contextOpen" x-cloak @click.stop>
                        <div class="fixed inset-0 z-[80]" @click.stop="contextOpen = false"
                             @contextmenu.prevent.stop="contextOpen = false"></div>
                        <div class="fixed z-[90] w-[244px] rounded-xl border border-slate-500 bg-[#162235] p-2 text-sm normal-case text-white shadow-2xl"
                            role="menu" aria-label="Call {{ $call->id }} actions"
                            :style="`left: ${contextX}px; top: ${contextY}px;`">
                            <div class="mb-2 border-b border-slate-600 px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-300">Call #{{ $call->id }} · P{{ $call->priority }}</div>
                            <a role="menuitem" href="{{ route('cad.call.show', $call->id) }}"
                                class="block rounded-lg px-3 py-3 hover:bg-slate-700 focus:bg-slate-700">View call details</a>
                            @if (auth()->user()->active_unit->department_type != 2)
                                @if ($call->attached_units->contains('id', auth()->user()->active_unit->id))
                                    <button type="button" role="menuitem" class="block w-full rounded-lg px-3 py-3 text-left hover:bg-slate-700 focus:bg-slate-700"
                                        wire:click="remove_unit_from_call({{ auth()->user()->active_unit->id }}, {{ $call->id }})"
                                        @click.stop="contextOpen = false">Detach my unit</button>
                                @else
                                    <button type="button" role="menuitem" class="block w-full rounded-lg px-3 py-3 text-left hover:bg-slate-700 focus:bg-slate-700"
                                        wire:click="add_unit_to_call({{ auth()->user()->active_unit->id }}, {{ $call->id }})"
                                        @click.stop="contextOpen = false">Attach my unit</button>
                                @endif
                            @endif
                        </div>
                    </div>
                </td>
                <td class="p-1 border border-slate-400">{{ $call->type_name }}</td>
                <td class="p-1 border border-slate-400">{{ $call->nature }} -
                    {{ $call->nature_info ? $call->nature_info['name'] : 'custom nature' }}
                </td>
                <td class="p-1 border border-slate-400">{{ $call->location }}</td>
                <td class="p-1 border border-slate-400">{{ $call->city }}</td>
                <td class="relative p-1 border border-slate-400" x-data="{ statusOpen: false }">
                    <div class="flex justify-between">
                        <span>{{ $call->priority }}</span>
                        <a @click="statusOpen = !statusOpen" class="underline cursor-pointer">
                            <svg class="w-6 h-6 text-white" fill="none" stroke-width="1.5" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                    <div @click.outside="statusOpen = false"
                        class="absolute right-0 z-50 w-32 p-3 space-y-3 text-white bg-gray-800 rounded"
                        x-show="statusOpen">
                        <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                            wire:click="set_call_priority({{ $call->id }}, '1')">1</a>
                        <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                            wire:click="set_call_priority({{ $call->id }}, '2')">2</a>
                        <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                            wire:click="set_call_priority({{ $call->id }}, '3')">3</a>
                        <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                            wire:click="set_call_priority({{ $call->id }}, '4')">4</a>
                        <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                            wire:click="set_call_priority({{ $call->id }}, '5')">5</a>
                    </div>
                </td>
                <td class="relative p-1 border border-slate-400" x-data="{ statusOpen: false }">
                    <div class="flex justify-between">
                        <span>{{ $call->status }} - {{ $call->status_info['name'] }}</span>
                        <a @click="statusOpen = !statusOpen" class="underline cursor-pointer">
                            <svg class="w-6 h-6 text-white" fill="none" stroke-width="1.5" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                    <div @click.outside="statusOpen = false"
                        class="absolute right-0 z-50 w-80 p-3 space-y-3 text-white bg-gray-800 rounded"
                        x-show="statusOpen">
                        @foreach ($call_statuses as $status => $props)
                            @continue($status === 'PANIC')
                            <a @click="statusOpen = false" class="block hover:bg-gray-500" href="#"
                                wire:click="set_call_status({{ $call->id }}, '{{ $status }}')">{{ $status }}
                                - {{ $props['name'] }}</a>
                        @endforeach
                    </div>
                </td>
                <td class="p-1 border border-slate-400">{{ $call->time }}m</td>
                <td class="relative p-1 border border-slate-400" x-data="{ unitsOpen: false }">
                    <div class="flex justify-between">
                        <span>
                            @foreach ($call->attached_units as $unit)
                                {{ $unit->unit_callsign }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </span>
                        @if (auth()->user()->active_unit->department_type != 2)
                            @if ($call->attached_units->contains(auth()->user()->active_unit->id))
                                <a class="cursor-pointer text-red-600"
                                    wire:click="remove_unit_from_call({{ auth()->user()->active_unit->id }}, {{ $call->id }})">
                                    <svg class="w-6 h-6" fill="none" stroke-width="1.5" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>

                                </a>
                            @else
                                <a class="cursor-pointer text-green-600"
                                    wire:click="add_unit_to_call({{ auth()->user()->active_unit->id }}, {{ $call->id }})">
                                    <svg class="w-6 h-6" fill="none" stroke-width="1.5" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            @endif
                        @endif
                    </div>
                    <div @click.outside="unitsOpen = false"
                        class="absolute right-0 z-50 w-32 p-3 space-y-3 text-white bg-gray-800 rounded"
                        x-show="unitsOpen">
                        {{-- @foreach ($active_units as $unit)
                                    @if (in_array($unit->badge_number, $call->nice_units))
                                        <a @click="unitsOpen = false" class="block bg-gray-500" href="#"
                                            wire:click="remove_unit_from_call({{ $unit->id }}, {{ $call->id }})">{{ $unit->badge_number }}</a>
                                    @else
                                        <a @click="unitsOpen = false" class="block hover:bg-gray-500" href="#"
                                            wire:click="add_unit_to_call({{ $unit->id }}, {{ $call->id }})">{{ $unit->badge_number }}</a>
                                    @endif
                                @endforeach --}}
                    </div>
                </td>
            </tr>
        @endforeach
    </table>
</div>
