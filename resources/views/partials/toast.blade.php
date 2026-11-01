{{-- Modal Pop-up Notifikasi & Konfirmasi (SweetAlert2) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal2-popup {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
        border-radius: 1.25rem !important;
        border: 1px solid #D4E2EE !important;
        box-shadow: 0 25px 50px -12px rgba(15, 30, 54, 0.25) !important;
        padding: 1.75rem 1.5rem !important;
    }
    .swal2-title {
        font-size: 1.35rem !important;
        font-weight: 700 !important;
        color: #0F1E36 !important;
        padding-top: 0.5rem !important;
    }
    .swal2-html-container {
        font-size: 0.925rem !important;
        color: #4A607A !important;
        line-height: 1.5 !important;
        margin-top: 0.5rem !important;
    }
    .swal2-confirm {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        padding: 0.65rem 1.5rem !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
    }
    .swal2-cancel {
        border-radius: 0.75rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        padding: 0.65rem 1.5rem !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
    }
    .swal2-timer-progress-bar {
        background: #0284C7 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {!! json_encode(session('success')) !!},
            timer: 3500,
            timerProgressBar: true,
            confirmButtonColor: '#0284C7',
            confirmButtonText: 'Selesai',
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Perhatian!',
            text: {!! json_encode(session('error')) !!},
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'Mengerti',
        });
    @endif

    @if (session('status'))
        Swal.fire({
            icon: 'info',
            title: 'Informasi',
            text: {!! json_encode(session('status')) !!},
            confirmButtonColor: '#0284C7',
            confirmButtonText: 'OK',
            timer: 3500,
            timerProgressBar: true,
        });
    @endif

    // Intercept semua form yang memiliki data-confirm agar muncul pop-up konfirmasi
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.dataset) return;

        const confirmMsg = form.dataset.confirm;
        if (!confirmMsg) return;

        if (form.dataset.swalConfirmed === 'true') {
            return;
        }

        e.preventDefault();

        const isDelete = form.querySelector('input[name="_method"][value="DELETE"]') || form.action.includes('destroy');
        const isLogout = form.action.includes('logout');

        Swal.fire({
            title: isLogout ? 'Konfirmasi Keluar' : (isDelete ? 'Konfirmasi Hapus' : 'Konfirmasi Tindakan'),
            text: confirmMsg,
            icon: isDelete || isLogout ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isDelete || isLogout ? '#DC2626' : '#0284C7',
            cancelButtonColor: '#94A3B8',
            confirmButtonText: isLogout ? 'Ya, Keluar' : (isDelete ? 'Ya, Hapus' : 'Ya, Lanjutkan'),
            cancelButtonText: 'Batal',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) {
                form.dataset.swalConfirmed = 'true';
                form.submit();
            }
        });
    });
});

// Helper global untuk memunculkan pop-up modal dari JavaScript
window.showPopup = function (type, title, message) {
    return Swal.fire({
        icon: type,
        title: title || (type === 'success' ? 'Berhasil!' : (type === 'error' ? 'Perhatian!' : 'Informasi')),
        text: message,
        timer: type === 'success' ? 3500 : undefined,
        timerProgressBar: type === 'success',
        confirmButtonColor: type === 'error' ? '#DC2626' : '#0284C7',
        confirmButtonText: 'OK'
    });
};
</script>
