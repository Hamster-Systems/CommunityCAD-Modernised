<?php

namespace App\Support\Cad;

use App\Models\Cad\CadAudioEvent;
use App\Models\Cad\ActiveUnit;
use App\Models\Call;

/**
 * Persist a compact event once when the underlying CAD record changes.
 * Clients may poll for unseen IDs without re-triggering on page refresh.
 */
class CadAudioPublisher
{
    public static function callCreated(Call $call): void
    {
        // New calls are dispatcher notifications, not a tone for every patrol
        // unit that happens to have CAD open.
        $dispatcher = ActiveUnit::query()->where('department_type', 2)
            ->where('status', 'not like', 'OFFDTY%')
            ->whereNotNull('user_id')->oldest()->first();
        if ($dispatcher) {
            self::publish('new_call', (int) $dispatcher->user_id, $call->id);
        }
    }

    public static function callUpdated(Call $call): void
    {
        $changedFields = array_diff(array_keys($call->getChanges()), ['updated_at']);
        if ($changedFields
            && $call->status !== 'PANIC'
            && $call->getOriginal('status') !== 'PANIC') {
            // The panic event is separately prioritized: don't also play a
            // call-update sound for the same emergency transition.
            // Only currently attached units hear a call update.
            self::notifyAttachedUnits($call);
        }
    }

    /**
     * Notify only the currently attached units about a newly added incident
     * note. Changes recorded purely for audit/status by the call logger must
     * not create additional sounds.
     */
    public static function noteAdded(Call $call): void
    {
        self::notifyAttachedUnits($call);
    }

    private static function notifyAttachedUnits(Call $call): void
    {
        foreach ($call->attached_units()->whereNotNull('user_id')
            ->distinct()->pluck('user_id') as $userId) {
            self::publish('call_updated', (int) $userId, $call->id);
        }
    }

    public static function unitUpdated(ActiveUnit $unit): void
    {
        if ($unit->wasChanged('is_panic')) {
            if ((bool) $unit->is_panic) {
                self::publish('panic', null, null);
            }
            // Suppress the accompanying unit-status tone while activating
            // or clearing a panic: one sound per state transition.
            return;
        }

        // Attaching an Available unit often changes BOTH status (AVL->BUSY)
        // and description. Play the assignment sound once, not two tones.
        $assigned = $unit->wasChanged('description')
            && preg_match('/^Added to call:\\s*(\\d+)$/', trim((string) $unit->description), $matches);
        if ($assigned) {
            self::publish('call_assigned', (int) $unit->user_id, (int) $matches[1]);
            return;
        }

        if ($unit->wasChanged('status') && ! $unit->is_panic) {
            self::publish('unit_status', (int) $unit->user_id, null);
        }
    }

    private static function publish(string $event, ?int $targetUserId, ?int $callId): void
    {
        CadAudioEvent::create([
            'event' => $event,
            'target_user_id' => $targetUserId,
            'call_id' => $callId,
            'created_at' => now(),
        ]);
    }
}
