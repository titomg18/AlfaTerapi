<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Pegawai Alpha Theraphy</title>

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
    // ---- Data (kirim dari controller; nilai di bawah hanya fallback/contoh) ----
    $transaksiHariIni   = $transaksiHariIni   ?? 14;
    $voucherHampirHabis = $voucherHampirHabis ?? 3;

    // Daftar kursi: ['nama' => 'Kursi 1', 'sisa_waktu' => '12:40' atau null jika kosong]
    $kursi = $kursi ?? [
        ['nama' => 'Kursi 1', 'sisa_waktu' => '12:40'],
        ['nama' => 'Kursi 2', 'sisa_waktu' => '21:05'],
        ['nama' => 'Kursi 3', 'sisa_waktu' => '38:12'],
        ['nama' => 'Kursi 4', 'sisa_waktu' => null],
        ['nama' => 'Kursi 5', 'sisa_waktu' => null],
    ];
    $kursiTerpakai = collect($kursi)->filter(fn ($k) => !empty(((object) $k)->sisa_waktu))->count();
    $kursiKosong   = collect($kursi)->count() - $kursiTerpakai;

    // Transaksi hari ini: ['pasien', 'kursi', 'durasi' (menit), 'waktu' (Carbon/string)]
    $transaksiTerbaru = $transaksiTerbaru ?? [
        ['pasien' => 'Siti Rahmawati', 'kursi' => 'Kursi 1', 'durasi' => 30, 'waktu' => '09.15'],
        ['pasien' => 'Agus Pratama',   'kursi' => 'Kursi 3', 'durasi' => 45, 'waktu' => '08.50'],
        ['pasien' => 'Dewi Lestari',   'kursi' => 'Kursi 2', 'durasi' => 30, 'waktu' => '08.30'],
    ];

    // Voucher hampir habis: ['pasien', 'sisa', 'total']
    $voucherList = $voucherList ?? [
        ['pasien' => 'Siti Rahmawati', 'sisa' => 2, 'total' => 30],
        ['pasien' => 'Hendra Wijaya',  'sisa' => 3, 'total' => 30],
        ['pasien' => 'Maya Anggraini', 'sisa' => 5, 'total' => 20],
    ];

    $user      = auth()->user();
    $userName  = $user->name ?? 'Pegawai';
    $userRole  = $user?->role?->label ?? 'Pegawai';
    $firstName = explode(' ', trim($userName))[0];
    $initials  = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');

    // Menu sidebar pegawai: [label, url, pola route aktif, path svg]
    $menu = [
        ['Dashboard', url('/pegawai/dashboard'), 'pegawai/dashboard*', '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['Pasien',    url('/pegawai/pasien'),    'pegawai/pasien*',    '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Kursi',     url('/pegawai/kursi'),     'pegawai/kursi*',     '<path d="M6 3v10h12v8M6 13v8M18 13V9"/>'],
        ['Timer',     url('/pegawai/timer'),     'pegawai/timer*',     '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/>'],
        ['Voucher',   url('/pegawai/voucher'),   'pegawai/voucher*',   '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/>'],
        ['Transaksi', url('/pegawai/transaksi'), 'pegawai/transaksi*', '<path d="M4 4h16v16H4zM8 9h8M8 13h8M8 17h4"/>'],
    ];
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    {{-- Sidebar --}}
    @include('partials.toast')

    <nav class="sticky top-0 z-20 flex h-screen w-[232px] flex-shrink-0 flex-col gap-1.5 self-start overflow-y-auto border-r border-line bg-white px-4 py-6
                max-[820px]:static max-[820px]:h-auto max-[820px]:w-auto max-[820px]:flex-row max-[820px]:overflow-x-auto max-[820px]:self-auto">
        <div class="flex items-center gap-2.5 px-2 pb-[22px] max-[820px]:pb-0">
            <img src="{{ asset('images/logo.jpg') }}" alt="Alpha Theraphy" class="h-10 w-10 flex-shrink-0 rounded-[10px] border border-line bg-white object-contain shadow-sm p-0.5">
            <div class="min-w-0">
                <div class="truncate text-[15px] font-extrabold leading-tight text-accent-dark">Alpha Theraphy</div>
                <div class="text-[10px] font-semibold tracking-wider text-muted">アルファ・セラピー</div>
            </div>
        </div>

        @foreach ($menu as [$label, $href, $pattern, $icon])
            <a href="{{ $href }}"
               class="flex items-center gap-3 whitespace-nowrap rounded-[10px] px-3 py-[11px] text-sm transition-colors
                      {{ request()->is($pattern)
                            ? 'bg-accent-soft font-semibold text-accent-dark'
                            : 'text-[#334E68] hover:bg-surface' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">{!! $icon !!}</svg>
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Halo, {{ $firstName }}</h1>
                <div class="mt-1 text-sm text-muted">{{ $tanggal }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ url('/pegawai/pasien/create') }}"
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
                <div class="text-[13px] text-muted">Sesi Hari Ini</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $transaksiHariIni }}</div>
                <div class="mt-0.5 text-xs text-muted">sesi terapi selesai</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Kursi Terpakai</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $kursiTerpakai }} <small class="text-base font-medium text-muted">/ {{ count($kursi) }}</small></div>
                <div class="mt-0.5 text-xs text-muted">sedang berjalan</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Kursi Kosong</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $kursiKosong }}</div>
                <div class="mt-0.5 text-xs text-muted">siap dipakai</div>
            </div>
            <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
                <div class="text-[13px] text-muted">Voucher Hampir Habis</div>
                <div class="mt-1.5 text-[30px] font-bold">{{ $voucherHampirHabis }}</div>
                <div class="mt-0.5 text-xs text-muted">perlu diingatkan</div>
            </div>
        </section>

        {{-- Status kursi (fokus utama pegawai) --}}
        <section class="rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex items-center justify-between">
                <h2 class="m-0 text-base font-bold">Status Kursi</h2>
                <a href="{{ url('/pegawai/kursi') }}" class="text-[13px] font-semibold text-accent hover:text-accent-dark">Kelola kursi</a>
            </div>
            <div class="grid grid-cols-5 gap-3 max-[1100px]:grid-cols-3 max-[820px]:grid-cols-2">
                @foreach ($kursi as $k)
                    @php $k = (object) $k; @endphp
                    @if (!empty($k->sisa_waktu))
                        <div class="rounded-xl bg-accent-soft px-4 py-3.5">
                            <div class="text-sm font-semibold">{{ $k->nama }}</div>
                            <div class="mt-1 text-[22px] font-bold tabular-nums text-accent-dark">{{ $k->sisa_waktu }}</div>
                            <div class="text-xs text-accent-dark">sisa waktu</div>
                        </div>
                    @else
                        <div class="rounded-xl border border-line bg-surface px-4 py-3.5">
                            <div class="text-sm font-semibold">{{ $k->nama }}</div>
                            <div class="mt-1 text-[22px] font-bold text-muted">Kosong</div>
                            <div class="text-xs text-muted">siap dipakai</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="grid grid-cols-[1.6fr_1fr] items-start gap-4 max-[820px]:grid-cols-1">

            {{-- Transaksi hari ini --}}
            <div class="min-w-0 rounded-[14px] border border-line bg-white p-5">
                <div class="mb-3.5 flex items-center justify-between">
                    <h2 class="m-0 text-base font-bold">Sesi Terbaru Hari Ini</h2>
                    <a href="{{ url('/pegawai/transaksi') }}" class="text-[13px] font-semibold text-accent hover:text-accent-dark">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[420px] border-collapse text-[13.5px]">
                        <thead>
                            <tr>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Pasien</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kursi</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Durasi</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Jam</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr:last-child>td]:border-b-0">
                            @forelse ($transaksiTerbaru as $t)
                                @php
                                    $t = (object) $t;
                                    $waktu = $t->waktu instanceof \Carbon\Carbon
                                        ? $t->waktu->format('H.i')
                                        : $t->waktu;
                                @endphp
                                <tr>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $t->pasien }}</td>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $t->kursi }}</td>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $t->durasi }} menit</td>
                                    <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $waktu }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-6 text-center text-muted">Belum ada sesi hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Voucher hampir habis --}}
            <div class="rounded-[14px] border border-line bg-white p-5">
                <h2 class="mb-3.5 text-base font-bold">Voucher Hampir Habis</h2>
                <div class="flex flex-col gap-3.5">
                    @forelse ($voucherList as $v)
                        @php
                            $v = (object) $v;
                            $persen = $v->total > 0 ? round(($v->sisa / $v->total) * 100) : 0;
                        @endphp
                        <div>
                            <div class="mb-1.5 flex justify-between text-[13.5px]">
                                <span class="font-semibold">{{ $v->pasien }}</span>
                                <span class="text-muted">sisa {{ $v->sisa }} dari {{ $v->total }}</span>
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

    </main>
</div>
</body>
</html>
