<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Finance Tracker') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            * { font-family: 'Inter', sans-serif; }
            [x-cloak] { display: none !important; }
            body { background: #0D0D14; }
            ::-webkit-scrollbar { width: 8px; height: 8px; }
            ::-webkit-scrollbar-track { background: #0D0D14; }
            ::-webkit-scrollbar-thumb { background: #4a4a6a; border-radius: 10px; border: 2px solid #0D0D14; }
            ::-webkit-scrollbar-thumb:hover { background: #7C5CFF; }
        </style>
    </head>
    <body class="antialiased text-gray-300 bg-[#0D0D14] overflow-x-hidden">

        <div class="flex min-h-screen w-full bg-[#0D0D14]">

            {{-- ======== LEFT SIDEBAR ======== --}}
            <div class="sticky top-0 h-screen shrink-0">
                @include('layouts.sidebar')
            </div>

            {{-- ======== MAIN AREA ======== --}}
            <div class="flex flex-col flex-1 min-w-0">

                {{-- TOP NAVIGATION BAR --}}
                <div class="sticky top-0 z-50 shrink-0">
                    @include('layouts.topnav')
                </div>

                {{-- PAGE CONTENT --}}
                <main class="flex-1 bg-[#0D0D14]">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)"
                 x-cloak
                 class="fixed top-20 right-6 z-[9999] max-w-sm rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-200 shadow-2xl">
                {{ session('error') }}
            </div>
        @endif

        <x-badge-popup
            :wallet-address="optional(auth()->user())->wallet_address ?? optional(auth()->user())->sui_address ?? ''"
            :package-id="config('sui.package_id')"
        />

        @auth
            <x-chat-widget />
        @endauth

    </body>
</html>
