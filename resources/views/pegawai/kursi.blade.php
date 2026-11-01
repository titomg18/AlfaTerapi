<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kursi - Alpha Theraphy</title>

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
    $userName = $user->name ?? 'Pegawai';
    $userRole = $user?->role?->label ?? 'Pegawai';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    @include('pegawai.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Kursi</h1>
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

        {{-- Info Read-Only --}}
        <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
            <svg class="mt-0.5 flex-shrink-0 text-accent" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>
            </svg>
            <p class="m-0">
                Daftar kursi terapi klinik. <span class="font-semibold text-ink">Pegawai hanya dapat melihat status kursi</span>. Penambahan atau perubahan data kursi hanya dapat dilakukan oleh Admin.
            </p>
        </div>

        {{-- Daftar kursi --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-base font-bold">Daftar Kursi</h2>
                    <span class="text-[13px] text-muted">{{ $kursis->count() }} kursi terdaftar</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">No. Kursi</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Status</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Keterangan</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Ditambahkan</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($kursis as $k)
                            <tr>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 font-semibold text-accent-dark">
                                    Kursi {{ $k->no_kursi }}
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    @if ($k->sesiAktif)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-soft px-2.5 py-0.5 text-xs font-semibold text-accent-dark">
                                            <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                            Terapi: {{ $k->sesiAktif->pasien?->nama ?? 'Pasien' }} (sesi {{ $k->sesiAktif->nomor }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Siap Digunakan
                                        </span>
                                    @endif
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    @if ($k->keterangan)
                                        <div class="line-clamp-2 max-w-[360px]" title="{{ $k->keterangan }}">{{ $k->keterangan }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 text-muted">
                                    {{ $k->created_at?->locale('id')->translatedFormat('j M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-6 text-center text-muted">Belum ada kursi yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @include('pegawai.partials.footer')
    </main>
</div>
</body>
</html>
