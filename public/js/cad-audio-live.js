/* One live sound manager per CAD page. No Livewire morphing / autoplay.
 * A monotonic database event ID is our exactly-once cursor; URL/volume
 * come from the server's allowlisted admin audio settings.
 */
(() => {
    'use strict';
    const root = document.body;
    if (!root || !root.dataset.cadAudioFeed || window.__cadAudioStarted) return;
    window.__cadAudioStarted = true;

    const endpoint = root.dataset.cadAudioFeed;
    const scope = 'communitycad:audio:' + root.dataset.cadAudioUser;
    let cursor = 0;
    try { cursor = Math.max(0, Number(localStorage.getItem(scope)) || 0); } catch (_) {}
    let unlocked = false;
    let blocked = null;
    let playing = null;
    let starting = false;
    let checking = false;
    const queue = [];
    let notice;

    const saveCursor = value => {
        cursor = Math.max(cursor, Number(value) || 0);
        try { localStorage.setItem(scope, String(cursor)); } catch (_) {}
    };

    const preferenceKey = scope + ':permission';
    let choice = null;
    try { choice = localStorage.getItem(preferenceKey); } catch (_) {}

    function rememberChoice(value) {
        choice = value;
        try { localStorage.setItem(preferenceKey, value); } catch (_) {}
    }

    function unlockFromGesture() {
        // An actual click is required by Firefox and Chromium. Resuming a
        // silent WebAudio context here may unlock future audio playback.
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            try {
                const context = new AudioContextClass();
                const source = context.createBufferSource();
                source.buffer = context.createBuffer(1, 1, context.sampleRate);
                source.connect(context.destination);
                source.start(0);
                context.resume().finally(() => context.close().catch(() => {}));
            } catch (_) {}
        }
    }

    function noticeButton() {
        if (notice) return notice;
        const overlay = document.createElement('div');
        overlay.hidden = true;
        overlay.style.cssText = 'position:fixed;inset:0;z-index:150;display:flex;align-items:center;justify-content:center;background:#000b;padding:20px';
        overlay.innerHTML = '<section role="dialog" aria-modal="true" aria-labelledby="cad-audio-heading" aria-describedby="cad-audio-desc" style="max-width:480px;width:100%;border:1px solid #38bdf8;border-radius:12px;background:#142237;color:#fff;box-shadow:0 15px 60px #000b;padding:24px;font:14px system-ui,sans-serif">' +
            '<h2 id="cad-audio-heading" style="font-size:21px;font-weight:750;margin:0 0 10px">Enable CAD notification audio?</h2>' +
            '<p id="cad-audio-desc" style="line-height:1.6;color:#cbd5e1;margin:0 0 18px">CAD can play live sounds for assignments, calls, unit status and panic alerts. Your browser requires you to approve playback by clicking Enable sounds.</p>' +
            '<div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:flex-end">' +
            '<button type="button" data-cad-audio-later style="border:1px solid #64748b;border-radius:7px;background:#233449;color:#fff;padding:10px 16px;cursor:pointer">Not now</button>' +
            '<button type="button" data-cad-audio-allow style="border:1px solid #38bdf8;border-radius:7px;background:#075985;color:#fff;padding:10px 16px;font-weight:700;cursor:pointer">Enable sounds</button>' +
            '</div></section>';
        // A hidden attribute alone is overridden by display:flex inline CSS.
        const show = visible => { overlay.style.display = visible ? 'flex' : 'none'; };
        Object.defineProperty(overlay, 'hidden', {
            configurable: true,
            get() { return overlay.style.display === 'none'; },
            set(value) { show(!value); }
        });
        overlay.hidden = true;
        const allow = overlay.querySelector('[data-cad-audio-allow]');
        const later = overlay.querySelector('[data-cad-audio-later]');
        allow.addEventListener('click', () => {
            unlockFromGesture();
            unlocked = true;
            rememberChoice('enabled');
            overlay.hidden = true;
            if (blocked) {
                const pending = blocked;
                blocked = null;
                pending.currentTime = 0;
                starting = true;
                pending.play().then(() => {
                    starting = false;
                    playing = pending;
                }).catch(() => {
                    starting = false;
                    blocked = pending;
                    overlay.hidden = false;
                });
            } else {
                drain();
            }
        });
        later.addEventListener('click', () => {
            rememberChoice('later');
            overlay.hidden = true;
            blocked = null;
            queue.length = 0;
            // Give a visible way to enable sound later without nagging.
            const enable = document.createElement('button');
            enable.type = 'button';
            enable.textContent = 'CAD sounds muted · Enable';
            enable.style.cssText = 'position:fixed;right:14px;bottom:14px;z-index:120;border:1px solid #38bdf8;border-radius:8px;background:#123b56;color:white;padding:10px 14px;cursor:pointer';
            enable.addEventListener('click', () => {
                overlay.hidden = false;
                allow.focus();
                enable.remove();
            });
            if (!document.querySelector('[data-cad-enable-later]')) {
                enable.dataset.cadEnableLater = '1';
                document.body.appendChild(enable);
            }
        });
        document.body.appendChild(overlay);
        notice = overlay;
        return overlay;
    }

    function drain() {
        if (playing || starting || blocked || !queue.length) return;
        const event = queue.shift();
        if (!event || !event.url || !/^https?:\/\//.test(event.url)) {
            drain();
            return;
        }
        const audio = new Audio(event.url);
        starting = true;
        audio.preload = 'auto';
        audio.volume = Math.min(1, Math.max(0, Number(event.volume) || 0));
        audio.addEventListener('ended', () => { if (playing === audio) playing = null; starting = false; drain(); }, {once:true});
        audio.addEventListener('error', () => { if (playing === audio) playing = null; starting = false; drain(); }, {once:true});
        audio.play().then(() => {
            starting = false;
            playing = audio;
            unlocked = true;
            if (notice) notice.hidden = true;
        }).catch(error => {
            starting = false;
            if (error && error.name === 'NotAllowedError') {
                blocked = audio;
                noticeButton().hidden = false;
                // Wait for an explicit user gesture instead of retrying on polls.
            } else {
                // Missing/unsupported audio must not be mistaken for autoplay.
                console.warn('CAD audio could not play:', event.type, error);
                drain();
            }
        });
    }

    function enqueue(event) {
        if (choice === 'later' || !event.url || Number(event.volume) <= 0) return;
        if (event.type === 'panic') {
            if (playing) { playing.pause(); playing = null; }
            starting = false;
            queue.length = 0;
            blocked = null;
        }
        queue.push(event);
        if (queue.length > 8) queue.shift();
        drain();
    }

    async function poll() {
        if (checking) return;
        checking = true;
        try {
            const response = await fetch(endpoint + '?since=' + encodeURIComponent(cursor),
                { credentials:'same-origin', cache:'no-store', headers:{Accept:'application/json'} });
            if (!response.ok) return;
            const data = await response.json();
            if (!Array.isArray(data.events) || typeof data.cursor !== 'number') return;
            for (const event of data.events) {
                // A second tab may have consumed an event already.
                let shared = cursor;
                try { shared = Math.max(shared, Number(localStorage.getItem(scope)) || 0); } catch (_) {}
                if (Number(event.id) <= shared) continue;
                saveCursor(event.id);
                enqueue(event);
            }
            saveCursor(data.cursor);
        } catch (_) {
            // Temporary network failures should never interrupt dispatch controls.
        } finally { checking = false; }
    }

    window.addEventListener('storage', event => {
        if (event.key === scope) cursor = Math.max(cursor, Number(event.newValue) || 0);
    });
    if (choice !== 'enabled') {
        noticeButton().hidden = choice === 'later';
        if (choice === 'later') notice.querySelector('[data-cad-audio-later]').click();
    }
    poll();
    setInterval(poll, 3000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
