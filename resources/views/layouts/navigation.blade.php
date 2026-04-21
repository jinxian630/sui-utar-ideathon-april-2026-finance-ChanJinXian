<nav x-data="{ open: false }" class="bg-[#12121E] border-b border-white/5 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-[0_0_12px_rgba(124,92,255,0.4)] group-hover:shadow-[0_0_18px_rgba(124,92,255,0.6)] transition-all">
                            N
                        </div>
                        <span class="text-white font-semibold text-sm hidden sm:block tracking-tight">Finance Tracker</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:flex">
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'bg-purple-600/10 text-purple-400 border-purple-500/20' : 'text-gray-400 hover:text-white border-transparent hover:bg-white/5' }} flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition-all duration-150">
                        <span class="text-base">🏠</span> {{ __('Dashboard') }}
                    </a>
                    <a href="{{ route('user') }}"
                       class="{{ request()->routeIs('user') ? 'bg-purple-600/10 text-purple-400 border-purple-500/20' : 'text-gray-400 hover:text-white border-transparent hover:bg-white/5' }} flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition-all duration-150">
                        <span class="text-base">👤</span> {{ __('Users') }}
                    </a>
                </div>
            </div>

            <!-- Right Side: Wallet + User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:gap-4">

                <!-- Sui Wallet Chip -->
                @if(Auth::user()->wallet_address)
                    <div class="flex items-center bg-teal-500/10 border border-teal-500/20 rounded-full px-3 py-1.5 gap-2">
                        <div class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></div>
                        <span class="text-teal-400 font-mono text-xs tracking-wide">
                            {{ substr(Auth::user()->wallet_address, 0, 6) }}...{{ substr(Auth::user()->wallet_address, -4) }}
                        </span>
                    </div>
                @else
                    <span class="text-xs text-gray-500 border border-[#2d2d3d] px-3 py-1.5 rounded-full bg-white/5">
                        ⛓️ No Wallet
                    </span>
                @endif

                <!-- User Dropdown -->
                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 pl-3 border-l border-[#2d2d3d] focus:outline-none group">
                            <div class="text-right hidden md:block">
                                <div class="text-sm font-medium text-gray-200 group-hover:text-white transition-colors">{{ Auth::user()->name }}</div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-wider">{{ Auth::user()->role ?? 'Personal' }}</div>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-[#1A1A2E] border-2 border-[#2d2d3d] group-hover:border-purple-500/50 transition-all flex items-center justify-center">
                                <span class="text-xs font-bold text-purple-400">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                            <svg class="fill-current h-3.5 w-3.5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="bg-[#12121E] border border-[#2d2d3d] rounded-xl shadow-xl shadow-black/40 overflow-hidden py-1 -mt-1">
                            <div class="px-4 py-3 border-b border-[#2d2d3d] mb-1">
                                <p class="text-xs text-gray-500 mb-0.5">Signed in as</p>
                                <p class="text-sm text-white font-medium truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="text-gray-300 hover:text-white hover:bg-[#1A1A2E] flex items-center gap-2 mx-1 rounded-lg text-sm">
                                ⚙️ {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="text-red-400 hover:text-red-300 hover:bg-red-500/10 flex items-center gap-2 mx-1 rounded-lg text-sm">
                                    🚪 {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-200 hover:bg-white/10 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-[#12121E] border-t border-white/5">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'text-purple-400 bg-purple-600/10' : 'text-gray-400' }} block px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-white/5 transition">
                🏠 {{ __('Dashboard') }}
            </a>
            <a href="{{ route('user') }}" class="{{ request()->routeIs('user') ? 'text-purple-400 bg-purple-600/10' : 'text-gray-400' }} block px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-white/5 transition">
                👤 {{ __('Users') }}
            </a>
        </div>

        <div class="pt-4 pb-1 border-t border-white/5 px-4">
            <div class="mb-3">
                <div class="font-medium text-sm text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-white/5 transition">
                    ⚙️ {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2.5 rounded-lg text-sm text-red-400 hover:bg-red-500/10 transition">
                        🚪 {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>


