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
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);

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

            $lockedCall->update(['status' => $target]);
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
