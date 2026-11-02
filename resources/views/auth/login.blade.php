<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Alpha Theraphy</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind via CDN (untuk development). Di production, ganti dengan @vite(['resources/css/app.css', 'resources/js/app.js'])
         dan pindahkan konfigurasi di bawah ke tailwind.config.js --}}
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
                        danger: {
                            DEFAULT: '#B42318',
                            soft: '#FEF3F2',
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
<body class="min-h-screen flex items-center justify-center p-6 font-sans bg-page text-ink">
@include('partials.toast')
<div class="w-full max-w-[420px]">

    <div class="bg-white border border-line rounded-2xl px-7 py-8 shadow-sm">
        {{-- Logo di dalam card --}}
        <div class="flex flex-col items-center justify-center gap-2 mb-6 text-center">
            <img src="{{ asset('images/logo.jpg') }}" alt="Alpha Theraphy" class="h-16 w-16 rounded-2xl object-contain border border-line bg-white shadow-sm p-1">
            <div>
                <div class="font-bold text-2xl text-accent-dark leading-tight">Alpha Theraphy</div>
                <div class="text-xs font-semibold tracking-wider text-muted">アルファ・セラピー</div>
            </div>
        </div>

        @if (session('status'))
            <div class="bg-accent-soft border border-[#BAE6FD] text-accent-dark rounded-[10px] px-3.5 py-3 text-[13.5px] mb-[18px]">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="flex gap-2.5 bg-danger-soft border border-[#FDA29B] text-danger rounded-[10px] px-3.5 py-3 text-[13.5px] mb-[18px]" role="alert">
                <div>
                    @if ($errors->count() === 1)
                        {{ $errors->first() }}
                    @else
                        <ul class="m-0 pl-4 list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ Route::has('login') ? route('login') : url('/login') }}" id="login-form" novalidate>
            @csrf

            <div class="mb-[18px]">
                <label for="username" class="block text-[13px] font-semibold mb-1.5">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    autocapitalize="none"
                    spellcheck="false"
                    required
                    autofocus
                    class="w-full min-h-[46px] px-3.5 text-sm text-ink bg-white border rounded-[10px] outline-none transition
                           focus:border-accent focus:ring-[3px] focus:ring-accent/15
                           {{ $errors->has('username') ? 'border-danger' : 'border-line' }}">
                @error('username')
                    <div class="mt-1.5 text-[12.5px] text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-[18px]">
                <label for="password" class="block text-[13px] font-semibold mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan kata sandi"
                        autocomplete="current-password"
                        required
                        class="w-full min-h-[46px] pl-3.5 pr-[70px] text-sm text-ink bg-white border rounded-[10px] outline-none transition
                               focus:border-accent focus:ring-[3px] focus:ring-accent/15
                               {{ $errors->has('password') ? 'border-danger' : 'border-line' }}">
                    <button type="button" id="toggle-password" aria-label="Tampilkan kata sandi"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 bg-transparent text-accent text-[12.5px] font-semibold px-2.5 py-2 rounded-lg cursor-pointer hover:bg-accent-soft">
                        Lihat
                    </button>
                </div>
                @error('password')
                    <div class="mt-1.5 text-[12.5px] text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between mb-[22px] text-[13.5px]">
                <label class="flex items-center gap-2 font-medium cursor-pointer">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                           class="w-4 h-4 accent-accent">
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="font-semibold text-accent no-underline hover:text-accent-dark hover:underline">Lupa kata sandi?</a>
                @endif
            </div>

            <button type="submit" id="submit-btn"
                    class="w-full min-h-[46px] rounded-[10px] bg-accent text-white text-sm font-semibold cursor-pointer transition-colors
                           hover:bg-accent-dark disabled:opacity-70 disabled:cursor-not-allowed">
                Masuk
            </button>
        </form>
    </div>

    <div class="text-center mt-5 text-[12.5px] text-muted">&copy; {{ date('Y') }} Alpha Theraphy. Hak cipta dilindungi.</div>
</div>

<script>
    (function () {
        var pwd = document.getElementById('password');
        var toggle = document.getElementById('toggle-password');
        toggle.addEventListener('click', function () {
            var show = pwd.type === 'password';
            pwd.type = show ? 'text' : 'password';
            toggle.textContent = show ? 'Sembunyi' : 'Lihat';
            toggle.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });

        document.getElementById('login-form').addEventListener('submit', function () {
            var btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.textContent = 'Memproses...';
        });
    })();
</script>
</body>
</html>
