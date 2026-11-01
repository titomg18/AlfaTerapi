<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaksi - Alfa Terapi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind (CDN). Kalau project sudah pakai Vite/build, hapus dua baris ini. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            DEFAULT: '#0F766E',
                            dark:    '#0B5A54',
                            soft:    '#E6F3F1',
                        },
                        surface: '#F3F7F6',
                        line:    '#DCE7E5',
                        ink:     '#12302E',
                        muted:   '#4F6664',
                        warn:    '#B4531A',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
</head>
<body class="m-0 bg-surface font-sans text-ink">

@php
    $user     = auth()->user();
    $userName = $user->name ?? 'Admin';
    $userRole = $user?->role?->label ?? 'Admin';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');

    $hariIni = now()->toDateString();
    $presets = [
        'Hari ini'        => ['dari' => $hariIni, 'sampai' => $hariIni],
        '7 hari terakhir' => ['dari' => now()->subDays(6)->toDateString(), 'sampai' => $hariIni],
        'Bulan ini'       => ['dari' => now()->startOfMonth()->toDateString(), 'sampai' => $hariIni],
    ];

    $adaFilter = $q !== '' || $dari || $sampai;
    $dateInput = 'min-h-[44px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';

    // Menu sidebar: [label, url, pola route aktif, path svg]
    $menu = [
        ['Dashboard', url('/admin/dashboard'), 'admin/dashboard*', '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['Pasien',    route('admin.pasien.index'),    'admin/pasien*',    '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Kursi',     route('admin.kursi.index'),     'admin/kursi*',     '<path d="M6 3v10h12v8M6 13v8M18 13V9"/>'],
        ['Timer',     route('admin.timer.index'),     'admin/timer*',     '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/>'],
        ['Voucher',   route('admin.voucher.index'),   'admin/voucher*',   '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/>'],
        ['Transaksi', route('admin.transaksi.index'), 'admin/transaksi*', '<path d="M4 4h16v16H4zM8 9h8M8 13h8M8 17h4"/>'],
        ['Pegawai',   route('admin.pegawai.index'),   'admin/pegawai*',   '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5 7-5s7 1.5 7 5M17 4.5a3.5 3.5 0 0 1 0 7M22 20c0-2.5-1.5-4-4-4.6"/>'],
    ];
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    {{-- Sidebar --}}
    <nav class="flex w-[232px] flex-shrink-0 flex-col gap-1.5 border-r border-line bg-white px-4 py-6
                max-[820px]:w-auto max-[820px]:flex-row max-[820px]:overflow-x-auto">
        <div class="flex items-center gap-2.5 px-2 pb-[22px] max-[820px]:pb-0">
            <div class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-accent font-bold text-white">A</div>
            <div class="whitespace-nowrap text-base font-bold">Alfa Terapi</div>
        </div>

        @foreach ($menu as [$label, $href, $pattern, $icon])
            <a href="{{ $href }}"
               class="flex items-center gap-3 whitespace-nowrap rounded-[10px] px-3 py-[11px] text-sm transition-colors
                      {{ request()->is($pattern)
                            ? 'bg-accent-soft font-semibold text-accent-dark'
                            : 'text-[#3D5654] hover:bg-surface' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">{!! $icon !!}</svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Transaksi</h1>
                <div class="mt-1 text-sm text-muted">{{ $tanggal }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2.5 rounded-xl border border-line bg-white py-1.5 pl-1.5 pr-3.5">
                    <div class="flex h-[34px] w-[34px] items-center justify-center rounded-full bg-[#CFE6E2] text-[13px] font-bold text-accent-dark">{{ $initials }}</div>
                    <div class="text-[13px] leading-[1.3]">
                        <div class="font-semibold">{{ $userName }}</div>
                        <div class="text-muted">{{ ucfirst($userRole) }}</div>
                    </div>
                </div>

                {{-- Logout (harus POST + CSRF sesuai route) --}}
                <form method="POST" action="{{ route('logout') }}"
                      onsubmit="return confirm('Yakin ingin keluar?')">
                    @csrf
                    <button type="submit"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-[10px] border border-line bg-white px-4 text-sm font-semibold text-warn transition-colors hover:bg-[#FBEFE7]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        {{-- Laporan --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5">
                <h2 class="m-0 text-base font-bold">Laporan Pemakaian Voucher</h2>
                <span class="text-[13px] text-muted">
                    @if ($dari || $sampai)
                        {{ $dari ? \Carbon\Carbon::parse($dari)->locale('id')->translatedFormat('j M Y') : 'awal' }}
                        s.d.
                        {{ $sampai ? \Carbon\Carbon::parse($sampai)->locale('id')->translatedFormat('j M Y') : 'sekarang' }}
                    @else
                        Semua tanggal
                    @endif
                </span>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.transaksi.index') }}" class="mb-3 flex flex-wrap items-end gap-3">
                <div>
                    <label for="q" class="mb-1 block text-xs font-semibold text-muted">Cari</label>
                    <input id="q" type="search" name="q" value="{{ $q }}" placeholder="Nama pasien / kode voucher"
                           class="{{ $dateInput }} w-[220px] max-[520px]:w-full">
                </div>
                <div>
                    <label for="dari" class="mb-1 block text-xs font-semibold text-muted">Dari tanggal</label>
                    <input id="dari" type="date" name="dari" value="{{ $dari }}" class="{{ $dateInput }}">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-xs font-semibold text-muted">Sampai tanggal</label>
                    <input id="sampai" type="date" name="sampai" value="{{ $sampai }}" class="{{ $dateInput }}">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-4 text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                        Terapkan
                    </button>
                    @if ($adaFilter)
                        <a href="{{ route('admin.transaksi.index') }}"
                           class="inline-flex min-h-[44px] items-center rounded-[10px] border border-line bg-white px-4 text-sm font-semibold text-muted transition-colors hover:bg-surface">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- Pilihan cepat --}}
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="text-xs text-muted">Pilih cepat:</span>
                @foreach ($presets as $label => $range)
                    @php $aktif = $dari === $range['dari'] && $sampai === $range['sampai']; @endphp
                    <a href="{{ route('admin.transaksi.index', array_filter($range + ['q' => $q])) }}"
                       class="inline-flex min-h-[32px] items-center rounded-lg border px-2.5 text-xs font-semibold transition-colors
                              {{ $aktif ? 'border-accent bg-accent-soft text-accent-dark' : 'border-line bg-white text-accent-dark hover:bg-accent-soft' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Tanggal</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Pasien</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Sesi</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kursi</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Waktu</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Terapis</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kode Voucher</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($transaksis as $s)
                            <tr>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-semibold">
                                    {{ $s->dimulai_pada->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EEF3F2] px-2 py-3 font-semibold">{{ $s->pasien?->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 tabular-nums">
                                    <span class="font-semibold">{{ $s->nomor }}</span>
                                    <span class="text-muted">/ {{ $s->voucher?->jumlah_sesi ?? 30 }}</span>
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3">
                                    {{ $s->no_kursi ? 'Kursi ' . $s->no_kursi : '-' }}
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 tabular-nums text-muted">
                                    {{ $s->dimulai_pada->format('H.i') }} - {{ $s->selesai_pada->format('H.i') }}
                                    @unless ($s->selesai_pada->isSameDay($s->dimulai_pada))
                                        <span class="text-xs">(+1 hari)</span>
                                    @endunless
                                    <div class="text-xs">{{ $s->durasi_menit }} menit</div>
                                </td>
                                <td class="border-b border-[#EEF3F2] px-2 py-3">{{ $s->nama_pegawai ?? '-' }}</td>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-mono text-[13px]">
                                    {{ $s->voucher?->kode ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-2 py-6 text-center text-muted">
                                    @if ($adaFilter)
                                        Tidak ada sesi selesai yang cocok dengan filter.
                                    @else
                                        Belum ada sesi yang selesai. Sesi akan muncul di sini setelah timer habis.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transaksis->hasPages())
                <div class="mt-4">{{ $transaksis->links() }}</div>
            @endif
        </section>

    </main>
</div>
</body>
</html>