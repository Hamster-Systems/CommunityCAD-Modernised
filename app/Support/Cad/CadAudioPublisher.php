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
        self::publish('new_call', null, $call->id);
    }

    public static function callUpdated(Call $call): void
    {
        if ($call->wasChanged('status')) {
            self::publish('call_updated', null, $call->id);
        }
    }

    public static function unitUpdated(ActiveUnit $unit): void
    {
        if ($unit->wasChanged('is_panic') && (bool) $unit->is_panic) {
            self::publish('panic', null, null);
            return;
        }

        if ($unit->wasChanged('status') && ! $unit->is_panic) {
            self::publish('unit_status', null, null);
        }

        if ($unit->wasChanged('description')
            && preg_match('/^Added to call:\\s*(\\d+)$/', trim((string) $unit->description), $matches)) {
            self::publish('call_assigned', (int) $unit->user_id, (int) $matches[1]);
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
