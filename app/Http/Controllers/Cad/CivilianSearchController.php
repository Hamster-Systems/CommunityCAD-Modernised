<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\CallCivilian;
use App\Models\Civilian;
use App\Support\Cad\CallActivityLogger;
use Illuminate\Http\Request;

class CivilianSearchController extends Controller
{
    public function search()
    {
        return view('cad.civilian_search.search');
    }

    public function return(Civilian $civilian)
    {
        $calls = Call::where('status', '!=', 'CLO')->where('status', 'not like', 'CLO-%')->orderBy('priority', 'desc')->get();

        return view('cad.civilian_search.return', compact('civilian', 'calls'));
    }

    public function link_civilian_to_call(Civilian $civilian, Call $call, Request $request)
    {
        $validated = $request->validate([
            'call_id' => 'required',
            'type' => 'required',
        ]);

        $targetCall = Call::findOrFail($validated['call_id']);
        CallCivilian::create([
            'call_id' => $validated['call_id'],
            'civilian_id' => $civilian->id,
            'type' => $validated['type'],
        ]);

        CallActivityLogger::record($targetCall, 'PERSON',
            'Person ' . trim($civilian->first_name . ' ' . $civilian->last_name) . ' linked as ' . $validated['type'] . '.');

        return redirect()->route('cad.name.return', $civilian->id)->with('alerts', [['message' => 'Civilian linked to call '.$call->id, 'level' => 'success']]);
    }
}
