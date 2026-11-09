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
