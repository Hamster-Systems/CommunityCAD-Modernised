<?php

namespace App\Http\Livewire\Cad;

use App\Models\CallLog as ModelsCallLog;
use Livewire\Component;

class CallChat extends Component
{
    public $call_chat;

    public $call;

    public $filter = 'ALL';

    public function mount()
    {
        // Activity is queried at render time so Livewire polling reflects new notes.
    }

    public function render()
    {
        $query = ModelsCallLog::where('call_id', $this->call->id);
        if (in_array($this->filter, ['CALL', 'UNIT', 'PERSON', 'VEHICLE', 'REPORT', 'NOTE'], true)) {
            if ($this->filter === 'NOTE') {
                $query->where(function ($q) {
                    $q->where('text', 'like', '[NOTE] %')
                        ->orWhere('text', 'not like', '[%');
                });
            } else {
                $query->where('text', 'like', '[' . $this->filter . '] %');
            }
        }

        $this->call_chat = $query->orderByDesc('created_at')
            ->orderByDesc('id')->limit(100)->get();
        return view('livewire.cad.call-chat');
    }
}
