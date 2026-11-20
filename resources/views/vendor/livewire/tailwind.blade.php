@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->total() > 0)
        <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            {{-- Left: Per-Page Selector (Controlled by Livewire $perPage) --}}
            <div class="flex items-center gap-2 text-slate-500">
                <span>Show</span>
                <select
                    wire:model.live="perPage"
                    class="py-1 pl-2.5 pr-8 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00236f]/20 focus:border-[#00236f] transition-all cursor-pointer shadow-2xs"
                >
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span>entries per page</span>
            </div>

            {{-- Mobile Pagination View --}}
            <div class="flex justify-between items-center sm:hidden gap-2">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-lg">
                        {!! __('pagination.previous') !!}
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                        {!! __('pagination.previous') !!}
                    </button>
                @endif

                <span class="text-xs text-slate-500 font-medium">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>

                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                        {!! __('pagination.next') !!}
                    </button>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-50 border border-slate-200 cursor-not-allowed rounded-lg">
                        {!! __('pagination.next') !!}
                    </span>
                @endif
            </div>

            {{-- Desktop Pagination View --}}
            <div class="hidden sm:flex sm:items-center sm:gap-4 sm:justify-end">
                <div>
                    <p class="text-xs text-slate-500 whitespace-nowrap">
                        <span>{!! __('Showing') !!}</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->firstItem() ?? 1 }}</span>
                        <span>{!! __('to') !!}</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->lastItem() ?? $paginator->total() }}</span>
                        <span>{!! __('of') !!}</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
                        <span>{!! __('results') !!}</span>
                    </p>
                </div>

                @if ($paginator->hasPages())
                    <div>
                        <div class="inline-flex items-center rounded-lg border border-slate-200 bg-white divide-x divide-slate-200 shadow-2xs overflow-hidden">
                            {{-- Previous Page Link --}}
                            @if ($paginator->onFirstPage())
                                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="w-7 h-7 flex items-center justify-center text-slate-300 bg-slate-50 cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @else
                                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors cursor-pointer" aria-label="{{ __('pagination.previous') }}">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($elements as $element)
                                {{-- "Three Dots" Separator --}}
                                @if (is_string($element))
                                    <span aria-disabled="true" class="min-w-[28px] h-7 px-1.5 flex items-center justify-center text-xs text-slate-400 select-none bg-slate-50/50">
                                        {{ $element }}
                                    </span>
                                @endif

                                {{-- Array Of Links --}}
                                @if (is_array($element))
                                    @foreach ($element as $page => $url)
                                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                            @if ($page == $paginator->currentPage())
                                                <span aria-current="page" class="min-w-[28px] h-7 px-2 flex items-center justify-center text-xs font-bold bg-[#00236f] text-white select-none">
                                                    {{ $page }}
                                                </span>
                                            @else
                                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="min-w-[28px] h-7 px-2 flex items-center justify-center text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors cursor-pointer" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                                    {{ $page }}
                                                </button>
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($paginator->hasMorePages())
                                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="w-7 h-7 flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 transition-colors cursor-pointer" aria-label="{{ __('pagination.next') }}">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @else
                                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="w-7 h-7 flex items-center justify-center text-slate-300 bg-slate-50 cursor-not-allowed">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </nav>
    @endif
</div>
