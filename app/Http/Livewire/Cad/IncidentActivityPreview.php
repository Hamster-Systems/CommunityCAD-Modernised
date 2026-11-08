<?php

namespace App\Http\Livewire\Cad;

use App\Models\CallLog;
use Livewire\Component;

class IncidentActivityPreview extends Component
{
    public int $callId;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function mount(int $callId): void
    {
        $this->callId = $callId;
    }

    public function render()
    {
        // Small read-only snapshot. The full searchable, paginated composer
        // lives exclusively on the Activity tab.
        $events = CallLog::query()
            ->where('call_id', $this->callId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('livewire.cad.incident-activity-preview', compact('events'));
    }
}
