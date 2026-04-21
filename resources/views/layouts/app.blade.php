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
            ::-webkit-scrollbar { width: 5px; height: 5px; }
            ::-webkit-scrollbar-track { background: transparent; }
            ::-webkit-scrollbar-thumb { background: #2a2a3d; border-radius: 10px; }
            ::-webkit-scrollbar-thumb:hover { background: #3a3a5d; }
        </style>
    </head>
    <body class="antialiased text-gray-300 overflow-hidden">

        <div class="flex h-screen bg-[#0D0D14]">

            {{-- ======== LEFT SIDEBAR ======== --}}
            @include('layouts.sidebar')

            {{-- ======== MAIN AREA ======== --}}
            <div class="flex flex-col flex-1 min-w-0 h-full overflow-hidden">

                {{-- TOP NAVIGATION BAR --}}
                @include('layouts.topnav')

                {{-- PAGE CONTENT --}}
                <main class="flex-1 overflow-y-auto bg-[#0D0D14]">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- AI Chat Widget (all authenticated pages) --}}
        @auth
            <x-chat-widget />
        @endauth

    </body>
</html>
