<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pasien - Alpha Theraphy</title>
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
    $userName = $user->name ?? 'Pegawai';
    $userRole = $user?->role?->label ?? 'Pegawai';
    $initials = collect(preg_split('/\s+/', trim($userName)))->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    $tanggal = \Carbon\Carbon::now()->locale('id')->translatedFormat('l, j F Y');

    // Class input bersama (js-field: border merah dibersihkan JS saat pop-up dibuka ulang)
    $inputClass = 'js-field min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    @include('pegawai.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Pasien</h1>
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

        {{-- Daftar pasien --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-base font-bold">Daftar Pasien</h2>
                    <span class="text-[13px] text-muted">{{ $pasiens->total() }} pasien ditampilkan</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('pegawai.pasien.index') }}" class="flex gap-2">
                        @if ($status !== '')
                            <input type="hidden" name="status" value="{{ $status }}">
                        @endif
                        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama / no. telepon"
                               aria-label="Cari pasien"
                               class="min-h-[44px] w-[220px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20 max-[520px]:w-full">
                        <button type="submit"
                                class="inline-flex min-h-[44px] items-center rounded-[10px] border border-line bg-white px-4 text-sm font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                            Cari
                        </button>
                    </form>

                    <button type="button" id="openCreate"
                            class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                        + Daftarkan Pasien
                    </button>
                </div>
            </div>

            {{-- Filter Status Tabs --}}
            <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-line pb-3.5 text-xs">
                @php
                    $tabs = [
                        ['key' => '', 'label' => 'Semua', 'count' => $counts['total']],
                        ['key' => 'pending', 'label' => 'Menunggu ACC', 'count' => $counts['pending']],
                        ['key' => 'disetujui', 'label' => 'Disetujui', 'count' => $counts['disetujui']],
                        ['key' => 'ditolak', 'label' => 'Ditolak', 'count' => $counts['ditolak']],
                    ];
                @endphp
                @foreach ($tabs as $tab)
                    @php $isActive = $status === $tab['key']; @endphp
                    <a href="{{ route('pegawai.pasien.index', array_filter(['status' => $tab['key'], 'q' => $q])) }}"
                       class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 font-semibold transition
                              {{ $isActive ? 'bg-accent text-white shadow-sm' : 'bg-surface text-ink hover:bg-slate-200' }}">
                        <span>{{ $tab['label'] }}</span>
                        <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $isActive ? 'bg-white/20 text-white' : ($tab['key'] === 'pending' && $tab['count'] > 0 ? 'bg-amber-500 text-white' : 'bg-white text-muted border border-line') }}">
                            {{ $tab['count'] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Notifikasi jika ada pendaftaran mandiri pending --}}
            @if ($counts['pending'] > 0 && $status !== 'pending')
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-900">
                    <div class="flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-200 font-bold text-amber-800">!</span>
                        <span>Ada <strong>{{ $counts['pending'] }}</strong> pendaftaran pasien mandiri dari tamu yang menunggu persetujuan (ACC).</span>
                    </div>
                    <a href="{{ route('pegawai.pasien.index', array_filter(['status' => 'pending', 'q' => $q])) }}"
                       class="font-bold underline hover:text-amber-950">
                        Buka Pasien Menunggu ACC &rarr;
                    </a>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Nama</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">No. Telepon</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Alamat</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Status</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Terdaftar</th>
                            <th class="border-b border-line p-2 text-right text-xs font-semibold text-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($pasiens as $p)
                            <tr>
                                <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">{{ $p->nama }}</td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3">{{ $p->no_telepon }}</td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    <div class="line-clamp-2 max-w-[240px]" title="{{ $p->alamat }}">{{ $p->alamat }}</div>
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3">
                                    @if ($p->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-300 bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu ACC
                                        </span>
                                    @elseif ($p->status === 'disetujui')
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-300 bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 text-muted">
                                    {{ $p->created_at?->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- Tombol ACC jika belum disetujui --}}
                                        @if ($p->status !== 'disetujui')
                                            <form method="POST" action="{{ route('pegawai.pasien.approve', $p) }}"
                                                  data-confirm="Setujui (ACC) pasien {{ $p->nama }}? Pasien akan dapat membeli dan menggunakan voucher terapi.">
                                                @csrf
                                                <button type="submit" title="Setujui (ACC) Pasien"
                                                        class="inline-flex min-h-[34px] items-center gap-1 rounded-lg bg-emerald-600 px-3 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    ACC
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Tombol Tolak jika berstatus pending --}}
                                        @if ($p->status === 'pending')
                                            <form method="POST" action="{{ route('pegawai.pasien.reject', $p) }}"
                                                  data-confirm="Tolak pendaftaran pasien {{ $p->nama }}?">
                                                @csrf
                                                <button type="submit" title="Tolak Pendaftaran"
                                                        class="inline-flex min-h-[34px] items-center gap-1 rounded-lg border border-rose-300 bg-white px-2.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Edit: data dibaca JS untuk mengisi pop-up --}}
                                        <button type="button" data-edit
                                                data-id="{{ $p->id }}"
                                                data-nama="{{ $p->nama }}"
                                                data-alamat="{{ $p->alamat }}"
                                                data-telepon="{{ $p->no_telepon }}"
                                                class="inline-flex min-h-[34px] items-center rounded-lg border border-line bg-white px-2.5 text-xs font-semibold text-accent-dark transition hover:bg-accent-soft">
                                            Edit
                                        </button>

                                        {{-- Hapus --}}
                                        <form method="POST" action="{{ route('pegawai.pasien.destroy', $p) }}"
                                              data-confirm="Hapus pasien {{ $p->nama }}? Tindakan ini tidak bisa dibatalkan.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex min-h-[34px] items-center rounded-lg border border-line bg-white px-2.5 text-xs font-semibold text-warn transition hover:bg-[#FBEFE7]">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 py-8 text-center text-muted">
                                    @if ($q !== '')
                                        Tidak ada pasien yang cocok dengan "{{ $q }}".
                                    @elseif ($status === 'pending')
                                        Tidak ada pendaftaran pasien yang sedang menunggu persetujuan (ACC).
                                    @elseif ($status === 'ditolak')
                                        Tidak ada pasien berstatus ditolak.
                                    @else
                                        Belum ada pasien. Klik "+ Daftarkan Pasien" untuk menambahkan.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($pasiens->hasPages())
                <div class="mt-4">{{ $pasiens->links() }}</div>
            @endif
        </section>

        @include('pegawai.partials.footer')
    </main>
