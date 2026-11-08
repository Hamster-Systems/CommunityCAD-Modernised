<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Cad\ActiveUnit;
use App\Models\Department;
use App\Models\Officer;
use App\Models\UserDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AddUnitController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        if (isset(auth()->user()->active_unit)) {
            return redirect()->route('cad.home');
        }

        $validated = $request->validate([
            'mdt_identity_id' => ['required', 'integer'],
        ]);

        // The chosen identity must belong to the logged-in user. It must
        // also belong to an MDT-eligible department they currently hold.
        $identity = \App\Models\MdtIdentity::where('user_id', auth()->id())
            ->with('userDepartment.department')
            ->findOrFail($validated['mdt_identity_id']);

        $membership = $identity->userDepartment;
        abort_unless($membership && (string) $membership->user_id === (string) auth()->id()
            && $membership->department
            && in_array((int) $membership->department->type, [1, 2, 4], true), 403);

        // officer_id remains nullable for backwards compatibility with
        // legacy records; a civilian and legacy Officer are no longer required.
        ActiveUnit::create([
            'user_id' => auth()->id(),
            'user_department_id' => $membership->id,
            'mdt_identity_id' => $identity->id,
            'department_type' => $membership->department->type,
            'subdivision' => $identity->subdivision,
            'status' => 'OFFDTY',
            'description' => 'SIGNED IN: ' . date('G:i:s'),
        ]);

        return redirect()->route('cad.home');
    }
}
