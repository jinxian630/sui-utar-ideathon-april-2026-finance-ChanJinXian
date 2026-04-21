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
        .error-msg { color: #f87171; font-size: 0.7rem; margin-top: 0.3rem; }

        /* Password strength bar */
        #strength-bar {
            height: 3px; border-radius: 2px; margin-top: 0.4rem;
            transition: width 0.3s, background 0.3s;
            background: #7C5CFF; width: 0%;
        }
    </style>

    <!-- Heading -->
    <h2 style="color:white;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">Create your account 🚀</h2>
    <p style="color:#5a5a7a;font-size:0.78rem;margin:0 0 1.5rem;">Start tracking your finances with Nuance</p>

    <!-- Validation errors -->
    @if ($errors->any())
        <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#f87171;font-size:0.75rem;margin-bottom:1rem;">
            @foreach ($errors->all() as $error)
                <p style="margin:0.1rem 0;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" style="display:flex;flex-direction:column;gap:1rem;">
        @csrf

        <!-- Name -->
        <div>
            <label class="auth-label" for="name">Full Name</label>
            <input id="name" class="auth-input" type="text" name="name"
                   value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="John Doe">
            @error('name')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email -->
        <div>
            <label class="auth-label" for="email">Email Address</label>
            <input id="email" class="auth-input" type="email" name="email"
                   value="{{ old('email') }}" required autocomplete="username"
                   placeholder="you@example.com">
            @error('email')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label class="auth-label" for="password">Password</label>
            <input id="password" class="auth-input" type="password" name="password"
                   required autocomplete="new-password" placeholder="Min. 8 characters"
                   oninput="updateStrength(this.value)">
            <div style="background:rgba(255,255,255,0.05);border-radius:2px;margin-top:0.4rem;overflow:hidden;">
                <div id="strength-bar"></div>
            </div>
            <p id="strength-label" style="color:#3a3a5a;font-size:0.6rem;margin-top:0.2rem;"></p>
            @error('password')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="auth-label" for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" class="auth-input" type="password"
                   name="password_confirmation" required autocomplete="new-password"
                   placeholder="Repeat your password">
            @error('password_confirmation')
                <p class="error-msg">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="auth-btn" style="margin-top:0.25rem;">Create Account →</button>
    </form>

    <!-- Login link -->
    <p style="text-align:center;color:#5a5a7a;font-size:0.78rem;margin-top:1.5rem;">
        Already have an account?
        <a href="{{ route('login') }}"
           style="color:#7C5CFF;font-weight:700;text-decoration:none;"
           onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#7C5CFF'">
            Sign in →
        </a>
    </p>

    <script>
        function updateStrength(val) {
            const bar   = document.getElementById('strength-bar');
            const label = document.getElementById('strength-label');
            let score = 0;
            if (val.length >= 8)  score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: '0%',   color: '#3a3a5a', text: '' },
                { pct: '25%',  color: '#f87171', text: 'Weak' },
                { pct: '50%',  color: '#fb923c', text: 'Fair' },
                { pct: '75%',  color: '#facc15', text: 'Good' },
                { pct: '100%', color: '#4ade80', text: 'Strong 💪' },
            ];
            const lvl = levels[score] || levels[0];
            bar.style.width   = lvl.pct;
            bar.style.background = lvl.color;
            label.style.color    = lvl.color;
            label.textContent    = lvl.text;
        }
    </script>

</x-guest-layout>
