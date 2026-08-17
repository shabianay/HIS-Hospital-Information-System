<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrian Laboratorium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex h-full flex-col justify-between p-6 text-white">
    <div x-data="labBoard()" x-init="init()" class="flex h-full flex-col">
        {{-- Header --}}
        <header class="flex items-center justify-between border-b border-slate-700/50 pb-4">
            <div class="flex items-center gap-4">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-600 shadow-glass-sm">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25M6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Zm6.75-6h.008v.008h-.008V12Zm0 3h.008v.008h-.008V15Zm0 3h.008v.008h-.008V18"/></svg>
                </span>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">RUMAH SAKIT HIS</h1>
                    <p class="text-xs text-slate-400">Display Antrian Laboratorium</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-lg font-semibold text-primary-400" x-text="time">Memuat waktu...</span>
            </div>
        </header>

        {{-- Main --}}
        <main class="my-6 grid flex-1 grid-cols-12 gap-6">
            {{-- Left: Current Sample --}}
            <div class="col-span-8 flex flex-col items-center justify-between rounded-2xl border border-slate-700/50 bg-slate-800 p-8 text-center">
                <div>
                    <span class="text-xl font-semibold uppercase tracking-wider text-primary-400">Pemeriksaan Sedang Berlangsung</span>
                    <div class="mt-4 text-5xl font-extrabold text-white" x-text="current ? current.patient_name : '-'">-</div>
                    <div class="mt-2 text-2xl font-semibold text-slate-300">Silakan Menuju ke Loket Laboratorium</div>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <template x-if="current && current.tests.length > 0">
                            <template x-for="test in current.tests" :key="test">
                                <span class="rounded-lg bg-primary-600 px-3 py-1 text-sm font-semibold" x-text="test"></span>
                            </template>
                        </template>
                        <span x-show="current && current.is_urgent" class="rounded-lg bg-red-600 px-3 py-1 text-sm font-bold">URGEN</span>
                    </div>
                </div>
                <div class="mt-6 w-full border-t border-slate-700/50 pt-6">
                    <p class="text-sm text-slate-400">Mohon persiapkan lembar permintaan laboratorium Anda.</p>
                </div>
            </div>

            {{-- Right: Waiting list --}}
            <div class="col-span-4 flex flex-col gap-4">
                <div class="rounded-2xl border border-slate-700/50 bg-slate-800 p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-300">Antrean Menunggu</h3>
                        <span class="rounded-lg bg-slate-700 px-2 py-1 font-mono text-sm font-bold text-primary-400" x-text="waiting.length">0</span>
                    </div>
                    <div class="mt-4 space-y-3 max-h-[70vh] overflow-y-auto">
                        <template x-if="waiting.length === 0">
                            <div class="rounded-xl border border-slate-700/50 bg-slate-900/50 p-4 text-center text-slate-400">
                                Tidak ada pasien dalam antrean.
                            </div>
                        </template>
                        <template x-for="(item, index) in waiting" :key="item.id">
                            <div class="rounded-xl border border-slate-700/50 bg-slate-900/50 p-4">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-2xl font-black text-primary-400" x-text="String(index + 1).padStart(2, '0')"></span>
                                    <span x-show="item.is_urgent" class="rounded bg-red-600 px-2 py-0.5 text-xs font-bold">URGEN</span>
                                </div>
                                <p class="mt-1 truncate text-base font-semibold" x-text="item.patient_name"></p>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <template x-for="test in item.tests.slice(0, 3)" :key="test">
                                        <span class="rounded bg-slate-700 px-2 py-0.5 text-xs" x-text="test"></span>
                                    </template>
                                    <span x-show="item.tests.length > 3" class="rounded bg-slate-700 px-2 py-0.5 text-xs" x-text="'+' + (item.tests.length - 3)"></span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Permintaan <span x-text="item.created_at"></span></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer / Running Text --}}
        <footer class="overflow-hidden whitespace-nowrap rounded-2xl border border-primary-800/50 bg-primary-900/50 px-6 py-3">
            <div class="inline-block animate-marquee text-sm font-semibold text-primary-300">
                PENGUMUMAN: Hasil pemeriksaan laboratorium dapat diambil di loket laboratorium atau diserahkan kepada dokter pemeriksa. Terima kasih atas kerja samanya.
            </div>
        </footer>
    </div>

    <script>
        function labBoard() {
            return {
                initial: @json($initial),
                in_progress: @json($initial['in_progress'] ?? null),
                waiting: @json($initial['waiting'] ?? []),
                time: '',
                timer: null,
                init() {
                    this.refreshClock();
                    setInterval(() => this.refreshClock(), 1000);
                    this.refresh();
                    this.timer = setInterval(() => this.refresh(), 5000);
                },
                refreshClock() {
                    let d = new Date();
                    this.time = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' - ' + d.toLocaleTimeString('id-ID');
                },
                async refresh() {
                    try {
                        const res = await fetch('{{ route('queue.display.lab.json') }}');
                        const data = await res.json();
                        if (data && Array.isArray(data.waiting)) {
                            this.in_progress = data.in_progress;
                            this.waiting = data.waiting;
                        }
                    } catch (e) {
                        // ignore transient network errors, keep current data
                    }
                },
                get current() {
                    return this.in_progress || null;
                },
            };
        }
    </script>

    <style>
        @keyframes marquee {
            0% { transform: translate3d(100%, 0, 0); }
            100% { transform: translate3d(-100%, 0, 0); }
        }
        .animate-marquee {
            animation: marquee 25s linear infinite;
        }
    </style>
</body>
</html>