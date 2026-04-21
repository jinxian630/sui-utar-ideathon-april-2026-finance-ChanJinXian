<x-guest-layout>

    <style>
        .auth-label {
            display: block;
            font-size: 0.65rem;
            color: #6b6b8a;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.4rem;
            font-weight: 600;
        }
        .auth-input {
            width: 100%;
            background: #0a0a14;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 0.75rem;
            color: white;
            font-size: 0.875rem;
            padding: 0.7rem 1rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            box-sizing: border-box;
        }
        .auth-input::placeholder { color: #3a3a5a; }
        .auth-input:focus {
            border-color: rgba(124,92,255,0.5);
            box-shadow: 0 0 0 3px rgba(124,92,255,0.12);
        }
        .auth-btn {
            width: 100%;
            background: linear-gradient(135deg, #7C5CFF, #4f46e5);
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            border-radius: 0.75rem;
            padding: 0.75rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(124,92,255,0.4);
            transition: opacity 0.2s, transform 0.15s;
            letter-spacing: 0.02em;
        }
        .auth-btn:hover { opacity: 0.9; transform: translateY(-1px); }
        .auth-btn:active { transform: translateY(0); }
        .auth-divider {
            display: flex; align-items: center; gap: 0.75rem;
            margin: 1.25rem 0;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
        }
        .auth-divider span { color: #3a3a5a; font-size: 0.65rem; white-space: nowrap; }
        .web3-btn {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            color: #a0a0c0;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.7rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .web3-btn:hover { background: rgba(124,92,255,0.12); border-color: rgba(124,92,255,0.3); color: white; }
        .error-msg { color: #f87171; font-size: 0.7rem; margin-top: 0.3rem; }
    </style>

    <!-- Heading -->
    <h2 style="color:white;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">Welcome back 👋</h2>
    <p style="color:#5a5a7a;font-size:0.78rem;margin:0 0 1.5rem;">Sign in to your Nuance account</p>

    <!-- Session Status -->
    @if (session('status'))
        <div style="background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#4ade80;font-size:0.75rem;margin-bottom:1rem;">
            {{ session('status') }}
        </div>
    @endif

    <!-- General errors -->
    @if ($errors->any())
        <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#f87171;font-size:0.75rem;margin-bottom:1rem;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:1rem;">
        @csrf

        <!-- Email -->
        <div>
            <label class="auth-label" for="email">Email Address</label>
            <input id="email" class="auth-input" type="email" name="email"
                   value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="you@example.com">
        </div>

        <!-- Password -->
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
                <label class="auth-label" for="password" style="margin-bottom:0;">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="color:#7C5CFF;font-size:0.65rem;font-weight:600;text-decoration:none;transition:color 0.2s;"
                       onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#7C5CFF'">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" class="auth-input" type="password" name="password"
                   required autocomplete="current-password" placeholder="••••••••">
        </div>

        <!-- Remember Me -->
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input id="remember_me" type="checkbox" name="remember"
                   style="width:15px;height:15px;accent-color:#7C5CFF;border-radius:4px;">
            <span style="color:#5a5a7a;font-size:0.78rem;">Remember me</span>
        </label>

        <!-- Submit -->
        <button type="submit" class="auth-btn">Sign In →</button>
    </form>

    <!-- Divider -->
    <div class="auth-divider"><span>or continue with</span></div>

    <!-- zkLogin / Web3 -->
    <button type="button" id="zklogin-btn" class="web3-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
            <path d="M12 4C12 4 8 9.5 8 12.5C8 14.9 9.8 17 12 17C14.2 17 16 14.9 16 12.5C16 9.5 12 4 12 4Z" fill="currentColor"/>
        </svg>
        Sign in with Google (Sui zkLogin · Web3 KYC)
    </button>
    <p style="color:#3a3a5a;font-size:0.6rem;text-align:center;margin-top:0.5rem;">Sui Testnet · Decentralised Identity</p>

    <!-- Register link -->
    <p style="text-align:center;color:#5a5a7a;font-size:0.78rem;margin-top:1.5rem;">
        Don't have an account?
        <a href="{{ route('register') }}"
           style="color:#7C5CFF;font-weight:700;text-decoration:none;"
           onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#7C5CFF'">
            Create one →
        </a>
    </p>

    @vite(['resources/js/zklogin.js'])

</x-guest-layout>
