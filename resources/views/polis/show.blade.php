@extends('layouts.app')
@section('title', 'Detail Poli')
@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <div>
                <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $poli->name }}</h2>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">Kode: <span class="font-mono text-primary-600 dark:text-primary-400">{{ $poli->code }}</span></p>
            </div>
            <div class="flex gap-2">
                <x-secondary-button href="{{ route('polis.edit', $poli) }}">Edit</x-secondary-button>
                <x-secondary-button href="{{ route('polis.index') }}">Kembali</x-secondary-button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama Poli</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $poli->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Kode Poli</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $poli->code }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Status</span>
                @if($poli->is_active)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                @endif
            </div>
            <div class="md:col-span-2">
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Deskripsi</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $poli->description ?: '-' }}</span>
            </div>
        </div>
    </div>
</div>
@endsection