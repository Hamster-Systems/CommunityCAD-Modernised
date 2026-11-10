# SMTP / Email administration (Issue #39 follow-up)

This feature is developed on `feature/admin-smtp-settings`, **stacked on** `feature/home-registration-linked-accounts` / PR #40. It must not be merged into `develop` until the account PR is merged or the stack is rebased.

## Behaviour
- Admin → Settings → **SMTP / Email** is restricted to owner users.
- Server details: SMTP host, port, STARTTLS/TLS or none, username and password.
- Sender identity: From address and From name.
- Password is stored using Laravel's `encrypted` Eloquent cast and never returned in the HTML form. It can be changed without redisplaying the old value.
- Saving disabled settings leaves existing `.env` mail configuration in control. Enabling stored SMTP overrides Laravel mail settings for subsequent HTTP requests.
- Test emails are sent to an administrator-supplied recipient via the configured SMTP server. The UI intentionally reports SMTP acceptance, not final inbox delivery.
- On an unconfigured/fresh install, Laravel can still run migrations without an SMTP table.

## Local Docker installation
Back up the **development** database before migrations.

```bash
cd ~/Documents/CommunityCAD-Modernised
git fetch origin
git switch --track origin/feature/admin-smtp-settings

cd ~/Documents/CommunityCAD-Dev
docker compose up -d --build --no-deps app
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan view:cache
```

If the local branch already exists, `git switch feature/admin-smtp-settings && git pull --ff-only origin feature/admin-smtp-settings` instead.

Log in as the configured owner and visit `/admin/settings/smtp`. Save a mail configuration with **Enabled**, send a test message and verify receipt. Then register a development test user and confirm email verification uses the same sender/SMTP service.

## Security/operations notes
- Never paste credentials, SMTP passwords, access tokens or full `.env` values in issue comments or screenshots.
- Keep a stable and backed-up `APP_KEY`; encrypted SMTP passwords cannot be decrypted after changing the key without controlled key rotation.
- Restrict access to trusted owner users. If SMTP is exposed in multi-tenant hosting later, add network egress controls and destination validation for host/port before enabling per-tenant SMTP.
- A test email does not guarantee delivery: check SPF/DKIM/DMARC, spam folders and provider logs.
- Queue workers may need a restart after changing settings; queued mail and long-running processes should be verified during QA.
- This feature does not introduce arbitrary server commands, template editing, or automated mail campaigns.

## Remaining work
- Test successful/failed SMTP connection in the Docker environment.
- Validate route authorisation and preservation of the encrypted password on blank edit.
- Verify successful email verification with SMTP enabled and `.env` fallback when disabled.
- Confirm UI on mobile and desktop and document providers' app-password requirements.
- Later PR: configurable email templates and optional delivery logs, without exposing sensitive message data.


## Branded HTML emails
- SMTP test messages now use `emails.smtp-test` with `emails.smtp-test-text` plain-text alternative.
- Local-account verification notifications now use `emails.verify-account` with `emails.verify-account-text`.
- Shared header/footer partials provide consistent CommunityCAD styling, with email-client-friendly table layout and inline CSS. Text is Blade-escaped; verification links are signed and unchanged.
- The existing configured From name is used for branding. No inline images or externally hosted assets are required, avoiding broken email logos.
- This is a code-based template milestone; admin-editable templates, previews and delivery history remain future work.

QA: send an SMTP test email to Thunderbird and other major clients; inspect multipart HTML/plain-text content, dark/light rendering, verify link behaviour and mobile layout. Run `php artisan view:cache` after deploying new Blade views.
