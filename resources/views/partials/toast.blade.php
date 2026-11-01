{{-- Floating Toast Notification Pop-up --}}
<div id="toast-container" class="pointer-events-none fixed top-5 right-5 z-50 flex max-w-sm w-[calc(100vw-2.5rem)] flex-col gap-3" aria-live="polite">
    @if (session('success'))
        <div class="toast-item pointer-events-auto relative overflow-hidden flex items-start gap-3.5 rounded-2xl border border-emerald-200/80 bg-white/95 p-4 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] backdrop-blur transition-all duration-300 translate-x-0 opacity-100" role="status">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <div class="text-[13px] font-bold text-ink">Berhasil</div>
                <div class="mt-0.5 text-xs text-muted leading-relaxed break-words">{{ session('success') }}</div>
            </div>
            <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="shrink-0 rounded-lg p-1 text-muted transition-colors hover:bg-surface hover:text-ink" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div class="toast-progress absolute bottom-0 left-0 h-1 bg-emerald-500/80 transition-all duration-[4500ms] ease-linear w-full"></div>
        </div>
    @endif

    @if (session('error'))
        <div class="toast-item pointer-events-auto relative overflow-hidden flex items-start gap-3.5 rounded-2xl border border-rose-200/80 bg-white/95 p-4 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] backdrop-blur transition-all duration-300 translate-x-0 opacity-100" role="alert">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <div class="text-[13px] font-bold text-ink">Perhatian</div>
                <div class="mt-0.5 text-xs text-muted leading-relaxed break-words">{{ session('error') }}</div>
            </div>
            <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="shrink-0 rounded-lg p-1 text-muted transition-colors hover:bg-surface hover:text-ink" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div class="toast-progress absolute bottom-0 left-0 h-1 bg-rose-500/80 transition-all duration-[4500ms] ease-linear w-full"></div>
        </div>
    @endif

    @if (session('status'))
        <div class="toast-item pointer-events-auto relative overflow-hidden flex items-start gap-3.5 rounded-2xl border border-accent/30 bg-white/95 p-4 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] backdrop-blur transition-all duration-300 translate-x-0 opacity-100" role="status">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent-soft text-accent-dark border border-accent/20">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <div class="text-[13px] font-bold text-ink">Informasi</div>
                <div class="mt-0.5 text-xs text-muted leading-relaxed break-words">{{ session('status') }}</div>
            </div>
            <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="shrink-0 rounded-lg p-1 text-muted transition-colors hover:bg-surface hover:text-ink" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div class="toast-progress absolute bottom-0 left-0 h-1 bg-accent transition-all duration-[4500ms] ease-linear w-full"></div>
        </div>
    @endif
</div>

<script>
    function dismissToast(el) {
        if (!el) return;
        el.classList.add('opacity-0', 'translate-x-8');
        setTimeout(() => el.remove(), 300);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.toast-item').forEach(el => {
            const bar = el.querySelector('.toast-progress');
            if (bar) {
                requestAnimationFrame(() => {
                    bar.style.width = '0%';
                });
            }
            setTimeout(() => dismissToast(el), 4500);
        });
    });

    // Helper global untuk memunculkan notifikasi pop-up dari JavaScript kapan saja
    window.showToast = function(type, message, customTitle = null) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const isSuccess = type === 'success';
        const isError = type === 'error' || type === 'danger';

        const borderClass = isSuccess ? 'border-emerald-200/80' : (isError ? 'border-rose-200/80' : 'border-accent/30');
        const iconBg = isSuccess ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : (isError ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-accent-soft text-accent-dark border border-accent/20');
        const barColor = isSuccess ? 'bg-emerald-500/80' : (isError ? 'bg-rose-500/80' : 'bg-accent');
        const defaultTitle = isSuccess ? 'Berhasil' : (isError ? 'Perhatian' : 'Informasi');
        const finalTitle = customTitle || defaultTitle;

        const iconSvg = isSuccess 
            ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
            : (isError 
                ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
                : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>');

        const toast = document.createElement('div');
        toast.className = `toast-item pointer-events-auto relative overflow-hidden flex items-start gap-3.5 rounded-2xl border ${borderClass} bg-white/95 p-4 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] backdrop-blur transition-all duration-300 translate-x-8 opacity-0`;
        toast.innerHTML = `
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${iconBg}">
                ${iconSvg}
            </div>
            <div class="min-w-0 flex-1 pt-0.5">
                <div class="text-[13px] font-bold text-ink">${finalTitle}</div>
                <div class="mt-0.5 text-xs text-muted leading-relaxed break-words">${message}</div>
            </div>
            <button type="button" onclick="dismissToast(this.closest('.toast-item'))" class="shrink-0 rounded-lg p-1 text-muted transition-colors hover:bg-surface hover:text-ink" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div class="toast-progress absolute bottom-0 left-0 h-1 ${barColor} transition-all duration-[4500ms] ease-linear w-full"></div>
        `;
        container.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-8', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
            const bar = toast.querySelector('.toast-progress');
            if (bar) {
                requestAnimationFrame(() => {
                    bar.style.width = '0%';
                });
            }
        });
        setTimeout(() => dismissToast(toast), 4500);
    };
</script>
