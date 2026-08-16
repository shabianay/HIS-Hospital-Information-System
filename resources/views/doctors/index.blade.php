@extends('layouts.app')
@section('title', 'Daftar Dokter')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Dokter</h3>
        <a href="{{ route('doctors.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Dokter
        </a>
    </div>

    <x-table placeholder="Cari nama / spesialisasi / No. SIP..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Nama Dokter</th>
                    <th class="pb-4 px-4 font-semibold">Spesialisasi</th>
                    <th class="pb-4 px-4 font-semibold">No. SIP</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse($doctors as $doctor)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $doctor->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $doctor->specialization ?: '-' }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $doctor->license_number }}</td>
                    <td class="py-4 px-4">
                        @if($doctor->is_active)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('doctors.show', $doctor) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Lihat</a>
                            <a href="{{ route('doctors.edit', $doctor) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 rounded-lg text-xs font-medium text-warning-700 dark:text-warning-400 hover:bg-warning-100 dark:hover:bg-warning-800 transition-all">Edit</a>
                            <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" onsubmit="return confirm('Hapus dokter ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="5" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data dokter.</td></tr>
                @endforelse
            </x-table>

    <div class="mt-6">
        {{ $doctors->links() }}
    </div>
</div>
@endsection