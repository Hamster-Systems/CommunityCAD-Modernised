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


## 10 November — CAD Update Modernization corrections

**Prompt behaviour:**
- Keep the original compact, inline En Route prompt for units that are already on duty and assigned to a call.
- The blocking callout **modal is shown only if that signed-in unit is unavailable/off duty** (`OFFDTY`), offering explicit on-duty/En Route acceptance or decline. Merely dispatching them never changes their duty state.

**Sound notification routing:**
- Panic is the only intentionally global sound event.
- Unit status and call assignment tones are targeted to the affected unit's signed-in account.
- Incident status/field updates are sent to units attached to the same incident, not the entire CAD. Timestamp-only changes do not ring.
- New-call tones go to on-duty dispatchers rather than every patrol unit.
- The audio-feed endpoint allows only targeted events or global panic, so older untargeted audio records cannot be played by unrelated units.
- Verify 2+ browser profiles representing unrelated units and 2 units attached to the same call.

**Per-department subdivisions:**
- Admin → Manage Departments → Edit (and Create) now configures available MDT subdivisions separately per department, one name per line.
- Additive migration stores each department's list as JSON and initially copies legacy options to existing departments to preserve current identifiers.
- MDT identity forms display only the chosen department's entries and validate membership/option ownership on submission. Changing the selected department clears incompatible selections.
- These settings are no longer read from global `cad.mdt_subdivisions` at runtime (the old setting is used only during data migration).
- Verify existing subdivision data and each department's administrative configuration after migration.

**Dispatcher workspace:** Future dedicated dispatcher console is tracked in GitHub issue #45. The current implementation does not redesign dispatch.

**Draft PR:** Changes committed, but not yet tested against the running Docker development installation. Migrate with a database backup first.


## Directed audio and panic display follow-up (10 November)

### Audio isolation
- Call status/field updates use the attached-unit recipient set; users not assigned to the incident cannot hear that call-update tone.
- Call notes added via both `CallLogController::store` and `CallChat::postNote` now explicitly publish the call-update event to current attached units (the call logger touches timestamps only, which is intentionally not a sound trigger).
- Assignment audio takes precedence over the incidental AVL → BUSY status change, preventing two overlapping tones when assigned to a call.
- The feed only includes matching `target_user_id` records plus global panic; historic unscoped non-panic events are filtered server-side.
- Default/custom/muted settings and volume still come from the existing Admin CAD Audio configuration.

### Panic flashing isolation
- `cad-panic-row` animates **table-cell backgrounds**, not the row, cell or its children’s opacity. Interactive menus, text, tooltips and buttons no longer dim with the panic pulse.
- Unit rows calculate a deterministic animation offset from the unit ID; incident rows use a call ID offset. Different units are usually out of phase instead of pulsing together.
- CSS is scoped to the CAD layout; no global Tailwind rebuild is required.
- `prefers-reduced-motion` displays a non-animated, readable panic highlight.

### QA matrix
- [ ] Unit A changes status: A hears its own status sound, B remains silent.
- [ ] A is attached: only A hears assignment tone; no duplicate BUSY tone.
- [ ] A and B attached to call 1, C attached to call 2: note/status updates on call 1 sound for A+B but not C.
- [ ] A panics: A+B+C hear global panic, no simultaneous status tone.
- [ ] Two units panic: their table backgrounds pulse out of phase; the incident and unit rows do not cause dropdowns, tooltip or action menus to fade.
- [ ] Hold open an incident action menu while panic is flashing; its background, text and focus remain legible.
- [ ] Confirm sound playback with two separate browser accounts and Admin CAD Audio custom/mute settings.
- [ ] Verify reduced-motion UI (no flashing), Livewire refresh and Firefox console.

**Implementation committed to PR #44, not yet browser/integration verified.**


## Co-responder audio propagation fix (10 November)

