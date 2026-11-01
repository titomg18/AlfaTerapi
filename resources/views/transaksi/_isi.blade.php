@php
    // Dipakai bersama oleh admin/transaksi dan pegawai/transaksi.
    // Variabel: $prefix ('admin'|'pegawai'), $transaksis, $totalSesi, $totalMenit, $totalPasien, $q, $dari, $sampai

    $fmtMenit = function (int $m) {
        $jam   = intdiv($m, 60);
        $menit = $m % 60;

        return collect([$jam ? "{$jam} jam" : null, $menit ? "{$menit} menit" : null])
            ->filter()->implode(' ') ?: '0 menit';
    };

    $hariIni = now()->toDateString();
    $presets = [
        'Hari ini'        => ['dari' => $hariIni, 'sampai' => $hariIni],
        '7 hari terakhir' => ['dari' => now()->subDays(6)->toDateString(), 'sampai' => $hariIni],
        'Bulan ini'       => ['dari' => now()->startOfMonth()->toDateString(), 'sampai' => $hariIni],
    ];

    $adaFilter = $q !== '' || $dari || $sampai;
    $dateInput = 'min-h-[44px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';
@endphp

{{-- Info --}}
<div class="flex items-start gap-3 rounded-xl border border-line bg-white px-4 py-3 text-sm text-muted">
    <svg class="mt-0.5 flex-shrink-0 text-accent" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v4h1"/>
    </svg>
    <p class="m-0">
        Sesi masuk ke sini <span class="font-semibold text-ink">otomatis setelah timer selesai</span>.
        Sesi yang masih berjalan belum ditampilkan. Tanggal yang dipakai adalah tanggal terapi dimulai.
    </p>
</div>

{{-- Ringkasan (sesuai filter yang aktif) --}}
<section class="grid grid-cols-3 gap-4 max-[640px]:grid-cols-1">
    <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
        <div class="text-[13px] text-muted">Total Sesi</div>
        <div class="mt-1.5 text-[30px] font-bold">{{ number_format($totalSesi, 0, ',', '.') }}</div>
        <div class="mt-0.5 text-xs text-muted">voucher terpakai</div>
    </div>
    <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
        <div class="text-[13px] text-muted">Total Waktu Terapi</div>
        <div class="mt-1.5 text-[30px] font-bold">{{ $fmtMenit($totalMenit) }}</div>
        <div class="mt-0.5 text-xs text-muted">jumlah semua durasi</div>
    </div>
    <div class="rounded-[14px] border border-line bg-white px-5 py-[18px]">
        <div class="text-[13px] text-muted">Pasien</div>
        <div class="mt-1.5 text-[30px] font-bold">{{ number_format($totalPasien, 0, ',', '.') }}</div>
        <div class="mt-0.5 text-xs text-muted">pasien berbeda</div>
    </div>
</section>

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
    <form method="GET" action="{{ route($prefix . '.transaksi.index') }}" class="mb-3 flex flex-wrap items-end gap-3">
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
                <a href="{{ route($prefix . '.transaksi.index') }}"
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
            <a href="{{ route($prefix . '.transaksi.index', array_filter($range + ['q' => $q])) }}"
               class="inline-flex min-h-[32px] items-center rounded-lg border px-2.5 text-xs font-semibold transition-colors
                      {{ $aktif ? 'border-accent bg-accent-soft text-accent-dark' : 'border-line bg-white text-accent-dark hover:bg-accent-soft' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse text-[13.5px]">
            <thead>
                <tr>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Tanggal</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Pasien</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Timer</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kursi</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Waktu</th>
                    <th class="border-b border-line p-2 text-left text-xs font-semibold text-muted">Kode Voucher</th>
                </tr>
            </thead>
            <tbody class="[&>tr:last-child>td]:border-b-0">
                @forelse ($transaksis as $v)
                    <tr>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-semibold">
                            {{ $v->dipakai_pada->locale('id')->translatedFormat('j M Y') }}
                        </td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3 font-semibold">{{ $v->pasien?->nama ?? '-' }}</td>
                        <td class="border-b border-[#EEF3F2] px-2 py-3">
                            {{ $v->nama_timer }}
                            <div class="text-xs text-muted">{{ $v->durasi_label }}</div>
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3">
                            {{ $v->no_kursi ? 'Kursi ' . $v->no_kursi : '-' }}
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 tabular-nums text-muted">
                            {{ $v->dipakai_pada->format('H.i') }} - {{ $v->selesai_pada->format('H.i') }}
                            @unless ($v->selesai_pada->isSameDay($v->dipakai_pada))
                                <span class="text-xs">(+1 hari)</span>
                            @endunless
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EEF3F2] px-2 py-3 font-mono text-[13px]">{{ $v->kode }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-2 py-6 text-center text-muted">
                            @if ($adaFilter)
                                Tidak ada sesi selesai yang cocok dengan filter.
                            @else
                                Belum ada sesi yang selesai. Sesi akan muncul di sini setelah timer voucher habis.
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