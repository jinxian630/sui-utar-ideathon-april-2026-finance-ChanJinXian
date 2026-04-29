<x-guest-layout>

    <style>
        .web3-btn {
            width: 100%;
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
        }

        .web3-btn:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .web3-btn:active {
            transform: translateY(0);
        }
    </style>

    <h2 style="color:white;font-size:1.25rem;font-weight:700;margin:0 0 0.25rem;">Welcome back</h2>
    <p style="color:#5a5a7a;font-size:0.78rem;margin:0 0 1.5rem;">Sign in to your Nuance account with Google zkLogin.</p>

    @if (session('status'))
        <div style="background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#4ade80;font-size:0.75rem;margin-bottom:1rem;">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.2);border-radius:0.625rem;padding:0.6rem 0.875rem;color:#f87171;font-size:0.75rem;margin-bottom:1rem;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <button type="button" id="zklogin-btn" data-zk-mode="login" class="web3-btn">
        <span style="width:24px;height:24px;border-radius:50%;background:white;color:#111827;display:inline-flex;align-items:center;justify-content:center;font-weight:900;">G</span>
        Sign in with Google zkLogin
    </button>

    <p style="color:#3a3a5a;font-size:0.65rem;text-align:center;margin-top:0.85rem;">Sui Testnet · Decentralised Identity</p>

    <p style="text-align:center;color:#5a5a7a;font-size:0.78rem;margin-top:1.5rem;">
        Don't have an account?
        <a href="{{ route('register') }}"
           style="color:#7C5CFF;font-weight:700;text-decoration:none;"
           onmouseover="this.style.color='#a78bfa'" onmouseout="this.style.color='#7C5CFF'">
            Create one
        </a>
    </p>

    @vite(['resources/js/zklogin.js'])

</x-guest-layout>
