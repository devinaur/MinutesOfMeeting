<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden - {{ config('app.name', 'YIID MoM Assistant') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="text-center max-w-md bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <h1 class="text-4xl font-extrabold text-slate-900 mb-2">403</h1>
        <h2 class="text-base font-semibold text-slate-800 mb-1">Access Restricted</h2>
        <p class="text-slate-500 text-sm mb-6">{{ $exception->getMessage() ?: 'You do not have authorization to view or process this resource.' }}</p>
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#00236f] hover:bg-[#1e3a8a] rounded-lg transition-colors shadow-2xs">
            &larr; Return to MoM Assistant
        </a>
    </div>
</body>
</html>
