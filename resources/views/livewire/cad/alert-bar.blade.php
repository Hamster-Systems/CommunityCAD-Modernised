<div class="space-y-2" wire:poll.5s>
    @if ($pendingEnrouteCallId)
        {{-- Only this assignment's unit sees the prompt. No automatic duty changes. --}}
        <div class="cad-assignment-backdrop" wire:key="assignment-dialog-{{ auth()->user()->active_unit->id }}-{{ $pendingEnrouteCallId }}-{{ auth()->user()->active_unit->updated_at?->getTimestamp() ?? 0 }}">
            <style>
                .cad-assignment-backdrop {
                    position: fixed; inset: 0; z-index: 9999;
                    display: flex; align-items: center; justify-content: center;
                    padding: 20px; background: rgba(3, 9, 19, .82);
                    text-transform: none;
                }
                .cad-assignment-dialog {
                    box-sizing: border-box; width: min(100%, 520px); max-height: calc(100vh - 40px);
                    overflow-y: auto; border: 1px solid #3a6483; border-radius: 14px;
                    background: #152338; padding: 24px; color: #eef6ff;
                    box-shadow: 0 25px 65px rgba(0, 0, 0, .6);
                    font: 14px/1.5 system-ui, sans-serif;
                }
                .cad-assignment-dialog h2 { font-size: 22px; line-height: 1.25; font-weight: 750; margin: 7px 0 12px; }
                .cad-assignment-kicker { color: #8fd4ff; font-size: 12px; font-weight: 700; letter-spacing: .06em; }
                .cad-assignment-details { color: #c5d7e8; margin: 0 0 12px; }
                .cad-assignment-note { background: #213650; padding: 12px; border-radius: 8px; font-size: 12px; color: #c9e5fd; margin: 14px 0; }
                .cad-assignment-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
                .cad-assignment-actions button, .cad-assignment-actions a {
                    border-radius: 8px; border: 1px solid #52667a; padding: 10px 14px; min-height: 42px;
                    font: 600 13px/20px system-ui, sans-serif; color: #fff;
                    background: #26364a; text-decoration: none; text-align: center;
                }
                .cad-assignment-actions .cad-assignment-accept { background: #126caa; border-color: #2699df; }
                .cad-assignment-actions button:focus-visible, .cad-assignment-actions a:focus-visible {
                    outline: 3px solid #a9e2ff; outline-offset: 2px;
                }
                .cad-assignment-actions button:disabled { opacity: .5; cursor: wait; }
                @media (max-width: 550px) { .cad-assignment-dialog { padding: 18px; }
                    .cad-assignment-actions { flex-direction: column; }
                    .cad-assignment-actions button, .cad-assignment-actions a { width: 100%; }
                }
            </style>
            <section class="cad-assignment-dialog" role="alertdialog" aria-modal="true"
                aria-labelledby="cad-assignment-title" aria-describedby="cad-assignment-description">
                <div class="cad-assignment-kicker">NEW DISPATCH ASSIGNMENT · CALL #{{ $pendingEnrouteCallId }}</div>
                <h2 id="cad-assignment-title">You've been assigned a callout</h2>
                <p id="cad-assignment-description" class="cad-assignment-details">
                    Your unit has been attached to this incident. Do you accept the assignment and mark yourself En Route?
                </p>
                @if ($pendingAssignmentOffDuty)
                    <p class="cad-assignment-note">You are currently off duty. Accepting will put you <strong>on duty</strong> and mark you <strong>En Route</strong>. Declining keeps you off duty and removes this assignment.</p>
                @else
                    <p class="cad-assignment-note">Accepting marks you En Route. You can also keep your current unit status and stay attached to the call.</p>
                @endif
                <div class="cad-assignment-actions">
                    <button type="button" class="cad-assignment-accept"
                        wire:click="answerAssignment({{ $pendingEnrouteCallId }}, true)"
                        wire:loading.attr="disabled" wire:target="answerAssignment">Accept · En Route</button>
                    <button type="button"
                        wire:click="answerAssignment({{ $pendingEnrouteCallId }}, false)"
                        wire:loading.attr="disabled" wire:target="answerAssignment">
                        {{ $pendingAssignmentOffDuty ? 'Decline callout' : 'Keep current status' }}
                    </button>
                    <a href="{{ route('cad.call.show', $pendingEnrouteCallId) }}">View call details</a>
                </div>
            </section>
        </div>
    @endif
    @foreach ($active_alerts as $alert)
        @if ($alert['color'] == 'red')
            <div class="bg-red-600 text-white animate-pulse py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ route($alert['link']) }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif
            </div>
        @elseif($alert['color'] == 'yellow')
            <div class="bg-yellow-600 text-white py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ route($alert['link']) }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif
            </div>
        @elseif($alert['color'] == 'green')
            <div class="bg-green-600 text-white py-1 text-center text-lg">
                @if ($alert['link'] != null)
                    <a href="{{ $alert['link'] }}">{{ $alert['message'] }}</a>
                @else
                    <p>{{ $alert['message'] }}</p>
                @endif
            </div>
        @endif
    @endforeach
</div>
