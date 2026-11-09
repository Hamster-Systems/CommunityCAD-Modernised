<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use App\Support\Cad\CallAssignmentStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CallUnitActionController extends Controller
{
    public function __invoke(Request $request, Call $call): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:attach,detach'],
        ]);

        $unit = $request->user()->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) $request->user()->id, 403);
        abort_if((int) $unit->department_type === 2, 403);

        DB::transaction(function () use ($call, $unit, $data) {
            $attached = $call->attached_units()->whereKey($unit->id)->exists();
            if ($data['action'] === 'attach' && !$attached) {
                abort_if(str_starts_with($unit->status, 'OFFDTY'), 409,
                    'Select Available before assigning your unit.');
                abort_if($call->status === 'CLO' || str_starts_with($call->status, 'CLO-'), 422);
                $call->attached_units()->syncWithoutDetaching([$unit->id]);
                $unit->update(['description' => 'Added to call: ' . $call->id]);
                CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign . ' assigned to the call.');
            } elseif ($data['action'] === 'detach' && $attached) {
                abort_if($unit->is_panic, 409, 'Clear the unit panic before detaching.');
                $call->attached_units()->detach($unit->id);
                $unit->update(['description' => 'Removed from call: ' . $call->id]);
                CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign . ' unassigned from the call.');
                CallAssignmentStatusService::reopenIfUnassigned($call);
            }
        });

        return redirect()->route('cad.call.show', $call->id);
    }
}
