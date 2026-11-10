@extends('layouts.admin_settings')

@section('content')
<style>
    /* Scoped SMTP admin styles — no impact on other Settings or CAD pages. */
    .smtp-page{width:100%;max-width:1050px;margin:0 auto;color:#f8fafc}
    .smtp-page *, .smtp-page *::before, .smtp-page *::after{box-sizing:border-box}
    .smtp-page-header{margin-bottom:22px}
    .smtp-page-header h1{font-size:26px;font-weight:750;line-height:1.2;margin:7px 0}
    .smtp-eyebrow{font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#9ab2d5;font-weight:750}
    .smtp-subtitle{font-size:13px;color:#aabacf;line-height:1.5}
    .smtp-panel{border:1px solid #2a3b53;background:#121f30;border-radius:14px;padding:23px 26px;margin-bottom:16px}
    .smtp-panel-title{font-size:16px;font-weight:700;color:#fff}
    .smtp-panel-description{font-size:12px;color:#9fb1c8;margin-top:5px;line-height:1.5}
    .smtp-section{padding:19px 0;border-bottom:1px solid #2a3b53}
    .smtp-section:first-child{padding-top:0}
    .smtp-section:last-child{border-bottom:0;padding-bottom:0}
    .smtp-section-heading{font-weight:700;font-size:14px;margin-bottom:13px}
    .smtp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 18px}
    .smtp-field{min-width:0}
    .smtp-field label{display:block;font-size:12px;font-weight:650;margin:0 0 7px;color:#e8eef7}
    .smtp-field input,.smtp-field select{display:block;width:100%;max-width:100%;min-width:0;height:42px;padding:9px 12px;border-radius:8px;border:1px solid #3a506c;background:#0b1626;color:#fff;font:inherit;font-size:13px}
    .smtp-field input::placeholder{color:#869bb6}
    .smtp-field input:focus,.smtp-field select:focus{outline:2px solid #79b6ff;outline-offset:1px;border-color:#79b6ff}
    .smtp-field .smtp-help{font-size:11px;line-height:1.45;margin-top:7px;color:#a4b6cc}
    .smtp-span-2{grid-column:span 2}
    .smtp-status-row{display:flex;gap:16px;justify-content:space-between;align-items:center;flex-wrap:wrap}
    .smtp-status-row .smtp-field{min-width:min(100%,320px);flex:0 1 320px}
    .smtp-pill{display:inline-flex;align-items:center;padding:5px 10px;border:1px solid #4d6585;border-radius:99px;color:#cfdef0;font-size:11px}
    .smtp-actions{display:flex;align-items:center;justify-content:flex-end;padding-top:18px}
    .smtp-button{appearance:none;display:inline-flex;justify-content:center;align-items:center;min-height:41px;padding:10px 19px;background:#6259ec;color:#fff;border:1px solid #786fff;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;text-decoration:none}
    .smtp-button:hover{background:#5349dc}
    .smtp-button:focus-visible{outline:3px solid #8ecaff;outline-offset:2px}
    .smtp-button:disabled{opacity:.48;cursor:not-allowed}
    .smtp-test-form{display:flex;gap:12px;align-items:flex-end;margin-top:16px}
    .smtp-test-form .smtp-field{flex:1}
    .smtp-message{padding:12px 15px;border-radius:8px;margin-bottom:14px;font-size:12px}
    .smtp-message-success{border:1px solid #278b67;background:#103a30;color:#d4ffed}
    .smtp-message-error{border:1px solid #b84c5b;background:#451c2a;color:#ffe0e3}
    .smtp-page-footer{color:#94a7bf;font-size:11px;margin:12px 2px 24px}
    @media(max-width:650px){
        .smtp-panel{padding:17px 16px}
        .smtp-grid{grid-template-columns:1fr;gap:13px}
        .smtp-span-2{grid-column:auto}
        .smtp-status-row .smtp-field{flex:1 1 100%;min-width:0}
        .smtp-test-form{flex-direction:column;align-items:stretch}
        .smtp-test-form .smtp-field{width:100%}
        .smtp-button{width:100%}
    }
</style>
<div class="smtp-page">
    <header class="smtp-page-header">
        <div class="smtp-eyebrow">Administration / Settings / Integrations</div>
        <h1>SMTP / Email</h1>
        <p class="smtp-subtitle">Configure outgoing email for verification, account recovery and community notifications.</p>
    </header>

    @if(session('smtp_status'))
        <div role="status" class="smtp-message smtp-message-success">{{ session('smtp_status') }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="smtp-message smtp-message-error">
            <strong>Please review these settings:</strong>
            <ul class="list-disc pl-5 mt-2">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="smtp-panel">
        <form method="POST" action="{{ route('admin.settings.smtp.update') }}">
            @csrf
            <section class="smtp-section">
                <div class="smtp-status-row">
                    <div>
                        <h2 class="smtp-panel-title">Outgoing email</h2>
                        <p class="smtp-panel-description">Use your own SMTP server or keep the server's existing mail configuration.</p>
                    </div>
                    <div class="smtp-field">
                        <label for="smtp_enabled">Mail configuration</label>
                        <select id="smtp_enabled" name="enabled" required>
                            <option value="0" @selected(old('enabled', $settings?->enabled ? '1' : '0') == '0')>Server default (SMTP disabled)</option>
                            <option value="1" @selected(old('enabled', $settings?->enabled ? '1' : '0') == '1')>Use custom SMTP</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="smtp-section">
                <h2 class="smtp-section-heading">SMTP server</h2>
                <div class="smtp-grid">
                    <div class="smtp-field">
                        <label for="smtp_host">Hostname</label>
                        <input id="smtp_host" name="host" type="text" maxlength="255" required autocomplete="off"
                            placeholder="smtp.example.com" value="{{ old('host', $settings?->host) }}">
                    </div>
                    <div class="smtp-field">
                        <label for="smtp_port">Port</label>
                        <input id="smtp_port" name="port" type="number" min="1" max="65535" required
                            value="{{ old('port', $settings?->port ?? 587) }}">
                    </div>
                    <div class="smtp-field">
                        <label for="smtp_encryption">Connection security</label>
                        <select id="smtp_encryption" name="encryption" required>
                            <option value="tls" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'tls')>STARTTLS (usually port 587)</option>
                            <option value="ssl" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'ssl')>TLS / SSL (usually port 465)</option>
                            <option value="none" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'none')>No encryption (trusted relay only)</option>
                        </select>
                    </div>
                    <div class="smtp-field">
                        <label for="smtp_username">SMTP username</label>
                        <input id="smtp_username" name="username" type="text" maxlength="255" autocomplete="off"
                            value="{{ old('username', $settings?->username) }}" placeholder="Optional for local relays">
                    </div>
                    <div class="smtp-field smtp-span-2">
                        <label for="smtp_password">SMTP password</label>
                        <input id="smtp_password" name="password" type="password" maxlength="2048" autocomplete="new-password"
                            placeholder="{{ $settings?->password ? 'Password saved — leave empty to keep it' : 'Enter SMTP password or app password' }}">
                        <p class="smtp-help">The saved password is encrypted and is never shown. Leave blank to keep the existing value.</p>
                    </div>
                </div>
            </section>

            <section class="smtp-section">
                <h2 class="smtp-section-heading">Sender identity</h2>
                <div class="smtp-grid">
                    <div class="smtp-field">
                        <label for="smtp_from_address">From email address</label>
                        <input id="smtp_from_address" name="from_address" type="email" maxlength="255" required
                            placeholder="no-reply@example.com" value="{{ old('from_address', $settings?->from_address) }}">
                    </div>
                    <div class="smtp-field">
                        <label for="smtp_from_name">From display name</label>
                        <input id="smtp_from_name" name="from_name" type="text" maxlength="255" required
                            value="{{ old('from_name', $settings?->from_name ?? get_setting('community_name')) }}">
                    </div>
                </div>
            </section>
            <div class="smtp-actions">
                <button type="submit" class="smtp-button">Save SMTP settings</button>
            </div>
        </form>
    </div>

    <div class="smtp-panel">
        <h2 class="smtp-panel-title">Send a test email</h2>
        <p class="smtp-panel-description">Check your connection after saving the settings. SMTP acceptance does not guarantee inbox delivery.</p>
        <form method="POST" action="{{ route('admin.settings.smtp.test') }}" class="smtp-test-form">
            @csrf
            <div class="smtp-field">
                <label for="smtp_test_recipient">Recipient email</label>
                <input id="smtp_test_recipient" type="email" name="recipient" maxlength="255" required
                    placeholder="you@example.com" value="{{ old('recipient') }}">
            </div>
            <button type="submit" class="smtp-button" @disabled(!($settings?->enabled))>Send test email</button>
        </form>
    </div>
    <p class="smtp-page-footer">Email templates and delivery logs are planned for a future update.</p>
</div>
@endsection
