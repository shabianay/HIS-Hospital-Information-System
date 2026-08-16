@extends('layouts.app')
@section('title', 'Notifikasi')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Notifikasi</h2>
        @if($notifications->total() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-5 py-2.5 border border-border-light dark:border-border-dark rounded-xl text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary-50 dark:hover:bg-primary-900/10 transition-all duration-200">Tandai Semua Sudah Dibaca</button>
        </form>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Notifikasi</th>
                    <th class="pb-4 px-4 font-semibold">Waktu</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                </tr>
            </x-slot>
            @forelse($notifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : json_decode((string) $notification->data, true);
                @endphp
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-4 px-4">
                        @if($notification->read_at)
                            <div class="text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark">{{ $data['title'] ?? 'Notifikasi' }}</div>
                            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $data['message'] ?? '' }}</p>
                        @else
                            <div class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $data['title'] ?? 'Notifikasi' }}</div>
                            <p class="text-sm text-text-primary-light dark:text-text-primary-dark">{{ $data['message'] ?? '' }}</p>
                        @endif
                        @if(!empty($data['url']))
                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="mt-1">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Lihat detail →</button>
                        </form>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $notification->created_at?->diffForHumans() }}</td>
                    <td class="py-4 px-4">
                        @if($notification->read_at)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400">Dibaca</span>
                        @else
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">Baru</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada notifikasi.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection