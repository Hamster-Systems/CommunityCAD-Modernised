<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Cad\CadAudioEvent;
use App\Support\Cad\CadAudio;
use Illuminate\Http\Request;

class CadAudioEventsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->active_unit, 403);

        $data = $request->validate([
            'since' => ['required', 'integer', 'min:0'],
        ]);

        $latest = (int) (CadAudioEvent::max('id') ?? 0);

        // A new browser tab starts at the live edge, not old event history.
        if ((int) $data['since'] === 0) {
            return response()->json(['cursor' => $latest, 'events' => []])
                ->header('Cache-Control', 'no-store');
        }

        $configuration = [];
        $events = CadAudioEvent::query()
            ->where('id', '>', (int) $data['since'])
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where(function ($q) use ($user) {
                $q->whereNull('target_user_id')
                    ->orWhere('target_user_id', $user->id);
            })
            ->orderBy('id')
            ->limit(40)
            ->get(['id', 'event', 'call_id'])
            ->map(function ($record) use (&$configuration) {
                if (! array_key_exists($record->event, CadAudio::EVENTS)) {
                    return null;
                }
                $settings = $configuration[$record->event]
                    ??= CadAudio::details($record->event);
                return [
                    'id' => $record->id,
                    'type' => $record->event,
                    'url' => $settings['mode'] === 'muted'
                        ? null : route('cad.audio.stream', ['event' => $record->event]),
                    'volume' => $settings['volume'] / 100,
                ];
            })
            ->filter()->values();

        // Advance to the latest event even for muted and untargeted events.
        return response()->json(['cursor' => $latest, 'events' => $events])
            ->header('Cache-Control', 'no-store');
    }
}
