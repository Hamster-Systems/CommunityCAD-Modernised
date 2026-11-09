<?php

namespace App\Http\Livewire\Cad;

use App\Models\CallLog;
use Livewire\Component;

class IncidentActivityHistory extends Component
{
    public int $callId;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function mount(int $callId): void
    {
        $this->callId = $callId;
    }

    public function render()
    {
        $entries = CallLog::where('call_id', $this->callId)
            ->orderByDesc('created_at')->orderByDesc('id')->limit(60)->get();

        return view('livewire.cad.incident-activity-history', compact('entries'));
    }
}
