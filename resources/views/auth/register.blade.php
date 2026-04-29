<x-guest-layout>
    <style>
        .web3-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            background: linear-gradient(135deg, #00e5ff, #7c3aed 55%, #ff2bd6);
            border: 1px solid rgba(103,232,249,0.35);
            color: white;
            font-size: 0.95rem;
            font-weight: 800;
            border-radius: 0.9rem;
            padding: 0.95rem;
            cursor: pointer;
            box-shadow: 0 0 28px rgba(34,211,238,0.18);
            transition: opacity 0.2s, transform 0.15s;
        }

        .web3-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .web3-btn:active {
            transform: translateY(0);
        }
    </style>

    <div>
        <div style="display:flex;align-items:center;justify-content:center;gap:0.4rem;margin-bottom:1.25rem;">
            <span style="color:#67e8f9;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">1 Auth</span>
            <span style="height:1px;width:28px;background:rgba(255,255,255,0.12);"></span>
            <span style="color:#a78bfa;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">2 Secure</span>
            <span style="height:1px;width:28px;background:rgba(255,255,255,0.12);"></span>
            <span style="color:#f0abfc;font-size:0.65rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">3 Explore</span>
        </div>

        <h2 style="color:white;font-size:1.35rem;font-weight:800;margin:0 0 0.25rem;">Create your Nuance vault</h2>
        <p style="color:#5a5a7a;font-size:0.78rem;margin:0 0 1.25rem;">Start securely with Google zkLogin.</p>

        @if ($errors->any())
            <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#f87171;font-size:0.75rem;margin-bottom:1rem;">
                @foreach ($errors->all() as $error)
                    <p style="margin:0.1rem 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <button type="button" id="zklogin-btn" data-zk-mode="register" class="web3-btn">
            <span style="width:24px;height:24px;border-radius:50%;background:white;color:#111827;display:inline-flex;align-items:center;justify-content:center;font-weight:900;">G</span>
            Sign up with Google zkLogin
        </button>

        <div style="display:flex;justify-content:center;margin-top:0.85rem;">
            <span style="display:inline-flex;align-items:center;gap:0.4rem;border:1px solid rgba(34,211,238,0.25);background:rgba(34,211,238,0.08);color:#67e8f9;border-radius:999px;padding:0.35rem 0.75rem;font-size:0.65rem;font-weight:700;">
                Powered by Sui zkLogin
            </span>
        </div>

        <p style="text-align:center;color:#5a5a7a;font-size:0.78rem;margin-top:1.5rem;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#7C5CFF;font-weight:700;text-decoration:none;">Sign in</a>
        </p>
    </div>

    @vite(['resources/js/zklogin.js'])
</x-guest-layout>
