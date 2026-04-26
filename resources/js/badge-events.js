import { SuiJsonRpcClient } from '@mysten/sui/jsonRpc';

const BADGE_NAMES = {
    saver: 'Saver',
    investor: 'Investor',
    wealth_builder: 'Wealth Builder',
    diamond_saver: 'Diamond Saver',
    finance_master: 'Finance Master',
};

function normalize(value) {
    return String(value || '').toLowerCase();
}

function eventKey(event) {
    const id = event.id || {};
    return id.txDigest && id.eventSeq
        ? `${id.txDigest}:${id.eventSeq}`
        : event.parsedJson?.badge_object_id || event.timestampMs || JSON.stringify(event.parsedJson || {});
}

function storageKey(wallet, packageId) {
    return `finance-tracker:badge-events:${wallet}:${packageId}`;
}

function readSeen(key) {
    try {
        return new Set(JSON.parse(localStorage.getItem(key) || '[]'));
    } catch {
        return new Set();
    }
}

function writeSeen(key, seen) {
    try {
        localStorage.setItem(key, JSON.stringify([...seen].slice(-80)));
    } catch {
        // Browsers can reject localStorage in private contexts. The modal still works.
    }
}

function badgeName(slug, level) {
    return BADGE_NAMES[slug] || `Level ${level || ''} Badge`.trim();
}

function dispatchBadgeEarned(payload) {
    const level = Number(payload.badge_level || 0);
    const objectId = payload.badge_object_id;

    window.dispatchEvent(new CustomEvent('badge-earned', {
        detail: {
            name: badgeName(payload.tier_slug, level),
            level,
            objectId,
            link: `https://testnet.suivision.xyz/object/${objectId}`,
            imageUrl: `/images/badges/level-${level}.png`,
        },
    }));
}

function initBadgeEvents() {
    const root = document.getElementById('badge-event-listener');
    if (!root) return;

    const wallet = normalize(root.dataset.walletAddress);
    const packageId = normalize(root.dataset.packageId);
    const rpcUrl = root.dataset.rpcUrl;

    if (!wallet || !packageId || !rpcUrl) return;

    const client = new SuiJsonRpcClient({
        network: 'testnet',
        url: rpcUrl,
    });

    const seenKey = storageKey(wallet, packageId);
    const seen = readSeen(seenKey);
    let polling = false;

    async function poll() {
        if (polling || document.visibilityState === 'hidden') return;
        polling = true;

        try {
            const response = await client.queryEvents({
                query: {
                    MoveEventType: `${packageId}::loyalty_badge::BadgeEarned`,
                },
                limit: 10,
                order: 'descending',
            });

            const events = [...(response.data || [])].reverse();
            const now = Date.now();

            for (const event of events) {
                const key = eventKey(event);
                const payload = event.parsedJson || {};
                const isCurrentUser = normalize(payload.user_address) === wallet;

                if (!isCurrentUser || seen.has(key)) {
                    continue;
                }

                seen.add(key);

                const eventAge = event.timestampMs ? now - Number(event.timestampMs) : 0;
                if (!eventAge || eventAge <= 60000) {
                    dispatchBadgeEarned(payload);
                }
            }

            writeSeen(seenKey, seen);
        } catch (error) {
            console.warn('Unable to poll Sui badge events.', error);
        } finally {
            polling = false;
        }
    }

    poll();
    window.setInterval(poll, 3000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBadgeEvents);
} else {
    initBadgeEvents();
}
