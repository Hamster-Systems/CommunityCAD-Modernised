# CommunityCAD Modernised — Revival Plan

> **Status: evaluation and planning only. Not production-ready.**
>
> This repository is an independent fork of [CommunityCAD/CommunityCAD](https://github.com/CommunityCAD/CommunityCAD), maintained as a potential modernisation initiative by Hamster Systems. It is not an official CommunityCAD release or affiliated with the original maintainers.

## Mission

Evaluate whether CommunityCAD can be maintained and modernised into a secure, accessible, self-hosted CAD/MDT platform while preserving the original project's history and crediting its contributors.

## Important licensing checkpoint

As reviewed on 8 November 2026, the upstream application's repository does not expose a root `LICENSE` file, although `composer.json` contains `"license": "MIT"`. This may reflect the upstream authors' intentions but should **not** be treated as conclusive permission to redistribute modified copies of the whole application.

Before publishing modified application releases or repackaging the original artwork, obtain clarification from the upstream copyright holders and document the applicable licences. Preserve upstream notices and Git history.

The separate [docker-community-cad](https://github.com/CommunityCAD/docker-community-cad) repository carries a restrictive CC BY-NC-ND 4.0 licence. Its scripts/configuration must not be copied into this fork without appropriate permission. A future Docker deployment should be designed independently.

## Branch conventions

- `Main`: inherited upstream source; preserve as a comparison baseline until development policy is established.
- `develop`: staging for accepted development and documentation changes.
- `docs/*`, `feature/*`, `fix/*`, `security/*`: short-lived work branches with pull requests into `develop`.

Do not assume the default branch has already been changed in GitHub settings.

## Evaluation roadmap

### Milestone 0 — Permission, audit and baseline

- [ ] Clarify source-code and branding redistribution rights with the upstream maintainers.
- [ ] Inventory dependencies and identify unsupported components.
- [ ] Audit authentication, registration, sessions, permissions and external integrations.
- [ ] Audit install/update scripts; document any data-destructive actions.
- [ ] Create repeatable non-production checks and a baseline test report.
- [ ] Establish an issue-reporting workflow when GitHub Issues have been enabled.

### Milestone 1 — Supported installation pathways

- [ ] Document native Windows installation and troubleshooting.
- [ ] Document native Linux installation, including AlmaLinux.
- [ ] Develop an independently authored Dockerfile and Compose setup after licensing clarification.
- [ ] Make initial setup idempotent and migrations safe; never silently drop production tables.
- [ ] Ensure the same application version and database migrations are used across all platforms.

### Milestone 2 — Application modernisation

- [ ] Upgrade framework and dependencies based on compatibility and security testing.
- [ ] Stabilise login, registration and role/permission boundaries.
- [ ] Improve accessibility, frontend responsiveness and community-specific branding.
- [ ] Add test coverage, automated checks, release notes and a documented upgrade path.

## Branding direction

**Working name:** CommunityCAD Modernised.

The intent is to carry over the visual direction of the independently created OpenCAD ReModernised shield branding, with updated product wording and appropriate attribution. Branding work must not imply official upstream endorsement. Confirm reuse rights for any artwork before distributing it.

## Security and release policy

This is a planning-stage fork. Do not expose it publicly, accept real user credentials, or use operational data until the application and deployment pathways have been reviewed and tested. Treat any new findings as unverified until reproduced, and avoid public disclosure of exploitable details before responsible triage.

## Credits

- Original application: [CommunityCAD/CommunityCAD](https://github.com/CommunityCAD/CommunityCAD).
- Upstream contributors: [CommunityCAD contributor history](https://github.com/CommunityCAD/CommunityCAD/graphs/contributors).
- Independent evaluation and planning: Hamster Systems.
