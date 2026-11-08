<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use Livewire\Component;

class UnitStatusStrip extends Component
{
    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function changeStatus(string $status): void
    {
        abort_unless(in_array($status, ['AVL', 'BUSY', 'ENRUTE', 'ONSCN', 'TRANS', 'BRK'], true), 422);
        $unit = auth()->user()?->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) auth()->id(), 403);
        abort_if($unit->is_panic || $unit->status === 'OFFDTY', 409);

        $call = $unit->calls()->where('status', '!=', 'CLO')
            ->where('status', 'not like', 'CLO-%')->orderByDesc('calls.created_at')->first();

        if (in_array($status, ['ENRUTE', 'ONSCN', 'TRANS'], true)) {
            abort_unless($call, 403);
        }

        $previous = $unit->status;
        if ($previous === $status) return;

        $unit->update(['status' => $status, 'description' => 'Status Set To: ' . $status]);

        if ($call) {
            CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign .
                ' status changed from ' . $previous . ' to ' . $status . '.');
        }

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
