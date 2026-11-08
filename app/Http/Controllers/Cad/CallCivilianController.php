<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\CallCivilian;
use App\Models\Civilian;
use App\Support\Cad\CallActivityLogger;

class CallCivilianController extends Controller
{
    public function add_civilian()
    {
    }

    public function remove_civilian(Call $call, Civilian $civilian)
    {
        $call_civilians = CallCivilian::where('call_id', $call->id)->where('civilian_id', $civilian->id)->get();

        foreach ($call_civilians as $call_civilian) {
            $call_civilian->delete();
        }

        if ($call_civilians->isNotEmpty()) {
            CallActivityLogger::record($call, 'PERSON', 'Person ' . trim($civilian->first_name . ' ' . $civilian->last_name) . ' removed from the incident.');
        }

        return redirect()->route('cad.call.show', $call->id);
    }
}
