@extends('layouts.app')
@section('title', 'Master Tes Laboratorium')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Master Tes Laboratorium</h2>
        <div class="flex gap-2">
            <a href="{{ route('lab.tests.csv') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Export CSV</a>
            <button type="button" @click="$refs.addForm.showModal()" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                Tambah Tes
            </button>
        </div>
    </div>

    <div x-data="{ showAdd: false }">
        <dialog x-ref="addForm" @click.self="showAdd = false" :open="showAdd"
            class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
            <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Tambah Tes Laboratorium</h3>
                <form method="POST" action="{{ route('lab.tests.store') }}" class="grid grid-cols-1 gap-5">
                    @csrf
                    <div>
                        <x-input-label value="Nama Tes" />
                        <x-text-input type="text" name="name" required placeholder="Contoh: Hemoglobin (Hb)" />
                    </div>
                    <div>
                        <x-input-label value="Kategori" />
                        <x-text-input type="text" name="category" placeholder="Contoh: Hematologi" />
                    </div>
                    <div>
                        <x-input-label value="Satuan" />
                        <x-text-input type="text" name="unit" placeholder="Contoh: g/dL" />
                    </div>
                    <div>
                        <x-input-label value="Nilai Rujukan" />
                        <x-text-input type="text" name="reference_range" placeholder="Contoh: 13.0 - 17.0" />
                    </div>
                    <div>
                        <x-input-label value="Tarif (Rp)" />
                        <x-text-input type="number" name="price" min="0" step="0.01" value="0" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showAdd = false" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Batal</button>
                        <x-primary-button type="submit">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari tes laboratorium...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Nama</th>
                    <th class="pb-4 px-4 font-semibold">Kategori</th>
                    <th class="pb-4 px-4 font-semibold">Satuan</th>
                    <th class="pb-4 px-4 font-semibold">Nilai Rujukan</th>
                    <th class="pb-4 px-4 font-semibold">Tarif</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($tests as $test)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $test->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $test->category }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $test->unit ?? '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $test->reference_range ?? '-' }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($test->price, 0, ',', '.') }}</td>
                <td class="py-4 px-4">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $test->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">{{ $test->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('lab.tests.update', $test) }}">
                            @csrf
                            @method('PUT')
                            <x-action-link type="submit" name="is_active" value="{{ $test->is_active ? 0 : 1 }}">{{ $test->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</x-action-link>
                        </form>
                        <x-action-delete action="{{ route('lab.tests.destroy', $test) }}" confirm="Hapus tes ini?">Hapus</x-action-delete>
                    </div>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada tes laboratorium.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $tests->links() }}
        </div>
    </div>
</div>
@endsection