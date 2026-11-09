<?php

namespace App\Support\Cad;

use App\Models\Call;

/**
 * Reopen an in-progress response when the last assigned unit departs.
 * Never rewrite a cleared, closed, or on-hold call.
 */
class CallAssignmentStatusService
{
    public static function reopenIfUnassigned(Call $call): void
    {
        $call = Call::query()->lockForUpdate()->findOrFail($call->id);

        // A manually detached panicked unit must not leave an orphaned
        // PANIC incident behind. Preserve the old status if another unit
        // remains attached, otherwise reopen an active response.
        if ($call->status === 'PANIC') {
            app(PanicResponseService::class)->restoreCallIfSafe($call);
            $call->refresh();
        }

        if (!in_array($call->status, ['ENRT', 'ARRVD'], true)
            || $call->attached_units()->exists()) {
            return;
        }

        $previous = $call->status;
        $call->update(['status' => 'RCVD']);

        CallActivityLogger::record($call, 'CALL',
            'Call reopened (RCVD) because no units remain assigned; previous status: ' . $previous . '.');
    }
}
