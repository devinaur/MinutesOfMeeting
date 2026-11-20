@props([
    'status' => 'draft',
    'label' => null,
])

@php
$normalized = strtolower(trim((string) $status));

$configs = [
    'draft' => [
        'bg' => 'bg-[#f1f5f9]',
        'text' => 'text-[#475569]',
        'border' => 'border-[#cbd5e1]',
        'label' => 'Draft',
        'icon' => 'draft',
    ],
    'pending' => [
        'bg' => 'bg-[#fffbeb]',
        'text' => 'text-[#b45309]',
        'border' => 'border-[#fde68a]',
        'label' => 'Pending',
        'icon' => 'schedule',
    ],
    'in_progress' => [
        'bg' => 'bg-[#eff6ff]',
        'text' => 'text-[#1d4ed8]',
        'border' => 'border-[#bfdbfe]',
        'label' => 'In Progress',
        'icon' => 'edit_note',
    ],
    'completed' => [
        'bg' => 'bg-[#ecfdf5]',
        'text' => 'text-[#059669]',
        'border' => 'border-[#a7f3d0]',
        'label' => 'Completed',
        'icon' => 'check_circle',
    ],
];

$config = $configs[$normalized] ?? [
    'bg' => 'bg-[#f1f5f9]',
    'text' => 'text-[#475569]',
    'border' => 'border-[#cbd5e1]',
    'label' => ucfirst(str_replace('_', ' ', $normalized)),
    'icon' => 'draft',
];

$displayLabel = $label ?? $config['label'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border text-[11px] font-semibold whitespace-nowrap {$config['bg']} {$config['text']} {$config['border']} select-none transition-colors"]) }}>
    @if ($config['icon'] === 'draft')
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
    @elseif ($config['icon'] === 'schedule')
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    @elseif ($config['icon'] === 'edit_note')
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    @elseif ($config['icon'] === 'check_circle')
        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    @endif

    <span>{{ $displayLabel }}</span>
</span>
