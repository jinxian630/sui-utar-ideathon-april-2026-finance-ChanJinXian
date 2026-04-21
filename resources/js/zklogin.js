import { generateRandomness } from '@mysten/sui/zklogin';
import { Ed25519Keypair } from '@mysten/sui/keypairs/ed25519';
import { jwtDecode } from 'jwt-decode';

function initZkLogin() {
    const loginBtn = document.getElementById('zklogin-btn');
    if (!loginBtn) return;

    loginBtn.addEventListener('click', async () => {
        // 1. Generate ephemeral key pair
        const ephemeralKeyPair = new Ed25519Keypair();
        const randomness = generateRandomness();
        
        // Save these temporarily in session storage to use after redirect
        sessionStorage.setItem('zklogin_ephemeral_key', ephemeralKeyPair.getSecretKey());
        sessionStorage.setItem('zklogin_randomness', randomness);
        sessionStorage.setItem('zklogin_max_epoch', '10'); // Arbitrary, in a real app query current epoch

        // 2. Compute Nonce (simplified for frontend demo purposes, would usually use Sui Client)
        // Normally using generateNonce from @mysten/sui/zklogin but it requires epoch information
        const { generateNonce } = await import('@mysten/sui/zklogin');
        const ephemeralPublicKey = ephemeralKeyPair.getPublicKey();
        const nonce = generateNonce(ephemeralPublicKey, 10, randomness);

        // 3. Redirect to Google OAuth
        const clientId = '18865380975-cijpdo630105pmvcb4hcb1ab3b06bdl7.apps.googleusercontent.com';
        const redirectUri = window.location.origin + '/login'; // Handle redirect on same page

        const params = new URLSearchParams({
            client_id: clientId,
            response_type: 'id_token',
            redirect_uri: redirectUri,
            scope: 'openid email profile',
            nonce: nonce,
        });

        window.location.href = `https://accounts.google.com/o/oauth2/v2/auth?${params.toString()}`;
    });

    handleZkLoginCallback();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initZkLogin);
} else {
    initZkLogin();
}

async function handleZkLoginCallback() {
        const hash = new URLSearchParams(window.location.hash.substring(1));
        const jwt = hash.get('id_token');

        if (jwt) {
            // Process the returned JWT
            const decodedJwt = jwtDecode(jwt);
            const { jwtToAddress } = await import('@mysten/sui/zklogin');
            
            // Generate the user's Sui address from the JWT and salt.
            // Note: For full production use, you'd request salt from a Salt provider service.
            // Using a static/deterministic salt here for demo/Assignment purposes.
            const userSalt = '123456789012345678';
            const zkLoginAddress = jwtToAddress(jwt, userSalt, false);
            
            // Clean up hash
            window.history.replaceState(null, null, window.location.pathname);
            
            fetch('/auth/zklogin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' // In login view we might need to add this
                },
                body: JSON.stringify({
                    wallet_address: zkLoginAddress,
                    email: decodedJwt.email
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(err => console.error("zkLogin Server Error: ", err));
        }
}
