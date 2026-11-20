@props([
    'title' => null,
    'value' => null,
    'subtitle' => null,
    'valueClass' => 'text-slate-900',
    'iconColor' => 'slate',
    'active' => false,
    'activeClass' => 'ring-2 ring-[#00236f] border-[#00236f]',
    'clickable' => false,
])

@php
$isClickable = $clickable || $attributes->has('wire:click') || $attributes->has('onclick') || $attributes->has('href');

$iconColorStyles = [
    'blue'    => 'bg-blue-50 text-[#00236f] border-blue-100',
    'emerald' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    'amber'   => 'bg-amber-50 text-amber-600 border-amber-100',
    'rose'    => 'bg-rose-50 text-rose-600 border-rose-100',
    'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-100',
    'purple'  => 'bg-purple-50 text-purple-700 border-purple-100',
    'slate'   => 'bg-slate-50 text-slate-600 border-slate-200/80',
];

$iconStyle = $iconColorStyles[$iconColor] ?? $iconColorStyles['slate'];

$cardClasses = 'bg-white border rounded-xl shadow-xs p-4 sm:p-5 transition-all flex flex-col justify-between '
    . ($isClickable ? 'cursor-pointer select-none active:scale-[0.99] hover:shadow-xs ' : '')
    . ($active ? $activeClass : 'border-slate-200/80 hover:border-slate-300');
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    <div>
        {{-- Top Row: Title & Badge/Icon --}}
        <div class="flex items-center justify-between gap-2">
            @if ($title)
                <span class="text-xs font-medium text-slate-500 truncate">
                    {{ $title }}
                </span>
            @elseif (isset($header))
                {{ $header }}
            @else
                <span></span>
            @endif

            @if (isset($badge))
                <div class="shrink-0">
                    {{ $badge }}
                </div>
            @elseif (isset($icon))
                <div class="w-8 h-8 rounded-lg border flex items-center justify-center shrink-0 {{ $iconStyle }}">
                    {{ $icon }}
                </div>
            @endif
        </div>

        {{-- Value Row --}}
        <div class="mt-2.5 flex items-baseline justify-between gap-2">
            @if (isset($valueSlot))
                {{ $valueSlot }}
            @elseif ($value instanceof \Illuminate\Contracts\Support\Htmlable)
                {{ $value }}
            @elseif ($value !== null)
                <span class="text-2xl font-bold font-mono {{ $valueClass }} tabular-nums tracking-tight">
                    {{ $value }}
                </span>
                {{ $slot }}
            @else
                {{ $slot }}
            @endif
        </div>
    </div>

    {{-- Subtitle / Footer Row --}}
    @if ($subtitle || isset($footer))
        <div class="mt-2 text-[11px] text-slate-400">
            @if (isset($footer))
                {{ $footer }}
            @else
                <p class="leading-relaxed">{{ $subtitle }}</p>
            @endif
        </div>
    @endif
</div>
