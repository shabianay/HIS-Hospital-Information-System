@extends('layouts.app')
@section('title', 'Pengeluaran (Keuangan)')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Pengeluaran (Keuangan)</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('expenses.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <button type="button" @click="$refs.addForm.showModal()" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Catat Pengeluaran</button>
        </div>
    </div>

    <div x-data="{ showAdd: false }">
        <dialog x-ref="addForm" @click.self="showAdd = false" :open="showAdd"
            class="backdrop:bg-black/50 bg-transparent p-0 m-auto rounded-2xl max-w-lg w-full border-none">
            <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-lg p-8">
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Catat Pengeluaran</h3>
                <form method="POST" action="{{ route('expenses.store') }}" class="grid grid-cols-1 gap-5">
                    @csrf
                    <div>
                        <x-input-label value="Kategori *" />
                        <select name="category" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                            @foreach(\App\Models\Expense::CATEGORIES as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Deskripsi *" />
                        <x-text-input type="text" name="description" required placeholder="Contoh: Pembelian ATK bulanan" />
                    </div>
                    <div>
                        <x-input-label value="Jumlah (Rp) *" />
                        <x-text-input type="number" name="amount" min="0" step="0.01" required />
                    </div>
                    <div>
                        <x-input-label value="Tanggal *" />
                        <x-text-input type="date" name="expense_date" value="{{ now()->toDateString() }}" required />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Dibayar Kepada" />
                            <x-text-input type="text" name="paid_to" placeholder="Contoh: Toko Sinar Jaya" />
                        </div>
                        <div>
                            <x-input-label value="Metode Pembayaran *" />
                            <select name="payment_method" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                @foreach(\App\Models\Expense::PAYMENT_METHODS as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <x-input-label value="Catatan (Opsional)" />
                        <textarea name="notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showAdd = false" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Batal</button>
                        <x-primary-button type="submit">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Pengeluaran</p>
            <p class="mt-1 text-xl font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format($summary['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Pengeluaran Bulan Ini</p>
            <p class="mt-1 text-xl font-bold text-warning-600 dark:text-warning-400">Rp {{ number_format($summary['month_total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Transaksi</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['count'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <form method="GET" action="{{ route('expenses.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
            <select name="category" class="w-full sm:w-56 border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <option value="">Semua Kategori</option>
                @foreach(\App\Models\Expense::CATEGORIES as $val => $label)
                    <option value="{{ $val }}" {{ request('category') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <span class="self-center text-sm text-text-secondary-light dark:text-text-secondary-dark">s/d</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <x-primary-button type="submit">Filter</x-primary-button>
        </form>

        <x-table placeholder="Cari deskripsi / no. transaksi...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Transaksi</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal</th>
                    <th class="pb-4 px-4 font-semibold">Kategori</th>
                    <th class="pb-4 px-4 font-semibold">Deskripsi</th>
                    <th class="pb-4 px-4 font-semibold">Dibayar Kepada</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($expenses as $expense)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $expense->expense_number }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $expense->expense_date?->format('d/m/Y') }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Expense::CATEGORIES[$expense->category] ?? $expense->category }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $expense->description }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $expense->paid_to ?? '-' }}</td>
                <td class="py-4 px-4 text-sm font-mono text-danger-600 dark:text-danger-400">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                <td class="py-4 px-4">
                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-danger-600 hover:text-red-700 dark:text-red-400">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada pengeluaran.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection