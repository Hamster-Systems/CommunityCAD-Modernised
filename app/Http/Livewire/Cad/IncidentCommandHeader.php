<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use App\Support\Cad\CallAssignmentStatusService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class IncidentCommandHeader extends Component
{
    public int $callId;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function mount(Call $call): void
    {
        $this->callId = $call->id;
    }

    public function toggleAssignment(): void
    {
        $call = Call::findOrFail($this->callId);
        $unit = auth()->user()?->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) auth()->id(), 403);
        abort_if((int) $unit->department_type === 2, 403);

        $service = app(\App\Support\Cad\CallUnitAssignmentService::class);
        if ($call->attached_units()->whereKey($unit->id)->exists()) {
            $service->detach($unit, $call);
        } else {
            $service->attach($unit, $call);
        }

        $this->emit('incidentUpdated');
    }

    public function render()
    {
        $call = Call::with(['attached_units.mdtIdentity', 'attached_units.user_department.department'])
            ->findOrFail($this->callId);
        $unit = auth()->user()->active_unit;
        $attached = $unit && $call->attached_units->contains('id', $unit->id);
        $natureLabel = $call->nature_info['name'] ?? $call->nature;
        $statusLabel = $call->status_info['name'] ?? $call->status;

        return view('livewire.cad.incident-command-header',
            compact('call', 'unit', 'attached', 'natureLabel', 'statusLabel'));
    }
}
