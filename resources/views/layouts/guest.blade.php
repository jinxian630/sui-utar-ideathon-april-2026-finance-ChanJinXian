<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Nuance') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: #0a0a14;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            pointer-events: none;
            animation: drift 12s ease-in-out infinite alternate;
        }
        .blob-1 { width: 500px; height: 500px; background: #7C5CFF; top: -120px; left: -120px; animation-duration: 14s; }
        .blob-2 { width: 400px; height: 400px; background: #4f46e5; bottom: -100px; right: -100px; animation-duration: 10s; animation-delay: -4s; }
        .blob-3 { width: 300px; height: 300px; background: #a78bfa; top: 50%; left: 50%; transform: translate(-50%,-50%); animation-duration: 16s; animation-delay: -8s; }

        @keyframes drift {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 20px) scale(1.08); }
        }

        /* Grid dots background */
        .grid-bg {
            position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .auth-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <!-- Background effects -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="grid-bg"></div>

    <div class="auth-card">

        <!-- Logo -->
        <div style="text-align:center; margin-bottom:2rem;">
            <a href="{{ url('/') }}" style="display:inline-flex; flex-direction:column; align-items:center; gap:0.6rem; text-decoration:none;">
                <div style="width:52px;height:52px;background:linear-gradient(135deg,#7C5CFF,#4f46e5);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;color:white;box-shadow:0 0 24px rgba(124,92,255,0.5);letter-spacing:-1px;">N</div>
                <span style="color:white;font-size:1.1rem;font-weight:700;letter-spacing:-0.3px;">Nuance</span>
                <span style="color:#5a5a7a;font-size:0.7rem;letter-spacing:0.1em;text-transform:uppercase;">Finance Tracker</span>
            </a>
        </div>

        <!-- Card -->
        <div style="background:rgba(18,18,30,0.85);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.08);border-radius:1.25rem;padding:2rem;box-shadow:0 24px 64px rgba(0,0,0,0.5);">
            {{ $slot }}
        </div>

        <!-- Footer note -->
        <p style="color:#3a3a5a;font-size:0.65rem;text-align:center;margin-top:1.5rem;">
            &copy; {{ date('Y') }} Nuance · Secure · Web3-Powered
        </p>
    </div>
</body>
</html>
