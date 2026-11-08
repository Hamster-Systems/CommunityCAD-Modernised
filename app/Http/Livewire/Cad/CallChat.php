<?php

namespace App\Http\Livewire\Cad;

use App\Models\CallLog as ModelsCallLog;
use Livewire\Component;

class CallChat extends Component
{
    public $call_chat;

    public $call;

    public function mount()
    {
        // Activity is queried at render time so Livewire polling reflects new notes.
    }

    public function render()
    {
        $this->call_chat = ModelsCallLog::where('call_id', $this->call->id)
            ->orderByDesc('created_at')->limit(100)->get();
        return view('livewire.cad.call-chat');
    }
}