</div>

{{-- Pop-up daftar / edit pasien --}}
<dialog id="pasienModal" aria-labelledby="modalTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="pasienForm" method="POST" action="{{ route('pegawai.pasien.store') }}" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
        <input type="hidden" name="_mode" value="create" id="modeField">
        <input type="hidden" name="_edit_id" value="" id="editIdField">

        <div class="flex items-center justify-between">
            <h2 id="modalTitle" class="m-0 text-base font-bold">Daftarkan Pasien</h2>
            <button type="button" id="closeModal" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div>
            <label for="nama" class="mb-1.5 block text-[13px] font-semibold">Nama pasien</label>
            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required autocomplete="off"
                   class="{{ $inputClass }} {{ $errors->has('nama') ? 'border-warn' : 'border-line' }}">
            @error('nama')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="no_telepon" class="mb-1.5 block text-[13px] font-semibold">No. telepon</label>
            <input id="no_telepon" name="no_telepon" type="tel" inputmode="tel" value="{{ old('no_telepon') }}" required autocomplete="off"
                   class="{{ $inputClass }} {{ $errors->has('no_telepon') ? 'border-warn' : 'border-line' }}">
            @error('no_telepon')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="alamat" class="mb-1.5 block text-[13px] font-semibold">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3" required
                      class="{{ $inputClass }} resize-y py-2.5 {{ $errors->has('alamat') ? 'border-warn' : 'border-line' }}">{{ old('alamat') }}</textarea>
            @error('alamat')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" id="submitBtn"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                Daftarkan Pasien
            </button>
            <button type="button" id="cancelModal"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] border border-line bg-white px-[18px] text-sm font-semibold text-muted transition-colors hover:bg-surface">
                Batal
            </button>
        </div>
    </form>
</dialog>

<script>
(function () {
    const modal = document.getElementById('pasienModal');
    const form  = document.getElementById('pasienForm');
    const f = {
        nama:    document.getElementById('nama'),
        telepon: document.getElementById('no_telepon'),
        alamat:  document.getElementById('alamat'),
    };
    const methodField = document.getElementById('methodField');
    const modeField   = document.getElementById('modeField');
    const editIdField = document.getElementById('editIdField');
    const title       = document.getElementById('modalTitle');
    const submitBtn   = document.getElementById('submitBtn');

    const storeUrl     = @json(route('pegawai.pasien.store'));
    const updateUrlTpl = @json(route('pegawai.pasien.update', ['pasien' => '__ID__']));

    // Atur tampilan pop-up: mode daftar atau edit
    function setMode(mode, id) {
        const edit = mode === 'edit';
        const cleanId = String(id || '').replace(/\D/g, '');

        modeField.value   = edit ? 'edit' : 'create';
        editIdField.value = edit ? cleanId : '';
        methodField.disabled = !edit;
        form.action = edit ? updateUrlTpl.replace('__ID__', cleanId) : storeUrl;

        title.textContent     = edit ? 'Edit Pasien' : 'Daftarkan Pasien';
        submitBtn.textContent = edit ? 'Simpan Perubahan' : 'Daftarkan Pasien';
    }

    function clearErrors() {
        form.querySelectorAll('.js-error').forEach(el => el.remove());
        form.querySelectorAll('.js-field').forEach(el => {
            el.classList.remove('border-warn');
            el.classList.add('border-line');
        });
    }

    function openCreate() {
        clearErrors();
        setMode('create');
        f.nama.value = '';
        f.telepon.value = '';
        f.alamat.value = '';
        modal.showModal();
        f.nama.focus();
    }

    function openEdit(btn) {
        clearErrors();
        setMode('edit', btn.dataset.id);
        f.nama.value = btn.dataset.nama;
        f.telepon.value = btn.dataset.telepon;
        f.alamat.value = btn.dataset.alamat;
        modal.showModal();
        f.nama.focus();
    }

    function closeModal() { modal.close(); }

    document.getElementById('openCreate').addEventListener('click', openCreate);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('cancelModal').addEventListener('click', closeModal);
    document.querySelectorAll('[data-edit]').forEach(btn =>
        btn.addEventListener('click', () => openEdit(btn))
    );

    // Klik di luar kotak pop-up (area gelap) menutupnya
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    @if ($errors->any() && old('_mode'))
        // Validasi gagal: buka lagi pop-up dengan isian & pesan error tadi
        setMode(@json(old('_mode')), @json((string) old('_edit_id')));
        modal.showModal();
    @elseif (request()->has('tambah'))
        // Dibuka dari tombol "Daftarkan Pasien" di dashboard (?tambah=1)
        openCreate();
    @endif
})();
</script>
</body>
</html>
