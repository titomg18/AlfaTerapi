<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengguna - Alpha Theraphy</title>

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

    // Role bawaan untuk akun baru
    $defaultRoleId = $roles->firstWhere('name', 'pegawai')?->id;

    // Class input bersama (js-field: border merah dibersihkan JS saat pop-up dibuka ulang)
    $inputClass = 'js-field min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20 disabled:cursor-not-allowed disabled:bg-surface disabled:text-muted';
@endphp

<div class="flex min-h-screen max-[820px]:flex-col">

    @include('admin.partials.sidebar')

    <main class="flex min-w-0 flex-1 flex-col gap-6 px-8 py-7 max-[820px]:px-4 max-[820px]:py-5">

        {{-- Header --}}
        <header class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="m-0 text-[26px] font-bold">Pengguna</h1>
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

{{-- Daftar pengguna --}}
        <section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
            <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="m-0 text-base font-bold">Daftar Pengguna</h2>
                    <span class="text-[13px] text-muted">{{ $users->count() }} akun</span>
                </div>
                <button type="button" id="openCreate"
                        class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                    + Tambah Pengguna
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] border-collapse text-[13.5px]">
                    <thead>
                        <tr>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Nama</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Username</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Role</th>
                            <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Ditambahkan</th>
                            <th class="border-b border-line p-2 text-right text-xs font-semibold text-muted">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="[&>tr:last-child>td]:border-b-0">
                        @forelse ($users as $u)
                            <tr>
                                <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">
                                    {{ $u->name }}
                                    @if ($u->id === $user->id)
                                        <span class="ml-1 text-xs font-medium text-muted">(Anda)</span>
                                    @endif
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">{{ $u->username }}</td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    @if ($u->role)
                                        <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold
                                                     {{ $u->role->name === 'admin'
                                                            ? 'bg-accent-soft text-accent-dark'
                                                            : 'bg-[#EDF2F7] text-muted' }}">
                                            {{ $u->role->label }}
                                        </span>
                                    @else
                                        <span class="text-xs text-warn">Belum ada role</span>
                                    @endif
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3 text-muted">
                                    {{ $u->created_at?->locale('id')->translatedFormat('j M Y') }}
                                </td>
                                <td class="border-b border-[#EDF2F7] px-2 py-3">
                                    <div class="flex justify-end gap-2">
                                        {{-- Edit: data dibaca JS untuk mengisi pop-up --}}
                                        <button type="button" data-edit
                                                data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}"
                                                data-username="{{ $u->username }}"
                                                data-role="{{ $u->role_id }}"
                                                data-self="{{ $u->id === $user->id ? '1' : '0' }}"
                                                class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                                            Edit
                                        </button>

                                        @if ($u->id !== $user->id)
                                            <form method="POST" action="{{ route('admin.pegawai.destroy', $u) }}"
                                                  data-confirm="Hapus pengguna {{ $u->name }}? Tindakan ini tidak bisa dibatalkan.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-warn transition-colors hover:bg-[#FBEFE7]">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-2 py-6 text-center text-muted">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @include('admin.partials.footer')
    </main>
</div>

