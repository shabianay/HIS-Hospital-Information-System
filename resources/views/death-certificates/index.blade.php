@extends('layouts.app')
@section('title', 'Surat Kematian')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Surat Kematian</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('death-certificates.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <a href="{{ route('death-certificates.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Terbitkan Surat</a>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <form method="GET" action="{{ route('death-certificates.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <span class="self-center text-sm text-text-secondary-light dark:text-text-secondary-dark">s/d</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <x-primary-button type="submit">Filter</x-primary-button>
        </form>

        <x-table placeholder="Cari no. surat / pasien...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Surat</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal Meninggal</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Penyebab</th>
                    <th class="pb-4 px-4 font-semibold">Dokter</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($certificates as $cert)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $cert->certificate_number }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $cert->date_of_death?->format('d/m/Y H:i') }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $cert->patient?->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\DeathCertificate::CAUSES[$cert->cause_of_death] ?? $cert->cause_of_death }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $cert->doctor_name ?? '-' }}</td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('death-certificates.pdf', $cert) }}" target="_blank" class="inline-flex items-center justify-center px-3 py-1.5 border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 rounded-lg text-xs font-medium text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all">Cetak</a>
                        <form method="POST" action="{{ route('death-certificates.destroy', $cert) }}" onsubmit="return confirm('Hapus surat kematian ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada surat kematian.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $certificates->links() }}
        </div>
    </div>
</div>
@endsection