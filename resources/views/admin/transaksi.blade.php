<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transaksi - Alpha Theraphy</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">

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
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    @include('admin.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Transaksi</h1>
                <div class="mt-1 text-sm text-muted">{{ $tanggal }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2.5 rounded-xl border border-line bg-white py-1.5 pl-1.5 pr-3.5">
                    <div class="flex h-[34px] w-[34px] items-center justify-center rounded-full bg-[#D0E6F8] text-[13px] font-bold text-accent-dark">{{ $initials }}</div>
                    <div class="text-[13px] leading-[1.3]">
                        <div class="font-semibold">{{ $userName }}</div>
                        <div class="text-muted">{{ ucfirst($userRole) }}</div>
                    </div>
                </div>

                {{-- Logout (harus POST + CSRF sesuai route) --}}
                <form method="POST" action="{{ route('logout') }}"
                      data-confirm="Yakin ingin keluar dari akun?">
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
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 font-semibold">
                                    {{ $s->dimulai_pada->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">{{ $s->pasien?->nama ?? '-' }}</td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 tabular-nums">
                                    <span class="font-semibold">{{ $s->nomor }}</span>
                                    <span class="text-muted">/ {{ $s->voucher?->jumlah_sesi ?? 30 }}</span>
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3">
                                    {{ $s->no_kursi ? 'Kursi ' . $s->no_kursi : '-' }}
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 tabular-nums text-muted">
                                    {{ $s->dimulai_pada->format('H.i') }} - {{ $s->selesai_pada->format('H.i') }}
                                    @unless ($s->selesai_pada->isSameDay($s->dimulai_pada))
                                        <span class="text-xs">(+1 hari)</span>
                                    @endunless
                                    <div class="text-xs">{{ $s->durasi_menit }} menit</div>
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $s->nama_pegawai ?? '-' }}</td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 font-mono text-[13px]">
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

        @include('admin.partials.footer')
    </main>
</div>
</body>
</html>