@props([
    'action' => null,
    'confirm' => 'Hapus data ini?',
])

<form action="{{ $action }}" method="POST" onsubmit="return confirm('{{ $confirm }}')" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit"
        class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">
        {{ $slot }}
    </button>
</form>