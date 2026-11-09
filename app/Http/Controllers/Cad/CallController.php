<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cad\CallStoreRequest;
use App\Models\Cad\CallNatures;
use App\Models\Cad\CallStatuses;
use App\Models\Call;
use App\Models\CallCivilian;
use App\Support\Cad\CallActivityLogger;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(): View
    {
        $all_calls = Call::orderBy('id', 'desc')->get();

        $recent_calls = $all_calls->where('created_at', '>', Carbon::now()->subDays(2));
        $all_calls = $all_calls->where('created_at', '<', Carbon::now()->subDays(2));

        return view('cad.call.index', compact('all_calls', 'recent_calls'));
    }

    public function create(): View
    {
        return view('cad.call.create');
    }

    public function store(CallStoreRequest $request): RedirectResponse
    {

        $input = $request->validated();

        if (! isset($input['linked_civilians'])) {
            $call = Call::create($input);
            CallActivityLogger::record($call, 'CALL', 'Incident created.');

            return redirect()->route('cad.mdt');
        }

        $linked_civilians = $input['linked_civilians'];
        $linked_civilians_types = $input['linked_civilians_types'];
        unset($input['linked_civilians'], $input['linked_civilians_types']);
        $call = Call::create($input);
        CallActivityLogger::record($call, 'CALL', 'Incident created.');

        $index = 0;
        foreach ($linked_civilians as $civilian_id) {
            CallCivilian::create([
                'call_id' => $call->id,
                'civilian_id' => $civilian_id,
                'type' => $linked_civilians_types[$index],
            ]);
            $linked = \App\Models\Civilian::find($civilian_id);
            CallActivityLogger::record($call, 'PERSON', 'Person ' . ($linked ? trim($linked->first_name . ' ' . $linked->last_name) : '#' . $civilian_id) . ' linked as ' . $linked_civilians_types[$index] . '.');
            $index++;
        }

        return redirect()->route('cad.mdt');
    }

    public function show(Call $call): View
    {
        $call->load(['attached_units.mdtIdentity', 'attached_units.user_department.department', 'call_civilians']);
        $call_natures = CallNatures::NATURECODES;
        $call_statuses = CallStatuses::STATUSCODES;
        $rps = CallCivilian::where('call_id', $call->id)->where('type', 'RP')->get();
        $recentActivity = $call->call_log()->orderByDesc('created_at')->limit(50)->get();

        return view('cad.call.show', compact('call', 'call_natures', 'call_statuses', 'rps', 'recentActivity'));
    }

    public function edit(Call $call): RedirectResponse
    {
        return redirect()->route('cad.call.show', $call->id);
    }

    public function update(Request $request, Call $call)
    {
        $validated = $request->validate([
            'status' => 'required',
            'nature' => 'required',
            // 'location' => 'required',
            // 'city' => 'required',
            'priority' => 'required',
        ]);

        abort_if(in_array($validated['status'], ['ENRT', 'ARRVD'], true)
            && !$call->attached_units()->exists(), 422,
            'Attach at least one unit before setting an en-route or on-scene call status.');

        $old = $call->only(['status', 'nature', 'priority', 'location', 'city', 'narrative']);
        $call->update($validated);

        $changes = [];
        foreach (['status' => 'Status', 'nature' => 'Nature', 'priority' => 'Priority',
                  'location' => 'Location', 'city' => 'City', 'narrative' => 'Narrative'] as $field => $label) {
            if ((string) ($old[$field] ?? '') !== (string) ($call->$field ?? '')) {
                $changes[$label] = [$old[$field] ?? null, $call->$field];
            }
        }
        CallActivityLogger::changes($call, $changes);

        if (isset($changes['Status']) && ($call->status === 'CLO' || str_starts_with($call->status, 'CLO-'))) {
            foreach ($call->attached_units as $unit) {
                $unit->update(['status' => 'AVL']);
                $call->attached_units()->detach($unit->id);
                CallActivityLogger::record($call, 'UNIT', 'Unit ' . $unit->unit_callsign . ' released on call closure.');
            }
            CallActivityLogger::record($call, 'CALL', 'Incident closed; assigned units released.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Incident saved successfully.',
                'updated_at' => $call->updated_at?->toIso8601String(),
            ]);
        }

        return redirect()->route('cad.call.show', $call->id);
    }
}
