<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Models\CallLog;
use App\Support\Cad\CadAudioPublisher;
use App\Support\Cad\CallActivityLogger;
use Livewire\Component;

class CallNotes extends Component
{
    public int $callId;
    public bool $compact = false;
    public string $message = '';

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function mount(int $callId, bool $compact = false): void
    {
        $this->callId = $callId;
        $this->compact = $compact;
    }

    public function addCallNote(): void
    {
        abort_unless(auth()->check(), 403);
        // Any signed-in CAD unit may add a call note, not only dispatch.
        // Do not allow authenticated portal users with no active CAD identity.
        abort_unless(auth()->user()->active_unit, 403, 'Sign into CAD to post a call note.');

        $this->validate(['message' => 'required|string|min:1|max:2000']);
        $message = trim($this->message);
        abort_if($message === '', 422, 'Enter a call note.');

        $call = Call::findOrFail($this->callId);
        abort_if($call->status === 'CLO' || str_starts_with($call->status, 'CLO-'), 409,
            'This call has been closed.');

        // One canonical activity record: the dedicated notes panel and the
        // Activity feed show the same author, timestamp, and original text.
        CallActivityLogger::record($call, 'NOTE', $message);
        CadAudioPublisher::noteAdded($call);

        $this->reset('message');
        $this->emit('incidentUpdated');
    }

    public function render()
    {
        // Operational notes only, not unit status changes, assignments, or
        // automated call status history. Keep the full audit trail in Activity.
        $notes = CallLog::query()->where('call_id', $this->callId)
            ->where(function ($query) {
                foreach (['[DISPATCH] %', '[NOTE] %', '[SITUATION] %', '[WITNESS] %'] as $prefix) {
                    $query->orWhere('text', 'like', $prefix);
                }
            })
            ->orderByDesc('created_at')->orderByDesc('id')
            ->limit($this->compact ? 3 : 40)->get();

        $call = Call::findOrFail($this->callId);
        $canPost = ! $this->compact && auth()->check()
            && auth()->user()->active_unit
            && $call->status !== 'CLO'
            && ! str_starts_with($call->status, 'CLO-');

        return view('livewire.cad.call-notes', compact('notes', 'canPost'));
    }
}
