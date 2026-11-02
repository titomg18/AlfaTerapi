<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Pegawai - Alpha Theraphy</title>
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
    $user      = auth()->user();
    $userName  = $user->name ?? 'Pegawai';
    $userRole  = $user?->role?->label ?? 'Pegawai';
    $firstName = explode(' ', trim($userName))[0];
    $initials  = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');
    $jumlahKursi = $kursis->count();
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    {{-- Sidebar Pegawai --}}
    @include('pegawai.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Halo, {{ $firstName }}</h1>
                <div class="mt-1 text-sm text-muted">{{ $tanggal }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pegawai.pasien.index', ['tambah' => 1]) }}"
                   class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white no-underline transition-colors hover:bg-accent-dark">
                    + Daftarkan Pasien
                </a>
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

        {{-- Ringkasan --}}
        <section class="grid grid-cols-4 gap-4 max-[820px]:grid-cols-2">
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Total Pasien</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ number_format($totalPasien, 0, ',', '.') }}</div>
                <div class="mt-0.5 text-xs text-muted">+{{ $pasienMingguIni }} minggu ini</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Kursi Terpakai</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $kursiTerpakai }} <small class="text-base font-medium text-muted">/ {{ $jumlahKursi }}</small></div>
                <div class="mt-0.5 text-xs text-muted">{{ $kursiKosong }} kursi kosong</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Voucher Aktif</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ number_format($voucherAktif, 0, ',', '.') }}</div>
                <div class="mt-0.5 text-xs text-muted">{{ $voucherHampirHabis }} hampir habis</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Sesi Hari Ini</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $transaksiHariIni }}</div>
                <div class="mt-0.5 text-xs text-muted">sesi terapi selesai</div>
            </div>
        </section>

        {{-- Status kursi (fokus utama pegawai) --}}
        <section class="rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex items-center justify-between">
                <div>
                    <h2 class="m-0 text-base font-bold">Status Kursi</h2>
                    <span class="text-xs text-muted">{{ $kursiKosong }} kosong dari {{ $jumlahKursi }} kursi</span>
                </div>
                <a href="{{ route('pegawai.kursi.index') }}" class="text-[13px] font-semibold text-accent hover:text-accent-dark">Lihat semua kursi</a>
            </div>

            @if ($kursis->isEmpty())
                <p class="m-0 py-4 text-center text-muted">
                    Belum ada kursi yang terdaftar.
                </p>
            @else
                <div class="grid grid-cols-5 gap-3 max-[1100px]:grid-cols-3 max-[820px]:grid-cols-2">
                    @foreach ($kursis as $k)
                        @php $s = $k->sesiAktif; @endphp
                        @if ($s)
                            <div class="rounded-xl bg-accent-soft px-4 py-3.5">
                                <div class="text-sm font-semibold">Kursi {{ $k->no_kursi }}</div>
                                <div class="mt-1 text-[22px] font-bold tabular-nums text-accent-dark"
                                     data-end="{{ $s->selesai_pada->toIso8601String() }}"></div>
                                <div class="truncate text-xs text-accent-dark" title="{{ $s->pasien?->nama }}">
                                    {{ $s->pasien?->nama }} · sesi {{ $s->nomor }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-xl border border-line bg-surface px-4 py-3.5">
                                <div class="text-sm font-semibold">Kursi {{ $k->no_kursi }}</div>
                                <div class="mt-1 text-[22px] font-bold text-muted">Kosong</div>
                                <div class="truncate text-xs text-muted">{{ $k->keterangan ?: 'siap dipakai' }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        <section class="grid grid-cols-[1.6fr_1fr] items-start gap-4 max-[820px]:grid-cols-1">

            {{-- Sesi terbaru --}}
            <div class="min-w-0 rounded-[14px] border border-line bg-white p-5">
                <div class="mb-3.5 flex items-center justify-between">
                    <h2 class="m-0 text-base font-bold">Sesi Terbaru</h2>
                    <a href="{{ route('pegawai.transaksi.index') }}" class="text-[13px] font-semibold text-accent hover:text-accent-dark">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[420px] border-collapse text-[13.5px]">
                        <thead>
                            <tr>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Pasien</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kursi</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Durasi</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr:last-child>td]:border-b-0">
                            @forelse ($transaksiTerbaru as $t)
                                <tr>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">{{ $t->pasien?->nama ?? '-' }}</td>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $t->no_kursi ? 'Kursi ' . $t->no_kursi : '-' }}</td>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $t->durasi_menit }} mnt</td>
                                    <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 tabular-nums text-muted">
                                        {{ $t->dimulai_pada?->format('H.i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-6 text-center text-muted">Belum ada sesi selesai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Voucher hampir habis --}}
            <div class="rounded-[14px] border border-line bg-white p-5">
                <div class="mb-3.5 flex items-center justify-between">
                    <h2 class="m-0 text-base font-bold">Voucher Hampir Habis</h2>
                    <a href="{{ route('pegawai.voucher.index') }}" class="text-[13px] font-semibold text-accent hover:text-accent-dark">Lihat semua</a>
                </div>
                <div class="flex flex-col gap-3.5">
                    @forelse ($voucherList as $v)
                        @php
                            $sisa   = $v->sisa_sesi;
                            $total  = $v->jumlah_sesi;
                            $persen = $total > 0 ? round(($sisa / $total) * 100) : 0;
                            $sisaHari = $v->berlaku_sampai
                                ? (int) now()->startOfDay()->diffInDays($v->berlaku_sampai->copy()->startOfDay())
                                : null;
                        @endphp
                        <div>
                            <div class="mb-1.5 flex justify-between text-[13.5px]">
                                <span class="font-semibold">{{ $v->pasien?->nama ?? '-' }}</span>
                                <span class="text-muted">
                                    sisa {{ $sisa }}/{{ $total }} sesi
                                    @if ($sisaHari !== null && $sisaHari <= 14)
                                        · <span class="text-warn font-medium">{{ $sisaHari <= 0 ? 'hari ini' : $sisaHari . ' hari lagi' }}</span>
                                    @endif
                                </span>
                            </div>
                            <div class="h-2 rounded-full bg-[#DDE8F2]">
                                <span class="block h-2 rounded-full bg-warn" style="width: {{ $persen }}%"></span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-muted">Tidak ada voucher yang hampir habis.</div>
                    @endforelse
                </div>
            </div>

        </section>

        @include('pegawai.partials.footer')
    </main>
</div>

<script>
(function () {
    // Hitung mundur di kartu kursi
    const pad = n => String(n).padStart(2, '0');
    const fmt = s => {
        const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), d = s % 60;
        return h ? h + ':' + pad(m) + ':' + pad(d) : pad(m) + ':' + pad(d);
    };
    const counters = Array.from(document.querySelectorAll('[data-end]'))
        .map(el => ({ el, end: Date.parse(el.dataset.end), alive: false }));
    let reloadScheduled = false;

    function tick() {
        const now = Date.now();
        counters.forEach(c => {
            const left = Math.ceil((c.end - now) / 1000);
            if (left > 0) {
                c.el.textContent = fmt(left);
                c.alive = true;
            } else {
                c.el.textContent = 'Selesai';
                if (c.alive && !reloadScheduled) {
                    reloadScheduled = true;
                    setTimeout(() => location.reload(), 2000);
                }
            }
        });
    }
    if (counters.length) { tick(); setInterval(tick, 1000); }
})();
</script>
</body>
</html>
