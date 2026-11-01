@php
    // Menu sidebar: [label, url, pola route aktif, path svg]
    $menu = [
        ['Dashboard', route('admin.dashboard'),       '*admin/dashboard*', '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>'],
        ['Pasien',    route('admin.pasien.index'),    '*admin/pasien*',    '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>'],
        ['Kursi',     route('admin.kursi.index'),     '*admin/kursi*',     '<path d="M6 3v10h12v8M6 13v8M18 13V9"/>'],
        ['Timer',     route('admin.timer.index'),     '*admin/timer*',     '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/>'],
        ['Voucher',   route('admin.voucher.index'),   '*admin/voucher*',   '<path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/>'],
        ['Transaksi', route('admin.transaksi.index'), '*admin/transaksi*', '<path d="M4 4h16v16H4zM8 9h8M8 13h8M8 17h4"/>'],
        ['Pegawai',   route('admin.pegawai.index'),   '*admin/pegawai*',   '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.5 3-5 7-5s7 1.5 7 5M17 4.5a3.5 3.5 0 0 1 0 7M22 20c0-2.5-1.5-4-4-4.6"/>'],
    ];
@endphp

{{-- Notifikasi Pop-up (Toast) --}}
@include('partials.toast')

{{-- Sidebar --}}
<nav class="sticky top-0 z-20 flex h-screen w-[232px] flex-shrink-0 flex-col gap-1.5 self-start overflow-y-auto border-r border-line bg-white px-4 py-6
            max-[820px]:static max-[820px]:h-auto max-[820px]:w-auto max-[820px]:flex-row max-[820px]:overflow-x-auto max-[820px]:self-auto">
    <div class="flex items-center gap-2.5 px-2 pb-[22px] max-[820px]:pb-0">
        <img src="{{ asset('images/logo.jpg') }}" alt="Alpha Theraphy" class="h-10 w-10 flex-shrink-0 rounded-[10px] border border-line bg-white object-contain shadow-sm p-0.5">
        <div class="min-w-0">
            <div class="truncate text-[15px] font-extrabold leading-tight text-accent-dark">Alpha Theraphy</div>
            <div class="text-[10px] font-semibold tracking-wider text-muted">アルファ・セラピー</div>
        </div>
    </div>

    @foreach ($menu as [$label, $href, $pattern, $icon])
        <a href="{{ $href }}"
           class="flex items-center gap-3 whitespace-nowrap rounded-[10px] px-3 py-[11px] text-sm transition-colors
                  {{ request()->is($pattern)
                        ? 'bg-accent-soft font-semibold text-accent-dark'
                        : 'text-[#334E68] hover:bg-surface' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">{!! $icon !!}</svg>
            {{ $label }}
        </a>
    @endforeach
</nav>
