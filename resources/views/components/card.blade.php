@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'footer' => null,
    'padding' => true,
])

@php
    $hasPadding = filter_var($padding, FILTER_VALIDATE_BOOLEAN);
@endphp

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden']) }}>
    @if ($title || isset($header) || $actions)
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <div>
                @if ($title)
                    <h3 class="text-sm font-semibold text-slate-900 leading-tight">
                        {{ $title }}
                    </h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $subtitle }}
                    </p>
                @endif
                @if (isset($header))
                    {{ $header }}
                @endif
            </div>

            @if ($actions)
                <div class="flex items-center gap-2 shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div @class(['p-5' => $hasPadding])>
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-5 py-3 bg-slate-50/70 border-t border-slate-100 text-xs text-slate-500">
            {{ $footer }}
        </div>
    @endif
</div>
