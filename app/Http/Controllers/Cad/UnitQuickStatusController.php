<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnitQuickStatusController extends Controller
{
    public function __invoke(Request $request, Call $call): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:AVL,ENRUTE,ONSCN,BRK'],
        ]);

        $unit = $request->user()->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) $request->user()->id, 403);
        abort_if($unit->is_panic, 409, 'Clear the active panic before changing unit status.');

        // Changing response status from an incident is only possible when
        // attached to that incident; 'available' remains a general status.
        if ($data['status'] !== 'AVL') {
            abort_unless($call->attached_units()->whereKey($unit->id)->exists(), 403);
        }
        if ($unit->status !== $data['status']) {
            $priorStatus = $unit->status;
            $unit->update([
                'status' => $data['status'],
                'description' => 'Status Set To: ' . $data['status'],
            ]);
            CallActivityLogger::record($call, 'UNIT',
                'Unit ' . $unit->unit_callsign . ' status changed from ' . $priorStatus . ' to ' . $data['status'] . '.');
        }

        return redirect()->route('cad.call.show', $call->id);
    }
}
