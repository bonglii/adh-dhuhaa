    </div><!-- end content-area -->
</div><!-- end main-wrap -->

<!-- ─── Footer Copyright ──────────────────────────────────────────────────── -->
<footer style="text-align:center;padding:14px 20px;font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;margin-top:auto;background:#fafafa;">
    © <?= date('Y') ?> SD IT Qurani Adh-Dhuhaa. All Rights Reserved
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    if ($('.datatable').length) {
        $('.datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
            },
            pageLength: 10,
            responsive: true
        });
    }
});

/**
 * confirmDelete — tampilkan konfirmasi lalu arahkan ke URL hapus.
 * Mendukung dua bentuk pemanggilan:
 *   confirmDelete(id, url)                      → url?id=N&action=delete
 *   confirmDelete(id, page, actionParam)         → page?action=actionParam&id=N
 */
function confirmDelete(id, urlOrPage, actionParam) {
    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;
    if (actionParam) {
        window.location.href = urlOrPage + '?action=' + actionParam + '&id=' + id;
    } else {
        window.location.href = urlOrPage + '?id=' + id + '&action=delete';
    }
}

function showAlert(msg, type='success') {
    const cls = type === 'success' ? 'alert-success-custom' : 'alert-error-custom';
    const icon = type === 'success' ? '✓' : '✗';
    const div = `<div class="alert-custom ${cls}" id="auto-alert">${icon} ${msg}</div>`;
    document.querySelector('.content-area').insertAdjacentHTML('afterbegin', div);
    setTimeout(() => document.getElementById('auto-alert')?.remove(), 4000);
}
</script>
</body>
</html>
