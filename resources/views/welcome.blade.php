<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Nuance') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #080b12;
            color: #f8fafc;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,0.9), transparent 80%);
        }
        a { color: inherit; }
        .page {
            position: relative;
            overflow: hidden;
        }
        .container {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }
        .nav {
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(18px);
            background: rgba(8, 11, 18, 0.82);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .nav-inner {
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            min-width: 0;
        }
        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #22d3ee, #7c3aed 52%, #d946ef);
            color: white;
            font-weight: 900;
            box-shadow: 0 0 26px rgba(34,211,238,0.24);
            flex: 0 0 auto;
        }
        .brand-text strong {
            display: block;
            font-size: 1rem;
            letter-spacing: -0.02em;
        }
        .brand-text span {
            display: block;
            color: #64748b;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-top: 0.1rem;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.15rem;
            color: #94a3b8;
            font-size: 0.84rem;
            font-weight: 650;
        }
        .nav-links a {
            text-decoration: none;
            transition: color 0.18s;
        }
        .nav-links a:hover { color: #e2e8f0; }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 800;
            border: 1px solid rgba(255,255,255,0.1);
            white-space: nowrap;
        }
        .btn-primary {
            border: 0;
            background: linear-gradient(135deg, #22d3ee, #7c3aed 56%, #d946ef);
            color: white;
            box-shadow: 0 14px 34px rgba(34,211,238,0.16);
        }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            color: #e2e8f0;
        }
        .hero {
            padding: 76px 0 54px;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 0.86fr);
            gap: 3.5rem;
            align-items: center;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #67e8f9;
            background: rgba(34,211,238,0.08);
            border: 1px solid rgba(34,211,238,0.22);
            border-radius: 999px;
            padding: 0.42rem 0.75rem;
            font-size: 0.76rem;
            font-weight: 800;
        }
        .hero h1 {
            margin: 1.1rem 0 1rem;
            font-size: clamp(2.7rem, 7vw, 5.25rem);
            line-height: 0.95;
            letter-spacing: -0.06em;
            max-width: 760px;
        }
        .hero h1 span {
            color: #67e8f9;
        }
        .hero-copy {
            color: #94a3b8;
            font-size: clamp(1rem, 2vw, 1.16rem);
            line-height: 1.72;
            max-width: 650px;
            margin: 0;
        }
        .hero-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1.65rem;
            flex-wrap: wrap;
        }
        .trust-row {
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
            margin-top: 1.4rem;
        }
        .trust-chip {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            color: #cbd5e1;
            border-radius: 999px;
            padding: 0.45rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .preview {
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(180deg, rgba(15,23,42,0.96), rgba(12,16,29,0.96));
            border-radius: 22px;
            padding: 1rem;
            box-shadow: 0 28px 90px rgba(0,0,0,0.38), 0 0 44px rgba(34,211,238,0.08);
        }
        .preview-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0.45rem 1rem;
        }
        .window-dots { display: flex; gap: 0.35rem; }
        .window-dots span {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #334155;
        }
        .preview-status {
            color: #67e8f9;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .preview-card {
            border: 1px solid rgba(255,255,255,0.09);
            background: #0b1020;
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 0.75rem;
        }
        .metric-label {
            color: #64748b;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 800;
        }
        .metric-value {
            font-size: 2.15rem;
            font-weight: 900;
            letter-spacing: -0.05em;
            margin-top: 0.35rem;
        }
        .metric-sub {
            color: #94a3b8;
            font-size: 0.84rem;
            margin-top: 0.2rem;
        }
        .progress-track {
            height: 10px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            overflow: hidden;
            margin-top: 0.8rem;
        }
        .progress-fill {
            width: 76%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #22d3ee, #d946ef);
        }
        .mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .mini-card {
            border: 1px solid rgba(255,255,255,0.09);
            background: rgba(255,255,255,0.035);
            border-radius: 14px;
            padding: 0.85rem;
        }
        .mini-card strong {
            display: block;
            color: #f8fafc;
            font-size: 0.92rem;
            margin-top: 0.25rem;
        }
        .mini-card span {
            color: #94a3b8;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .ai-bubble {
            border-color: rgba(34,211,238,0.18);
            background: rgba(34,211,238,0.075);
        }
        .ai-bubble p {
            margin: 0.45rem 0 0;
            color: #dbeafe;
            line-height: 1.5;
            font-size: 0.84rem;
        }
        .section {
            padding: 54px 0;
        }
        .section-heading {
            max-width: 720px;
            margin-bottom: 1.4rem;
        }
        .section-heading h2 {
            margin: 0;
            font-size: clamp(1.9rem, 4vw, 3.15rem);
            line-height: 1;
            letter-spacing: -0.045em;
        }
        .section-heading p {
            color: #94a3b8;
            line-height: 1.65;
            font-size: 1rem;
            margin: 0.85rem 0 0;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }
        .feature-card {
            min-height: 218px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(15,23,42,0.72);
            border-radius: 18px;
            padding: 1.15rem;
        }
        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(34,211,238,0.1);
            color: #67e8f9;
            font-weight: 900;
            margin-bottom: 0.95rem;
        }
        .feature-card h3 {
            margin: 0;
            font-size: 1.05rem;
        }
        .feature-card p {
            margin: 0.55rem 0 0;
            color: #94a3b8;
            line-height: 1.55;
            font-size: 0.9rem;
        }
        .flow {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.035);
            border-radius: 20px;
            padding: 1rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .flow-step {
            background: #0b1020;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 1rem;
        }
        .flow-step b {
            color: #67e8f9;
            font-size: 0.75rem;
        }
        .flow-step strong {
            display: block;
            margin-top: 0.35rem;
        }
        .flow-step p {
            color: #94a3b8;
            margin: 0.5rem 0 0;
            line-height: 1.5;
            font-size: 0.84rem;
        }
        .cta {
            margin: 54px 0 68px;
            border: 1px solid rgba(34,211,238,0.18);
            background: linear-gradient(135deg, rgba(34,211,238,0.1), rgba(124,58,237,0.11), rgba(217,70,239,0.09));
            border-radius: 24px;
            padding: clamp(1.35rem, 5vw, 2.4rem);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .cta h2 {
            margin: 0;
            font-size: clamp(1.55rem, 4vw, 2.5rem);
            letter-spacing: -0.045em;
        }
        .cta p {
            margin: 0.55rem 0 0;
            color: #cbd5e1;
            line-height: 1.55;
            max-width: 660px;
        }
        footer {
            border-top: 1px solid rgba(255,255,255,0.08);
            color: #64748b;
            padding: 1.4rem 0;
            font-size: 0.82rem;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        @media (max-width: 920px) {
            .nav-links { display: none; }
            .hero-grid { grid-template-columns: 1fr; gap: 2rem; }
            .features-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .flow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cta { align-items: flex-start; flex-direction: column; }
        }
        @media (max-width: 620px) {
            .container { width: min(100% - 24px, 1120px); }
            .nav-inner { height: auto; padding: 0.75rem 0; align-items: flex-start; }
            .brand-text span { display: none; }
            .nav-actions { gap: 0.45rem; }
            .btn { min-height: 38px; padding: 0 0.75rem; font-size: 0.78rem; }
            .hero { padding: 42px 0 34px; }
            .trust-row { gap: 0.45rem; }
            .trust-chip { font-size: 0.68rem; }
            .preview { padding: 0.75rem; }
            .mini-grid, .features-grid, .flow { grid-template-columns: 1fr; }
            .section { padding: 38px 0; }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="nav">
            <div class="container nav-inner">
                <a class="brand" href="{{ url('/') }}">
                    <div class="brand-mark">N</div>
                    <div class="brand-text">
                        <strong>Nuance</strong>
                        <span>Finance Tracker</span>
                    </div>
                </a>

                <nav class="nav-links" aria-label="Landing page navigation">
                    <a href="#features">Features</a>
                    <a href="#zklogin">Sui zkLogin</a>
                    <a href="#ai">AI Assistant</a>
                    <a href="#security">Security</a>
                </nav>

                <div class="nav-actions">
                    <a href="{{ route('login') }}" class="btn btn-secondary">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                </div>
            </div>
        </header>

        <main>
            <section class="hero">
                <div class="container hero-grid">
                    <div>
                        <div class="eyebrow">Sui Testnet savings + Gemini AI guidance</div>
                        <h1>Build savings habits with <span>Web3 proof</span>.</h1>
                        <p class="hero-copy">
                            Nuance helps users track income and expenses, move money into goals, unlock milestone badges, connect a Sui zkLogin wallet, and ask an AI financial assistant for practical savings recommendations.
                        </p>

                        <div class="hero-actions">
                            <a href="{{ route('register') }}" class="btn btn-primary">Create Account</a>
                            <a href="{{ route('login') }}" class="btn btn-secondary">Log in</a>
                        </div>

                        <div class="trust-row">
                            <span class="trust-chip">Google zkLogin onboarding</span>
                            <span class="trust-chip">Wallet-linked actions</span>
                            <span class="trust-chip">Role-based access</span>
                        </div>
                    </div>

                    <aside class="preview" aria-label="Dashboard preview">
                        <div class="preview-top">
                            <div class="window-dots"><span></span><span></span><span></span></div>
                            <span class="preview-status">Live Finance View</span>
                        </div>

                        <div class="preview-card">
                            <div class="metric-label">Wallet Balance</div>
                            <div class="metric-value">RM 4,400.00</div>
                            <div class="metric-sub">RM 600.00 away from Diamond Saver</div>
                            <div class="progress-track"><div class="progress-fill"></div></div>
                        </div>

                        <div class="mini-grid">
                            <div class="mini-card">
                                <span>Next Badge</span>
                                <strong>Diamond Saver</strong>
                            </div>
                            <div class="mini-card">
                                <span>Goal Progress</span>
                                <strong>76%</strong>
                            </div>
                        </div>

                        <div class="preview-card ai-bubble" style="margin-top:0.75rem;margin-bottom:0;">
                            <div class="metric-label">AI Assistant</div>
                            <p>Try RM 150 weekly goal deposits and round-ups to reach your next milestone faster.</p>
                        </div>
                    </aside>
                </div>
            </section>

            <section id="features" class="section">
                <div class="container">
                    <div class="section-heading">
                        <h2>Everything users need to manage savings milestones.</h2>
                        <p>Nuance combines daily finance tracking with goal progress, badge rewards, Sui identity, and AI advice in one dashboard.</p>
                    </div>

                    <div class="features-grid">
                        <article class="feature-card">
                            <div class="feature-icon">RM</div>
                            <h3>Track Money</h3>
                            <p>Record income and expenses, monitor wallet balance, and review recent transaction history from a focused dashboard.</p>
                        </article>
                        <article class="feature-card">
                            <div class="feature-icon">GO</div>
                            <h3>Save Toward Goals</h3>
                            <p>Create saving goals, deposit wallet funds, track progress, and withdraw completed goals back into the user wallet.</p>
                        </article>
                        <article class="feature-card">
                            <div class="feature-icon">LV</div>
                            <h3>Earn Badges</h3>
                            <p>Unlock Saver, Investor, Wealth Builder, Diamond Saver, and Finance Master milestone badges as savings grow.</p>
                        </article>
                        <article id="zklogin" class="feature-card">
                            <div class="feature-icon">ZK</div>
                            <h3>Sui zkLogin</h3>
                            <p>Register with Google, set a Nuance PIN, reveal a generated Sui Testnet wallet, and link identity to on-chain records.</p>
                        </article>
                        <article id="ai" class="feature-card">
                            <div class="feature-icon">AI</div>
                            <h3>AI Financial Assistant</h3>
                            <p>Ask Gemini-powered questions about saving habits, badge forecasts, goal deadlines, and next practical actions.</p>
                        </article>
                        <article id="security" class="feature-card">
                            <div class="feature-icon">RB</div>
                            <h3>Secure Access</h3>
                            <p>Role-based access protects admin pages while wallet-linked middleware protects Sui and goal-sensitive actions.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section-heading">
                        <h2>From signup to savings in four steps.</h2>
                        <p>The registration flow bridges Web2 familiarity with Web3-backed identity, so users can start without manually managing private keys.</p>
                    </div>

                    <div class="flow">
                        <div class="flow-step">
                            <b>Step 1</b>
                            <strong>Register</strong>
                            <p>Use Google zkLogin or the traditional email and password path.</p>
                        </div>
                        <div class="flow-step">
                            <b>Step 2</b>
                            <strong>Secure</strong>
                            <p>Set a 6-digit Nuance PIN for wallet identity generation.</p>
                        </div>
                        <div class="flow-step">
                            <b>Step 3</b>
                            <strong>Discover Wallet</strong>
                            <p>See the generated Sui Testnet address on the wallet welcome page.</p>
                        </div>
                        <div class="flow-step">
                            <b>Step 4</b>
                            <strong>Track Progress</strong>
                            <p>Add transactions, save to goals, earn badges, and ask the AI assistant for guidance.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="container">
                <div class="cta">
                    <div>
                        <h2>Ready to try Nuance?</h2>
                        <p>Create an account to explore goal deposits, badge progress, Sui wallet onboarding, and AI-powered savings recommendations.</p>
                    </div>
                    <div class="hero-actions" style="margin-top:0;">
                        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">Log in</a>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <div class="container footer-row">
                <span>Nuance Finance Tracker</span>
                <span> Laravel · Sui Testnet · Gemini AI</span>
            </div>
        </footer>
    </div>
</body>
</html>
