<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\CadSetting;
use App\Models\DiscordChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function general()
    {
        return view('admin.settings.general');
    }

    public function roleplay()
    {
        return view('admin.settings.roleplay');
    }

    public function application()
    {
        return view('admin.settings.application');
    }

    public function cad()
    {
        return view('admin.settings.cad');
    }

    public function discord()
    {
        $discord_channels = DiscordChannel::all()->pluck('channel_id', 'name')->toArray();

        return view('admin.settings.discord', compact('discord_channels'));
    }

    public function discord_roles()
    {
        // $discord_channels = DiscordChannel::all()->pluck('channel_id', 'name')->toArray();
        $discord_roles = [];

        if (get_setting('use_discord_roles')) {
            $response =
                Http::accept('application/json')
                ->withHeaders(['Authorization' => config('app.discord_bot_token')])
                ->get('https://discord.com/api/guilds/' . get_setting('discord_guild_id') . '/roles');

            $discord_roles = json_decode($response->body());
        }
        return view('admin.settings.discord_roles', compact('discord_roles'));
    }

    public function api_key()
    {
        if (!in_array(auth()->user()->id, config('cad.owner_ids'))) {
            return redirect()->route('admin.settings.general')->with('alerts', [['message' => 'API Key is only available to owners.', 'level' => 'error']]);
        }

        return view('admin.settings.api_key');
    }

    public function generate_api_key()
    {
        if (!in_array(auth()->user()->id, config('cad.owner_ids'))) {
            return redirect()->route('admin.settings.general')->with('alerts', [['message' => 'API Key is only available to owners.', 'level' => 'error']]);
        }
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $key = substr(str_shuffle(str_repeat($pool, 64)), 0, 64);
        CadSetting::updateOrCreate(
            ['name' => 'api_key'],
            ['value' => $key, 'type' => 'text']
        );

        Cache::forget('cad_settings');

        return redirect()->route('admin.settings.api_key')->with('alerts', [['message' => 'API Key Generated.', 'level' => 'success']]);
    }

    public function update(Request $request)
    {
        // The same General Settings form supports either a remote image URL
        // or an uploaded image. If both are provided, the upload takes priority.
        $hasLogoUpload = $request->hasFile('community_logo_file');

        $validator = Validator::make($request->all(), [
            'community_logo_file' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // An uploaded image takes precedence, so a stale URL should
            // not invalidate a valid file upload.
            'community_logo' => $hasLogoUpload
                ? ['nullable']
                : ['sometimes', 'nullable', 'url', 'max:2048'],
        ]);

        if ($validator->fails()) {
            // The shared /admin/settings POST route has no GET equivalent.
            // Never rely on redirect()->back() for validation failures here.
            return redirect()->route('admin.settings.general')
                ->withErrors($validator)
                ->withInput($request->except('community_logo_file'));
        }

        $data = $request->except('_token', 'community_logo_file');

        if ($request->hasFile('community_logo_file')) {
            $path = $request->file('community_logo_file')->store('branding', 'public');

            if (!$path) {
                return redirect()->route('admin.settings.general')
                    ->withErrors(['community_logo_file' => 'Unable to store the uploaded logo.'])
                    ->withInput($request->except('community_logo_file'));
            }

            $data['community_logo'] = Storage::disk('public')->url($path);
        }

        foreach ($data as $key => $value) {
            CadSetting::updateOrCreate(
                ['name' => $key],
                ['value' => $value, 'type' => 'text']
            );
        }

        Cache::forget('cad_settings');

        return redirect()->route('admin.settings.general')->with('alerts', [['message' => 'Settings Saved.', 'level' => 'success']]);
    }

    public function update_discord(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            DiscordChannel::updateOrCreate(
                ['name' => $key],
                ['channel_id' => $value, 'type' => 'text']
            );
        }

        return redirect()->route('admin.settings.discord')->with('alerts', [['message' => 'Discord Channels Updated.', 'level' => 'success']]);
    }
}
