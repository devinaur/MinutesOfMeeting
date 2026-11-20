@props([
    'type' => null,
    'title' => null,
    'message' => null,
])

@php
$flashData = null;

if ($message) {
    $flashData = [
        'type' => $type ?? 'info',
        'title' => $title,
        'message' => $message,
    ];
} elseif (session()->has('success')) {
    $flashData = [
        'type' => 'success',
        'title' => 'Success',
        'message' => session('success'),
    ];
} elseif (session()->has('warning')) {
    $flashData = [
        'type' => 'warning',
        'title' => 'SLA Warning',
        'message' => session('warning'),
    ];
} elseif (session()->has('error')) {
    $flashData = [
        'type' => 'error',
        'title' => 'An Error Occurred',
        'message' => session('error'),
    ];
} elseif (session()->has('status')) {
    $flashData = [
        'type' => 'info',
        'title' => 'Information',
        'message' => session('status'),
    ];
}

$styles = [
    'success' => [
        'bg' => 'bg-[#ecfdf5]',
        'border' => 'border-[#10b981]',
        'title' => 'text-[#065f46]',
        'text' => 'text-[#047857]',
        'icon' => 'check_circle',
    ],
    'warning' => [
        'bg' => 'bg-[#fff7ed]',
        'border' => 'border-[#fd761a]',
        'title' => 'text-[#9a3412]',
        'text' => 'text-[#c2410c]',
        'icon' => 'warning',
    ],
    'error' => [
        'bg' => 'bg-[#fef2f2]',
        'border' => 'border-[#dc2626]',
        'title' => 'text-[#991b1b]',
        'text' => 'text-[#b91c1c]',
        'icon' => 'error',
    ],
    'info' => [
        'bg' => 'bg-[#eff3ff]',
        'border' => 'border-[#00236f]',
        'title' => 'text-[#00164e]',
        'text' => 'text-[#1e3a8a]',
        'icon' => 'info',
    ],
];
@endphp

@if ($flashData)
    @php
    $typeKey = $flashData['type'] ?? 'info';
    $style = $styles[$typeKey] ?? $styles['info'];
    @endphp

    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        {{ $attributes->merge(['class' => "p-3.5 {$style['bg']} border-l-4 {$style['border']} rounded-r-lg flex items-start gap-3 shadow-xs mb-4"]) }}
        role="alert"
    >
        <div class="shrink-0 mt-0.5">
            @if ($style['icon'] === 'check_circle')
                <svg class="w-5 h-5 text-[#059669]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @elseif ($style['icon'] === 'warning')
                <svg class="w-5 h-5 text-[#c2410c]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            @elseif ($style['icon'] === 'error')
                <svg class="w-5 h-5 text-[#dc2626]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @else
                <svg class="w-5 h-5 text-[#00236f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            @endif
        </div>

        <div class="flex-1 min-w-0">
            @if (!empty($flashData['title']))
                <h4 class="text-xs font-bold uppercase tracking-wider {{ $style['title'] }}">
                    {{ $flashData['title'] }}
                </h4>
            @endif
            <div class="text-sm font-medium leading-relaxed {{ $style['text'] }} mt-0.5">
                {{ $flashData['message'] }}
            </div>
        </div>

        <button
            type="button"
            x-on:click="show = false"
            class="shrink-0 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded-md transition-colors"
            aria-label="Close"
        >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif
