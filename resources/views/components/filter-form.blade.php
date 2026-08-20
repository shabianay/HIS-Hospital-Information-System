@props([
    'action' => null,
    'cols' => 4,
    'resetUrl' => null,
    'applyLabel' => 'Terapkan Filter',
])

@php
    $colsClass = match ($cols) {
        2 => 'md:grid-cols-2',
        3 => 'md:grid-cols-3',
        4 => 'md:grid-cols-4',
        5 => 'md:grid-cols-5',
        6 => 'md:grid-cols-6',
        default => 'md:grid-cols-4',
    };
    $resetUrl = $resetUrl ?? url()->current();
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-4 p-6 bg-secondary-50 dark:bg-secondary-900/30 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm ' . $colsClass]) }}>
    {{ $slot }}
    <div class="flex items-end gap-2">
        <button type="submit"
            class="w-full px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-xl transition-colors shadow-glass-sm">
            {{ $applyLabel }}
        </button>
        <a href="{{ $resetUrl }}"
            class="shrink-0 px-6 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark font-semibold text-sm rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors shadow-glass-sm">
            Reset
        </a>
    </div>
</form>