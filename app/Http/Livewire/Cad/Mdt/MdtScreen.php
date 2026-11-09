<?php

namespace App\Http\Livewire\Cad\Mdt;

use App\Models\Cad\ActiveUnit;
use App\Models\Cad\CallNatures;
use App\Models\Cad\CallStatuses;
use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use App\Support\Cad\CallAssignmentStatusService;
use Illuminate\Support\Facades\DB;
use App\Support\Cad\UnitResponseStatusService;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class MdtScreen extends Component
{
    public $active_units;

    public $calls;

    public $active_panic = false;

    public $type_filters = [1, 2, 3];

    public $nature_filter = '';

    public $call_natures = CallNatures::NATURECODES;

    public $call_statuses = CallStatuses::STATUSCODES;

    protected $listeners = ['updated-page' => '$refresh'];

    public function mount()
    {
        if (session()->has('cad.type_filters')) {
            $this->type_filters = session('cad.type_filters');
        }
    }

    public function render()
    {
        $this->active_units = ActiveUnit::with(['officer', 'mdtIdentity', 'user_department', 'calls'])->get()->sortBy('user_department.department.type')->sortBy('user_department.department.initials');
        $this->calls = Call::where('status', '!=', 'CLO')->where('status', 'not like', 'CLO-%')->orderBy('priority', 'desc')
            ->when(! empty($this->type_filters), fn (Builder $query) => $query->whereIn('type', $this->type_filters))
            ->when(! empty($this->nature_filter), fn (Builder $query) => $query->where('nature', $this->nature_filter))
            ->get();

        session(['cad.type_filters' => $this->type_filters]);

        return view('livewire.cad.mdt.mdt-screen');
    }

    public function set_status(ActiveUnit $activeUnit, $status)
    {
        abort_unless((int) $activeUnit->id === (int) auth()->user()->active_unit?->id, 403);
        abort_unless(in_array($status, ['AVL', 'BUSY', 'BRK', 'ENRUTE', 'ONSCN', 'TRANS', 'UNAV'], true), 422);
        abort_if($activeUnit->is_panic, 409);

        app(UnitResponseStatusService::class)->change($activeUnit, $status);
        $this->emit('updated-page');
        $this->emit('incidentUpdated');
    }

    public function set_call_status(Call $call, $status)
    {
        abort_if($status === 'PANIC' || $call->status === 'PANIC', 409,
            'Clear the unit panic before editing the incident status.');
        abort_unless(array_key_exists($status, CallStatuses::STATUSCODES), 422);
        abort_if(in_array($status, ['ENRT', 'ARRVD'], true)
            && !$call->attached_units()->exists(), 422,
            'Attach at least one unit before setting an en-route or on-scene call status.');
        $oldStatus = $call->status;
        if ($oldStatus === $status) return;
        $call->update(['status' => $status]);

        $status_array = explode('-', $status);
        if ($status_array[0] == 'CLO') {
            $this->close_call($call);
        }

        CallActivityLogger::changes($call, ['Status' => [$oldStatus, $status]]);
        $this->emit('updated-page');
    }

    public function set_call_priority(Call $call, $priority)
    {
        abort_unless(in_array((string) $priority, ['1', '2', '3', '4', '5'], true), 422);
        $oldPriority = $call->priority;
        if ((string) $oldPriority === (string) $priority) return;
        $call->update(['priority' => $priority]);
        CallActivityLogger::changes($call, ['Priority' => [$oldPriority, $priority]]);
        $this->emit('updated-page');
    }

    public function remove_unit_from_call(ActiveUnit $activeUnit, Call $call)
    {
        abort_unless((int) $activeUnit->user_id === (int) auth()->id() && (int) $activeUnit->id === (int) auth()->user()->active_unit?->id, 403);

        DB::transaction(function () use ($call, $activeUnit) {
            if (!$call->attached_units()->whereKey($activeUnit->id)->exists()) return;
            $call->attached_units()->detach($activeUnit->id);
            CallActivityLogger::record($call, 'UNIT', 'Unit ' . $activeUnit->unit_callsign . ' detached from the call.');
            CallAssignmentStatusService::reopenIfUnassigned($call);
        });

        $activeUnit->update(['description' => 'Removed from call: '.$call->id]);

        $call->touch();
        $activeUnit->touch();
        $this->emit('updated-page');
    }

    public function add_unit_to_call(ActiveUnit $activeUnit, Call $call)
    {
        abort_unless((int) $activeUnit->user_id === (int) auth()->id() && (int) $activeUnit->id === (int) auth()->user()->active_unit?->id, 403);
        $call->attached_units()->syncWithoutDetaching([$activeUnit->id]);

        CallActivityLogger::record($call, 'UNIT', 'Unit ' . $activeUnit->unit_callsign . ' attached to the call.');

        $activeUnit->update(['description' => 'Added to call: '.$call->id]);

        $call->touch();
        $activeUnit->touch();
        $this->emit('updated-page');
    }

    public function close_call(Call $call)
    {
        foreach ($call->attached_units as $unit) {
            $unit->update(['status' => 'AVL']);
            $call->attached_units()->detach($unit->id);
        }

        $call->attached_units()->detach();

        CallActivityLogger::record($call, 'CALL', 'Incident closed and all units released.');
        $this->emit('updated-page');
    }

}
