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

        DB::transaction(function () use ($call, $unit) {
            $attached = $call->attached_units()->whereKey($unit->id)->exists();
            if ($attached) {
                $call->attached_units()->detach($unit->id);
                $unit->update(['description' => 'Removed from call: ' . $call->id]);
                CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign . ' unassigned from the call.');
                CallAssignmentStatusService::reopenIfUnassigned($call);
            } else {
                abort_if(str_starts_with($unit->status, 'OFFDTY'), 409,
                    'Select Available before assigning your unit.');
                abort_if($call->status === 'CLO' || str_starts_with($call->status, 'CLO-'), 422);
                $call->attached_units()->syncWithoutDetaching([$unit->id]);
                $unit->update(['description' => 'Added to call: ' . $call->id]);
                CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign . ' assigned to the call.');
            }
        });

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
