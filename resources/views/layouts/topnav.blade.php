{{-- TOP NAV BAR --}}
<header class="h-14 bg-[#0f0f1a] border-b border-white/5 flex items-center px-6 gap-4 shrink-0 z-30">

    {{-- Personal Plan Dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-2 bg-[#1a1a2e] border border-white/8 px-4 py-2 rounded-lg text-sm font-medium text-gray-200 hover:bg-white/5 transition-all">
            Personal plan
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-gray-500">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
            </svg>
        </button>
        <div x-show="open" x-cloak x-transition
             class="absolute top-10 left-0 bg-[#12121e] border border-white/10 rounded-xl shadow-2xl py-2 w-44 z-50">
            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">Personal plan</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">Business plan</a>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="flex-1 max-w-lg">
        <div class="relative group">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-600 group-focus-within:text-purple-400 transition-colors">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
            </svg>
            <input type="text" placeholder="Search keyword here..."
                   class="w-full bg-[#1a1a2e] border border-white/8 rounded-xl py-2 pl-9 pr-4 text-sm text-gray-300 focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/30 placeholder-gray-700 transition-all">
        </div>
    </div>

    {{-- Right: Wallet + User --}}
    <div class="flex items-center gap-4 ml-auto">

        {{-- Sui Wallet chip --}}
        @if(Auth::user()->wallet_address)
            <div class="flex items-center gap-2 bg-teal-500/10 border border-teal-500/20 rounded-full px-3 py-1.5 cursor-default" title="{{ Auth::user()->wallet_address }}">
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                <span class="text-teal-400 font-mono text-xs tracking-wide">
                    {{ substr(Auth::user()->wallet_address, 0, 6) }}...{{ substr(Auth::user()->wallet_address, -4) }}
                </span>
            </div>
        @else
            <span class="text-xs text-gray-600 border border-[#2d2d3d] px-3 py-1.5 rounded-full bg-white/3" title="No Sui wallet connected">
                ⛓️ No Wallet
            </span>
        @endif

        {{-- User Profile Dropdown --}}
        <div x-data="{ open: false }" class="relative flex items-center gap-3 cursor-pointer" @click.outside="open = false">
            <div @click="open = !open" class="flex items-center gap-3 group">
                {{-- Avatar letter --}}
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white text-xs font-bold border-2 border-transparent group-hover:border-purple-400 transition-all">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="hidden md:block text-right leading-tight select-none">
                    <p class="text-sm font-semibold text-gray-200 group-hover:text-white transition-colors">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-gray-600 uppercase tracking-wider">{{ Auth::user()->role ?? 'Personal' }} account</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5 text-gray-600">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
            </div>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-11 w-52 bg-[#12121e] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 overflow-hidden z-50 py-2">
                <div class="px-4 py-3 border-b border-white/5">
                    <p class="text-[10px] text-gray-600 uppercase tracking-wider">Signed in as</p>
                    <p class="text-sm text-white font-medium truncate mt-0.5">{{ Auth::user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400 hover:text-white hover:bg-white/5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 opacity-60"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-5.5-2.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM10 12a5.99 5.99 0 00-4.793 2.39A6.483 6.483 0 0010 16.5a6.483 6.483 0 004.793-2.11A5.99 5.99 0 0010 12z" clip-rule="evenodd"/></svg>
                    Profile & Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 opacity-60"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M19 10a.75.75 0 00-.75-.75H8.704l1.048-.943a.75.75 0 10-1.004-1.114l-2.5 2.25a.75.75 0 000 1.114l2.5 2.25a.75.75 0 101.004-1.114l-1.048-.943h9.546A.75.75 0 0019 10z" clip-rule="evenodd"/></svg>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
