# CommunityCAD real-time broadcasting — implementation plan

> **Planning only.** This PR establishes the real-time implementation scope; it does not yet enable WebSockets or alter CAD screens. Keep `Main` unchanged.

## Current baseline

- Laravel 10 (`composer.json` constraint `^10.0`), Livewire 2 (`^2.10`).
- The CAD unit-status strip polls approximately every 4 seconds and the MDT roster/call queue every 5 seconds.
- `config/broadcasting.php` defaults to the null driver; `routes/channels.php` has no active CAD authorisation rules.
- `resources/js/bootstrap.js` has commented-out Laravel Echo setup.
- Existing CAD audio/event behaviour must remain intact.

## Goal

Deliver authorised updates to connected CAD/MDT/dispatcher clients promptly when persisted CAD data changes, without requiring repeated full-page polling. Keep a bounded fallback refresh for network interruptions.

## Phase 1 — Verify runtime and infrastructure

- Verify actual container PHP version, Laravel patch level, composer dependencies and Docker Compose topology.
- Select a compatible self-hosted WebSocket broadcaster (evaluate Laravel Reverb only after verifying PHP >= 8.2 and Laravel >= 10.47; otherwise choose a supported alternative or upgrade in a separately reviewed PR).
- Configure WebSocket endpoint and secure reverse proxy (HTTPS/WSS), plus a managed daemon/container and appropriate queue worker if broadcasts are queued.
- Introduce configuration via environment variables; never commit credentials.
- Add connection-health checks, logging, restart and deploy instructions.
- Rebuild Laravel Mix assets using a temporary Node 20 Docker container on hosts without npm.

## Phase 2 — Secure event model

- Authenticated, **private** channels scoped to the active community/tenant and authorised CAD role or user.
- Server-side authorisation for every subscription; clients must not choose unauthorised tenant/department IDs.
- Publish events only after successful database persistence/commit.
- Use minimal payloads (IDs, change types and version/update timestamps), avoid exposing sensitive call or user data in broad channels.
- Ensure duplicate events and reconnections do not produce duplicate CAD audio alerts.
- Audit and test permissions across multiple users/communities.

## Phase 3 — First live vertical slice

- Broadcast unit-status transitions from the central status service.
- Subscribe in MDT/dispatch screens and refresh only affected Livewire components.
- Update the local user's status promptly on acknowledgement; propagate to other connected users.
- Keep 30–60-second fallback polling or resynchronisation when connectivity is lost; avoid 1-second full-roster polling.
- Verify simultaneous MDT/dispatcher sessions; status state remains authoritative on the server.

## Phase 4 — All CAD changes

- Incident create/update/close, priority and assignment changes.
- Panic state and unit response transitions.
- Incident notes/activity and dispatch messages.
- Integrate existing sound settings to play each notification once on the appropriate event.
- Review updates triggered by API/FiveM integrations as well as web controllers.

## Acceptance tests

- Two authenticated clients see updates across separate browsers without manually refreshing.
- Updates are delivered after persistence and no cross-community leakage occurs.
- Disconnected clients recover current state on reconnection; no stale displays.
- No duplicate notes, status transitions or audio triggered by reconnect/replay.
- Browser denied audio autoplay does not break realtime UI.
- Works through the existing development proxy via WSS.
- UI colour/layout unchanged, including high-contrast BUSY status.
- Existing CAD functionality remains usable if the WebSocket service is down.

## Rollout

Keep this feature branch draft while infrastructure and tests are developed. Migrate from polling screen by screen; do not disable fallback polling until end-to-end tests and deployment checks pass.
