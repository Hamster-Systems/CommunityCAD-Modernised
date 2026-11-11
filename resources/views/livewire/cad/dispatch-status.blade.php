<div class="flex" wire:poll.5s>
    {{-- Dispatcher presence status remains calculated in Livewire, but
         the visible Online/Offline badge has been replaced with New Call. --}}


    @if (auth()->user()->active_unit->status != 'OFFDTY' && auth()->user()->active_unit->department_type != 2)
        @if (auth()->user()->active_unit->is_panic)
            <form method="POST" action="{{ route('cad.stop_panic') }}" class="flex">@csrf
            <button type="submit"  class="flex items-center py-1 px-5 text-black font-bold animate-pulse bg-red-600 hover:bg-red-700 h-10">
                <svg class="w-6 h-6 mr-2" fill="#000000" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <path
                            d="M13.5 27c-.276.004-.504.224-.5.5v.5c0 1.1.9 2 2 2s2-.9 2-2v-.5c0-.333-.25-.5-.5-.5s-.5.162-.5.5v.5c0 .563-.437 1-1 1s-1-.437-1-1v-.5c.004-.282-.218-.504-.5-.5zM15 0c-1.65 0-3 1.35-3 3v.5c0 .65 1 .66 1 0V3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2v.5c0 .654 1 .66 1 0V3c0-1.65-1.35-3-3-3zm7.5 3C25.534 3 28 5.468 28 8.5c0 .665-1 .665-1 0C27 6.01 24.994 4 22.5 4c-.667 0-.663-1 0-1zm-15 0C4.468 3 2 5.468 2 8.5c0 .665 1 .665 1 0C3 6.01 5.01 4 7.5 4c.668 0 .665-1 0-1zM15 5c-4.32 0-6.688 1.81-7.838 4.102C6.012 11.394 6 14.096 6 16c0 2 0 5.817-2.88 9.174A.5.5 0 0 0 3.5 26h23a.5.5 0 0 0 .38-.826C24 21.817 24 18 24 16c0-1.904-.013-4.606-1.162-6.898C21.688 6.81 19.32 5 15 5zm0 1c4.054 0 5.937 1.543 6.943 3.55C22.95 11.56 23 14.108 23 16c0 1.852.107 5.567 2.586 9H4.414C6.894 21.567 7 17.852 7 16c0-1.893.05-4.44 1.057-6.45C9.063 7.544 10.947 6 15 6z">
                        </path>
                    </g>
                </svg>
                <p>Stop <br> Panic</p>
            </button>
        </form>
        @else
            <form method="POST" action="{{ route('cad.panic') }}" class="flex">@csrf
            <button type="submit"  class="flex items-center py-1 px-5 text-black bg-red-600 hover:bg-red-700 h-10">
                <svg class="w-5 h-5 mr-2" fill="#000000" version="1.1" viewBox="-1 0 30 30"
                    xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns:xlink="http://www.w3.org/1999/xlink"
                    xmlns="http://www.w3.org/2000/svg">
                    <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                        <title>alert</title>
                        <desc>Created with Sketch Beta.</desc>
                        <defs> </defs>
                        <g fill-rule="evenodd" fill="none" id="Page-1" sketch:type="MSPage" stroke-width="1"
                            stroke="none">
                            <g fill="#000000" id="Icon-Set" sketch:type="MSLayerGroup"
                                transform="translate(-362.000000, -880.000000)">
                                <path
                                    d="M365,904 L368,898 L368,890 C368,885.582 371.582,882 376,882 C380.418,882 384,885.582 384,890 L384,898 L387,904 L365,904 L365,904 Z M376,908 C374.695,908 373.597,907.163 373.184,906 L378.816,906 C378.403,907.163 377.305,908 376,908 L376,908 Z M386,898 L386,890 C386,884.478 381.522,880 376,880 C370.478,880 366,884.478 366,890 L366,898 L362,906 L371.101,906 C371.564,908.282 373.581,910 376,910 C378.419,910 380.436,908.282 380.899,906 L390,906 L386,898 L386,898 Z"
                                    id="alert" sketch:type="MSShapeGroup"> </path>
                            </g>
                        </g>
                    </g>
                </svg>
                <p>Panic</p>
            </button>
        </form>
        @endif
    @endif

    @if (auth()->user()->active_unit->status != 'OFFDTY' && auth()->user()->active_unit->department_type == 2)
        <form method="POST" action="{{ route('cad.clear_panic') }}" class="flex">@csrf
            <button type="submit"  class="flex items-center py-1 px-5 text-black font-bold bg-red-600 hover:bg-red-700 h-10">
            <svg class="w-6 h-6 mr-2" fill="#000000" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                    <path
                        d="M13.5 27c-.276.004-.504.224-.5.5v.5c0 1.1.9 2 2 2s2-.9 2-2v-.5c0-.333-.25-.5-.5-.5s-.5.162-.5.5v.5c0 .563-.437 1-1 1s-1-.437-1-1v-.5c.004-.282-.218-.504-.5-.5zM15 0c-1.65 0-3 1.35-3 3v.5c0 .65 1 .66 1 0V3c0-1.11.89-2 2-2 1.11 0 2 .89 2 2v.5c0 .654 1 .66 1 0V3c0-1.65-1.35-3-3-3zm7.5 3C25.534 3 28 5.468 28 8.5c0 .665-1 .665-1 0C27 6.01 24.994 4 22.5 4c-.667 0-.663-1 0-1zm-15 0C4.468 3 2 5.468 2 8.5c0 .665 1 .665 1 0C3 6.01 5.01 4 7.5 4c.668 0 .665-1 0-1zM15 5c-4.32 0-6.688 1.81-7.838 4.102C6.012 11.394 6 14.096 6 16c0 2 0 5.817-2.88 9.174A.5.5 0 0 0 3.5 26h23a.5.5 0 0 0 .38-.826C24 21.817 24 18 24 16c0-1.904-.013-4.606-1.162-6.898C21.688 6.81 19.32 5 15 5zm0 1c4.054 0 5.937 1.543 6.943 3.55C22.95 11.56 23 14.108 23 16c0 1.852.107 5.567 2.586 9H4.414C6.894 21.567 7 17.852 7 16c0-1.893.05-4.44 1.057-6.45C9.063 7.544 10.947 6 15 6z">
                    </path>
                </g>
            </svg>
            <p>Clear <br> Panics</p>
        </button>
        </form>
    @endif
</div>
