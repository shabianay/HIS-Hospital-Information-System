@props([
    'href' => null,
    'variant' => 'default',
    'type' => 'button',
])

@php
    $variants = [
        'default' => 'bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800',
        'primary' => 'bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-800',
        'warning' => 'bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 text-warning-700 dark:text-warning-400 hover:bg-warning-100 dark:hover:bg-warning-800',
        'danger' => 'bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800',
        'success' => 'bg-success-50 dark:bg-success-900/30 border border-success-200 dark:border-success-800 text-success-700 dark:text-success-400 hover:bg-success-100 dark:hover:bg-success-800',
        'primary-solid' => 'bg-primary-600 hover:bg-primary-700 border border-primary-700 text-white shadow-glass-sm hover:shadow-glass-md',
        'success-solid' => 'bg-success-600 hover:bg-success-700 border border-success-700 text-white shadow-glass-sm hover:shadow-glass-md',
        'info-solid' => 'bg-info-600 hover:bg-info-700 border border-info-700 text-white shadow-glass-sm hover:shadow-glass-md',
        'danger-solid' => 'bg-danger-600 hover:bg-danger-700 border border-danger-700 text-white shadow-glass-sm hover:shadow-glass-md',
    ];
    $classes = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all ' . ($variants[$variant] ?? $variants['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif