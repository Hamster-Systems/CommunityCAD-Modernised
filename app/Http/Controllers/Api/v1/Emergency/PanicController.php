<?php

namespace App\Http\Controllers\Api\v1\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Emergency\PanicRequest;
use App\Models\Cad\ActiveUnit;
use App\Support\Cad\PanicResponseService;

class PanicController extends Controller
{
    public function panic(PanicRequest $request, PanicResponseService $panic)
    {
        // Preserve the existing API caller contract but reuse the same
        // transactional call/assignment logic as the browser button.
        $unit = ActiveUnit::where('user_id', $request->user_id)->first();

        if (! $unit) {
            return response()->json([
                'success' => false,
                'message' => 'No active unit found for the given user.',
                'data' => [],
            ], 404);
        }

        if ($request->boolean('is_panic')) {
            $panic->activate($unit, $request->location);
            $message = 'You have pressed your panic button.';
        } else {
            $panic->clear($unit);
            $message = 'You have cleared your panic button.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [],
        ]);
    }
}
