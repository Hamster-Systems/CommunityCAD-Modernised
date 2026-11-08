<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Civilian;
use App\Models\MdtIdentity;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MdtIdentityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_department_id' => ['required', 'integer'],
            'display_name' => ['nullable', 'required_without:civilian_id', 'string', 'max:100'],
            'callsign' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9 -]*$/'],
            'rank' => ['required', 'string', 'max:80'],
            'subdivision' => ['nullable', 'string', 'max:80', Rule::in(config('cad.mdt_subdivisions', []))],
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
        $displayName = trim($validated['display_name'] ?? '');
        if ($civilianId !== null) {
            $civilian = Civilian::whereKey($civilianId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
            $civilianId = $civilian->id;
            // Derive the display name from the owned civilian, not posted text.
            $firstName = trim($civilian->first_name);
            $lastName = trim($civilian->last_name);
            $displayName = mb_substr($firstName, 0, 1) . '. ' . $lastName;
        }

        MdtIdentity::create([
            'user_id' => $request->user()->id,
            'user_department_id' => $membership->id,
            'display_name' => $displayName,
            'callsign' => trim($validated['callsign']),
            'rank' => trim($validated['rank']),
            'subdivision' => $validated['subdivision'] ?? null,
            'civilian_id' => $civilianId,
        ]);

        return redirect()->route('cad.landing')
            ->with('alerts', [['message' => 'MDT identity created. Select it to access the CAD.', 'level' => 'success']]);
    }
}
