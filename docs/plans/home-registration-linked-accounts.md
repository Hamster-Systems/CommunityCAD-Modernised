# Home, registration and connected accounts — rollout plan

Tracks Issue #39. **This branch starts with a homepage refresh; local registration and account linking are not yet implemented.**

## Current state and compatibility concerns
- CommunityCAD authenticates through Discord Socialite today.
- `AuthController::findOrNewUser()` currently looks up `User::where('id', $discordUser->getId())`: Discord IDs serve as **primary user IDs**.
- `User` has non-incrementing IDs. Other tables (reports, departments, roles, applications, activity) reference existing users.
- The current Steam route is a guest-oriented link flow. It must not be exposed as a new linking workflow until its ownership/security semantics are verified.
- Account status and application approval are distinct from authentication; do not grant CAD access simply because an account exists.

## Phase 1 — welcome page
- Modernise both authenticated and guest welcome surfaces with responsive cards, accessible controls and the existing dark-teal palette.
- Keep the existing Discord callback and profile/CAD/application destinations functional.
- Do not invent `register`, local-login or linked-provider routes before their server-side implementations exist.
- Check home page at several breakpoints and validate application eligibility branches, logout CSRF behaviour and signed-in display name.

## Phase 2 — first-party identity migration design
- Inventory all references to `users.id` and identify foreign keys/soft dependencies.
- Introduce a stable first-party user ID strategy without overwriting existing users or breaking linked records. Prefer a controlled, reversible-in-practice migration plan over changing user primary keys in-place without a full data audit.
- Model provider associations using unique `(provider, provider_user_id)` pairs; existing Discord links should be backfilled safely.
- Never automatically match or merge accounts by name, display name, or unverified email.
- Keep existing Discord sign-in working until a tested migration/backfill and recovery mechanism is available.
- Back up data before any schema operation; stage migration in development and test with real-shaped legacy fixtures.

## Phase 3 — credentials and optional providers
- Registration: unique email, appropriate display name, secure password hash, verification, password reset, CSRF protection, login throttling and optional 2FA.
- A new registration must not automatically receive member roles/CAD access.
- Account settings: link/unlink Discord using OAuth state and secure callbacks, Steam via verified OpenID response; enforce global uniqueness.
- Reauthentication for sensitive linking/unlinking; guard against disconnecting the only available login method.
- Sign-in can be enabled using local account credentials and verified provider accounts after migration tests.
- Allow administrator controls to enable/disable public registration/provider sign-in; preserve existing application policy.

## Acceptance checks
- Existing Discord users retain IDs, application history, reports, departments and roles through migration.
- Current site functionality works before any local credential flows are enabled.
- All new account pages have explicit empty, error and recovery states.
- Validate mobile, keyboard navigation and WCAG contrast.
- No unintended visual changes to the operational CAD, MDT, AdminCP or incident views.
- Docker build uses Laravel Mix compilation (via temporary Node container when npm is absent on host) before testing CSS.

**Do not merge auth/database changes without security review and test coverage.**
