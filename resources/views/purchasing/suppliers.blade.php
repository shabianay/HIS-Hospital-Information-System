@extends('layouts.app')
@section('title', 'Supplier')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Supplier & Pemasok</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('purchasing.suppliers.csv') }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <button type="button" @click="$refs.addForm.showModal()" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Tambah Supplier</button>
        </div>
    </div>

    <div x-data="{ showAdd: false }">
        <dialog x-ref="addForm" @click.self="showAdd = false" :open="showAdd"
            class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
            <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Tambah Supplier</h3>
                <form method="POST" action="{{ route('purchasing.suppliers.store') }}" class="grid grid-cols-1 gap-5">
                    @csrf
                    <div>
                        <x-input-label value="Nama Supplier *" />
                        <x-text-input type="text" name="name" required placeholder="Contoh: PT Kimia Farma" />
                    </div>
                    <div>
                        <x-input-label value="Kontak Person" />
                        <x-text-input type="text" name="contact_person" placeholder="Contoh: Bpk. Agus" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Telepon" />
                            <x-text-input type="text" name="phone" placeholder="Contoh: 021-5551234" />
                        </div>
                        <div>
                            <x-input-label value="Email" />
                            <x-text-input type="email" name="email" placeholder="supplier@email.com" />
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Alamat" />
                        <textarea name="address" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
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
        <x-table placeholder="Cari supplier...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Nama</th>
                    <th class="pb-4 px-4 font-semibold">Kontak Person</th>
                    <th class="pb-4 px-4 font-semibold">Telepon</th>
                    <th class="pb-4 px-4 font-semibold">Email</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($suppliers as $supplier)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $supplier->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $supplier->contact_person ?? '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $supplier->phone ?? '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $supplier->email ?? '-' }}</td>
                <td class="py-4 px-4">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $supplier->is_active ? 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400' : 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="$refs.editForm{{ $supplier->id }}.showModal()" class="text-xs font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Edit</button>
                        <form method="POST" action="{{ route('purchasing.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus supplier ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-danger-600 hover:text-red-700 dark:text-red-400">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>

            <dialog x-ref="editForm{{ $supplier->id }}" @click.self="this.close()"
                class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
                <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
                    <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Edit Supplier</h3>
                    <form method="POST" action="{{ route('purchasing.suppliers.update', $supplier) }}" class="grid grid-cols-1 gap-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label value="Nama Supplier *" />
                            <x-text-input type="text" name="name" value="{{ $supplier->name }}" required />
                        </div>
                        <div>
                            <x-input-label value="Kontak Person" />
                            <x-text-input type="text" name="contact_person" value="{{ $supplier->contact_person }}" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Telepon" />
                                <x-text-input type="text" name="phone" value="{{ $supplier->phone }}" />
                            </div>
                            <div>
                                <x-input-label value="Email" />
                                <x-text-input type="email" name="email" value="{{ $supplier->email }}" />
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Alamat" />
                            <textarea name="address" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ $supplier->address }}</textarea>
                        </div>
                        <label class="flex items-center gap-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                            <input type="checkbox" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                            Aktif
                        </label>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="$refs.editForm{{ $supplier->id }}.close()" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Batal</button>
                            <x-primary-button type="submit">Simpan</x-primary-button>
                        </div>
                    </form>
                </div>
            </dialog>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada supplier.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>
@endsection