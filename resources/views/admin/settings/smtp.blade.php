@extends('layouts.admin_settings')

@section('content')
    <header class="settings-page-header">
        <div class="settings-eyebrow">Administration / Settings / Integrations</div>
        <h1 class="text-2xl font-bold text-white">SMTP / Email</h1>
        <p class="mt-2 text-sm text-slate-400">Configure outgoing email for account verification and future notifications.</p>
    </header>

    @if (session('smtp_status'))
        <div role="status" class="mb-5 rounded-lg border border-green-600 bg-green-950/40 p-4 text-green-100">{{ session('smtp_status') }}</div>
    @endif
    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-lg border border-red-500 bg-red-950/40 p-4 text-red-100">
            <strong>SMTP settings need attention</strong>
            <ul class="mt-2 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card settings-card">
        <form method="POST" action="{{ route('admin.settings.smtp.update') }}" class="space-y-6">
            @csrf
            <div class="pill p-4">
                <h2 class="text-lg font-semibold">Outgoing mail configuration</h2>
                <p class="mt-1 text-sm text-slate-400">SMTP credentials are stored encrypted. When disabled, Laravel uses the server's existing environment-based mail configuration.</p>
                <label for="smtp_enabled" class="mt-5 block text-sm font-semibold">Use admin-configured SMTP</label>
                <select id="smtp_enabled" name="enabled" class="select-input w-full" required>
                    <option value="0" @selected(old('enabled', $settings?->enabled ? '1' : '0') == '0')>Disabled — use server mail settings</option>
                    <option value="1" @selected(old('enabled', $settings?->enabled ? '1' : '0') == '1')>Enabled</option>
                </select>
            </div>
            <div class="pill p-4">
                <h2 class="mb-3 text-lg font-semibold">Server details</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="smtp_host" class="settings-field-label">SMTP hostname</label>
                        <input id="smtp_host" name="host" type="text" maxlength="255" required class="text-input w-full"
                            placeholder="smtp.example.com" value="{{ old('host', $settings?->host) }}" autocomplete="off">
                    </div>
                    <div>
                        <label for="smtp_port" class="settings-field-label">Port</label>
                        <input id="smtp_port" name="port" type="number" min="1" max="65535" required class="text-input w-full"
                            value="{{ old('port', $settings?->port ?? 587) }}">
                    </div>
                    <div>
                        <label for="smtp_encryption" class="settings-field-label">Connection security</label>
                        <select id="smtp_encryption" name="encryption" required class="select-input w-full">
                            <option value="tls" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'tls')>STARTTLS (usually 587)</option>
                            <option value="ssl" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'ssl')>TLS/SSL (usually 465)</option>
                            <option value="none" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'none')>None (trusted relay only)</option>
                        </select>
                    </div>
                    <div>
                        <label for="smtp_username" class="settings-field-label">SMTP username</label>
                        <input id="smtp_username" name="username" type="text" maxlength="255" class="text-input w-full"
                            autocomplete="off" value="{{ old('username', $settings?->username) }}">
                    </div>
                    <div>
                        <label for="smtp_password" class="settings-field-label">SMTP password</label>
                        <input id="smtp_password" name="password" type="password" maxlength="2048" class="text-input w-full"
                            autocomplete="new-password" placeholder="{{ $settings?->password ? 'Saved — leave blank to keep' : 'Enter your SMTP password' }}">
                        <p class="settings-help">Stored password is never shown. Leave blank to retain the existing secret.</p>
                    </div>
                </div>
            </div>
            <div class="pill p-4">
                <h2 class="mb-3 text-lg font-semibold">Sender identity</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="smtp_from_address" class="settings-field-label">From email address</label>
                        <input id="smtp_from_address" name="from_address" type="email" maxlength="255" required
                            class="text-input w-full" value="{{ old('from_address', $settings?->from_address) }}" placeholder="no-reply@example.com">
                    </div>
                    <div>
                        <label for="smtp_from_name" class="settings-field-label">From name</label>
                        <input id="smtp_from_name" name="from_name" type="text" maxlength="255" required
                            class="text-input w-full" value="{{ old('from_name', $settings?->from_name ?? get_setting('community_name')) }}">
                    </div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="secondary-button-md settings-save-button">Save SMTP settings</button>
            </div>
        </form>
    </div>

    <div class="card settings-card mt-5">
        <div class="pill p-4">
            <h2 class="text-lg font-semibold">Send a test email</h2>
            <p class="mt-1 text-sm text-slate-400">Save and enable SMTP first. A successful send means the SMTP server accepted the message; inbox delivery may take longer.</p>
            <form method="POST" action="{{ route('admin.settings.smtp.test') }}" class="mt-4 space-y-3">
                @csrf
                <label for="smtp_test_recipient" class="settings-field-label">Recipient email</label>
                <input id="smtp_test_recipient" type="email" name="recipient" class="text-input w-full"
                    maxlength="255" required placeholder="you@example.com" value="{{ old('recipient') }}">
                <button type="submit" class="secondary-button-md" @disabled(!($settings?->enabled))>Send test email</button>
            </form>
        </div>
    </div>
    <p class="mt-5 text-sm text-slate-400">Email templates and delivery history will be introduced in a later enhancement.</p>
@endsection
