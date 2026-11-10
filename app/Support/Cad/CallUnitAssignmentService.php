<?php

namespace App\Support\Cad;

use App\Models\Cad\ActiveUnit;
use App\Models\Call;
use Illuminate\Support\Facades\DB;

/**
 * Shared plus/minus assignment behaviour for MDT, call details and dispatch.
 * Panic is a unit emergency flag, not an assignment lock.
 */
class CallUnitAssignmentService
{
    public function attach(ActiveUnit $unit, Call $call): void
    {
        DB::transaction(function () use ($unit, $call) {
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);
            $call = Call::query()->lockForUpdate()->findOrFail($call->id);

            // An off-duty unit can receive an invitation while signed in,
            // but only if the acceptance UI is enabled. Never silently
            // attach off-duty units when the prompt is disabled.
            abort_if(str_starts_with($unit->status, 'OFFDTY')
                && ! config('cad.prompt_enroute_on_assignment', true), 409,
                'Select Available before assigning the unit.');
            abort_if($unit->is_panic, 409, 'Clear panic before attaching to a new call.');
            abort_if(in_array($call->status, ['CLR', 'CLO'], true)
                || str_starts_with($call->status, 'CLO-'), 409, 'The call is no longer open.');

            if ($call->attached_units()->whereKey($unit->id)->exists()) {
                return;
            }

            $call->attached_units()->syncWithoutDetaching([$unit->id]);
            // Available units become Busy while awaiting a response.
            // Off-duty units remain OFFDTY until they accept the callout.
            // Preserve existing response stages when already responding.
            $attributes = ['description' => 'Added to call: ' . $call->id];
            if ($unit->status === 'AVL') {
                $attributes['status'] = 'BUSY';
            }
            $unit->update($attributes);

            CallActivityLogger::record($call, 'UNIT',
                'Unit ' . $unit->unit_callsign . ' attached to the call.');
            CadAudioPublisher::unitActivity($call, (int) $unit->user_id);
        });
    }

    public function detach(ActiveUnit $unit, Call $call): void
    {
        DB::transaction(function () use ($unit, $call) {
            $unit = ActiveUnit::query()->lockForUpdate()->findOrFail($unit->id);
            $call = Call::query()->lockForUpdate()->findOrFail($call->id);
            if (! $call->attached_units()->whereKey($unit->id)->exists()) {
                return;
            }

            $call->attached_units()->detach($unit->id);

            $remaining = $unit->calls()->exists();
            $attributes = ['description' => 'Removed from call: ' . $call->id];
            if (! $unit->is_panic && ! $remaining
                && ! str_starts_with($unit->status, 'OFFDTY')) {
                $attributes['status'] = 'AVL';
            }
            // Never change status/pre_panic_status or is_panic on detachment.
            $unit->update($attributes);

            CallActivityLogger::record($call, 'UNIT',
                'Unit ' . $unit->unit_callsign . ' detached from the call.'
                . ($unit->is_panic ? ' Unit panic remains active.' : ''));

            CallAssignmentStatusService::reopenIfUnassigned($call);
            // The unit has already been detached. Notify remaining responders,
            // not the departing unit which received its own status sound.
            CadAudioPublisher::unitActivity($call, (int) $unit->user_id);
        });
    }
}
