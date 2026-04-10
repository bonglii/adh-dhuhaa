    </div><!-- end content-area -->
</div><!-- end main-wrap -->

<!-- Footer -->
<footer style="text-align:center;padding:14px 20px;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;margin-top:auto;background:#fafafa;">
    © 2026 ISB Atma Luhur. All Rights Reserved
</footer>

<!-- Toast container -->
<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;"></div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<style>
.toast-notif {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 280px;
    max-width: 380px;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 500;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    pointer-events: all;
    animation: toastIn .3s ease;
    transition: opacity .4s ease, transform .4s ease;
}
.toast-notif.hiding { opacity: 0; transform: translateX(30px); }
.toast-success { background: #fff; border-left: 4px solid #16a34a; color: #15803d; }
.toast-error   { background: #fff; border-left: 4px solid #dc2626; color: #b91c1c; }
.toast-warning { background: #fff; border-left: 4px solid #d97706; color: #b45309; }
.toast-info    { background: #fff; border-left: 4px solid #2563eb; color: #1d4ed8; }
.toast-icon { font-size: 18px; flex-shrink: 0; }
.toast-msg  { flex: 1; line-height: 1.4; }
.toast-close { background: none; border: none; cursor: pointer; font-size: 16px; color: #9ca3af; padding: 0; flex-shrink: 0; }
.toast-close:hover { color: #374151; }
@keyframes toastIn { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform: translateX(0); } }
</style>
<script>
$(document).ready(function() {
    // DataTables — tabel umum
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            pageLength: 10,
            responsive: true
        });
    }

    // Tabel penilaian tidak pakai DataTables (ada checkbox + filter custom)
});

// ── Toast Notification ────────────────────────────────────────────
function showToast(msg, type) {
    type = type || 'success';
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const icon  = icons[type] || icons.success;

    const toast = document.createElement('div');
    toast.className = 'toast-notif toast-' + type;
    toast.innerHTML =
        '<span class="toast-icon">' + icon + '</span>' +
        '<span class="toast-msg">' + msg + '</span>' +
        '<button class="toast-close" onclick="this.parentElement.remove()">✕</button>';

    document.getElementById('toast-container').appendChild(toast);

    // Auto-hide setelah 4 detik
    setTimeout(function() {
        toast.classList.add('hiding');
        setTimeout(function() { toast.remove(); }, 400);
    }, 4000);
}

// Tampilkan toast dari ?msg= di URL otomatis
(function() {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');
    if (!msg) return;

    // Deteksi tipe dari isi pesan
    const lower = msg.toLowerCase();
    let type = 'success';
    if (lower.includes('gagal') || lower.includes('error') || lower.includes('tidak bisa')) type = 'error';
    else if (lower.includes('tidak ada') || lower.includes('sudah ada') || lower.includes('⚠') || lower.includes('peringatan')) type = 'warning';

    showToast(msg, type);

    // Bersihkan ?msg= dari URL tanpa reload
    const url = new URL(window.location.href);
    url.searchParams.delete('msg');
    window.history.replaceState({}, '', url);
})();

// ── confirmDelete ─────────────────────────────────────────────────
function confirmDelete(id, urlOrPage, actionParam) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
    if (actionParam) {
        window.location.href = urlOrPage + '?action=' + actionParam + '&id=' + id;
    } else {
        window.location.href = urlOrPage + '?id=' + id + '&action=delete';
    }
}

// Legacy support — showAlert tetap berfungsi
function showAlert(msg, type) {
    showToast(msg, type || 'success');
}
</script>
</body>
</html>
