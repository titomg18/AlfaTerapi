<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengguna - Alfa Terapi</title>

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

    // Class input bersama (border merah jika field error)
    $inputClass = 'min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';

    // Menu sidebar: [label, url, pola route aktif, path svg]
    $menu = [
        ['Dashboard', url('/admin/dashboard'), 'admin/dashboard*', '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['Pasien',    url('/admin/pasien'),    'admin/pasien*',    '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Kursi',     url('/admin/kursi'),     'admin/kursi*',     '<path d="M6 3v10h12v8M6 13v8M18 13V9"/>'],
        ['Timer',     url('/admin/timer'),     'admin/timer*',     '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/>'],
        ['Voucher',   url('/admin/voucher'),   'admin/voucher*',   '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/>'],
        ['Transaksi', url('/admin/transaksi'), 'admin/transaksi*', '<path d="M4 4h16v16H4zM8 9h8M8 13h8M8 17h4"/>'],
        ['Pegawai',   route('admin.pegawai.index'), 'admin/pegawai*', '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5 7-5s7 1.5 7 5M17 4.5a3.5 3.5 0 0 1 0 7M22 20c0-2.5-1.5-4-4-4.6"/>'],
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
                <h1 class="m-0 text-[26px] font-bold">Pengguna</h1>
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

        {{-- Notifikasi sukses --}}
        @if (session('success'))
            <div role="status" class="rounded-xl border border-accent/30 bg-accent-soft px-4 py-3 text-sm font-semibold text-accent-dark">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-[1.6fr_1fr] items-start gap-4 max-[900px]:grid-cols-1">

            {{-- Daftar pengguna --}}
            <div class="min-w-0 rounded-[14px] border border-line bg-white p-5">
                <div class="mb-3.5 flex items-center justify-between">
                    <h2 class="m-0 text-base font-bold">Daftar Pengguna</h2>
                    <span class="text-[13px] text-muted">{{ $users->count() }} akun</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] border-collapse text-[13.5px]">
                        <thead>
                            <tr>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Nama</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Username</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Role</th>
                                <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Ditambahkan</th>
                            </tr>
                        </thead>
                        <tbody class="[&>tr:last-child>td]:border-b-0">
                            @forelse ($users as $u)
                                <tr>
                                    <td class="border-b border-[#EEF3F2] px-2 py-3 font-semibold">{{ $u->name }}</td>
                                    <td class="border-b border-[#EEF3F2] px-2 py-3">{{ $u->username }}</td>
                                    <td class="border-b border-[#EEF3F2] px-2 py-3">
                                        @if ($u->role)
                                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                         {{ $u->role->name === 'admin'
                                                                ? 'bg-accent-soft text-accent-dark'
                                                                : 'bg-[#EEF3F2] text-muted' }}">
                                                {{ $u->role->label }}
                                            </span>
                                        @else
                                            <span class="text-xs text-warn">Belum ada role</span>
                                        @endif
                                    </td>
                                    <td class="border-b border-[#EEF3F2] px-2 py-3 text-muted">
                                        {{ $u->created_at?->locale('id')->translatedFormat('j M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-6 text-center text-muted">Belum ada pengguna.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Form tambah pengguna --}}
            <div class="rounded-[14px] border border-line bg-white p-5">
                <h2 class="mb-3.5 text-base font-bold">Tambah Pengguna</h2>

                <form method="POST" action="{{ route('admin.pegawai.store') }}" class="flex flex-col gap-4" novalidate>
                    @csrf

                    <div>
                        <label for="name" class="mb-1.5 block text-[13px] font-semibold">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required
                               autocomplete="off"
                               class="{{ $inputClass }} {{ $errors->has('name') ? 'border-warn' : 'border-line' }}">
                        @error('name')
                            <p class="mt-1 text-xs text-warn">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="mb-1.5 block text-[13px] font-semibold">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required
                               autocomplete="off"
                               class="{{ $inputClass }} {{ $errors->has('username') ? 'border-warn' : 'border-line' }}">
                        @error('username')
                            <p class="mt-1 text-xs text-warn">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="role_id" class="mb-1.5 block text-[13px] font-semibold">Role</label>
                        @php $defaultRoleId = $roles->firstWhere('name', 'pegawai')?->id; @endphp
                        <select id="role_id" name="role_id" required
                                class="{{ $inputClass }} {{ $errors->has('role_id') ? 'border-warn' : 'border-line' }}">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}" @selected((string) old('role_id', $defaultRoleId) === (string) $r->id)>
                                    {{ $r->label }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id')
                            <p class="mt-1 text-xs text-warn">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-1.5 block text-[13px] font-semibold">Kata sandi</label>
                        <input id="password" name="password" type="password" required
                               autocomplete="new-password"
                               class="{{ $inputClass }} {{ $errors->has('password') ? 'border-warn' : 'border-line' }}">
                        @error('password')
                            <p class="mt-1 text-xs text-warn">{{ $message }}</p>
                        @else
                            <p class="mt-1 text-xs text-muted">Minimal 8 karakter.</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-[13px] font-semibold">Ulangi kata sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               autocomplete="new-password"
                               class="{{ $inputClass }} border-line">
                    </div>

                    <button type="submit"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                        + Tambah Pengguna
                    </button>
                </form>
            </div>

        </section>

    </main>
</div>
</body>
</html>