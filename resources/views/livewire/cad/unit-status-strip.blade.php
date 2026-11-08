<div wire:poll.4s class="max-w-7xl mx-auto border-t border-slate-500/50 px-2 py-2 flex flex-wrap items-center gap-2 text-white normal-case">
    @php
        $options = [
            'AVL' => 'Available',
            'BUSY' => 'Busy',
            'ENRUTE' => 'En Route',
            'ONSCN' => 'On Scene',
            'TRANS' => 'Transporting',
            'BRK' => 'Break',
        ];
    @endphp
    <span class="mr-1 text-xs font-bold uppercase tracking-wide text-slate-300">
        {{ $unit?->unit_callsign ?? 'UNIT' }} · STATUS
    </span>
    @foreach ($options as $code => $label)
        @php
            $requiresCall = in_array($code, ['ENRUTE', 'ONSCN', 'TRANS'], true);
            $blocked = !$unit || $unit->status === 'OFFDTY' || $unit->is_panic || ($requiresCall && !$hasOpenCall);
            $selected = $unit && $unit->status === $code;
        @endphp
        <button type="button" wire:click="changeStatus('{{ $code }}')"
            wire:loading.attr="disabled" wire:target="changeStatus"
            @disabled($blocked) aria-pressed="{{ $selected ? 'true' : 'false' }}"
            title="{{ $blocked && $requiresCall ? 'Attach to an open call first' : $label }}"
            class="rounded-md border px-3 py-2 text-xs font-semibold transition
                {{ $selected ? 'border-sky-400 bg-sky-800 text-white ring-1 ring-sky-400' : 'border-slate-500 bg-slate-800 hover:bg-slate-700' }}
                disabled:cursor-not-allowed disabled:opacity-40">
            {{ $label }}
        </button>
    @endforeach
    <span class="ml-auto text-xs text-slate-300">{{ $hasOpenCall ? 'Assigned to a call' : 'No active assignment' }}</span>
</div>
