<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Pasien Baru - Alpha Theraphy</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: {
                            DEFAULT: '#0284C7',
                            dark: '#0D2D56',
                            soft: '#E0F2FE',
                        },
                        page: '#F4F7FB',
                        line: '#D4E2EE',
                        ink: '#0F1E36',
                        muted: '#4A607A',
                        warn: {
                            DEFAULT: '#B4531A',
                            soft: '#FEF3F2',
                        },
                        success: {
                            DEFAULT: '#059669',
                            soft: '#ECFDF5',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-screen flex flex-col justify-between p-4 sm:p-6 font-sans bg-page text-ink antialiased">
@include('partials.toast')

<main class="w-full max-w-[520px] mx-auto my-auto py-6">

    <div class="bg-white border border-line rounded-2xl p-6 sm:p-8 shadow-sm">
        {{-- Header Logo --}}
        <div class="flex flex-col items-center justify-center gap-2 mb-6 text-center">
            <img src="{{ asset('images/logo.jpg') }}" alt="Alpha Theraphy"
                 class="h-16 w-16 rounded-2xl object-contain border border-line bg-white shadow-sm p-1">
            <div>
                <h1 class="font-bold text-2xl text-accent-dark leading-tight">Alpha Theraphy</h1>
                <p class="text-xs font-semibold tracking-wider text-muted">アルファ・セラピー</p>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 mt-1 rounded-full bg-accent-soft text-accent-dark text-xs font-semibold">
                <span class="inline-block w-2 h-2 rounded-full bg-accent animate-pulse"></span>
                Pendaftaran Pasien Mandiri
            </div>
        </div>

        {{-- Success State Banner --}}
        @if (session('registered_pasien'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-950">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-emerald-900">Pendaftaran Berhasil Dikirim!</h2>
                        <p class="text-xs text-emerald-700">Data Anda sudah tercatat dalam sistem kami.</p>
                    </div>
                </div>

                <div class="text-sm space-y-1.5 mt-3 pt-3 border-t border-emerald-200/80">
                    <div><span class="text-xs text-emerald-700">Nama Pasien:</span> <strong class="text-emerald-900">{{ session('registered_pasien')['nama'] }}</strong></div>
                    <div><span class="text-xs text-emerald-700">No. WhatsApp / Telepon:</span> <span class="font-mono text-emerald-900 font-semibold">{{ session('registered_pasien')['no_telepon'] }}</span></div>
                    <div class="mt-2 text-xs text-emerald-800 bg-white/60 p-2.5 rounded-lg border border-emerald-200">
                        ⏳ <strong>Status: Menunggu Persetujuan (ACC).</strong> Mohon informasikan kepada staf atau terapis saat Anda tiba di klinik untuk verifikasi dan aktivasi voucher terapi.
                    </div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('guest.dashboard') }}"
                       class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-[10px] bg-accent px-4 text-xs font-bold text-white shadow-sm transition hover:bg-accent-dark">
                        Pantau Monitor Kursi Terapi
                    </a>
                    <a href="{{ route('guest.register') }}"
                       class="inline-flex min-h-[42px] items-center justify-center rounded-[10px] border border-emerald-300 bg-white px-4 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100/50">
                        Daftar Pasien Lain
                    </a>
                </div>
            </div>
        @endif

        @if (!session('registered_pasien'))
            <div class="mb-5 text-center sm:text-left">
                <h2 class="text-lg font-bold text-ink">Formulir Pendaftaran Pasien</h2>
                <p class="text-xs text-muted mt-0.5">Silakan isi data lengkap Anda di bawah ini dengan benar.</p>
            </div>

            <form method="POST" action="{{ route('guest.register.store') }}" novalidate>
                @csrf

                {{-- Nama Pasien --}}
                <div class="mb-4">
                    <label for="nama" class="block text-[13px] font-semibold mb-1.5 text-ink">
                        Nama Lengkap Pasien <span class="text-warn">*</span>
                    </label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="{{ old('nama') }}"
                        placeholder="Contoh: Adi Putra Santoso"
                        autocomplete="name"
                        required
                        autofocus
                        class="w-full min-h-[44px] px-3.5 text-sm text-ink bg-white border rounded-[10px] outline-none transition
                               focus:border-accent focus:ring-[3px] focus:ring-accent/15
                               {{ $errors->has('nama') ? 'border-warn' : 'border-line' }}">
                    @error('nama')
                        <div class="mt-1.5 text-xs text-warn font-medium">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nomor Telepon --}}
                <div class="mb-4">
                    <label for="no_telepon" class="block text-[13px] font-semibold mb-1.5 text-ink">
                        Nomor WhatsApp / Telepon <span class="text-warn">*</span>
                    </label>
                    <input
                        type="tel"
                        id="no_telepon"
                        name="no_telepon"
                        inputmode="tel"
                        value="{{ old('no_telepon') }}"
                        placeholder="Contoh: 081234567890"
                        autocomplete="tel"
                        required
                        class="w-full min-h-[44px] px-3.5 text-sm text-ink bg-white border rounded-[10px] outline-none transition
                               focus:border-accent focus:ring-[3px] focus:ring-accent/15
                               {{ $errors->has('no_telepon') ? 'border-warn' : 'border-line' }}">
                    @error('no_telepon')
                        <div class="mt-1.5 text-xs text-warn font-medium">{{ $message }}</div>
                    @enderror
                    <p class="mt-1 text-[11px] text-muted">Nomor ini digunakan untuk konfirmasi sesi terapi Anda.</p>
                </div>

                {{-- Alamat --}}
                <div class="mb-5">
                    <label for="alamat" class="block text-[13px] font-semibold mb-1.5 text-ink">
                        Alamat Tempat Tinggal <span class="text-warn">*</span>
                    </label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Contoh: Jl. Diponegoro No. 45, RT 02 / RW 03, Surabaya"
                        required
                        class="w-full px-3.5 py-2.5 text-sm text-ink bg-white border rounded-[10px] outline-none transition resize-y
                               focus:border-accent focus:ring-[3px] focus:ring-accent/15
                               {{ $errors->has('alamat') ? 'border-warn' : 'border-line' }}">{{ old('alamat') }}</textarea>
                    @error('alamat')
                        <div class="mt-1.5 text-xs text-warn font-medium">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-5 p-3 rounded-xl bg-accent-soft/60 border border-accent/20 text-[12px] text-accent-dark flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-accent shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <span>Setelah menekan tombol di bawah, data pendaftaran Anda akan masuk ke antrean verifikasi staf kami dan dapat disetujui (ACC) saat Anda datang ke klinik.</span>
                </div>

                <button
                    type="submit"
                    class="w-full min-h-[46px] px-5 text-sm font-bold text-white bg-accent rounded-[10px] transition shadow-sm
                           hover:bg-accent-dark active:scale-[0.99] flex items-center justify-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <line x1="19" y1="8" x2="19" y2="14"/>
                        <line x1="22" y1="11" x2="16" y2="11"/>
                    </svg>
                    Kirim Pendaftaran Pasien
                </button>
            </form>
        @endif

        {{-- Navigasi Bawah --}}
        <div class="mt-6 pt-5 border-t border-line text-center text-xs text-muted">
            <a href="{{ route('guest.dashboard') }}" class="inline-flex items-center gap-1.5 font-semibold text-accent hover:text-accent-dark transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Kembali ke Monitor Kursi Terapi
            </a>
        </div>
    </div>

    <footer class="text-center text-xs text-muted mt-6">
        &copy; {{ date('Y') }} Alpha Theraphy. Hak cipta dilindungi.
    </footer>
</main>

</body>
</html>
