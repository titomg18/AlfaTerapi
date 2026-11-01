@php
    // Dipakai bersama oleh admin/transaksi dan pegawai/transaksi.
    // Variabel: $prefix ('admin'|'pegawai'), $transaksis, $totalSesi, $totalMenit, $totalPasien, $q, $dari, $sampai

    $hariIni = now()->toDateString();
    $presets = [
        'Hari ini'        => ['dari' => $hariIni, 'sampai' => $hariIni],
        '7 hari terakhir' => ['dari' => now()->subDays(6)->toDateString(), 'sampai' => $hariIni],
        'Bulan ini'       => ['dari' => now()->startOfMonth()->toDateString(), 'sampai' => $hariIni],
    ];

    $adaFilter = $q !== '' || $dari || $sampai;
    $dateInput = 'min-h-[44px] rounded-[10px] border border-line bg-white px-3 text-sm outline-none transition-colors focus:border-accent focus:ring-2 focus:ring-accent/20';
@endphp

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
                    @php
                        $mulai = $v->dimulai_pada ?? $v->dipakai_pada;
                        $selesai = $v->selesai_pada;
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 font-semibold">
                            {{ $mulai?->locale('id')->translatedFormat('j M Y') ?? '-' }}
                        </td>
                        <td class="border-b border-[#EDF2F7] px-2 py-3 font-semibold">{{ $v->pasien?->nama ?? '-' }}</td>
                        <td class="border-b border-[#EDF2F7] px-2 py-3">
                            {{ $v->nama_timer ?? $v->voucher?->nama_timer ?? 'Terapi' }}
                            <div class="text-xs text-muted">{{ $v->durasi_label ?? ($v->durasi_menit . ' menit') }} · sesi {{ $v->nomor }}</div>
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3">
                            {{ $v->no_kursi ? 'Kursi ' . $v->no_kursi : '-' }}
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 tabular-nums text-muted">
                            @if ($mulai && $selesai)
                                {{ $mulai->format('H.i') }} - {{ $selesai->format('H.i') }}
                                @unless ($selesai->isSameDay($mulai))
                                    <span class="text-xs">(+1 hari)</span>
                                @endunless
                            @else
                                -
                            @endif
                        </td>
                        <td class="whitespace-nowrap border-b border-[#EDF2F7] px-2 py-3 font-mono text-[13px]">{{ $v->kode ?? $v->voucher?->kode ?? '-' }}</td>
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