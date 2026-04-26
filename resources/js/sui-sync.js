function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function postJson(url, payload = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Unable to sync this record to Sui Testnet.');
    }

    return data;
}

async function syncEntry(button, pageConfig) {
    const originalHtml = button.innerHTML;
    const originalTitle = button.title;
    button.disabled = true;
    button.title = 'Publishing to Sui Testnet';
    button.setAttribute('aria-label', 'Publishing to Sui Testnet');
    button.innerHTML = `
        <svg class="w-4 h-4 animate-spin pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    `;

    try {
        const saved = await postJson(button.dataset.markUrl);

        if (saved.profile_object_id) {
            pageConfig.root.dataset.profileObjectId = saved.profile_object_id;
        }

        const link = document.createElement('a');
        link.href = saved.suivision_url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.title = 'View on SuiVision Testnet';
        link.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold bg-emerald-500/10 text-emerald-300 border-emerald-500/25 hover:bg-emerald-500/20 hover:border-emerald-500/40 transition shadow-[0_0_8px_rgba(16,185,129,0.15)]';
        link.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_4px_#10b981]"></span>Settled <span class="opacity-40 font-mono text-[10px]">${saved.sui_digest.slice(0, 5)}...</span>`;

        const row = button.closest('tr');
        const statusCell = row?.querySelector('[data-sui-status-cell]');
        if (statusCell) {
            statusCell.replaceChildren(link);
        }

        button.remove();
    } catch (error) {
        alert(error.message);
        button.disabled = false;
        button.title = originalTitle;
        button.setAttribute('aria-label', originalTitle || 'Sync to Sui Testnet');
        button.innerHTML = originalHtml;
    }
}

function initSuiSync() {
    const root = document.getElementById('sui-sync-page');
    if (!root) return;

    const pageConfig = { root };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.sui-sync-entry');
        if (!button || !root.contains(button)) return;

        event.preventDefault();
        event.stopPropagation();
        syncEntry(button, pageConfig);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSuiSync);
} else {
    initSuiSync();
}
