<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Civilian;
use App\Models\MdtIdentity;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MdtIdentityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_department_id' => ['required', 'integer'],
            'display_name' => ['required', 'string', 'max:100'],
            'callsign' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9 -]*$/'],
            'rank' => ['required', 'string', 'max:80'],
            'civilian_id' => ['nullable', 'integer'],
        ]);

        // Never trust a posted department ID. It must be an existing
        // membership belonging to the authenticated user and MDT-eligible.
        $membership = UserDepartment::whereKey($validated['user_department_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($membership->department &&
            in_array((int) $membership->department->type, [1, 2, 4], true), 403);

        $civilianId = $validated['civilian_id'] ?? null;
        if ($civilianId !== null) {
            $civilian = Civilian::whereKey($civilianId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
            $civilianId = $civilian->id;
        }

        MdtIdentity::create([
            'user_id' => $request->user()->id,
            'user_department_id' => $membership->id,
            'display_name' => trim($validated['display_name']),
            'callsign' => trim($validated['callsign']),
            'rank' => trim($validated['rank']),
            'civilian_id' => $civilianId,
        ]);

        return redirect()->route('cad.landing')
            ->with('alerts', [['message' => 'MDT identity created. Select it to access the CAD.', 'level' => 'success']]);
    }
}
