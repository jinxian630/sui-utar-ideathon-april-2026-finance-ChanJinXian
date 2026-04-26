{{-- TOP NAV BAR --}}
<header class="h-16 border-b flex items-center px-6 shrink-0 z-30 w-full"
        style="background:#0f0f1a; border-color:rgba(255,255,255,0.08); box-shadow:0 2px 20px rgba(0,0,0,0.5); justify-content:flex-end;">

    {{-- Right side only --}}
    <div style="display:flex;align-items:center;gap:0.75rem;">

        {{-- Sui Wallet chip --}}
        @if(Auth::user()->wallet_address)
            <div style="display:flex;align-items:center;gap:0.5rem;background:rgba(20,184,166,0.12);border:1px solid rgba(20,184,166,0.4);border-radius:999px;padding:0.4rem 1rem;cursor:default;"
                 title="{{ Auth::user()->wallet_address }}">
                <span style="width:8px;height:8px;border-radius:50%;background:#2dd4bf;display:inline-block;animation:pulse 2s infinite;flex-shrink:0;"></span>
                <span style="color:#5eead4;font-family:monospace;font-size:0.75rem;font-weight:600;letter-spacing:0.04em;">
                    {{ substr(Auth::user()->wallet_address, 0, 6) }}...{{ substr(Auth::user()->wallet_address, -4) }}
                </span>
            </div>
        @else
            <div style="display:flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.12);border-radius:999px;padding:0.4rem 1rem;">
                <span style="color:#6b7280;font-size:0.75rem;font-weight:500;">⛓️ No Wallet</span>
            </div>
        @endif

        {{-- User Profile Dropdown --}}
        <div x-data="{ open: false }" style="position:relative;" @click.outside="open = false">

            {{-- Trigger Button --}}
            <button @click="open = !open"
                    style="display:flex;align-items:center;gap:0.6rem;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:0.75rem;padding:0.4rem 0.75rem;cursor:pointer;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(124,92,255,0.15)';this.style.borderColor='rgba(124,92,255,0.4)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='rgba(255,255,255,0.12)'">

                {{-- Avatar --}}
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#7C5CFF,#4f46e5);display:flex;align-items:center;justify-content:center;color:white;font-size:0.875rem;font-weight:700;flex-shrink:0;box-shadow:0 0 12px rgba(124,92,255,0.4);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>

                <div style="text-align:left;line-height:1.2;">
                    <p style="color:white;font-size:0.875rem;font-weight:600;white-space:nowrap;">{{ Auth::user()->name }}</p>
                    <p style="color:#9ca3af;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;">{{ Auth::user()->role ?? 'Personal' }}</p>
                </div>

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                     style="width:16px;height:16px;color:#9ca3af;flex-shrink:0;transition:transform 0.2s;"
                     :style="open ? 'transform:rotate(180deg)' : 'transform:rotate(0deg)'">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>

            {{-- Dropdown Panel --}}
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translateY(-4px)"
                 x-transition:enter-end="opacity-100 translateY(0)"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position:absolute;right:0;top:calc(100% + 10px);width:240px;
                        background:#1a1a2e;border:1px solid rgba(255,255,255,0.15);
                        border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,0.8);
                        overflow:hidden;z-index:9999;">

                {{-- Header --}}
                <div style="padding:0.875rem 1rem;border-bottom:1px solid rgba(255,255,255,0.08);background:rgba(124,92,255,0.08);">
                    <p style="color:#9ca3af;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.25rem;">Signed in as</p>
                    <p style="color:white;font-size:0.875rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</p>
                    <p style="color:#6b7280;font-size:0.75rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:0.1rem;">{{ Auth::user()->email }}</p>
                </div>

                {{-- Profile & Settings --}}
                @if(Auth::user()->isAdmin())
                    <a href="{{ route('admin.index') }}"
                       style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;text-decoration:none;transition:background 0.15s;"
                       onmouseover="this.style.background='rgba(34,211,238,0.12)'"
                       onmouseout="this.style.background='transparent'">
                        <div style="width:30px;height:30px;border-radius:0.5rem;background:rgba(34,211,238,0.18);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#67e8f9" style="width:16px;height:16px;">
                                <path fill-rule="evenodd" d="M10 1.944a.75.75 0 01.447.147l6 4.5a.75.75 0 01.303.6V12.5a4.25 4.25 0 01-2.125 3.68l-4.25 2.454a.75.75 0 01-.75 0l-4.25-2.454A4.25 4.25 0 013.25 12.5V7.19a.75.75 0 01.303-.6l6-4.5A.75.75 0 0110 1.944zm2.78 7.336a.75.75 0 10-1.06-1.06L9.25 10.69l-.97-.97a.75.75 0 00-1.06 1.06l1.5 1.5a.75.75 0 001.06 0l3-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span style="color:#e5e7eb;font-size:0.875rem;font-weight:500;">Admin Panel</span>
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}"
                   style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='rgba(124,92,255,0.12)'"
                   onmouseout="this.style.background='transparent'">
                    <div style="width:30px;height:30px;border-radius:0.5rem;background:rgba(124,92,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#a78bfa" style="width:16px;height:16px;">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span style="color:#e5e7eb;font-size:0.875rem;font-weight:500;">Profile &amp; Settings</span>
                </a>

                <div style="border-top:1px solid rgba(255,255,255,0.08);margin-top:0.25rem;padding-top:0.25rem;">
                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                style="width:100%;display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:transparent;border:none;cursor:pointer;transition:background 0.15s;"
                                onmouseover="this.style.background='rgba(239,68,68,0.1)'"
                                onmouseout="this.style.background='transparent'">
                            <div style="width:30px;height:30px;border-radius:0.5rem;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="#f87171" style="width:16px;height:16px;">
                                    <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/>
                                    <path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span style="color:#f87171;font-size:0.875rem;font-weight:500;">Log Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
    </style>
</header>
