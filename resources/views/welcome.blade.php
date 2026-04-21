<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Nuance') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
            color: white;
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

        /* Grid dots background */
        .grid-bg {
            position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #7C5CFF, #4f46e5);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            text-decoration: none;
            box-shadow: 0 4px 16px rgba(124,92,255,0.4);
            transition: all 0.2s;
            margin: 0.5rem;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-2px); }

        .btn-secondary {
            display: inline-block;
            background: rgba(255,255,255,0.05);
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.875rem 2rem;
            border-radius: 0.75rem;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.2s;
            margin: 0.5rem;
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }

        .zk-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(124,92,255,0.15);
            color: #a78bfa;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            border: 1px solid rgba(124,92,255,0.3);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="grid-bg"></div>

    <div class="hero-content">
        <!-- Logo -->
        <div style="width:72px;height:72px;background:linear-gradient(135deg,#7C5CFF,#4f46e5);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;font-weight:900;color:white;box-shadow:0 0 32px rgba(124,92,255,0.5);margin:0 auto 1.5rem;">N</div>
        
        <div class="zk-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6C12 6 8 11.5 8 14.5C8 16.9 9.8 19 12 19C14.2 19 16 16.9 16 14.5C16 11.5 12 6 12 6Z" fill="currentColor"/></svg>
            Powered by Sui zkLogin
        </div>

        <h1 style="font-size:3rem;font-weight:800;line-height:1.1;letter-spacing:-1px;margin:0 0 1rem;">
            Nuance <br>
            <span style="color:#a78bfa;">Finance Tracker</span>
        </h1>
        
        <p style="color:#a0a0c0;font-size:1.1rem;line-height:1.6;margin:0 0 2.5rem;max-width:500px;margin-left:auto;margin-right:auto;">
            Take control of your financial future. Track expenses, earn achievement badges, and leverage our AI-robo advisor to invest smarter.
        </p>

        <div>
            <a href="{{ route('login') }}" class="btn-primary">Log in</a>
            <a href="{{ route('register') }}" class="btn-secondary">Register</a>
        </div>

        <p style="color:#5a5a7a;font-size:0.75rem;margin-top:4rem;">
            UCCT3114 Assignment 1
        </p>
    </div>
</body>
</html>
