# Changelog

This development changelog records noteworthy fixes and changes before a tagged release.

## Unreleased

### Fixed — CAD report creation (Issue #37, PR #38; 2026-11-09)
- **Identified issue:** opening `/cad/report/create` returned HTTP 500 for independent MDT unit identities because the template accessed a missing legacy Officer relationship through `active_unit->officer->name`.
- Use the identity-aware reporting officer name and handle a missing legacy `officer_id` when saving reports.
- Record a snapshot of the reporting unit's name, callsign, rank and department when the report is created so historical report attribution remains stable when unit identity details change.
- Display the stored identity on reports while retaining a fallback for older records without snapshots.
- Add nullable report identity columns without deleting or backfilling existing report data.

**Verification:** the report creation page was confirmed to render in local development. Full report submission, identity persistence and editing regression checks remain to be verified.

**Follow-up:** Keep Issue #37 open for a dedicated visual and usability upgrade to the reports workflow.
