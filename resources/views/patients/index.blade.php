@extends('layouts.app')
@section('title', 'Daftar Pasien')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Pasien</h3>
        <a href="{{ route('patients.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pasien
        </a>
    </div>

    <x-table placeholder="Cari nama / NIK / no. telepon..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">No. RM</th>
                    <th class="pb-4 px-4 font-semibold">NIK</th>
                    <th class="pb-4 px-4 font-semibold">Nama</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal Lahir</th>
                    <th class="pb-4 px-4 font-semibold">Jenis Kelamin</th>
                    <th class="pb-4 px-4 font-semibold">No. Telepon</th>
                    <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse($patients as $patient)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->rm_number ?: '-' }}</td>
                    <td class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->nik }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $patient->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d/m/Y') : '-' }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $patient->phone_number ?: '-' }}</td>
                    <td class="py-4 px-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Lihat</a>
                            <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg text-xs font-medium text-yellow-700 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-800 transition-all">Edit</a>
                            <form action="{{ route('patients.destroy', $patient) }}" method="POST" onsubmit="return confirm('Hapus pasien ini?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="7" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data pasien.</td></tr>
                @endforelse
            </x-table>

    <div class="mt-6">
        {{ $patients->links() }}
    </div>
</div>
@endsection
