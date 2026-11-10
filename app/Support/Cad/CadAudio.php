<?php

namespace App\Support\Cad;

use App\Models\Admin\CadSetting;
use Illuminate\Support\Facades\Storage;

class CadAudio
{
    public const EVENTS = [
        'call_assigned' => ['title' => 'Call assignment', 'description' => 'A unit is attached or dispatched to a call.', 'default' => 'addedtocall.mp3'],
        'new_call' => ['title' => 'New dispatch', 'description' => 'A new incident is created.', 'default' => 'newcall.mp3'],
        'dispatch_closed' => ['title' => 'Dispatch closed', 'description' => 'An assigned incident is closed.', 'default' => 'statusupdated.mp3'],
        'dispatch_note' => ['title' => 'Dispatch note', 'description' => 'Dispatch adds a note or operational update to an incident.', 'default' => 'statusupdated.mp3'],
        'call_updated' => ['title' => 'Call status updated', 'description' => 'An incident changes status or core call details.', 'default' => 'statusupdated.mp3'],
        'call_activity_updated' => ['title' => 'Call Updated', 'description' => 'An attached call receives a new note or a responder joins, leaves or changes response status. Separate from call status updates.', 'default' => 'statusupdated.mp3'],
        'unit_status' => ['title' => 'Unit status updated by dispatch', 'description' => 'A dispatcher or authorised user changes another unit\'s status.', 'default' => 'statusupdated.mp3'],
        'unit_status_self' => ['title' => 'Unit status updated (self)', 'description' => 'The signed-in unit changes its own status. Upload a recording saying “Your status has been updated.”', 'default' => 'statusupdated.mp3'],
        'panic' => ['title' => 'Panic alert', 'description' => 'A unit activates its emergency panic button.', 'default' => 'panic_button.mp3'],
    ];

    public static function setting(string $event, string $attribute, ?string $default = null): ?string
    {
        if (! isset(self::EVENTS[$event]) || ! in_array($attribute, ['mode', 'volume', 'file'], true)) {
            return $default;
        }

        return CadSetting::query()->where('name', 'cad_audio_' . $event . '_' . $attribute)
            ->value('value') ?? $default;
    }

    public static function details(string $event): array
    {
        abort_unless(isset(self::EVENTS[$event]), 404);
        // Legacy "default" settings now behave like Custom with the bundled
        // fallback. Preserve muted and existing user-uploaded file settings.
        $mode = self::setting($event, 'mode', 'custom');
        if (! in_array($mode, ['custom', 'muted'], true)) {
            $mode = 'custom';
        }
        $volume = (int) self::setting($event, 'volume', '100');
        $file = self::setting($event, 'file');
        $hasCustom = $file
            && preg_match('/^cad-audio\\/' . preg_quote($event, '/') . '\\/[0-9a-f-]{36}\\.(mp3|ogg|wav)$/', $file)
            && Storage::disk('local')->exists($file);

        return array_merge(self::EVENTS[$event], [
            'mode' => $mode,
            'volume' => max(0, min(100, $volume)),
            'file' => $hasCustom ? $file : null,
            'has_custom' => (bool) $hasCustom,
        ]);
    }

    public static function url(string $event): ?string
    {
        return self::details($event)['mode'] === 'muted'
            ? null
            : route('cad.audio.stream', ['event' => $event]);
    }

    public static function volume(string $event): float
    {
        return self::details($event)['volume'] / 100;
    }
}
