<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kursi - Alfa Terapi</title>

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

    // Class input bersama (js-field: border merah dibersihkan JS saat pop-up dibuka ulang)
    $inputClass = 'js-field min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';

    // Menu sidebar: [label, url, pola route aktif, path svg]
    $menu = [
        ['Dashboard', url('/admin/dashboard'), 'admin/dashboard*', '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['Pasien',    route('admin.pasien.index'), 'admin/pasien*', '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Kursi',     route('admin.kursi.index'),  'admin/kursi*',  '<path d="M6 3v10h12v8M6 13v8M18 13V9"/>'],
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
                <h1 class="m-0 text-[26px] font-bold">Kursi</h1>
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

        {{-- Notifikasi --}}
        @if (session('success'))
            <div role="status" class="rounded-xl border border-accent/30 bg-accent-soft px-4 py-3 text-sm font-semibold text-accent-dark">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div role="alert" class="rounded-xl border border-warn/30 bg-[#FBEFE7] px-4 py-3 text-sm font-semibold text-warn">
                {{ session('error') }}
            </div>
        @endif

        {{-- Daftar kursi --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-base font-bold">Daftar Kursi</h2>
                    <span class="text-[13px] text-muted">{{ $kursis->count() }} kursi</span>
                </div>
                <button type="button" id="openCreate"
                        class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                    + Tambah Kursi
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">No. Kursi</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Keterangan</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Ditambahkan</th>
                            <th class="border-b border-line p-2 text-right text-xs font-semibold text-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($kursis as $k)
                            <tr>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-semibold">{{ $k->no_kursi }}</td>
                                <td class="border-b border-[#EEF3F2] px-2 py-3">
                                    @if ($k->keterangan)
                                        <div class="line-clamp-2 max-w-[360px]" title="{{ $k->keterangan }}">{{ $k->keterangan }}</div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 text-muted">
                                    {{ $k->created_at?->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EEF3F2] px-2 py-3">
                                    <div class="flex justify-end gap-2">
                                        {{-- Edit: data dibaca JS untuk mengisi pop-up --}}
                                        <button type="button" data-edit
                                                data-id="{{ $k->id }}"
                                                data-no="{{ $k->no_kursi }}"
                                                data-keterangan="{{ $k->keterangan }}"
                                                class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.kursi.destroy', $k) }}"
                                              data-confirm="Hapus kursi {{ $k->no_kursi }}? Tindakan ini tidak bisa dibatalkan."
                                              onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-warn transition-colors hover:bg-[#FBEFE7]">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-6 text-center text-muted">Belum ada kursi. Klik "Tambah Kursi" untuk menambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

{{-- Pop-up tambah / edit kursi --}}
<dialog id="kursiModal" aria-labelledby="modalTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="kursiForm" method="POST" action="{{ route('admin.kursi.store') }}" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
        <input type="hidden" name="_mode" value="create" id="modeField">
        <input type="hidden" name="_edit_id" value="" id="editIdField">

        <div class="flex items-center justify-between">
            <h2 id="modalTitle" class="m-0 text-base font-bold">Tambah Kursi</h2>
            <button type="button" id="closeModal" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div>
            <label for="no_kursi" class="mb-1.5 block text-[13px] font-semibold">No. kursi</label>
            <input id="no_kursi" name="no_kursi" type="text" value="{{ old('no_kursi') }}" required autocomplete="off" maxlength="20"
                   class="{{ $inputClass }} {{ $errors->has('no_kursi') ? 'border-warn' : 'border-line' }}">
            @error('no_kursi')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="keterangan" class="mb-1.5 block text-[13px] font-semibold">
                Keterangan <span class="font-normal text-muted">(opsional)</span>
            </label>
            <textarea id="keterangan" name="keterangan" rows="3" maxlength="255"
                      class="{{ $inputClass }} resize-y py-2.5 {{ $errors->has('keterangan') ? 'border-warn' : 'border-line' }}">{{ old('keterangan') }}</textarea>
            @error('keterangan')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" id="submitBtn"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                Tambah Kursi
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
    const modal = document.getElementById('kursiModal');
    const form  = document.getElementById('kursiForm');
    const f = {
        no:         document.getElementById('no_kursi'),
        keterangan: document.getElementById('keterangan'),
    };
    const methodField = document.getElementById('methodField');
    const modeField   = document.getElementById('modeField');
    const editIdField = document.getElementById('editIdField');
    const title       = document.getElementById('modalTitle');
    const submitBtn   = document.getElementById('submitBtn');

    const storeUrl     = @json(route('admin.kursi.store'));
    const updateUrlTpl = @json(route('admin.kursi.update', ['kursi' => '__ID__']));

    // Atur tampilan pop-up: mode tambah atau edit
    function setMode(mode, id) {
        const edit = mode === 'edit';
        const cleanId = String(id || '').replace(/\D/g, '');

        modeField.value   = edit ? 'edit' : 'create';
        editIdField.value = edit ? cleanId : '';
        methodField.disabled = !edit;
        form.action = edit ? updateUrlTpl.replace('__ID__', cleanId) : storeUrl;

        title.textContent     = edit ? 'Edit Kursi' : 'Tambah Kursi';
        submitBtn.textContent = edit ? 'Simpan Perubahan' : 'Tambah Kursi';
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
        f.no.value = '';
        f.keterangan.value = '';
        modal.showModal();
        f.no.focus();
    }

    function openEdit(btn) {
        clearErrors();
        setMode('edit', btn.dataset.id);
        f.no.value = btn.dataset.no;
        f.keterangan.value = btn.dataset.keterangan || '';
        modal.showModal();
        f.no.focus();
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
        openCreate();
    @endif
})();
</script>
</body>
</html>