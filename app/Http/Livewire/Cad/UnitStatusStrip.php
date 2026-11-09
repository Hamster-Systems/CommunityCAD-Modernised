<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Support\Cad\UnitResponseStatusService;
use Livewire\Component;

class UnitStatusStrip extends Component
{
    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function changeStatus(string $status): void
    {
        abort_unless(in_array($status, ['AVL', 'BUSY', 'ENRUTE', 'ONSCN', 'TRANS', 'BRK', 'UNAV'], true), 422);
        $unit = auth()->user()?->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) auth()->id(), 403);
        abort_if($unit->is_panic, 409, 'Clear the active panic before changing unit status.');

        $call = $unit->calls()->where('status', '!=', 'CLO')
            ->where('status', 'not like', 'CLO-%')->orderByDesc('calls.created_at')->first();

        if (in_array($status, ['ENRUTE', 'ONSCN', 'TRANS'], true)) {
            abort_unless($call, 403);
        }

        app(UnitResponseStatusService::class)->change($unit, $status, $call);

        $this->emit('incidentUpdated');
    }

    public function render()
    {
        $unit = auth()->user()->active_unit;
        $hasOpenCall = $unit?->calls()->where('status', '!=', 'CLO')
            ->where('status', 'not like', 'CLO-%')->exists() ?? false;

        return view('livewire.cad.unit-status-strip', compact('unit', 'hasOpenCall'));
    }
}
