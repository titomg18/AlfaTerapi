<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Kontrol Terapi - {{ $voucher->kode }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind (CDN). Kalau project sudah pakai Vite/build, hapus dua baris ini. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: { DEFAULT: '#0284C7', dark: '#0D2D56', soft: '#E0F2FE' },
                        kartu:  { DEFAULT: '#1D4E9E', soft: '#DCE9F8' },
                        surface: '#F4F7FB',
                        line:    '#D4E2EE',
                        ink:     '#0F1E36',
                        muted:   '#4A607A',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="m-0 bg-surface font-sans text-ink">

@php
    $sesis = $voucher->sesis->keyBy('nomor');
    $tgl   = fn ($d) => $d?->locale('id')->translatedFormat('j F Y');
@endphp

<div class="mx-auto max-w-[1100px] p-4 print:max-w-none print:p-0">

    {{-- Kontrol (tidak ikut tercetak) --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 print:hidden">
        <a href="{{ route($prefix . '.voucher.index') }}"
           class="inline-flex min-h-[44px] items-center rounded-[10px] border border-line bg-white px-4 text-sm font-semibold text-muted transition-colors hover:bg-white/60">
            &larr; Kembali ke voucher
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex min-h-[44px] items-center rounded-[10px] bg-accent px-[18px] text-sm font-semibold text-white transition-colors hover:bg-accent-dark">
            Cetak kartu
        </button>
    </div>

    <article class="overflow-hidden rounded-xl border border-kartu/30 bg-white shadow-sm print:rounded-none print:shadow-none">

        {{-- Judul --}}
        <header class="bg-kartu-soft px-6 pb-3 pt-5 text-center">
            <h1 class="m-0 text-[34px] font-extrabold leading-tight text-kartu max-[640px]:text-2xl">Kartu Kontrol Terapi</h1>
            <p class="m-0 text-sm font-bold tracking-wide text-kartu">TERAPI LISTRIK SEHAT ALPHA THERAPHY</p>
        </header>

        <div class="px-6 pb-4 pt-4">
            <h2 class="m-0 text-xl font-extrabold text-kartu max-[640px]:text-base">
                KARTU KEHADIRAN TERAPI &mdash; {{ $voucher->jumlah_sesi }} SESI
            </h2>

            {{-- Identitas --}}
            <div class="mt-3 grid grid-cols-2 gap-x-8 gap-y-2 text-sm max-[640px]:grid-cols-1">
                <div class="flex items-end gap-2">
                    <span class="whitespace-nowrap font-bold text-kartu">Nama Pasien :</span>
                    <span class="min-h-[22px] flex-1 border-b border-ink/60 px-1 font-semibold">{{ $voucher->pasien?->nama }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="whitespace-nowrap font-bold text-kartu">No. Registrasi :</span>
                    <span class="min-h-[22px] flex-1 border-b border-ink/60 px-1 font-mono font-semibold">{{ $voucher->kode }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="whitespace-nowrap font-bold text-kartu">Tanggal Beli :</span>
                    <span class="min-h-[22px] flex-1 border-b border-ink/60 px-1 font-semibold">{{ $tgl($voucher->created_at) }}</span>
                </div>
                <div class="flex items-end gap-2">
                    <span class="whitespace-nowrap font-bold text-kartu">Berlaku Sampai :</span>
                    <span class="min-h-[22px] flex-1 border-b border-ink/60 px-1 font-semibold">{{ $tgl($voucher->berlaku_sampai) }}</span>
                </div>
            </div>

            {{-- Kotak sesi --}}
            <div class="mt-4 grid grid-cols-6 gap-2 max-[640px]:grid-cols-3 print:grid-cols-6">
                @for ($i = 1; $i <= $voucher->jumlah_sesi; $i++)
                    @php $s = $sesis->get($i); @endphp
                    <div class="rounded-sm border border-kartu px-2 pb-1.5 pt-1" style="break-inside: avoid">
                        <div class="text-center text-xl font-extrabold leading-none text-kartu">{{ $i }}</div>
                        <div class="mt-1.5 flex items-end gap-1 text-[10px] leading-tight">
                            <span class="font-semibold">Tgl :</span>
                            <span class="min-h-[14px] flex-1 border-b border-ink/60 px-0.5 font-semibold">{{ $s?->dimulai_pada->format('d/m/Y') }}</span>
                        </div>
                        <div class="mt-1 flex items-end gap-1 text-[10px] leading-tight">
                            <span class="font-semibold">Ttd :</span>
                            <span class="min-h-[14px] flex-1 truncate border-b border-ink/60 px-0.5 font-semibold" title="{{ $s?->nama_pegawai }}">{{ $s?->nama_pegawai }}</span>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Catatan --}}
        <footer class="flex flex-wrap items-end justify-between gap-3 bg-kartu-soft px-6 py-3 text-[11px] text-ink">
            <ul class="m-0 list-disc pl-4">
                <li>Diparaf &amp; diisi tanggal oleh terapis setiap selesai sesi terapi</li>
                <li>Harap dibawa setiap kali terapi, kartu berlaku untuk {{ $voucher->jumlah_sesi }} sesi</li>
                <li>Kartu ini tidak dapat dipindahtangankan</li>
            </ul>
            <p class="m-0 font-bold">
                TERAPI LISTRIK SEHAT ALPHA THERAPHY &bull; Graha Family Blok TS No 06 Lt 6, Surabaya &bull; 085147425282
            </p>
        </footer>
    </article>

    <p class="mt-3 text-center text-xs text-muted print:hidden">
        Terpakai {{ $voucher->sesis->count() }} dari {{ $voucher->jumlah_sesi }} sesi.
        Kotak yang masih kosong bisa diisi tangan setelah dicetak.
    </p>
</div>
</body>
</html>