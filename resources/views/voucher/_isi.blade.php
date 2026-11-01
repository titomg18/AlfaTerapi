@php
    // Dipakai bersama oleh admin/voucher dan pegawai/voucher.
    // Variabel: $prefix ('admin'|'pegawai'), $canDelete (bool), $vouchers, $kursis, $timers, $pasiens, $q, $status

    $inputClass = 'js-field min-h-[44px] w-full rounded-[10px] border bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';

    $kursiKosong = $kursis->filter(fn ($k) => ! $k->sesiAktif)->values();

    $pasienJson = $pasiens
        ->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'telp' => $p->no_telepon])
        ->values();

    $statusLabel = ['tersedia' => 'Tersedia', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'];
    $statusBadge = [
        'tersedia' => 'bg-accent-soft text-accent-dark',
        'berjalan' => 'bg-[#FFF3D6] text-[#8A5A00]',
        'selesai'  => 'bg-[#EEF3F2] text-muted',
    ];
@endphp

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
@error('kursi_id')
    <div role="alert" class="rounded-xl border border-warn/30 bg-[#FBEFE7] px-4 py-3 text-sm font-semibold text-warn">
        {{ $message }}
    </div>
@enderror

{{-- Status kursi --}}
<section class="rounded-[14px] border border-line bg-white p-5">
    <div class="mb-3.5 flex items-center justify-between">
        <h2 class="m-0 text-base font-bold">Status Kursi</h2>
        <span class="text-[13px] text-muted">{{ $kursiKosong->count() }} kosong dari {{ $kursis->count() }} kursi</span>
    </div>

    @if ($kursis->isEmpty())
        <p class="m-0 py-4 text-center text-muted">
            Belum ada kursi.
            {{ $canDelete ? 'Tambahkan lewat menu Kursi.' : 'Minta admin menambahkan kursi.' }}
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
                        <div class="truncate text-xs text-accent-dark" title="{{ $s->pasien?->nama }}">{{ $s->pasien?->nama }}</div>
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

{{-- Daftar voucher --}}
<section class="min-w-0 rounded-[14px] border border-line bg-white p-5">
    <div class="mb-3.5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="m-0 text-base font-bold">Daftar Voucher</h2>
            <span class="text-[13px] text-muted">{{ $vouchers->total() }} voucher</span>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route($prefix . '.voucher.index') }}" class="flex flex-wrap gap-2">
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari kode / nama pasien"
                       aria-label="Cari voucher"
                       class="min-h-[44px] w-[210px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20 max-[520px]:w-full">
                <select name="status" aria-label="Filter status"
                        class="min-h-[44px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20">
                    <option value="">Semua status</option>
                    @foreach ($statusLabel as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="inline-flex min-h-[44px] items-center rounded-[10px] border border-line bg-white px-4 text-sm font-semibold text-accent-dark transition-colors hover:bg-accent-soft">
                    Cari
                </button>
            </form>

            <button type="button" id="openBeli"
                    class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
                + Beli Voucher
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[13.5px]">
            <thead>
                <tr>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kode</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Pasien</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Timer</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Status</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Dibeli</th>
                    <th class="border-b border-line p-2 text-right text-xs font-semibold text-muted">Aksi</th>
                </tr>
            </thead>
            <tbody class="[&>tr:last-child>td]:border-b-0">
                @forelse ($vouchers as $v)
                    @php $st = $v->status; @endphp
                    <tr>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-mono font-semibold">{{ $v->kode }}</td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3 font-semibold">{{ $v->pasien?->nama ?? '-' }}</td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3">
                            {{ $v->nama_timer }}
                            <div class="text-xs text-muted">{{ $v->durasi_label }}</div>
                        </td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3">
                            <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusBadge[$st] }}">
                                {{ $statusLabel[$st] }}
                            </span>
                            @if ($st === 'berjalan')
                                <div class="mt-1 text-xs text-muted">
                                    Kursi {{ $v->kursi?->no_kursi ?? '-' }} · sisa
                                    <span class="font-semibold tabular-nums text-ink" data-end="{{ $v->selesai_pada->toIso8601String() }}"></span>
                                </div>
                            @elseif ($st === 'selesai')
                                <div class="mt-1 text-xs text-muted">
                                    Kursi {{ $v->kursi?->no_kursi ?? 'dihapus' }} · selesai {{ $v->selesai_pada->locale('id')->translatedFormat('H.i') }}
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 text-muted">
                            {{ $v->created_at?->locale('id')->translatedFormat('j M Y, H.i') }}
                        </td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3">
                            <div class="flex justify-end gap-2">
                                @if ($st === 'tersedia')
                                    <button type="button" data-pakai
                                            data-id="{{ $v->id }}"
                                            data-kode="{{ $v->kode }}"
                                            data-pasien="{{ $v->pasien?->nama }}"
                                            data-timer="{{ $v->nama_timer }} ({{ $v->durasi_label }})"
                                            class="inline-flex min-h-[36px] items-center rounded-lg bg-accent px-3 text-[13px] font-semibold text-white transition-colors hover:bg-accent-dark">
                                        Pakai
                                    </button>

                                    @if ($canDelete)
                                        <form method="POST" action="{{ route('admin.voucher.destroy', $v) }}"
                                              data-confirm="Hapus voucher {{ $v->kode }}? Tindakan ini tidak bisa dibatalkan."
                                              onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex min-h-[36px] items-center rounded-lg border border-line bg-white px-3 text-[13px] font-semibold text-warn transition-colors hover:bg-[#FBEFE7]">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-2 py-6 text-center text-muted">
                            @if ($q !== '' || $status !== '')
                                Tidak ada voucher yang cocok dengan pencarian.
                            @else
                                Belum ada voucher. Klik "Beli Voucher" untuk membuat.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($vouchers->hasPages())
        <div class="mt-4">{{ $vouchers->links() }}</div>
    @endif
</section>

{{-- Pop-up beli voucher --}}
<dialog id="beliModal" aria-labelledby="beliTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="beliForm" method="POST" action="{{ route($prefix . '.voucher.store') }}" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_form" value="beli">

        <div class="flex items-center justify-between">
            <h2 id="beliTitle" class="m-0 text-base font-bold">Beli Voucher</h2>
            <button type="button" data-close aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div>
            <label for="cariPasien" class="mb-1.5 block text-[13px] font-semibold">Pasien</label>
            <input id="cariPasien" type="search" placeholder="Cari nama / no. telepon" autocomplete="off"
                   class="mb-2 min-h-[44px] w-full rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20">
            <select id="pasien_id" name="pasien_id" size="5" aria-label="Pilih pasien"
                    class="{{ $inputClass }} {{ $errors->has('pasien_id') ? 'border-warn' : 'border-line' }}"></select>
            <p id="pasienHint" class="mt-1 text-xs text-muted" hidden></p>
            @error('pasien_id')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-muted">
                Pasien belum terdaftar?
                <a href="{{ route($prefix . '.pasien.index', ['tambah' => 1]) }}" class="font-semibold text-accent hover:text-accent-dark">Daftarkan pasien baru</a>
            </p>
        </div>

        <div>
            <label for="timer_id" class="mb-1.5 block text-[13px] font-semibold">Timer (durasi terapi)</label>
            @if ($timers->isEmpty())
                <p class="m-0 rounded-[10px] bg-[#FBEFE7] px-3 py-2 text-xs text-warn">
                    Belum ada timer. Minta admin membuat timer terlebih dahulu.
                </p>
            @else
                <select id="timer_id" name="timer_id"
                        class="{{ $inputClass }} {{ $errors->has('timer_id') ? 'border-warn' : 'border-line' }}">
                    @foreach ($timers as $t)
                        <option value="{{ $t->id }}" @selected((string) old('timer_id') === (string) $t->id)>
                            {{ $t->nama }} - {{ $t->durasi_label }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('timer_id')
                <p class="js-error mt-1 text-xs text-warn">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" @disabled($timers->isEmpty())
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark disabled:cursor-not-allowed disabled:opacity-50">
                Beli Voucher
            </button>
            <button type="button" data-close
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] border border-line bg-white px-[18px] text-sm font-semibold text-muted transition-colors hover:bg-surface">
                Batal
            </button>
        </div>
    </form>
</dialog>

{{-- Pop-up pakai voucher di kursi kosong --}}
<dialog id="pakaiModal" aria-labelledby="pakaiTitle"
        class="m-auto max-h-[90vh] w-[min(92vw,460px)] overflow-y-auto rounded-[14px] border border-line bg-white p-0 text-ink shadow-2xl backdrop:bg-ink/40">

    <form id="pakaiForm" method="POST" action="#" class="flex flex-col gap-4 p-5" novalidate>
        @csrf
        <input type="hidden" name="_form" value="pakai">

        <div class="flex items-center justify-between">
            <h2 id="pakaiTitle" class="m-0 text-base font-bold">Pakai Voucher</h2>
            <button type="button" data-close aria-label="Tutup"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-muted transition-colors hover:bg-surface">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="rounded-[10px] bg-surface px-3 py-2.5 text-sm">
            <div id="pvKode" class="font-mono font-semibold"></div>
            <div id="pvPasien" class="font-semibold"></div>
            <div id="pvTimer" class="text-muted"></div>
        </div>

        <fieldset class="m-0 border-0 p-0">
            <legend class="mb-1.5 p-0 text-[13px] font-semibold">Pilih kursi kosong</legend>
            @if ($kursiKosong->isEmpty())
                <p class="m-0 rounded-[10px] bg-[#FBEFE7] px-3 py-2 text-xs text-warn">
                    Semua kursi sedang dipakai. Tunggu sampai ada kursi yang kosong.
                </p>
            @else
                <div class="grid grid-cols-3 gap-2 max-[420px]:grid-cols-2">
                    @foreach ($kursiKosong as $k)
                        <label class="block cursor-pointer">
                            <input type="radio" name="kursi_id" value="{{ $k->id }}" class="peer sr-only" @checked($loop->first)>
                            <span class="flex min-h-[44px] items-center justify-center rounded-[10px] border border-line bg-white px-2 text-sm font-semibold transition-colors
                                         peer-checked:border-accent peer-checked:bg-accent-soft peer-checked:text-accent-dark
                                         peer-focus-visible:ring-2 peer-focus-visible:ring-accent/40">
                                Kursi {{ $k->no_kursi }}
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </fieldset>

        <p class="m-0 text-xs text-muted">Hitung mundur dimulai saat tombol "Mulai Terapi" ditekan.</p>

        <div class="flex flex-col gap-2 sm:flex-row-reverse">
            <button type="submit" @disabled($kursiKosong->isEmpty())
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark disabled:cursor-not-allowed disabled:opacity-50">
                Mulai Terapi
            </button>
            <button type="button" data-close
                    class="inline-flex min-h-[44px] flex-1 items-center justify-center rounded-[10px] border border-line bg-white px-[18px] text-sm font-semibold text-muted transition-colors hover:bg-surface">
                Batal
            </button>
        </div>
    </form>
</dialog>

<script>
(function () {
    // ===== Hitung mundur =====
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
                // Muat ulang sekali saat waktu habis supaya status kursi diperbarui
                // (hanya jika hitung mundur sempat berjalan di halaman ini).
                if (c.alive && !reloadScheduled) {
                    reloadScheduled = true;
                    setTimeout(() => {
                        if (!document.querySelector('dialog[open]')) location.reload();
                    }, 2000);
                }
            }
        });
    }
    if (counters.length) { tick(); setInterval(tick, 1000); }

    // ===== Pop-up beli voucher =====
    const beli = {
        modal: document.getElementById('beliModal'),
        form:  document.getElementById('beliForm'),
        cari:  document.getElementById('cariPasien'),
        sel:   document.getElementById('pasien_id'),
        hint:  document.getElementById('pasienHint'),
    };
    const semuaPasien = @json($pasienJson);
    const BATAS = 100;

    function renderPasien(filter, terpilih) {
        const q = (filter || '').trim().toLowerCase();
        const cocok = semuaPasien.filter(p =>
            !q || p.nama.toLowerCase().includes(q) || String(p.telp || '').toLowerCase().includes(q)
        );

        beli.sel.innerHTML = '';
        cocok.slice(0, BATAS).forEach(p => {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.nama + ' - ' + (p.telp || '');
            if (terpilih !== null && terpilih !== undefined && String(p.id) === String(terpilih)) o.selected = true;
            beli.sel.appendChild(o);
        });

        if (!cocok.length) {
            const o = document.createElement('option');
            o.disabled = true;
            o.textContent = q ? 'Pasien tidak ditemukan' : 'Belum ada pasien terdaftar';
            beli.sel.appendChild(o);
        }

        beli.hint.hidden = cocok.length <= BATAS;
        beli.hint.textContent = 'Menampilkan ' + BATAS + ' dari ' + cocok.length + ' pasien. Ketik untuk mempersempit.';
    }

    function clearErrors() {
        beli.form.querySelectorAll('.js-error').forEach(el => el.remove());
        beli.form.querySelectorAll('.js-field').forEach(el => {
            el.classList.remove('border-warn');
            el.classList.add('border-line');
        });
    }

    function openBeli() {
        clearErrors();
        beli.cari.value = '';
        renderPasien('', null);
        beli.modal.showModal();
        beli.cari.focus();
    }

    document.getElementById('openBeli').addEventListener('click', openBeli);
    beli.cari.addEventListener('input', () => renderPasien(beli.cari.value, beli.sel.value));

    // ===== Pop-up pakai voucher =====
    const pakai = {
        modal: document.getElementById('pakaiModal'),
        form:  document.getElementById('pakaiForm'),
        kode:  document.getElementById('pvKode'),
        pasien: document.getElementById('pvPasien'),
        timer: document.getElementById('pvTimer'),
    };
    const pakaiUrlTpl = @json(route($prefix . '.voucher.pakai', ['voucher' => '__ID__']));

    document.querySelectorAll('[data-pakai]').forEach(btn => btn.addEventListener('click', () => {
        const id = String(btn.dataset.id).replace(/\D/g, '');
        pakai.form.action = pakaiUrlTpl.replace('__ID__', id);
        pakai.kode.textContent = btn.dataset.kode;
        pakai.pasien.textContent = btn.dataset.pasien;
        pakai.timer.textContent = btn.dataset.timer;
        pakai.modal.showModal();
    }));

    // ===== Tutup pop-up: tombol X / Batal, klik area gelap, atau Esc =====
    document.querySelectorAll('[data-close]').forEach(b =>
        b.addEventListener('click', () => b.closest('dialog').close())
    );
    [beli.modal, pakai.modal].forEach(m =>
        m.addEventListener('click', e => { if (e.target === m) m.close(); })
    );

    @if ($errors->any() && old('_form') === 'beli')
        // Validasi gagal: buka lagi pop-up dengan isian & pesan error tadi
        beli.cari.value = '';
        renderPasien('', @json(old('pasien_id')));
        beli.modal.showModal();
    @elseif (request()->has('beli'))
        openBeli();
    @endif
})();
</script>