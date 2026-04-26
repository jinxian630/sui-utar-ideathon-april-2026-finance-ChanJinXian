<x-guest-layout>
    <style>
        .auth-label { display:block;font-size:0.65rem;color:#6b6b8a;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.4rem;font-weight:600; }
        .auth-input { width:100%;background:#0a0a14;border:1px solid rgba(255,255,255,0.08);border-radius:0.75rem;color:white;font-size:0.875rem;padding:0.7rem 1rem;outline:none;transition:border-color 0.2s, box-shadow 0.2s;box-sizing:border-box; }
        .auth-input::placeholder { color:#3a3a5a; }
        .auth-input:focus { border-color:rgba(124,92,255,0.5);box-shadow:0 0 0 3px rgba(124,92,255,0.12); }
        .auth-btn { width:100%;background:linear-gradient(135deg,#7C5CFF,#4f46e5);color:white;font-weight:700;font-size:0.875rem;border:none;border-radius:0.75rem;padding:0.75rem;cursor:pointer;box-shadow:0 4px 16px rgba(124,92,255,0.4);transition:opacity 0.2s, transform 0.15s;letter-spacing:0.02em; }
        .auth-btn:hover { opacity:0.9;transform:translateY(-1px); }
        .error-msg { color:#f87171;font-size:0.7rem;margin-top:0.3rem; }
    </style>

    <div x-data="{ showTraditional: {{ $errors->any() ? 'true' : 'false' }}, password: '' }">
        <div style="display:flex;align-items:center;justify-content:center;gap:0.4rem;margin-bottom:1.25rem;">
            <span style="color:#67e8f9;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">1 Auth</span>
            <span style="height:1px;width:28px;background:rgba(255,255,255,0.12);"></span>
            <span style="color:#a78bfa;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">2 Secure</span>
            <span style="height:1px;width:28px;background:rgba(255,255,255,0.12);"></span>
            <span style="color:#f0abfc;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">3 Explore</span>
        </div>

        <h2 style="color:white;font-size:1.35rem;font-weight:800;margin:0 0 0.25rem;">Create your Nuance vault</h2>
        <p style="color:#5a5a7a;font-size:0.78rem;margin:0 0 1.25rem;">Start with Google zkLogin or use the traditional email path.</p>

        @if ($errors->any())
            <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#f87171;font-size:0.75rem;margin-bottom:1rem;">
                @foreach ($errors->all() as $error)
                    <p style="margin:0.1rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div x-show="!showTraditional" x-cloak>
            <button type="button" id="zklogin-btn" data-zk-mode="register"
                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:0.65rem;background:linear-gradient(135deg,#00e5ff,#7c3aed 55%,#ff2bd6);border:1px solid rgba(103,232,249,0.35);color:white;font-size:0.95rem;font-weight:800;border-radius:0.9rem;padding:0.95rem;cursor:pointer;box-shadow:0 0 28px rgba(34,211,238,0.18);">
                <span style="width:24px;height:24px;border-radius:50%;background:white;color:#111827;display:inline-flex;align-items:center;justify-content:center;font-weight:900;">G</span>
                Sign up with Google zkLogin
            </button>

            <div style="display:flex;justify-content:center;margin-top:0.85rem;">
                <span style="display:inline-flex;align-items:center;gap:0.4rem;border:1px solid rgba(34,211,238,0.25);background:rgba(34,211,238,0.08);color:#67e8f9;border-radius:999px;padding:0.35rem 0.75rem;font-size:0.65rem;font-weight:700;">
                    Powered by Sui zkLogin
                </span>
            </div>

            <div style="margin:1.25rem 0;display:flex;align-items:center;gap:0.75rem;">
                <span style="height:1px;background:rgba(255,255,255,0.08);flex:1;"></span>
                <span style="color:#4a4a6a;font-size:0.65rem;">or</span>
                <span style="height:1px;background:rgba(255,255,255,0.08);flex:1;"></span>
            </div>

            <button type="button" @click="showTraditional = true"
                    style="width:100%;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.1);color:#c4c4d8;font-size:0.82rem;font-weight:700;border-radius:0.75rem;padding:0.75rem;cursor:pointer;">
                Or register with email/password
            </button>
        </div>

        <form x-show="showTraditional" x-cloak method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:1rem;">
            @csrf

            <div>
                <label class="auth-label" for="name">Full Name</label>
                <input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="John Doe">
                @error('name') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="auth-label" for="email">Email Address</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@example.com">
                @error('email') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="auth-label" for="password">Password</label>
                <input id="password" x-model="password" class="auth-input" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters">
                <div style="background:rgba(255,255,255,0.05);border-radius:2px;margin-top:0.4rem;overflow:hidden;">
                    <div :style="{
                        width: password.length === 0 ? '0%' : ((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) * 25 + '%',
                        background: ((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) <= 1 ? '#f87171' : (((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) <= 2 ? '#fb923c' : (((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) === 3 ? '#facc15' : '#4ade80'))
                    }" style="height:3px;border-radius:2px;transition:width 0.3s, background 0.3s;"></div>
                </div>
                <p x-text="password.length === 0 ? '' : (((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) <= 1 ? 'Weak' : (((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) <= 2 ? 'Fair' : (((password.length >= 8) + /[A-Z]/.test(password) + /[0-9]/.test(password) + /[^A-Za-z0-9]/.test(password)) === 3 ? 'Good' : 'Strong')))" style="color:#6b6b8a;font-size:0.6rem;margin-top:0.2rem;"></p>
                @error('password') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="auth-label" for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat your password">
                @error('password_confirmation') <p class="error-msg">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="auth-btn" style="margin-top:0.25rem;">Create Account</button>
            <button type="button" @click="showTraditional = false" style="background:transparent;border:none;color:#7C5CFF;font-size:0.75rem;font-weight:700;cursor:pointer;">Back to zkLogin</button>
        </form>

        <p style="text-align:center;color:#5a5a7a;font-size:0.78rem;margin-top:1.5rem;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#7C5CFF;font-weight:700;text-decoration:none;">Sign in</a>
        </p>
    </div>

    @vite(['resources/js/zklogin.js'])
</x-guest-layout>
