@extends('layouts.app')
@section('title', 'Detail Pengguna')
@section('content')
<div class="space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-6 sm:p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <div>
                <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $user->name }}</h3>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">{{ $user->email }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('users.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Kembali</a>
                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 rounded-xl text-sm font-semibold text-warning-700 dark:text-warning-400 hover:bg-warning-100 dark:hover:bg-warning-800 transition-all">Edit</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="rounded-xl border border-border-light dark:border-border-dark p-5">
                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-3">Informasi Akun</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Nama Lengkap</dt>
                        <dd class="font-medium text-text-primary-light dark:text-text-primary-dark break-words">{{ $user->name }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Email</dt>
                        <dd class="font-medium text-text-primary-light dark:text-text-primary-dark break-words">{{ $user->email }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Peran</dt>
                        <dd class="flex flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800">{{ $role->name }}</span>
                            @endforeach
                        </dd>
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Status</dt>
                        <dd>
                            @if($user->is_active)
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800">Nonaktif</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-border-light dark:border-border-dark p-5">
                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-3">Riwayat</p>
                <dl class="space-y-3 text-sm">
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Terdaftar</dt>
                        <dd class="font-medium text-text-primary-light dark:text-text-primary-dark">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1">
                        <dt class="text-text-secondary-light dark:text-text-secondary-dark w-full sm:w-32 shrink-0">Diperbarui</dt>
                        <dd class="font-medium text-text-primary-light dark:text-text-primary-dark">{{ $user->updated_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection