<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GlobalUnitStatusController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['AVL', 'BUSY', 'BRK', 'ENRUTE', 'ONSCN', 'TRANS'])],
        ]);

        $unit = $request->user()->active_unit;
        abort_unless($unit && (int) $unit->user_id === (int) $request->user()->id, 403);
        abort_if($unit->is_panic, 409, 'Clear the active panic before changing your status.');
        abort_if($unit->status === 'OFFDTY', 409, 'Go on duty before changing your status.');

        // Incident-specific response and transport states require a call.
        $assignedCall = $unit->calls()
            ->where('status', '!=', 'CLO')->where('status', 'not like', 'CLO-%')
            ->orderByDesc('calls.created_at')->first();
        if (in_array($data['status'], ['ENRUTE', 'ONSCN', 'TRANS'], true)) {
            abort_unless($assignedCall, 403, 'Attach to an open call before selecting this status.');
        }

        if ($unit->status !== $data['status']) {
            $unit->update([
                'status' => $data['status'],
                'description' => 'Unit status changed to ' . $data['status'],
            ]);
            if ($assignedCall) {
                CallLog::create([
                    'from' => $unit->log_identity,
                    'text' => 'Unit status changed to ' . $data['status'],
                    'call_id' => $assignedCall->id,
                ]);
                $assignedCall->touch();
            }
        }

        return redirect()->back();
    }
}
