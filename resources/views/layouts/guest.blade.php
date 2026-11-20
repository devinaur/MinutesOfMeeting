<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'YIID Minutes of Meeting Assistant') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=mom1">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=mom1">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=mom1">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=mom1">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=mom1">

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50 selection:bg-[#00236f] selection:text-white min-h-full flex flex-col justify-center items-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-md">
            <!-- Corporate Header / Brand -->
            <div class="text-center mb-6">
                <a href="{{ route('home') }}" class="inline-flex flex-col items-center group">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-[#00236f] text-white shadow-md mb-3 group-hover:bg-[#1e3a8a] transition-all">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-slate-900 tracking-tight">{{ config('app.name', 'YIID Minutes of Meeting Assistant') }}</h1>
                    <span class="text-xs text-slate-500 font-medium">PT Yusen Logistics Interlink Indonesia</span>
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white border border-slate-200/90 shadow-sm rounded-2xl p-6 sm:p-8">
                {{ $slot }}
            </div>

            <!-- Footer Note -->
            <div class="text-center mt-6 text-[11px] text-slate-400">
                &copy; {{ date('Y') }} PT Yusen Logistics Interlink Indonesia. All rights reserved.
            </div>
        </div>
    </body>
</html>
