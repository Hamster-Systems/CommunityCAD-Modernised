<div wire:poll.5s>
    <div class="max-w-7xl mx-auto">
        @include('inc.cad.call_table')
    </div>

    <div class="mt-12 max-w-7xl mx-auto">
        <div class="flex justify-around mb-3 space-x-3">
            @if (auth()->user()->active_unit)

                @if (auth()->user()->active_unit->status == 'OFFDTY')
                    <a class="new-button-md" href="#" wire:click="on_duty({{ auth()->user()->active_unit->id }})">
                        {{ strtoupper(get_setting('on_duty_button_text', 'ON DUTY')) }}
                    </a>
                    <a class="delete-button-md" href="{{ route('cad.offduty.create') }}">
                        {{ strtoupper(get_setting('off_duty_button_text', 'OFF DUTY')) }}</a>
                @else
                    <span class="text-xs text-slate-400">Unit status is now managed in the top toolbar.</span>
                    <a class="delete-button-md" href="{{ route('cad.offduty.create') }}">
                        {{ strtoupper(get_setting('off_duty_button_text', 'OFF DUTY')) }}
                    </a>
                @endif

            @endif

        </div>
        @include('inc.cad.mdt.active_units')
    </div>
</div>
