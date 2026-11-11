<?php

namespace App\Http\Livewire\Cad;

use App\Models\Cad\ActiveUnit;
use App\Models\Call;
use App\Support\Cad\UnitResponseStatusService;
use App\Support\Cad\CallUnitAssignmentService;
use Illuminate\Support\Facades\DB;
use App\Support\Cad\CadAudio;
use Carbon\Carbon;
use Livewire\Component;

class AlertBar extends Component
{
    public $active_alerts = [];

    public ?int $pendingEnrouteCallId = null;

    public bool $pendingAssignmentOffDuty = false;

    protected $listeners = ['updated-page' => '$refresh', 'incidentUpdated' => '$refresh'];

    /**
     * Remember the answer for this particular assignment, not just the call ID.
     * Reassignment to the same call can therefore ask again.
     */
    private function assignmentKey(ActiveUnit $unit, int $callId): string
    {
        return 'cad.assignment_enroute.' . $unit->id . '.' . $callId;
    }

    private function currentAssignment(ActiveUnit $unit): ?Call
    {
        if (! config('cad.prompt_enroute_on_assignment', true)
            || $unit->is_panic
            || in_array($unit->status, ['ENRUTE', 'ONSCN', 'TRANS'], true)
            || ! preg_match('/^Added to call:\s*(\d+)$/', trim((string) $unit->description), $matches)
            || ! $unit->updated_at
            || $unit->updated_at->lt(now()->subMinutes(10))) {
            return null;
        }

        $callId = (int) $matches[1];
        if (session($this->assignmentKey($unit, $callId)) === $unit->updated_at->format('Y-m-d H:i:s.u')) {
            return null;
        }

        return $unit->calls()->whereKey($callId)
            ->where('status', '!=', 'CLR')
            ->where('status', '!=', 'CLO')
            ->where('status', 'not like', 'CLO-%')
            ->first();
    }

    public function answerAssignment(int $callId, bool $markEnroute): void
    {
        $unit = auth()->user()?->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) auth()->id(), 403);
        $call = $this->currentAssignment($unit);
        abort_unless($call && (int) $call->id === $callId, 409, 'This assignment prompt has expired.');

        abort_if($unit->is_panic, 409, 'Clear the active panic before changing status.');
        $assignmentTimestamp = $unit->updated_at->format('Y-m-d H:i:s.u');
        $wasOffDuty = str_starts_with($unit->status, 'OFFDTY');

        if ($markEnroute) {
            // Explicit acceptance is the only point where an off-duty unit
            // becomes on duty. Perform both transitions atomically so a failed
            // En Route transition cannot leave the unit incorrectly on duty.
            DB::transaction(function () use ($unit, $call, $wasOffDuty) {
                $response = app(UnitResponseStatusService::class);
                if ($wasOffDuty) {
                    $response->change($unit, 'BUSY', $call);
                }
                $response->change($unit, 'ENRUTE', $call);
            });
        } elseif ($wasOffDuty) {
            // Declining an invitation leaves the unit off duty, without an
            // active assignment they cannot respond to.
            app(CallUnitAssignmentService::class)->detach($unit, $call);
        }
        // Already-on-duty users can keep their current status and remain
        // attached. Do NOT call the global Available action (it detaches).
        session()->put($this->assignmentKey($unit, $callId), $assignmentTimestamp);
        $this->pendingEnrouteCallId = null;
        $this->pendingAssignmentOffDuty = false;
        $this->emit('incidentUpdated');
        $this->emit('updated-page');
    }

    public function render()
    {
        $this->active_alerts = [];
        $this->pendingEnrouteCallId = null;
        $this->pendingAssignmentOffDuty = false;
        $unit = auth()->user()?->active_unit;

        if (! $unit) {
            return view('livewire.cad.alert-bar');
        }

        if ($unit->is_panic) {
            $this->active_alerts['is_panic'] = [
                'color' => 'red',
                'message' => 'You have activated your panic button at ' . $unit->location,
                'link' => null,
                'audio' => CadAudio::url('panic'),
                'volume' => CadAudio::volume('panic'),
            ];
        }

        $activePanicUnits = ActiveUnit::where('is_panic', '1')->with('user_department')->get();
        if (! $unit->is_panic) {
            foreach ($activePanicUnits as $activeUnit) {
                $this->active_alerts['is_panic' . $activeUnit->id] = [
                    'color' => 'red',
                    'message' => 'Unit (' . ($activeUnit->unit_callsign) .
                        ') has activated their panic button at ' . $activeUnit->location,
                    'link' => null,
                    'audio' => 'audio/panic_button.mp3',
                ];
            }
        }

        foreach (Call::where('created_at', '>', Carbon::now()->subSeconds(15))->get() as $call) {
            $this->active_alerts['new_call' . $call->id] = [
                'color' => 'green',
                'message' => 'A new call has been created',
                'link' => null,
                'audio' => CadAudio::url('new_call'),
                'volume' => CadAudio::volume('new_call'),
            ];
        }

        $assignment = $this->currentAssignment($unit);
        if ($assignment) {
            $this->pendingEnrouteCallId = (int) $assignment->id;
            $this->pendingAssignmentOffDuty = str_starts_with($unit->status, 'OFFDTY');
        } elseif (! config('cad.prompt_enroute_on_assignment', true)
            && preg_match('/^Added to call:\s*(\d+)$/', trim((string) $unit->description), $matches)
            && $unit->updated_at && $unit->updated_at->gt(now()->subSeconds(15))) {
            // Keep the legacy view-call notification if prompting is disabled.
            $call = $unit->calls()->whereKey((int) $matches[1])->first();
            if ($call) {
                $this->active_alerts['added_to_call' . $call->id] = [
                    'color' => 'green',
                    'message' => 'You have been added to a call. Click to see call.',
                    'link' => route('cad.call.show', $call->id),
                    'audio' => CadAudio::url('call_assigned'),
                    'volume' => CadAudio::volume('call_assigned'),
                ];
            }
        }

        return view('livewire.cad.alert-bar');
    }
}
