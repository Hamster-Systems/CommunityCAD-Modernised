<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\SmtpSetting;
use App\Support\Mail\SmtpConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SmtpSettingsController extends Controller
{
    public function index()
    {
        $settings = SmtpSetting::query()->first();

        return view('admin.settings.smtp', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'host' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.:-]+$/'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:2048'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $settings = SmtpSetting::query()->first() ?: new SmtpSetting();
        $password = $data['password'] ?? null;
        unset($data['password']);

        $settings->fill($data);

        // A blank password means leave the stored credential unchanged.
        // Never echo it back into the form or return it in a response.
        if ($password !== null && $password !== '') {
            $settings->password = $password;
        }

        $settings->save();

        // The next HTTP request and worker boot use the persisted settings.
        // Applying immediately allows an authorised administrator to test.
        if ($settings->enabled) {
            SmtpConfiguration::apply($settings);
        }

        return redirect()->route('admin.settings.smtp')
            ->with('smtp_status', 'SMTP settings saved. Send a test email to verify delivery.');
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient' => ['required', 'email', 'max:255'],
        ]);

        $settings = SmtpSetting::query()->first();
        if (!$settings || !$settings->enabled) {
            return redirect()->route('admin.settings.smtp')
                ->withErrors(['recipient' => 'Enable and save SMTP settings before sending a test.']);
        }

        try {
            SmtpConfiguration::apply($settings);
            Mail::send(
                ['html' => 'emails.smtp-test', 'text' => 'emails.smtp-test-text'],
                ['communityName' => $settings->from_name],
                function ($message) use ($data) {
                    $message->to($data['recipient'])->subject('CommunityCAD SMTP test');
                }
            );
        } catch (\Throwable $exception) {
            // Do not expose transport exceptions or passwords to the browser.
            // Admins can diagnose their SMTP host, port and server logs.
            return redirect()->route('admin.settings.smtp')
                ->withErrors(['recipient' => 'The test email could not be sent. Check SMTP configuration and the server logs.']);
        }

        return redirect()->route('admin.settings.smtp')
            ->with('smtp_status', 'The SMTP server accepted the test message. Check the recipient inbox for delivery.');
    }
}
