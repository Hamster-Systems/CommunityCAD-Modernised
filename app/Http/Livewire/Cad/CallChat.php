<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Models\CallLog;
use App\Support\Cad\CallActivityLogger;
use Livewire\Component;

class CallChat extends Component
{
    public Call $call;

    public string $filter = 'ALL';

    public string $note = '';

    public int $limit = 50;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    protected $rules = [
        'note' => 'required|string|max:2000',
    ];

    public function postNote(): void
    {
        $this->validate();
        abort_unless(auth()->check() && auth()->user()->active_unit, 403);

        CallActivityLogger::record($this->call, 'NOTE', trim($this->note));

        $this->note = '';
        $this->emit('incidentUpdated');
        $this->dispatchBrowserEvent('cad-activity-posted');
    }

    public function setFilter(string $filter): void
    {
        abort_unless(in_array($filter, ['ALL', 'CALL', 'UNIT', 'PERSON', 'VEHICLE', 'REPORT', 'NOTE'], true), 422);
        $this->filter = $filter;
        $this->limit = 50;
    }

    public function showMore(): void
    {
        $this->limit = min(300, $this->limit + 50);
    }

    public function render()
    {
        $query = CallLog::where('call_id', $this->call->id);

        if ($this->filter === 'NOTE') {
            $query->where(function ($q) {
                $q->where('text', 'like', '[NOTE] %')
                    ->orWhere('text', 'not like', '[%');
            });
        } elseif ($this->filter !== 'ALL') {
            $query->where('text', 'like', '[' . $this->filter . '] %');
        }

        $entries = $query->orderByDesc('created_at')->orderByDesc('id')
            ->limit($this->limit + 1)->get();

        return view('livewire.cad.call-chat', [
            'call_chat' => $entries->take($this->limit),
            'hasMore' => $entries->count() > $this->limit,
        ]);
    }
}
