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

        $service = app(\App\Support\Cad\CallUnitAssignmentService::class);
        if ($data['action'] === 'attach') {
            $service->attach($unit, $call);
        } else {
            $service->detach($unit, $call);
        }

        return redirect()->route('cad.call.show', $call->id);
    }
}
