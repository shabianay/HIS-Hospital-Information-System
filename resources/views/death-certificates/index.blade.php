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
        <x-filter-form action="{{ route('death-certificates.index') }}" applyLabel="Filter" cols="3">
            <div>
                <x-input-label for="date_from">Dari Tanggal</x-input-label>
                <x-text-input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" />
            </div>
            <div>
                <x-input-label for="date_to">Sampai Tanggal</x-input-label>
                <x-text-input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" />
            </div>
        </x-filter-form>

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
                        <x-action-link href="{{ route('death-certificates.pdf', $cert) }}" target="_blank" variant="primary">Cetak</x-action-link>
                        <x-action-delete action="{{ route('death-certificates.destroy', $cert) }}" confirm="Hapus surat kematian ini?">Hapus</x-action-delete>
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