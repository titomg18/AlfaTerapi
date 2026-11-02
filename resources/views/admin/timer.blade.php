<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Timer - Alpha Theraphy</title>
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

    // Class input bersama (js-field: border merah dibersihkan JS saat pop-up dibuka ulang)
    $inputClass = 'js-field min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';

    // Pilihan durasi cepat (menit)
    $presets = [15, 30, 45, 60];
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    @include('admin.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Timer</h1>
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

{{-- Info: timer berlaku di semua kursi --}}
        <div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
            <svg class="mt-0.5 flex-shrink-0 text-accent" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>
            </svg>
            <p class="m-0">
                Setiap timer yang dibuat di sini <span class="font-semibold text-ink">otomatis tersedia di semua kursi</span>
                @if ($jumlahKursi > 0)
                    (saat ini {{ $jumlahKursi }} kursi), termasuk kursi yang ditambahkan kemudian.
                @else
                    , termasuk kursi yang ditambahkan kemudian. Belum ada kursi yang terdaftar.
                @endif
            </p>
        </div>

        {{-- Daftar timer --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-base font-bold">Daftar Timer</h2>
                    <span class="text-[13px] text-muted">{{ $timers->count() }} timer</span>
                </div>
                <button type="button" id="openCreate"
                        class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                    + Buat Timer
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Nama Timer</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Durasi</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Berlaku di</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Dibuat</th>
                            <th class="border-b border-line p-2 text-right text-xs font-semibold text-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($timers as $t)
                            <tr>
                                <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">{{ $t->nama }}</td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3">
                                    <span class="font-semibold tabular-nums">{{ $t->durasi_label }}</span>
                                    <span class="ml-1 text-xs text-muted">({{ $t->durasi_menit }} mnt)</span>
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    <span class="inline-block rounded-full bg-accent-soft px-2.5 py-0.5 text-xs font-semibold text-accent-dark">
                                        Semua kursi{{ $jumlahKursi > 0 ? ' (' . $jumlahKursi . ')' : '' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 text-muted">
                                    {{ $t->created_at?->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    <div class="flex justify-end gap-2">
                                        {{-- Edit: data dibaca JS untuk mengisi pop-up --}}
                                        <button type="button" data-edit
                                                data-id="{{ $t->id }}"
                                                data-nama="{{ $t->nama }}"
                                                data-durasi="{{ $t->durasi_menit }}"
                                                class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.timer.destroy', $t) }}"
                                              data-confirm="Hapus timer {{ $t->nama }}? Timer ini akan hilang dari semua kursi.">
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
                                <td colspan="5" class="px-2 py-6 text-center text-muted">Belum ada timer. Klik "Buat Timer" untuk menambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @include('admin.partials.footer')
    </main>
</div>

{{-- Pop-up buat / edit timer --}}
<dialog id="timerModal" aria-labelledby="modalTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="timerForm" method="POST" action="{{ route('admin.timer.store') }}" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
        <input type="hidden" name="_mode" value="create" id="modeField">
        <input type="hidden" name="_edit_id" value="" id="editIdField">

        <div class="flex items-center justify-between">
            <h2 id="modalTitle" class="m-0 text-base font-bold">Buat Timer</h2>
            <button type="button" id="closeModal" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div>
            <label for="nama" class="mb-1.5 block text-[13px] font-semibold">Nama timer</label>
            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required autocomplete="off" maxlength="100"
                   placeholder="Contoh: Terapi 30 menit"
                   class="{{ $inputClass }} {{ $errors->has('nama') ? 'border-warn' : 'border-line' }}">
            @error('nama')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="durasi_menit" class="mb-1.5 block text-[13px] font-semibold">Durasi (menit)</label>
            <input id="durasi_menit" name="durasi_menit" type="number" inputmode="numeric" min="1" max="600" step="1"
                   value="{{ old('durasi_menit') }}" required
                   class="{{ $inputClass }} {{ $errors->has('durasi_menit') ? 'border-warn' : 'border-line' }}">
            @error('durasi_menit')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror

            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="text-xs text-muted">Pilih cepat:</span>
                @foreach ($presets as $m)
                    <button type="button" data-preset="{{ $m }}"
                            class="inline-flex min-h-[32px] items-center rounded-lg border border-line bg-white px-2.5 text-xs font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                        {{ $m }} mnt
                    </button>
                @endforeach
            </div>
            <p id="durasiPreview" class="mt-2 text-xs font-semibold text-accent-dark" hidden></p>
        </div>

        <p class="m-0 rounded-[10px] bg-accent-soft px-3 py-2 text-xs text-accent-dark">
            Timer ini otomatis tersedia di semua kursi.
        </p>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" id="submitBtn"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                Buat Timer
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
    const modal = document.getElementById('timerModal');
    const form  = document.getElementById('timerForm');
    const f = {
        nama:    document.getElementById('nama'),
        durasi:  document.getElementById('durasi_menit'),
        preview: document.getElementById('durasiPreview'),
    };
    const methodField = document.getElementById('methodField');
    const modeField   = document.getElementById('modeField');
    const editIdField = document.getElementById('editIdField');
    const title       = document.getElementById('modalTitle');
    const submitBtn   = document.getElementById('submitBtn');

    const storeUrl     = @json(route('admin.timer.store'));
    const updateUrlTpl = @json(route('admin.timer.update', ['timer' => '__ID__']));

    // Atur tampilan pop-up: mode buat atau edit
    function setMode(mode, id) {
        const edit = mode === 'edit';
        const cleanId = String(id || '').replace(/\D/g, '');

        modeField.value   = edit ? 'edit' : 'create';
        editIdField.value = edit ? cleanId : '';
        methodField.disabled = !edit;
        form.action = edit ? updateUrlTpl.replace('__ID__', cleanId) : storeUrl;

        title.textContent     = edit ? 'Edit Timer' : 'Buat Timer';
        submitBtn.textContent = edit ? 'Simpan Perubahan' : 'Buat Timer';
    }

    // Tampilkan "= 1 jam 30 menit" di bawah input durasi
    function updatePreview() {
        const m = parseInt(f.durasi.value, 10);
        if (!Number.isInteger(m) || m < 1) { f.preview.hidden = true; return; }
        const jam = Math.floor(m / 60), sisa = m % 60;
        const parts = [];
        if (jam)  parts.push(jam + ' jam');
        if (sisa) parts.push(sisa + ' menit');
        f.preview.textContent = '= ' + parts.join(' ');
        f.preview.hidden = false;
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
        f.durasi.value = '';
        updatePreview();
        modal.showModal();
        f.nama.focus();
    }

    function openEdit(btn) {
        clearErrors();
        setMode('edit', btn.dataset.id);
        f.nama.value = btn.dataset.nama;
        f.durasi.value = btn.dataset.durasi;
        updatePreview();
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
    document.querySelectorAll('[data-preset]').forEach(btn =>
        btn.addEventListener('click', () => { f.durasi.value = btn.dataset.preset; updatePreview(); })
    );
    f.durasi.addEventListener('input', updatePreview);

    // Klik di luar kotak pop-up (area gelap) menutupnya
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    @if ($errors->any() && old('_mode'))
        // Validasi gagal: buka lagi pop-up dengan isian & pesan error tadi
        setMode(@json(old('_mode')), @json((string) old('_edit_id')));
        updatePreview();
        modal.showModal();
    @elseif (request()->has('tambah'))
        openCreate();
    @endif
})();
</script>
</body>
</html>