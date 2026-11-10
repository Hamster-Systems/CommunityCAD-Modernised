<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\CadSetting;
use App\Support\Cad\CadAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CadAudioController extends Controller
{
    public function index()
    {
        $events = [];
        foreach (array_keys(CadAudio::EVENTS) as $event) {
            $events[$event] = CadAudio::details($event);
        }

        return view('admin.cad_audio.index', compact('events'));
    }

    public function update(Request $request, string $event)
    {
        abort_unless(isset(CadAudio::EVENTS[$event]), 404);
        $validated = $request->validate([
            'mode' => ['required', 'in:custom,muted'],
            'volume' => ['required', 'integer', 'between:0,100'],
            'sound' => ['nullable', 'file', 'mimes:mp3,ogg,wav', 'max:2048'],
        ]);

        $previous = CadAudio::details($event);
        // Every enabled notification already has a bundled default sound.
        // Uploading a file automatically enables Custom, even if the last
        // saved state was Muted. No preliminary mode change is required.
        if ($request->hasFile('sound')) {
            $validated['mode'] = 'custom';
        }

        $newPath = null;
        if ($request->hasFile('sound')) {
            $upload = $request->file('sound');
            $mime = $upload->getMimeType();
            $allowed = [
                'audio/mpeg' => 'mp3',
                'audio/mp3' => 'mp3',
                'audio/ogg' => 'ogg',
                'application/ogg' => 'ogg',
                'audio/wav' => 'wav',
                'audio/x-wav' => 'wav',
                'audio/wave' => 'wav',
                'audio/vnd.wave' => 'wav',
            ];
            if (! isset($allowed[$mime])) {
                throw ValidationException::withMessages([
                    'sound' => 'Only recognised MP3, OGG and WAV audio files are accepted.',
                ]);
            }

            // Never trust the uploaded file name or write it into public/.
            // Files use generated names inside the existing persistent app storage.
            $newPath = Storage::disk('local')->putFileAs(
                'cad-audio/' . $event,
                $upload,
                Str::uuid() . '.' . $allowed[$mime]
            );
            if (! $newPath) {
                throw ValidationException::withMessages(['sound' => 'The audio file could not be saved.']);
            }
        }

        try {
            DB::transaction(function () use ($event, $validated, $newPath, $previous) {
                foreach ([
                'mode' => $validated['mode'],
                'volume' => (string) $validated['volume'],
                'file' => $newPath ?: ($previous['file'] ?? ''),
            ] as $attribute => $value) {
                CadSetting::updateOrCreate(
                    ['name' => 'cad_audio_' . $event . '_' . $attribute],
                    ['value' => $value ?? '', 'type' => 'text']
                );
                }
            });
            Cache::forget('cad_settings');
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            throw $exception;
        }

        // Muting must NOT delete the user's uploaded recording.
        if ($newPath && $previous['file'] && $previous['file'] !== $newPath) {
            Storage::disk('local')->delete($previous['file']);
        }

        return redirect()->route('admin.cad_audio.index')
            ->with('audio_success', CadAudio::EVENTS[$event]['title'] . ' settings saved.');
    }

    public function reset(string $event)
    {
        abort_unless(isset(CadAudio::EVENTS[$event]), 404);
        $previous = CadAudio::details($event);
        DB::transaction(function () use ($event) {
            foreach (['mode' => 'custom', 'volume' => '100', 'file' => ''] as $attribute => $value) {
                CadSetting::updateOrCreate(
                    ['name' => 'cad_audio_' . $event . '_' . $attribute],
                    ['value' => $value, 'type' => 'text']
                );
            }
        });
        Cache::forget('cad_settings');
        if ($previous['file']) {
            Storage::disk('local')->delete($previous['file']);
        }

        return redirect()->route('admin.cad_audio.index')
            ->with('audio_success', CadAudio::EVENTS[$event]['title'] . ' restored to default.');
    }

    public function stream(string $event)
    {
        abort_unless(isset(CadAudio::EVENTS[$event]), 404);
        // Authenticated CAD users or administrators can hear configured audio;
        // arbitrary file paths never originate in request parameters.
        abort_unless(auth()->user()?->active_unit || auth()->user()?->can('cad_settings'), 403);

        $settings = CadAudio::details($event);
        // Administrators may inspect the saved recording even while a
        // notification is muted. The live CAD playback route must remain
        // unavailable when muted.
        $isAdminPreview = request()->routeIs('admin.cad_audio.preview');
        abort_if($settings['mode'] === 'muted' && ! $isAdminPreview, 404);

        if ($settings['file']) {
            $path = Storage::disk('local')->path($settings['file']);
        } else {
            $path = public_path('audio/' . CadAudio::EVENTS[$event]['default']);
        }

        abort_unless(is_file($path), 404);
        $mime = match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'wav' => 'audio/wav',
            default => 'application/octet-stream',
        };

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
