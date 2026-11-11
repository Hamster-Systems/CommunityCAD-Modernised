<?php

namespace App\Support\Cad;

use App\Models\Cad\ActiveUnit;
use App\Models\Call;
use Illuminate\Support\Facades\DB;

class UnitResponseStatusService
{
    /**
     * Align an officer's unit status with the response stage of their assigned
     * call. Never downgrade a call or overwrite a disposition / closure status.
     */
    public function change(ActiveUnit $unit, string $status, ?Call $selectedCall = null): void
    {
        DB::transaction(function () use ($unit, $status, $selectedCall) {
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);            abort_if($unit->is_panic, 409, 'Clear your active panic before changing duty status.');


            // The global status toolbar is the single duty control.
            if ($status === 'UNAV') {
                if (!str_starts_with($unit->status, 'OFFDTY')) {
                    $assignedCalls = $unit->calls()->get();
                    foreach ($assignedCalls as $assignedCall) {
                        $assignedCall->attached_units()->detach($unit->id);
                        CallAssignmentStatusService::reopenIfUnassigned($assignedCall);
                        CallActivityLogger::record($assignedCall, 'UNIT',
                            'Unit ' . $unit->unit_callsign . ' went unavailable and detached from the call.');
                        CadAudioPublisher::unitActivity($assignedCall, (int) $unit->user_id);
                    }
                    $unit->update([
                        'status' => 'OFFDTY',
                        'description' => 'Unit unavailable (off duty in CAD)',
                        'off_duty_at' => now(),
                    ]);
                }
                return;
            }

            if (str_starts_with($unit->status, 'OFFDTY')) {
                abort_if(in_array($status, ['ENRUTE', 'ONSCN', 'TRANS'], true), 409,
                    'Select Available first, then attach to a call.');
                $unit->update([
                    'status' => 'AVL',
                    'description' => 'Status Set To: AVL',
                    'first_on_duty_at' => now(),
                    'off_duty_at' => null,
                    'off_duty_type' => null,
                ]);
                // Refresh the in-memory state for the normal status transition below.
                $unit->refresh();
            }

            // Available releases this unit from every assigned incident. If it
            // was the last responding unit, return the incident to Call Open.
            if ($status === 'AVL') {
                foreach ($unit->calls()->get() as $assignedCall) {
                    $assignedCall->attached_units()->detach($unit->id);
                    CallActivityLogger::record($assignedCall, 'UNIT',
                        'Unit ' . $unit->unit_callsign . ' became available and detached from the call.');
                    CadAudioPublisher::unitActivity($assignedCall, (int) $unit->user_id);
                    CallAssignmentStatusService::reopenIfUnassigned($assignedCall);
                }
                if ($unit->status !== 'AVL') {
                    $unit->update([
                        'status' => 'AVL',
                        'description' => 'Status Set To: AVL',
                    ]);
                }
                return;
            }

            $call = $selectedCall
                ? $unit->calls()->whereKey($selectedCall->id)->first()
                : $unit->calls()->where('status', '!=', 'CLO')
                    ->where('status', 'not like', 'CLO-%')
                    ->orderByDesc('calls.created_at')->first();

            if ($selectedCall) {
                abort_unless($call, 403, 'Your unit is not assigned to this call.');
            }

            if (in_array($status, ['ENRUTE', 'ONSCN', 'TRANS'], true)) {
                abort_unless($call && !$this->isClosed($call->status), 403,
                    'Attach to an open call before selecting this status.');
            }

            $oldUnitStatus = $unit->status;
            if ($oldUnitStatus !== $status) {
                $unit->update([
                    'status' => $status,
                    'description' => 'Status Set To: ' . $status,
                ]);

                if ($call) {
                    CallActivityLogger::record($call, 'UNIT',
                        'Unit ' . $unit->unit_callsign . ' status changed from ' .
                        $oldUnitStatus . ' to ' . $status . '.');
                    CadAudioPublisher::unitActivity($call, (int) $unit->user_id);
                }
            }

            if (!$call) {
                return;
            }

            // Only advance response stages. AVL, BUSY, BRK and TRANS do not
            // change call disposition. Transporting is a unit state, not a
            // defined CommunityCAD call state.
            $target = match ($status) {
                'ENRUTE' => 'ENRT',
                'ONSCN' => 'ARRVD',
                default => null,
            };
            if (!$target) {
                return;
            }

            $lockedCall = Call::query()->lockForUpdate()->findOrFail($call->id);
            $oldCallStatus = $lockedCall->status;
            $allowedFrom = $target === 'ENRT'
                ? ['RCVD', 'HLD']
                : ['RCVD', 'HLD', 'ENRT'];

            if (!in_array($oldCallStatus, $allowedFrom, true)) {
                return;
            }

            // Other responders have already received the unit-activity sound.
            // Keep automatic call-stage progression from generating a duplicate.
            $lockedCall->updateQuietly(['status' => $target]);
            CallActivityLogger::record($lockedCall, 'CALL',
                'Automatically updated call status from ' . $oldCallStatus .
                ' to ' . $target . ' when unit ' . $unit->unit_callsign .
                ' changed status to ' . $status . '.');
        });
    }

    private function isClosed(string $status): bool
    {
        return $status === 'CLO' || str_starts_with($status, 'CLO-');
    }
}
