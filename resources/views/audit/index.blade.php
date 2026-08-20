@extends('layouts.app')
@section('title', 'Audit Log')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Audit Log</h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('audit.export.csv', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">
                Export CSV
            </a>
            <a href="{{ route('audit.export.pdf', request()->query()) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">
                Export PDF
            </a>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-filter-form action="{{ route('audit.index') }}" applyLabel="Filter">
            <div>
                <x-input-label for="action">Aksi</x-input-label>
                <x-select name="action" id="action">
                    <option value="">Semua Aksi</option>
                    @foreach(['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus'] as $val => $label)
                        <option value="{{ $val }}" {{ request('action') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="auditable_type">Modul</x-input-label>
                <x-select name="auditable_type" id="auditable_type">
                    <option value="">Semua Modul</option>
                    @foreach([
                        'App\Models\Patient' => 'Pasien',
                        'App\Models\Appointment' => 'Pendaftaran',
                        'App\Models\MedicalRecord' => 'Rekam Medis',
                        'App\Models\Billing' => 'Billing',
                        'App\Models\BillingItem' => 'Item Tagihan',
                        'App\Models\Doctor' => 'Dokter',
                        'App\Models\Poli' => 'Poli',
                        'App\Models\Schedule' => 'Jadwal',
                        'App\Models\Medicine' => 'Obat',
                        'App\Models\MedicineStock' => 'Stok Obat',
                        'App\Models\StockMutation' => 'Mutasi Stok',
                        'App\Models\LabRequest' => 'Permintaan Lab',
                        'App\Models\LabTest' => 'Tes Lab',
                        'App\Models\Tariff' => 'Tarif',
                        'App\Models\User' => 'User',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ request('auditable_type') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="date">Tanggal</x-input-label>
                <x-text-input type="date" name="date" id="date" value="{{ request('date') }}" />
            </div>
        </x-filter-form>

        <x-table placeholder="Cari admin / modul...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Waktu</th>
                    <th class="pb-4 px-4 font-semibold">Admin</th>
                    <th class="pb-4 px-4 font-semibold">Modul</th>
                    <th class="pb-4 px-4 font-semibold">ID</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                    <th class="pb-4 px-4 font-semibold">Detail</th>
                </tr>
            </x-slot>
            @forelse($logs as $log)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                <td class="py-4 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $log->user?->name ?? '(Sistem)' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ class_basename($log->auditable_type) }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $log->auditable_id }}</td>
                <td class="py-4 px-4">
                    @php
                        $badge = [
                            'created' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
                            'updated' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400',
                            'deleted' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
                        ];
                        $label = ['created' => 'Dibuat', 'updated' => 'Diubah', 'deleted' => 'Dihapus'];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge[$log->action] ?? 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400' }}">{{ $label[$log->action] ?? $log->action }}</span>
                </td>
                <td class="py-4 px-4">
                    <details class="text-sm">
                        <summary class="cursor-pointer text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-semibold">Lihat</summary>
                        <div class="mt-2 rounded-xl bg-background-light dark:bg-background-dark p-4 border border-border-light dark:border-border-dark">
                            @if($log->new_values)
                                <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark mb-1">Nilai Baru</p>
                                <pre class="text-xs text-text-primary-light dark:text-text-primary-dark whitespace-pre-wrap break-all">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                            @if($log->old_values)
                                <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark mt-3 mb-1">Nilai Lama</p>
                                <pre class="text-xs text-text-primary-light dark:text-text-primary-dark whitespace-pre-wrap break-all">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @endif
                        </div>
                    </details>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada log.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection