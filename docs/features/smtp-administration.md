# SMTP administration

This isolated feature is based directly on `develop` so it can ship independently of unfinished first-party account registration (PR #40).

It adds owner-only SMTP/email configuration under Admin → Settings, with an encrypted password, SMTP connectivity test, HTML and text test email, and fallback to the existing environment mail configuration when custom SMTP is disabled.

## Deploy to Docker development

Back up your development database before running an additive migration.

```bash
cd ~/Documents/CommunityCAD-Modernised
git fetch origin
git switch develop
git pull --ff-only origin develop

cd ~/Documents/CommunityCAD-Dev
docker compose up -d --build --no-deps app
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
docker compose exec -T app php artisan view:cache
```

## Verification checklist
- Owner-only access to SMTP settings, updates and test endpoint; other admin users are denied
- Host, port, encryption, sender and credentials persist; blank password means keep saved secret
- Password is encrypted at rest and never included in page HTML
- An SMTP test email reaches an external mailbox in both HTML and plain-text representations
- Disabling custom SMTP returns Laravel to existing `.env` mail settings
- Migration and first install work without an existing SMTP configuration

**Scope:** This standalone PR intentionally omits the account verification email template and changes to first-party account registration. Once PR #40 is merged, its verification email can use saved SMTP automatically; the branded verification template still lives in the stacked feature branch and can be reconciled separately.

Future improvements: editable email templates, preview, delivery logs, connection diagnostics, and queue-worker validation.

Security: keep `APP_KEY` stable so encrypted credentials remain decryptable. Avoid posting SMTP passwords or mail logs containing secrets.
