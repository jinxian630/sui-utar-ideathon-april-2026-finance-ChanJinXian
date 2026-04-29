import { generateNonce, generateRandomness, jwtToAddress } from '@mysten/sui/zklogin';
import { Ed25519Keypair } from '@mysten/sui/keypairs/ed25519';
import { jwtDecode } from 'jwt-decode';

const GOOGLE_CLIENT_ID = '18865380975-cijpdo630105pmvcb4hcb1ab3b06bdl7.apps.googleusercontent.com';

function initZkLogin() {
    const loginBtn = document.getElementById('zklogin-btn');

    if (loginBtn) {
        loginBtn.addEventListener('click', () => startGoogleZkLogin(loginBtn.dataset.zkMode || 'login'));
    }

    handleZkLoginCallback();
}

function startGoogleZkLogin(mode) {
    const ephemeralKeyPair = new Ed25519Keypair();
    const randomness = generateRandomness();
    const maxEpoch = '10';

    sessionStorage.setItem('zklogin_ephemeral_key', ephemeralKeyPair.getSecretKey());
    sessionStorage.setItem('zklogin_randomness', randomness);
    sessionStorage.setItem('zklogin_max_epoch', maxEpoch);
    sessionStorage.setItem('zklogin_mode', mode);

    const nonce = generateNonce(ephemeralKeyPair.getPublicKey(), Number(maxEpoch), randomness);
    const redirectUri = `${window.location.origin}/login`;

    const params = new URLSearchParams({
        client_id: GOOGLE_CLIENT_ID,
        response_type: 'id_token',
        redirect_uri: redirectUri,
        scope: 'openid email profile',
        nonce,
    });

    window.location.href = `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;
}

async function handleZkLoginCallback() {
    const hash = new URLSearchParams(window.location.hash.substring(1));
    const jwt = hash.get('id_token');

    if (!jwt) return;

    window.history.replaceState(null, '', window.location.pathname);

    const decodedJwt = jwtDecode(jwt);
    const mode = sessionStorage.getItem('zklogin_mode') || 'login';

    if (mode === 'login') {
        const status = await checkZkLoginStatus(decodedJwt.email);

        if (!status.hasPin) {
            showRedirectNotice(status.message, status.redirect || '/register');
            return;
        }
    }

    showPinDialog(mode, async (pin) => {
        const verifier = await sha256Hex(`${decodedJwt.sub}|${decodedJwt.email}|${pin}`);
        const salt = saltFromHex(verifier);
        const zkLoginAddress = jwtToAddress(jwt, salt, false);

        const response = await fetch('/auth/zklogin', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                mode,
                wallet_address: zkLoginAddress,
                email: decodedJwt.email,
                name: decodedJwt.name || null,
                zk_subject: decodedJwt.sub,
                zk_pin_verifier: verifier,
            }),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok && data.redirect) {
            sessionStorage.removeItem('zklogin_mode');
            window.location.href = data.redirect;
            return;
        }

        if (!response.ok || !data.redirect) {
            throw new Error(data.message || 'Unable to complete zkLogin.');
        }

        sessionStorage.removeItem('zklogin_mode');
        window.location.href = data.redirect;
    });
}

async function checkZkLoginStatus(email) {
    const response = await fetch('/auth/zklogin/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ email }),
    });

    const data = await response.json().catch(() => ({}));

    if (response.ok && data.has_pin) {
        return { hasPin: true };
    }

    return {
        hasPin: false,
        message: data.message || 'No Nuance PIN found for this Google account. Please register with Google zkLogin to create your PIN.',
        redirect: data.redirect || '/register',
    };
}

function showRedirectNotice(message, redirect) {
    sessionStorage.removeItem('zklogin_mode');

    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(5,8,18,0.86);display:flex;align-items:center;justify-content:center;padding:1.25rem;';
    overlay.innerHTML = `
        <div style="width:100%;max-width:420px;background:#12121e;border:1px solid rgba(34,211,238,0.28);border-radius:1.25rem;padding:1.5rem;box-shadow:0 24px 80px rgba(0,0,0,0.75);text-align:center;">
            <p style="color:#67e8f9;font-size:0.65rem;font-weight:800;letter-spacing:0.16em;text-transform:uppercase;margin:0 0 0.5rem;">Register required</p>
            <h2 style="color:white;font-size:1.2rem;font-weight:800;margin:0;">Create your Nuance PIN</h2>
            <p style="color:#8a8aa3;font-size:0.82rem;line-height:1.6;margin:0.75rem 0 1rem;">${escapeHtml(message)}</p>
            <button type="button" id="zk-register-redirect" style="width:100%;border:0;border-radius:0.85rem;background:linear-gradient(135deg,#00e5ff,#7c3aed 55%,#ff2bd6);color:white;font-weight:800;padding:0.85rem;cursor:pointer;">Go to Register</button>
        </div>
    `;

    document.body.appendChild(overlay);
    overlay.querySelector('#zk-register-redirect').addEventListener('click', () => {
        window.location.href = redirect;
    });

    setTimeout(() => {
        window.location.href = redirect;
    }, 1800);
}

function showPinDialog(mode, onSubmit) {
    const isRegister = mode === 'register';
    const title = isRegister ? 'Set your Nuance PIN' : 'Enter your Nuance PIN';
    const description = isRegister
        ? 'Create a 6-digit PIN to secure your on-chain identity. The raw PIN is never sent to Nuance.'
        : 'Use the 6-digit PIN you created during zkLogin registration. The raw PIN is never sent to Nuance.';
    const buttonText = isRegister ? 'Create Wallet' : 'Log in';
    const loadingText = isRegister ? 'Creating wallet...' : 'Logging in...';

    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(5,8,18,0.86);display:flex;align-items:center;justify-content:center;padding:1.25rem;';
    overlay.innerHTML = `
        <div style="width:100%;max-width:420px;background:#12121e;border:1px solid rgba(34,211,238,0.28);border-radius:1.25rem;padding:1.5rem;box-shadow:0 24px 80px rgba(0,0,0,0.75);">
            <p style="color:#67e8f9;font-size:0.65rem;font-weight:800;letter-spacing:0.16em;text-transform:uppercase;margin:0 0 0.5rem;">Secure</p>
            <h2 style="color:white;font-size:1.25rem;font-weight:800;margin:0;">${title}</h2>
            <p style="color:#8a8aa3;font-size:0.82rem;line-height:1.6;margin:0.75rem 0 1rem;">${description}</p>
            <input id="zk-pin-input" type="password" inputmode="numeric" maxlength="6" placeholder="6-digit PIN"
                   style="width:100%;background:#070a12;border:1px solid rgba(255,255,255,0.1);border-radius:0.8rem;color:white;font-size:1.2rem;letter-spacing:0.35em;text-align:center;padding:0.85rem;outline:none;">
            <p id="zk-pin-error" style="display:none;color:#f87171;font-size:0.75rem;margin:0.6rem 0 0;">Enter exactly 6 digits.</p>
            <button id="zk-pin-submit" type="button" style="width:100%;margin-top:1rem;border:0;border-radius:0.85rem;background:linear-gradient(135deg,#00e5ff,#7c3aed 55%,#ff2bd6);color:white;font-weight:800;padding:0.85rem;cursor:pointer;">${buttonText}</button>
        </div>
    `;

    document.body.appendChild(overlay);

    const input = overlay.querySelector('#zk-pin-input');
    const error = overlay.querySelector('#zk-pin-error');
    const submit = overlay.querySelector('#zk-pin-submit');

    input.focus();
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 6);
        error.style.display = 'none';
    });

    submit.addEventListener('click', async () => {
        if (!/^\d{6}$/.test(input.value)) {
            error.style.display = 'block';
            return;
        }

        submit.disabled = true;
        submit.textContent = loadingText;

        try {
            await onSubmit(input.value);
        } catch (exception) {
            error.textContent = exception.message || 'Unable to complete zkLogin.';
            error.style.display = 'block';
            submit.disabled = false;
            submit.textContent = buttonText;
        }
    });
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}

async function sha256Hex(value) {
    const encoded = new TextEncoder().encode(value);
    const digest = await crypto.subtle.digest('SHA-256', encoded);
    return [...new Uint8Array(digest)]
        .map((byte) => byte.toString(16).padStart(2, '0'))
        .join('');
}

function saltFromHex(hex) {
    return BigInt(`0x${hex.slice(0, 32)}`).toString();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initZkLogin);
} else {
    initZkLogin();
}
