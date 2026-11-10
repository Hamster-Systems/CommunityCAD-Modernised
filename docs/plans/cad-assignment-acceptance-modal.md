# CAD assignment acceptance — modal upgrade

This branch builds on the already-merged assignment prompt (PR #28), without resetting the MDT/CAD layout or audio subsystem.

## Confirmed existing implementation
- Shared `CallUnitAssignmentService::attach` is used by dispatcher and self-attach flows.
- Shared `AlertBar` is included in `inc/cad/mdt-nav.blade.php`, and polls every five seconds.
- Before this branch, the prompt was an **inline banner**, only allowed for on-duty units. The assignment service rejected units with status `OFFDTY`.
- Accepted dispatch calls update unit response state via `UnitResponseStatusService` and advance incident response status where applicable.

## Changes in this branch
- Render a centered, keyboard-focus-visible, responsive callout **modal**, not an inline banner.
- Show its call ID and **Accept · En Route**, an alternative action, and View Call.
- Dispatch or self-attach of a signed-in off-duty unit creates an assignment *invitation* without changing duty state.
- An off-duty unit must **explicitly accept** to go on duty and En Route. Duty + response transitions execute in a DB transaction.
- Off-duty **Decline** removes the assignment and keeps the unit off duty.
- Already-on-duty units can **Keep current status** without accidentally detaching.
- Existing assigned-call audio, panic flags, other incident states and unit status toolbar remain unchanged.
- Continue to use the existing `CAD_PROMPT_ENROUTE_ON_ASSIGNMENT` toggle and current 10-minute assignment description polling signal.

## QA before merging
- [ ] Dispatch adds an **on-duty** unit to a call: modal appears in that officer's other browser on home and MDT within 5 seconds.
- [ ] Officer accepts: unit becomes ENRUTE, incident changes from RCVD to ENRT, assignment remains, activity log updated.
- [ ] Officer selects Keep current status: on-duty assignment remains attached, status unchanged, no repeated modal.
- [ ] Dispatcher attaches an **off-duty** but signed-in unit: modal appears, unit stays OFFDTY before response.
- [ ] Officer accepts: becomes on duty + ENRUTE, and incident progresses as appropriate.
- [ ] Officer declines: unit stays OFFDTY, call assignment removed, incident response status reopens if appropriate.
- [ ] Self-attach on-duty/off-duty shows same modal (including redirects to call details).
- [ ] Panic unit cannot accept; closed incidents cannot be assigned.
- [ ] Assignment prompts do not leak to other officers' sessions.
- [ ] Repeated polling does not repeat a handled prompt; re-assignment creates a new prompt.
- [ ] Modal fits small screens; keyboard focus and screen readers tested.
- [ ] No regressions to existing CAD activity, dispatch controls, clock, panic or audio notifications.

**Important limitation:** The existing ten-minute, `active_units.description`-based assignment signal is not a durable notification queue. A future event-driven task (PR #35) can replace it. This draft has not been tested in the user's Docker environment.

## Location / Postal recovery
PR #34 merged only a BUSY text-colour improvement and Docker documentation, not the earlier Location/Postal work. Its description says the earlier changes were rolled back to a `backup/pr34-before-visual-rollback-20261109` ref. That ref is not currently reachable as a GitHub branch from the connector, so it must be located through local Git history/reflog, a commit SHA or other preserved backup before any selective restoration. Do not restore the old entire layout/CSS change blindly.