**Observed:** Direct changes to a call already played for attached units, but UNIT activity records from a new assignment, arrival on scene or response-stage changes only made a sound for the unit performing the action.

**Change:**
- `CadAudioPublisher::unitActivity(call, actorUserId)` publishes `call_updated` to **other** currently attached units, excluding the actor.
- `CallUnitAssignmentService` calls it on attach and detach, after the assignment and activity record are saved.
- `UnitResponseStatusService` calls it on response/status changes including En Route, On Scene, Available and Unavailable detachment.
- Automatic response-stage progression now uses `updateQuietly` to prevent a second notification via the Call model observer after the unit-activity event. Direct call edits still use normal model updates and continue to sound for all attached units.
- The responding unit continues hearing its own `call_assigned` / `unit_status` notification instead of a duplicate `call_updated` sound.

**QA:** With A+B on call 1 and C on call 2: attach A to call 1 (B hears call update, A hears assignment); B sets ONSCN (A hears call update, B hears status); a direct call edit sounds for A+B, not C; detaching B sounds for A, not C; activating panic remains global. Ensure one sound per action per participant.


## Transporting status colour + call age (10 November)

- Transporting uses a distinct violet selected-state control in the shared MDT toolbar. Unit roster rows use a scoped `cad-status-transport` class with readable violet text instead of relying on the previously compiled utility class.
- The `Call::time` accessor now measures elapsed **whole minutes from `calls.created_at`**, not from `updated_at`. Calls remain aged when their notes, assignments, status or priority change.
- `Call::age_seconds` provides non-negative integer elapsed seconds so future age-based priority rules can share the same source of truth; **no automatic priority escalation is enabled in this PR**.
- The call TIME table cell retains the existing minute display and provides a tooltip with the incident creation timestamp. Unit roster TIME is untouched and continues to reflect last unit update.

### Regression tests
- [ ] Choose Transporting on an assigned unit: button is violet and unit roster text remains readable, including inside dispatch.
- [ ] Change unit to another state: Transporting active colour clears.
- [ ] Create a call, wait >1 minute, add a note/change status/priority/attach a unit: call TIME continues increasing instead of returning to 0.
- [ ] Unit status TIME continues to reflect last unit status update as before.
- [ ] Historical calls retain their created timestamp and no database migration is necessary.
- [ ] Verify reduced-motion and panic styles do not obscure the Transporting state.


## Separate Call Updated audio setting (10 November)

- The Admin CAD Audio page now has **three distinct slots**: `Call status updated` (existing `call_updated`), `Call Updated` (new `call_activity_updated`), and `Unit status updated` (existing `unit_status`).
- Direct incident status/priority/core field edits retain the old `call_updated` sound and the customer’s existing upload/volume settings; personal unit status stays on `unit_status`.
- Newly posted Call Notes / Activity notes and updates when other responders attach, detach or change their unit response status now emit `call_activity_updated`, with recipients restricted to the relevant attached users.
- The new audio slot automatically appears from the `CadAudio::EVENTS` allowlist in Admin CAD Audio. It supports its own custom MP3/OGG/WAV, volume, mute, preview, and reset controls with persistent storage; no controller/route/schema changes needed.
- The initial built-in fallback reuses the existing notification clip until an admin uploads the **"Your call has been updated"** custom MP3 to the new Call Updated slot. Existing configured audio is never overwritten.

### Regression QA
- [ ] Admin CAD Audio shows Call status updated, Call Updated, Unit status updated separately.
- [ ] Upload Call Updated MP3, save, preview, confirm changes to its volume/mute do not modify the other two slots.
- [ ] Unit A posts a Call Note: A+B attached units hear Call Updated; unrelated C hears nothing.
- [ ] Unit B goes On Scene: B hears Unit status, A hears Call Updated, C hears nothing.
- [ ] Dispatcher manually changes call priority: attached units hear the existing Call status updated slot, not Call Updated.
- [ ] Panic remains global and no extra call-update sound is emitted.
