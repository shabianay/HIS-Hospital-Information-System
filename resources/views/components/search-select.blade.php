@props([
    'name' => 'value',
    'endpoint' => '#',
    'placeholder' => 'Cari dan pilih...',
    'emptyMessage' => 'Tidak ditemukan.',
    'selectedText' => null,
    'disabled' => false,
])

<div
    x-data="{
        open: false,
        query: '',
        loading: false,
        results: [],
        selectedId: {{ json_encode(old($name) ?: request($name)) }},
        selectedLabel: {{ json_encode($selectedText) }},
        async search() {
            if (this.query.trim().length === 0) return;
            this.loading = true;
            this.open = true;
            try {
                const res = await fetch('{{ $endpoint }}?q=' + encodeURIComponent(this.query), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.results = data;
            } catch (e) {
                this.results = [];
            } finally {
                this.loading = false;
            }
        },
        select(item) {
            this.selectedId = item.id;
            this.selectedLabel = item.label;
            this.query = '';
            this.open = false;
        },
        clear() {
            this.selectedId = '';
            this.selectedLabel = '';
            this.query = '';
            this.results = [];
        }
    }"
    class="relative"
>
    <input type="hidden" name="{{ $name }}" :value="selectedId" :required="{{ $disabled ? 'false' : 'true' }}">
    <template x-if="selectedLabel">
        <div class="flex items-center justify-between gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-4 py-3">
            <span class="text-sm font-medium text-primary-800 dark:text-primary-300" x-text="selectedLabel"></span>
            <button type="button" @click="clear()" class="text-primary-500 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300" aria-label="Batal pilih">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
    <template x-if="!selectedLabel">
        <div class="relative">
            <input
                type="text"
                x-model="query"
                @input.debounce.400ms="search()"
                @focus="open = query.length > 0"
                @click.away="open = false"
                :disabled="{{ $disabled ? 'true' : 'false' }}"
                placeholder="{{ $placeholder }}"
                class="w-full border border-border-light bg-surface-light px-4 py-3 pr-10 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"
            >
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-text-secondary-light dark:text-text-secondary-dark">
                <svg x-show="!loading" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </div>
        </div>
    </template>

    <div
        x-show="open && !selectedLabel"
        x-cloak
        x-transition.opacity
        class="absolute z-50 mt-2 w-full max-h-60 overflow-y-auto rounded-xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark shadow-glass-lg"
    >
        <p x-show="results.length === 0 && !loading" class="px-4 py-3 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $emptyMessage }}</p>
        <ul>
            <template x-for="(item, i) in results" :key="i">
                <li>
                    <button type="button" @click="select(item)" class="w-full px-4 py-2.5 text-left text-sm text-text-primary-light dark:text-text-primary-dark hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-colors" x-text="item.label"></button>
                </li>
            </template>
        </ul>
    </div>
</div>