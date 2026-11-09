<?php

namespace App\Support\Cad;

use App\Models\Cad\ActiveUnit;
use App\Models\Call;
use Illuminate\Support\Facades\DB;

/**
 * Panic is an emergency overlay on the existing call/response state.
 * Never detach responding units or discard their previous state.
 */
class PanicResponseService
{
    public function activate(ActiveUnit $unit, ?string $location = null): void
    {
        DB::transaction(function () use ($unit, $location) {
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);

            if ($unit->is_panic) {
                return;
            }
            abort_if(str_starts_with($unit->status, 'OFFDTY'), 409,
                'Sign on duty before activating a unit panic.');

            $unit->update([
                'is_panic' => true,
                'pre_panic_status' => $unit->status,
                'status' => 'PANIC',
                'location' => $location ?? $unit->location,
            ]);

            foreach ($unit->calls()->where('status', '!=', 'CLO')
                ->where('status', 'not like', 'CLO-%')
                ->where('status', '!=', 'CLR')->get() as $attachedCall) {

                $call = Call::query()->lockForUpdate()->findOrFail($attachedCall->id);
                if ($call->status !== 'PANIC') {
                    $previous = $call->status;
                    $call->update([
                        'pre_panic_status' => $previous,
                        'status' => 'PANIC',
                    ]);
                    CallActivityLogger::record($call, 'CALL',
                        'PANIC UNIT: unit ' . $unit->unit_callsign .
                        ' activated panic. Previous call status: ' . $previous . '.');
                } else {
                    CallActivityLogger::record($call, 'UNIT',
                        'Additional panic activated by unit ' . $unit->unit_callsign . '.');
                }
            }
        });
    }

    public function clear(ActiveUnit $unit): void
    {
        DB::transaction(function () use ($unit) {
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);
            // Recheck the unit's attached incidents even when this request is
            // repeated: an earlier clear may have updated the unit while its
            // call was left in PANIC by an interrupted/legacy workflow.
            $assignedCalls = $unit->calls()->get();

            if ($unit->is_panic) {
                $restoredUnitStatus = $assignedCalls->isEmpty()
                    ? 'AVL'
                    : ($unit->pre_panic_status ?: 'BUSY');

                $unit->update([
                    'is_panic' => false,
                    'status' => $restoredUnitStatus,
                    'pre_panic_status' => null,
                ]);
            } elseif ($unit->status === 'PANIC') {
                $unit->update([
                    'status' => $assignedCalls->isEmpty()
                        ? 'AVL' : ($unit->pre_panic_status ?: 'BUSY'),
                    'pre_panic_status' => null,
                ]);
            }

            foreach ($assignedCalls as $call) {
                $this->restoreCallIfSafe($call, $unit);
            }
        });
    }

    /**
     * Called after clearing a panic or when someone explicitly detaches
     * a panicked unit. Multiple panicked responders keep the call in PANIC.
     */
    public function restoreCallIfSafe(Call $attachedCall, ?ActiveUnit $clearedUnit = null): void
    {
        $call = Call::query()->lockForUpdate()->findOrFail($attachedCall->id);
        if ($call->status !== 'PANIC'
            || $call->attached_units()->where('active_units.is_panic', true)->exists()) {
            return;
        }

        $previous = $call->pre_panic_status ?: 'RCVD';
        if (! $call->attached_units()->exists()
            && in_array($previous, ['ENRT', 'ARRVD'], true)) {
            $previous = 'RCVD';
        }

        $call->update(['status' => $previous, 'pre_panic_status' => null]);
        CallActivityLogger::record($call, 'CALL',
            'PANIC UNIT cleared; call returned to ' . $previous .
            ($clearedUnit ? ' after unit ' . $clearedUnit->unit_callsign . ' cleared panic.' : '.'));
    }
}
