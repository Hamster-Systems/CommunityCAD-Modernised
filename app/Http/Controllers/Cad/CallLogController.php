<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Support\Cad\CallActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CallLogController extends Controller
{
    public function store(Call $call, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
        ]);

        CallActivityLogger::record($call, 'NOTE', $validated['text']);

        return redirect()->route('cad.call.show', $call->id);
    }
}
