@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-text-primary-light dark:text-text-primary-dark mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
