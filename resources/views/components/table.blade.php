@props([
    'searchable' => true,
    'action' => null,
    'placeholder' => 'Cari data...',
])

<div
    x-data="{
        search: '',
        init() {
            this.$nextTick(() => {
                const headers = [...this.$el.querySelectorAll('thead th')];
                this.$el.querySelectorAll('tbody tr').forEach(row => {
                    [...row.querySelectorAll('td')].forEach((td, i) => {
                        if (!td.hasAttribute('colspan') && headers[i]) td.setAttribute('data-label', headers[i].textContent.trim());
                    });
                });
            });
        },
        allHidden() {
            if (!this.search) return false;
            const rows = this.$refs.body.querySelectorAll('[data-search-row]');
            return rows.length > 0 && Array.from(rows).every(r => getComputedStyle(r).display === 'none');
        }
    }"
    class="w-full"
>
    @if($searchable)
        <div class="relative mb-5 max-w-md">
            @if($action)
                <form method="GET" action="{{ $action }}" role="search">
            @endif
            <input
                type="text"
                name="search"
                x-model="search"
                value="{{ request('search') }}"
                placeholder="{{ $placeholder }}"
                class="w-full border border-border-light bg-surface-light pl-10 pr-10 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark"
            >
            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-text-secondary-light dark:text-text-secondary-dark">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            @if($action)
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:text-primary-500 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors" aria-label="Cari">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                </button>
                </form>
            @else
                <button type="button" x-show="search" @click="search = ''" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-lg text-text-secondary-light dark:text-text-secondary-dark hover:text-primary-500 hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors" aria-label="Bersihkan pencarian">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="x-table w-full text-left border-collapse">
            <thead>
                {{ $head }}
            </thead>
            <tbody x-ref="body" class="divide-y divide-border-light dark:divide-border-dark">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if($searchable)
        <div x-show="allHidden()" x-cloak class="py-12 text-center text-sm text-text-secondary-light dark:text-text-secondary-dark">
            Tidak ditemukan hasil untuk pencarian.
        </div>
    @endif
</div>
