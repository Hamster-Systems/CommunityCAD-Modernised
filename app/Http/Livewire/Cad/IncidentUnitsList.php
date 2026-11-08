<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use Livewire\Component;

class IncidentUnitsList extends Component
{
    public int $callId;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function mount(int $callId): void
    {
        $this->callId = $callId;
    }

    public function render()
    {
        $call = Call::with(['attached_units.mdtIdentity', 'attached_units.user_department.department'])
            ->findOrFail($this->callId);

        return view('livewire.cad.incident-units-list', ['units' => $call->attached_units]);
    }
}
