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

    function noticeButton() {
        if (notice) return notice;
        notice = document.createElement('button');
        notice.type = 'button';
        notice.textContent = 'Enable CAD audio';
        notice.setAttribute('aria-label', 'Enable notification audio after your browser blocked autoplay');
        notice.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:120;background:#123b56;color:#fff;border:1px solid #38bdf8;border-radius:8px;padding:10px 15px;font:600 13px system-ui,sans-serif;box-shadow:0 6px 24px #0008;cursor:pointer';
        notice.hidden = true;
        notice.addEventListener('click', () => {
            unlocked = true;
            notice.hidden = true;
            // This click is the user gesture required by Firefox/Chrome.
            if (blocked) {
                const pending = blocked;
                blocked = null;
                pending.currentTime = 0;
                pending.play().then(() => { playing = pending; }).catch(() => {
                    blocked = pending;
                    notice.hidden = false;
                });
            } else {
                drain();
            }
        });
        document.body.appendChild(notice);
        return notice;
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
        }).catch(() => {
            starting = false;
            blocked = audio;
            noticeButton().hidden = false;
            // Do not retry every poll; wait for explicit user interaction.
        });
    }

    function enqueue(event) {
        if (!event.url || Number(event.volume) <= 0) return;
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
    poll();
    setInterval(poll, 3000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
})();
