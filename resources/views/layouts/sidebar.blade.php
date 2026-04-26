{{-- FIX 10: Icon-only LEFT SIDEBAR — active state purple circle --}}
<aside x-data="{ expanded: false }"
       @click="expanded = !expanded"
       class="h-full bg-[#0f0f1a] border-r border-white/5 flex flex-col py-5 shrink-0 z-40 transition-all duration-300 overflow-hidden cursor-pointer"
       :class="expanded ? 'w-56 px-4' : 'w-16 items-center px-0'">

    {{-- Logo --}}
    <div class="flex items-center" :class="expanded ? 'mb-8 px-1' : 'justify-center mb-6'">
        <a href="{{ route('dashboard') }}" @click.stop
           class="w-10 h-10 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center text-white font-extrabold text-lg shadow-[0_0_15px_rgba(124,92,255,0.45)] hover:shadow-[0_0_22px_rgba(124,92,255,0.7)] transition-all shrink-0">
            N
        </a>
        <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-bold text-lg tracking-wide text-white whitespace-nowrap">Nuance</span>
    </div>

    <div class="text-[9px] font-semibold uppercase tracking-widest text-gray-600 mb-4 transition-all"
         :class="expanded ? 'px-3 text-left w-full' : 'text-center'">Menu</div>

    {{-- Nav Icons --}}
    <nav class="flex flex-col gap-1 flex-1 w-full" :class="expanded ? '' : 'items-center px-2'">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" title="Dashboard" @click.stop
           class="{{ request()->routeIs('dashboard')
                        ? 'text-white bg-purple-600 border-purple-500 shadow-[0_0_12px_rgba(124,92,255,0.5)]'
                        : 'text-gray-500 border-transparent hover:text-gray-200 hover:bg-white/5' }}
                  h-10 rounded-xl flex items-center border transition-all duration-200"
           :class="expanded ? 'w-full px-3 justify-start' : 'w-10 justify-center'">
            <div class="shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.061l8.69-8.69z"/>
                    <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198c.03-.028.061-.056.091-.086L12 5.432z"/>
                </svg>
            </div>
            <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Dashboard</span>
        </a>

        {{-- Savings --}}
        <a href="{{ route('savings.index') }}" title="Savings" @click.stop
           class="{{ request()->routeIs('savings.*')
                        ? 'text-white bg-purple-600 border-purple-500 shadow-[0_0_12px_rgba(124,92,255,0.5)]'
                        : 'text-gray-500 border-transparent hover:text-gray-200 hover:bg-white/5' }}
                  h-10 rounded-xl flex items-center border transition-all duration-200"
           :class="expanded ? 'w-full px-3 justify-start' : 'w-10 justify-center'">
            <div class="shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                  <path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z" />
                  <path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd" />
                </svg>
            </div>
            <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Savings</span>
        </a>

        {{-- Badges --}}
        <a href="{{ route('badges') }}" title="Badges" @click.stop
           class="{{ request()->routeIs('badges')
                        ? 'text-white bg-purple-600 border-purple-500 shadow-[0_0_12px_rgba(124,92,255,0.5)]'
                        : 'text-gray-500 border-transparent hover:text-gray-200 hover:bg-white/5' }}
                  h-10 rounded-xl flex items-center border transition-all duration-200"
           :class="expanded ? 'w-full px-3 justify-start' : 'w-10 justify-center'">
            <div class="shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 00-.584.859 6.753 6.753 0 006.138 5.6 6.73 6.73 0 002.743 1.346A6.707 6.707 0 019.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a2.25 2.25 0 000 4.5h9a2.25 2.25 0 000-4.5h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.706 6.706 0 01-1.112-3.173 6.73 6.73 0 002.743-1.347 6.753 6.753 0 006.139-5.6.75.75 0 00-.585-.858 47.077 47.077 0 00-3.07-.543V2.62a.75.75 0 00-.658-.744 49.798 49.798 0 00-6.093-.377.75.75 0 00-.657.744zm0 2.629c0 1.196.312 2.32.857 3.294A5.266 5.266 0 013.16 5.337a45.6 45.6 0 012.006-.343v.256zm13.5 0v-.256c.674.1 1.343.214 2.006.343a5.265 5.265 0 01-2.863 3.207 6.72 6.72 0 00.857-3.294z" clip-rule="evenodd"/>
                </svg>
            </div>
            <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Badges</span>
        </a>

        {{-- Wallet --}}
        <a href="{{ route('profile.edit') }}" title="Wallet / Profile" @click.stop
           class="{{ request()->routeIs('profile.*')
                        ? 'text-white bg-purple-600 border-purple-500 shadow-[0_0_12px_rgba(124,92,255,0.5)]'
                        : 'text-gray-500 border-transparent hover:text-gray-200 hover:bg-white/5' }}
                  h-10 rounded-xl flex items-center border transition-all duration-200"
           :class="expanded ? 'w-full px-3 justify-start' : 'w-10 justify-center'">
            <div class="shrink-0 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path d="M2.273 5.625A4.483 4.483 0 015.25 4.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0018.75 3H5.25a3 3 0 00-2.977 2.625zM2.273 8.625A4.483 4.483 0 015.25 7.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0018.75 6H5.25a3 3 0 00-2.977 2.625zM5.25 9a3 3 0 00-3 3v6a3 3 0 003 3h13.5a3 3 0 003-3v-6a3 3 0 00-3-3H15a.75.75 0 00-.75.75 2.25 2.25 0 01-4.5 0A.75.75 0 009 9H5.25z"/>
                </svg>
            </div>
            <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Wallet</span>
        </a>

        @if(auth()->user()?->isAdmin())
            {{-- Admin --}}
            <a href="{{ route('admin.index') }}" title="Admin Panel" @click.stop
               class="{{ request()->routeIs('admin.*')
                            ? 'text-white bg-cyan-600 border-cyan-500 shadow-[0_0_12px_rgba(34,211,238,0.45)]'
                            : 'text-gray-500 border-transparent hover:text-gray-200 hover:bg-white/5' }}
                      h-10 rounded-xl flex items-center border transition-all duration-200"
               :class="expanded ? 'w-full px-3 justify-start' : 'w-10 justify-center'">
                <div class="shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M12 2.25c.244 0 .486.04.717.118l6 2.027A1.875 1.875 0 0120 6.17v4.705c0 4.475-2.59 8.548-6.64 10.453l-.56.263a1.875 1.875 0 01-1.6 0l-.56-.263A11.625 11.625 0 014 10.875V6.17c0-.803.51-1.517 1.283-1.775l6-2.027A2.25 2.25 0 0112 2.25zm3.53 7.28a.75.75 0 00-1.06-1.06L11 11.94l-1.47-1.47a.75.75 0 00-1.06 1.06l2 2a.75.75 0 001.06 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Admin Panel</span>
            </a>
        @endif

    </nav>

    <div class="mt-auto w-full flex flex-col gap-4" :class="expanded ? 'px-4' : 'px-2 items-center'">
        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" @click.stop
                    class="text-red-500 border-transparent hover:text-red-400 hover:bg-red-500/10 h-10 rounded-xl flex items-center border transition-all duration-200 w-full"
                    :class="expanded ? 'px-3 justify-start' : 'w-10 justify-center mx-auto'">
                <div class="shrink-0 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm10.72 4.72a.75.75 0 011.06 0l3 3a.75.75 0 010 1.06l-3 3a.75.75 0 11-1.06-1.06l1.72-1.72H9a.75.75 0 010-1.5h10.94l-1.72-1.72a.75.75 0 010-1.06z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span x-show="expanded" x-transition.opacity.duration.300ms x-cloak class="ml-3 font-medium text-sm whitespace-nowrap">Logout</span>
            </button>
        </form>

        {{-- Expand/Collapse Hint (Optional) --}}
        <div class="w-full flex text-gray-600" :class="expanded ? 'justify-end pr-1' : 'justify-center'">
            <svg x-show="!expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 4.5l7.5 7.5-7.5 7.5m-6-15l7.5 7.5-7.5 7.5" />
            </svg>
            <svg x-cloak x-show="expanded" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 hover:text-white transition-colors cursor-pointer" @click.stop="expanded = false">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
            </svg>
        </div>
    </div>

</aside>
