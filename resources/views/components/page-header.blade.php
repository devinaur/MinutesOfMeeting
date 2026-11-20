@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-6 border-b border-slate-200']) }}>
    <div>
        @if (!empty($breadcrumbs))
            <nav class="flex items-center gap-2 text-[11px] font-medium text-slate-400 mb-1.5 uppercase tracking-wider select-none">
                @foreach ($breadcrumbs as $label => $url)
                    @if (!$loop->last)
                        <a href="{{ $url }}" class="hover:text-slate-700 transition-colors">{{ $label }}</a>
                        <span class="text-slate-300">/</span>
                    @else
                        <span class="text-slate-600 font-medium">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="text-xs text-slate-500 mt-1 max-w-3xl leading-relaxed">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</div>
