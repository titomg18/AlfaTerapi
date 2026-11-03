<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitor Kursi Terapi - Alpha Theraphy</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            DEFAULT: '#0284C7',
                            dark:    '#0D2D56',
                            soft:    '#E0F2FE',
                        },
                        surface: '#F4F7FB',
                        line:    '#D4E2EE',
                        ink:     '#0F1E36',
                        muted:   '#4A607A',
                        warn:    '#B4531A',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                },
            },
        };
    </script>
</head>
<body class="m-0 flex min-h-screen flex-col bg-[#EDF3F9] font-sans text-ink antialiased">

@php
    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');
@endphp

    {{-- Header Monitor TV --}}
    <header class="flex-shrink-0 border-b border-line bg-white px-6 py-4 shadow-sm max-[800px]:px-4 max-[800px]:py-3">
        <div class="mx-auto flex max-w-[1920px] flex-wrap items-center justify-between gap-4">

            {{-- Identitas Klinik --}}
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="Alpha Theraphy"
                     class="h-12 w-12 flex-shrink-0 rounded-xl border border-line bg-white object-contain p-0.5 shadow-sm max-[800px]:h-10 max-[800px]:w-10">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-extrabold leading-none tracking-tight text-accent-dark max-[800px]:text-lg">Alpha Theraphy</span>
                        <span class="rounded-full bg-accent-soft px-2.5 py-0.5 text-[11px] font-bold text-accent uppercase tracking-wider">Live Display</span>
                    </div>
                    <div class="mt-1 text-[11px] font-semibold tracking-wider text-muted">アルファ・セラピー · Sistem Pemantauan Kursi Terapi</div>
                </div>
            </div>

            {{-- Indikator Ringkasan TV --}}
            <div class="flex items-center gap-2.5 max-[1024px]:hidden">
                <div class="flex items-center gap-2 rounded-xl border border-line bg-surface px-4 py-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-medium text-muted">Kosong:</span>
                    <span id="stat-kosong" class="text-sm font-bold text-emerald-700">{{ $kursiKosong }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-line bg-surface px-4 py-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-accent animate-pulse"></span>
                    <span class="text-xs font-medium text-muted">Terpakai:</span>
                    <span id="stat-terpakai" class="text-sm font-bold text-accent-dark">{{ $kursiTerpakai }}</span>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-line bg-surface px-4 py-2">
                    <span class="text-xs font-medium text-muted">Total:</span>
                    <span id="stat-total" class="text-sm font-bold text-ink">{{ $totalKursi }} Kursi</span>
                </div>
            </div>

            {{-- Jam Realtime & Navigasi --}}
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <div id="live-clock" class="font-mono text-2xl font-bold tracking-tight text-accent-dark max-[800px]:text-xl">
                        {{ now()->format('H:i:s') }} <span class="text-xs font-semibold text-muted">WIB</span>
                    </div>
                    <div id="live-date" class="text-xs font-medium text-muted">{{ $tanggal }}</div>
                </div>

                {{-- Tombol Daftar Pasien Baru (Guest) --}}
                <a href="{{ route('guest.register') }}"
                   class="inline-flex min-h-[40px] items-center gap-1.5 rounded-xl bg-accent px-3.5 text-xs font-bold text-white shadow-sm transition hover:bg-accent-dark">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    <span>Daftar Pasien</span>
                </a>

                {{-- Tombol Fullscreen --}}
                <button type="button" id="btn-fullscreen" title="Layar Penuh / TV Mode"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted transition hover:bg-accent-soft hover:text-accent-dark">
                    <svg id="icon-fs-enter" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                    </svg>
                    <svg id="icon-fs-exit" class="hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"/>
                    </svg>
                </button>
            </div>

        </div>
    </header>

    {{-- Konten Utama Grid Kursi --}}
    <main class="flex-1 p-6 max-[800px]:p-4">
        <div class="mx-auto max-w-[1920px]">

            @if ($kursis->isEmpty())
                <div class="rounded-2xl border border-line bg-white p-12 text-center text-muted">
                    <p class="text-lg font-semibold">Belum ada data kursi terapi.</p>
                </div>
            @else
                <div id="grid-kursi" class="grid grid-cols-5 gap-5 max-[1600px]:grid-cols-4 max-[1200px]:grid-cols-3 max-[800px]:grid-cols-2 max-[500px]:grid-cols-1">
                    @foreach ($kursis as $k)
                        @php $s = $k->sesiAktif; @endphp
                        <div id="kursi-card-{{ $k->id }}"
                             class="kursi-card relative flex flex-col justify-between overflow-hidden rounded-2xl border transition-all duration-300
                             {{ $s ? 'border-accent/40 bg-gradient-to-b from-accent-soft/70 to-white shadow-md shadow-accent/5' : 'border-line bg-white shadow-sm' }}
                             p-5 min-h-[190px]">

                            {{-- Baris Atas: Nomor Kursi & Badge Status --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-muted">Kursi Terapi</span>
                                    <div class="text-2xl font-black tracking-tight text-accent-dark">{{ $k->no_kursi }}</div>
                                </div>

                                <div class="badge-status">
                                    @if ($s)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-xs font-bold text-white shadow-sm">
                                            <span class="h-2 w-2 rounded-full bg-white animate-ping"></span>
                                            TERAPI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            KOSONG
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Baris Tengah: Tampilan Timer / Status Siap --}}
                            <div class="my-3 text-center">
                                @if ($s)
                                    <div class="font-mono text-4xl font-extrabold tracking-tight tabular-nums text-accent-dark max-[800px]:text-3xl"
                                         data-end="{{ $s->selesai_pada->toIso8601String() }}">--:--</div>
                                    <div class="mt-1 text-xs font-semibold text-accent">Sisa Waktu Terapi</div>
                                @else
                                    <div class="text-2xl font-black text-muted/50 tracking-wide uppercase">Siap Digunakan</div>
                                    <div class="mt-1 text-xs text-muted">{{ $k->keterangan ?: 'Kursi siap untuk pasien berikutnya' }}</div>
                                @endif
                            </div>

                            {{-- Baris Bawah: Informasi Pasien & Sesi --}}
                            <div class="border-t border-line/60 pt-2.5 text-xs text-muted flex items-center justify-between">
                                @if ($s)
                                    <span class="truncate font-semibold text-ink" title="{{ $s->pasien?->nama }}">
                                        {{ $s->pasien?->nama ?? 'Pasien' }}
                                    </span>
                                    <span class="rounded bg-accent-soft px-1.5 py-0.5 text-[11px] font-bold text-accent-dark">
                                        Sesi {{ $s->nomor }}
                                    </span>
                                @else
                                    <span class="text-muted/70">Tersedia</span>
                                    <span class="text-emerald-600 font-semibold">Tersedia</span>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </main>

    {{-- Footer Ticker / Running Text ala Monitor Rumah Sakit --}}
    <footer class="flex-shrink-0 border-t border-line bg-white px-6 py-2.5 max-[800px]:px-4">
        <div class="mx-auto flex max-w-[1920px] items-center justify-between gap-4 text-xs text-muted">
            <div class="flex items-center gap-2 overflow-hidden whitespace-nowrap">
                <span class="rounded bg-accent-dark px-2 py-0.5 font-bold text-white text-[10px] uppercase tracking-wider">Info</span>
                <span class="truncate">
                    Selamat datang di <strong class="text-ink">Alpha Theraphy</strong>. Kursi terapi yang kosong siap digunakan. Harap konfirmasi ke petugas untuk memulai sesi.
                </span>
            </div>
            <div class="whitespace-nowrap font-medium text-muted/70 max-[800px]:hidden">
                &copy; {{ date('Y') }} Alpha Theraphy · アルファ・セラピー
            </div>
        </div>
    </footer>

    {{-- Script Realtime & Live Sync TV --}}
    <script>
    (function () {
        // 1. Live Clock Header
        const liveClockEl = document.getElementById('live-clock');
        function updateClock() {
            if (!liveClockEl) return;
            const d = new Date();
            const h = String(d.getHours()).padStart(2, '0');
            const m = String(d.getMinutes()).padStart(2, '0');
            const s = String(d.getSeconds()).padStart(2, '0');
            liveClockEl.innerHTML = `${h}:${m}:${s} <span class="text-xs font-semibold text-muted">WIB</span>`;
        }
        setInterval(updateClock, 1000);

        // 2. Fullscreen Support
        const btnFs = document.getElementById('btn-fullscreen');
        const iconEnter = document.getElementById('icon-fs-enter');
        const iconExit = document.getElementById('icon-fs-exit');

        if (btnFs) {
            btnFs.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
            });
            document.addEventListener('fullscreenchange', function () {
                const isFs = !!document.fullscreenElement;
                iconEnter.classList.toggle('hidden', isFs);
                iconExit.classList.toggle('hidden', !isFs);
            });
        }

        // 3. Countdown Timers
        const pad = n => String(n).padStart(2, '0');
        const fmt = s => {
            const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), d = s % 60;
            return h ? `${h}:${pad(m)}:${pad(d)}` : `${pad(m)}:${pad(d)}`;
        };

        function tickCounters() {
            const now = Date.now();
            let needsRefresh = false;

            document.querySelectorAll('[data-end]').forEach(el => {
                const end = Date.parse(el.dataset.end);
                const left = Math.ceil((end - now) / 1000);

                if (left > 0) {
                    el.textContent = fmt(left);
                } else {
                    el.textContent = '00:00';
                    if (!el.dataset.expired) {
                        el.dataset.expired = 'true';
                        needsRefresh = true;
                    }
                }
            });

            if (needsRefresh) {
                setTimeout(fetchLiveStatus, 1500);
            }
        }
        setInterval(tickCounters, 1000);
        tickCounters();

        // 4. Background Sync Polling (Setiap 8 detik mengambil status terbaru dari server)
        async function fetchLiveStatus() {
            try {
                const res = await fetch('/?json=1', {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();

                // Update Ringkasan Stat
                const elKosong = document.getElementById('stat-kosong');
                const elTerpakai = document.getElementById('stat-terpakai');
                const elTotal = document.getElementById('stat-total');
                if (elKosong) elKosong.textContent = data.kursiKosong;
                if (elTerpakai) elTerpakai.textContent = data.kursiTerpakai;
                if (elTotal) elTotal.textContent = `${data.totalKursi} Kursi`;

                // Update Tiap Kartu Kursi
                data.kursis.forEach(k => {
                    const card = document.getElementById(`kursi-card-${k.id}`);
                    if (!card) return;

                    const timerEl = card.querySelector('[data-end]');
                    const isCurrentlyRunning = !!timerEl;

                    // Bila status berubah (dari kosong ke terapi, atau sebaliknya)
                    if (isCurrentlyRunning !== k.sedang_terapi) {
                        location.reload();
                    } else if (k.sedang_terapi && timerEl && timerEl.dataset.end !== k.selesai_pada_iso) {
                        timerEl.dataset.end = k.selesai_pada_iso;
                        delete timerEl.dataset.expired;
                    }
                });
            } catch (e) {
                // Ignore network glitches silently
            }
        }
        setInterval(fetchLiveStatus, 8000);

    })();
    </script>
</body>
</html>
