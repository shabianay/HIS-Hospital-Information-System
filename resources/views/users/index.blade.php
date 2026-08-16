@extends('layouts.app')
@section('title', 'Manajemen User')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Pengguna / Users</h3>
        <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah User
        </a>
    </div>
    <x-table placeholder="Cari nama / email user..." class="overflow-hidden">
        <x-slot name="head">
            <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                <th class="pb-4 px-4 font-semibold">Nama</th>
                <th class="pb-4 px-4 font-semibold">Email</th>
                <th class="pb-4 px-4 font-semibold">Role / Hak Akses</th>
                <th class="pb-4 px-4 font-semibold">Aksi</th>
            </tr>
        </x-slot>
        @forelse($users as $user)
        <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-semibold">{{ $user->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $user->email }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        @foreach($user->roles as $role)
                            <span class="mr-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 rounded-lg text-xs font-medium text-warning-700 dark:text-warning-400 hover:bg-warning-100 dark:hover:bg-warning-800 transition-all">Edit</a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="4" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data user.</td></tr>
                @endforelse
    </x-table>
    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>
@endsection