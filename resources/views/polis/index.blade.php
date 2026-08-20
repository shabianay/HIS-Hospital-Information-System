@extends('layouts.app')
@section('title', 'Daftar Poli')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Poli</h3>
        <div class="flex gap-2">
            <a href="{{ route('polis.index.csv') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Export CSV</a>
            <a href="{{ route('polis.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Poli
            </a>
        </div>
    </div>

    <x-table placeholder="Cari kode / nama poli..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Kode Poli</th>
                    <th class="pb-4 px-4 font-semibold">Nama Poli</th>
                    <th class="pb-4 px-4 font-semibold">Deskripsi</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse($polis as $poli)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $poli->code }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $poli->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $poli->description ?: '-' }}</td>
                    <td class="py-4 px-4">
                        @if($poli->is_active)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <x-action-link href="{{ route('polis.show', $poli) }}">Lihat</x-action-link>
                            <x-action-link href="{{ route('polis.edit', $poli) }}" variant="warning">Edit</x-action-link>
                            <x-action-delete action="{{ route('polis.destroy', $poli) }}" confirm="Hapus poli ini?">Hapus</x-action-delete>
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="5" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data poli.</td></tr>
                @endforelse
            </x-table>
    <div class="mt-6">
        {{ $polis->links() }}
    </div>
</div>
@endsection