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
    private function eligibleMembership(Request $request, int $id): UserDepartment
    {
        $membership = UserDepartment::whereKey($id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        abort_unless($membership->department &&
            in_array((int) $membership->department->type, [1, 2, 4], true), 403);

        return $membership;
    }

    private function validateIdentity(Request $request): array
    {
        return $request->validate([
            'user_department_id' => ['required', 'integer'],
            'display_name' => ['nullable', 'required_without:civilian_id', 'string', 'max:100'],
            'callsign' => ['required', 'string', 'max:32', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9 -]*$/'],
            'rank' => ['required', 'string', 'max:80'],
            'subdivisions' => ['nullable', 'array', 'max:8'],
            'subdivisions.*' => ['required', 'string', 'max:80', 'distinct'],
            'civilian_id' => ['nullable', 'integer'],
        ]);
    }

    private function identityValues(Request $request, array $validated): array
    {
        $membership = $this->eligibleMembership($request, (int) $validated['user_department_id']);
        $civilianId = $validated['civilian_id'] ?? null;
        $displayName = trim($validated['display_name'] ?? '');

        if ($civilianId !== null) {
            // Ensure the linked character belongs to this user. Never trust
            // the posted full name when a civilian is selected.
            $civilian = Civilian::whereKey($civilianId)
                ->where('user_id', $request->user()->id)
                ->firstOrFail();

            $displayName = mb_substr(trim($civilian->first_name), 0, 1)
                . '. ' . trim($civilian->last_name);
            $civilianId = $civilian->id;
        }

        $subdivisions = array_values($validated['subdivisions'] ?? []);
        $allowed = $membership->department->mdt_subdivisions ?? [];
        foreach ($subdivisions as $subdivision) {
            if (! in_array($subdivision, $allowed, true)) {
                throw \\Illuminate\\Validation\\ValidationException::withMessages([
                    'subdivisions' => 'One or more subdivisions are not available in the selected department.',
                ]);
            }
        }
        return [
            'user_department_id' => $membership->id,
            'display_name' => $displayName,
            'callsign' => trim($validated['callsign']),
            'rank' => trim($validated['rank']),
            'civilian_id' => $civilianId,
            'subdivisions' => $subdivisions,
            // Keep the legacy single-value field for existing CAD screens.
            'subdivision' => $subdivisions[0] ?? null,
        ];
    }

    public function store(Request $request)
    {
        $validated = $this->validateIdentity($request);

        MdtIdentity::create(array_merge(
            ['user_id' => $request->user()->id],
            $this->identityValues($request, $validated)
        ));

        return redirect()->route('cad.landing')
            ->with('alerts', [['message' => 'MDT identifier created.', 'level' => 'success']]);
    }

    public function edit(Request $request)
    {
        $validated = $request->validate(['mdt_identity_id' => ['required', 'integer']]);

        $identity = MdtIdentity::where('user_id', $request->user()->id)
            ->findOrFail($validated['mdt_identity_id']);

        $this->eligibleMembership($request, (int) $identity->user_department_id);

        $available_departments = UserDepartment::where('user_id', $request->user()->id)
            ->with('department')->get()->filter(fn ($membership) => $membership->department &&
                in_array((int) $membership->department->type, [1, 2, 4], true));

        $civilians = Civilian::where('user_id', $request->user()->id)
            ->orderBy('first_name')->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        $subdivisions = $available_departments->flatMap(fn ($membership) => $membership->department->mdt_subdivisions ?? [])->unique()->values()->all();

        return view('cad.identifier-edit', compact('identity', 'available_departments', 'civilians', 'subdivisions'));
    }

    public function update(Request $request, MdtIdentity $identity)
    {
        abort_unless((string) $identity->user_id === (string) $request->user()->id, 404);
        $this->eligibleMembership($request, (int) $identity->user_department_id);

        $validated = $this->validateIdentity($request);
        $identity->update($this->identityValues($request, $validated));

        return redirect()->route('cad.landing')
            ->with('alerts', [['message' => 'MDT identifier updated. Changes apply on your next sign-in.', 'level' => 'success']]);
    }
}