{{-- Pop-up tambah / edit pengguna --}}
<dialog id="userModal" aria-labelledby="modalTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="userForm" method="POST" action="{{ route('admin.pegawai.store') }}" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_method" value="PUT" id="methodField" disabled>
        <input type="hidden" name="_mode" value="create" id="modeField">
        <input type="hidden" name="_edit_id" value="" id="editIdField">

        <div class="flex items-center justify-between">
            <h2 id="modalTitle" class="m-0 text-base font-bold">Tambah Pengguna</h2>
            <button type="button" id="closeModal" aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div>
            <label for="name" class="mb-1.5 block text-[13px] font-semibold">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="off"
                   class="{{ $inputClass }} {{ $errors->has('name') ? 'border-warn' : 'border-line' }}">
            @error('name')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="username" class="mb-1.5 block text-[13px] font-semibold">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="off"
                   class="{{ $inputClass }} {{ $errors->has('username') ? 'border-warn' : 'border-line' }}">
            @error('username')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role_id" class="mb-1.5 block text-[13px] font-semibold">Role</label>
            <select id="role_id" name="role_id"
                    class="{{ $inputClass }} {{ $errors->has('role_id') ? 'border-warn' : 'border-line' }}">
                @foreach ($roles as $r)
                    <option value="{{ $r->id }}" @selected((string) old('role_id', $defaultRoleId) === (string) $r->id)>
                        {{ $r->label }}
                    </option>
                @endforeach
            </select>
            @error('role_id')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
            <p id="selfHint" class="mt-1 text-xs text-muted" hidden>Role akun Anda sendiri tidak bisa diubah.</p>
        </div>

        <div>
            <label for="password" id="passwordLabel" class="mb-1.5 block text-[13px] font-semibold">Kata sandi</label>
            <input id="password" name="password" type="password" required autocomplete="new-password"
                   class="{{ $inputClass }} {{ $errors->has('password') ? 'border-warn' : 'border-line' }}">
            @error('password')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
            <p id="passwordHint" class="mt-1 text-xs text-muted">Minimal 8 karakter.</p>
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-[13px] font-semibold">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                   class="{{ $inputClass }} border-line">
        </div>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" id="submitBtn"
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                Tambah Pengguna
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
    const modal   = document.getElementById('userModal');
    const form    = document.getElementById('userForm');
    const f = {
        name:     document.getElementById('name'),
        username: document.getElementById('username'),
        role:     document.getElementById('role_id'),
        password: document.getElementById('password'),
        confirm:  document.getElementById('password_confirmation'),
    };
    const methodField  = document.getElementById('methodField');
    const modeField    = document.getElementById('modeField');
    const editIdField  = document.getElementById('editIdField');
    const title        = document.getElementById('modalTitle');
    const submitBtn    = document.getElementById('submitBtn');
    const passwordLbl  = document.getElementById('passwordLabel');
    const passwordHint = document.getElementById('passwordHint');
    const selfHint     = document.getElementById('selfHint');

    const storeUrl     = @json(route('admin.pegawai.store'));
    const updateUrlTpl = @json(route('admin.pegawai.update', ['user' => '__ID__']));
    const defaultRole  = @json($defaultRoleId);
    const selfId       = @json((string) $user->id);
    const selfRole     = @json($user->role_id);

    // Atur tampilan pop-up: mode tambah atau edit
    function setMode(mode, id, isSelf) {
        const edit = mode === 'edit';
        const cleanId = String(id || '').replace(/\D/g, '');

        modeField.value   = edit ? 'edit' : 'create';
        editIdField.value = edit ? cleanId : '';
        methodField.disabled = !edit;
        form.action = edit ? updateUrlTpl.replace('__ID__', cleanId) : storeUrl;

        title.textContent     = edit ? 'Edit Pengguna' : 'Tambah Pengguna';
        submitBtn.textContent = edit ? 'Simpan Perubahan' : 'Tambah Pengguna';
        passwordLbl.textContent  = edit ? 'Kata sandi baru (opsional)' : 'Kata sandi';
        passwordHint.textContent = edit
            ? 'Kosongkan jika tidak ingin mengganti. Minimal 8 karakter.'
            : 'Minimal 8 karakter.';
        f.password.required = !edit;
        f.confirm.required  = !edit;

        f.role.disabled = !!isSelf;   // admin tidak boleh mengubah role akunnya sendiri
        selfHint.hidden = !isSelf;
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
        f.name.value = '';
        f.username.value = '';
        f.role.value = defaultRole;
        f.password.value = '';
        f.confirm.value = '';
        modal.showModal();
        f.name.focus();
    }

    function openEdit(btn) {
        clearErrors();
        const isSelf = btn.dataset.self === '1';
        setMode('edit', btn.dataset.id, isSelf);
        f.name.value = btn.dataset.name;
        f.username.value = btn.dataset.username;
        f.role.value = btn.dataset.role || defaultRole;
        f.password.value = '';
        f.confirm.value = '';
        modal.showModal();
        f.name.focus();
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
        (function () {
            const mode   = @json(old('_mode'));
            const editId = @json((string) old('_edit_id'));
            const isSelf = mode === 'edit' && editId === selfId;
            setMode(mode, editId, isSelf);
            if (isSelf) { f.role.value = selfRole; }
            modal.showModal();
        })();
    @endif
})();
</script>
</body>
</html>