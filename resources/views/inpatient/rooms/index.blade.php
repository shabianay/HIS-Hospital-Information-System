@extends('layouts.app')
@section('title', 'Kamar Rawat Inap')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Kamar Rawat Inap</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('rooms.index.csv') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Export CSV</a>
            <a href="{{ route('rooms.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kamar
            </a>
        </div>
    </div>
    <x-table placeholder="Cari kode / nama kamar..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Kode</th>
                    <th class="pb-4 px-4 font-semibold">Nama Kamar</th>
                    <th class="pb-4 px-4 font-semibold">Tipe</th>
                    <th class="pb-4 px-4 font-semibold">Tarif / Hari</th>
                    <th class="pb-4 px-4 font-semibold">Tempat Tidur</th>
                    <th class="pb-4 px-4 font-semibold">Terisi</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($rooms as $room)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $room->code }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $room->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Room::ROOM_TYPES[$room->room_type] ?? $room->room_type }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($room->price_per_day, 0, ',', '.') }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $room->beds_count }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $room->admissions_count > 0 ? 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800' : 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800' }}">
                            {{ $room->admissions_count }} / {{ $room->beds_count }}
                        </span>
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        @if($room->is_active)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <div class="flex items-center gap-2">
                            <x-action-link href="{{ route('rooms.show', $room) }}">Lihat</x-action-link>
                            <x-action-link href="{{ route('rooms.edit', $room) }}" variant="warning">Edit</x-action-link>
                            <x-action-delete action="{{ route('rooms.destroy', $room) }}" confirm="Hapus kamar ini?">Hapus</x-action-delete>
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="8" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data kamar.</td></tr>
                @endforelse
            </x-table>
    <div class="mt-6">
        {{ $rooms->links() }}
    </div>
</div>
@endsection