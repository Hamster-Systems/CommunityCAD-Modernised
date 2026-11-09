<?php

namespace App\Support\Cad;

use App\Models\Admin\CadSetting;
use Illuminate\Support\Facades\Storage;

class CadAudio
{
    public const EVENTS = [
        'call_assigned' => ['title' => 'Call assignment', 'description' => 'A unit is attached or dispatched to a call.', 'default' => 'addedtocall.mp3'],
        'new_call' => ['title' => 'New call', 'description' => 'A new incident is created.', 'default' => 'newcall.mp3'],
        'call_updated' => ['title' => 'Call status updated', 'description' => 'An incident changes status.', 'default' => 'statusupdated.mp3'],
        'unit_status' => ['title' => 'Unit status updated', 'description' => 'A unit changes its response status.', 'default' => 'statusupdated.mp3'],
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
        $mode = self::setting($event, 'mode', 'default');
        if (! in_array($mode, ['default', 'custom', 'muted'], true)) {
            $mode = 'default';
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
