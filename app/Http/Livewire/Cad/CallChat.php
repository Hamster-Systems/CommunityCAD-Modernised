<?php

namespace App\Http\Livewire\Cad;

use App\Models\Call;
use App\Models\CallLog;
use App\Support\Cad\CallActivityLogger;
use Livewire\Component;
use Livewire\WithPagination;

class CallChat extends Component
{
    use WithPagination;

    public Call $call;
    public string $filter = 'ALL';
    public string $search = '';
    public string $entryType = 'NOTE';
    public string $note = '';
    public int $perPage = 20;

    protected $listeners = ['incidentUpdated' => '$refresh'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        abort_unless(in_array($filter, ['ALL', 'CALL', 'UNIT', 'PERSON', 'VEHICLE', 'REPORT', 'NOTE', 'SITUATION', 'DISPATCH', 'WITNESS'], true), 422);

        $this->filter = $filter;
        $this->resetPage();
    }

    public function postNote(): void
    {
        $this->validate([
            'entryType' => 'required|in:NOTE,SITUATION,DISPATCH,WITNESS',
            'note' => 'required|string|max:2000',
        ]);
        abort_unless(auth()->check() && auth()->user()->active_unit, 403);

        $message = trim($this->note);
        abort_if($message === '', 422, 'Please enter a message.');

        $call = Call::findOrFail($this->call->id);
        abort_if($call->status === 'CLO' || str_starts_with($call->status, 'CLO-'), 422, 'This incident has been closed.');

        CallActivityLogger::record($call, $this->entryType, $message);
        $this->note = '';
        $this->resetPage();
        $this->emit('incidentUpdated');
    }

    public function render()
    {
        $query = CallLog::query()->where('call_id', $this->call->id);

        if ($this->filter === 'NOTE') {
            $query->where(function ($q) {
                $q->where('text', 'like', '[NOTE] %')
                    ->orWhere(function ($legacy) {
                        $legacy->where('text', 'not like', '[%')
                            ->where('text', 'not like', '%ASSIGN%')
                            ->where('text', 'not like', '%STATUS SET TO%')
                            ->where('text', 'not like', 'Call Status%')
                            ->where('text', 'not like', 'Call Priority%');
                    });
            });
        } elseif ($this->filter === 'UNIT') {
            $query->where(function ($q) {
                $q->where('text', 'like', '[UNIT] %')
                    ->orWhere('text', 'like', 'Officer%assign%')
                    ->orWhere('text', 'like', '%STATUS SET TO%');
            });
        } elseif ($this->filter === 'CALL') {
            $query->where(function ($q) {
                $q->where('text', 'like', '[CALL] %')
                    ->orWhere('text', 'like', 'Call Status%')
                    ->orWhere('text', 'like', 'Call Priority%')
                    ->orWhere('text', 'like', 'Call Nature%');
            });
        } elseif ($this->filter !== 'ALL') {
            $query->where('text', 'like', '[' . $this->filter . '] %');
        }

        $needle = trim($this->search);
        if ($needle !== '') {
            // Escape LIKE wildcards so a search for '%' does not match everything.
            $needle = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_substr($needle, 0, 100));
            $query->where(function ($q) use ($needle) {
                $q->whereRaw("text LIKE ? ESCAPE '!'", ['%' . $needle . '%'])
                    ->orWhereRaw("`from` LIKE ? ESCAPE '!'", ['%' . $needle . '%']);
            });
        }

        $entries = $query->orderByDesc('created_at')->orderByDesc('id')->paginate($this->perPage);

        return view('livewire.cad.call-chat', ['entries' => $entries]);
    }
}
