<?php

/**
 * item.php — Tabel Item Penilaian (Bank Soal)
 *
 * Halaman untuk mengelola daftar item (point penilaian)
 * yang akan dipilih saat membuat Custom Penilaian di masing-masing indikator.
 *
 * Item disimpan ke tabel `item` (sebelumnya: item_master).
 * Pengaitan ke indikator dilakukan di halaman Custom Penilaian.
 * Status item menampilkan keterangan tahun ajaran jika sudah dipakai
 * di komponen tertentu (diambil dari tabel komponen.ta_komponen).
 */

require_once 'includes/config.php';
requireLogin();

$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// ================================================================
// HAPUS ITEM
// ================================================================
if ($action === 'delete' && $id) {
    $cek = $pdo->prepare("SELECT COUNT(*) FROM isi WHERE id_item = ?");
    $cek->execute([$id]);
    if ((int)$cek->fetchColumn() > 0) {
        session_write_close();
        header('Location: item.php?msg=' . urlencode('Item tidak bisa dihapus karena sudah digunakan di custom penilaian.'));
        exit;
    }
    $pdo->prepare("DELETE FROM item WHERE id_item = ?")->execute([$id]);
    session_write_close();
    header('Location: item.php?msg=' . urlencode('Item berhasil dihapus.'));
    exit;
}

// ================================================================
// SIMPAN / UPDATE ITEM
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $nama_item = trim(sanitize($_POST['nama_item'] ?? ''));
    $item_id   = (int)($_POST['item_id'] ?? 0);

    if (!$nama_item) {
        $msg = ['type' => 'err', 'text' => 'Nama item tidak boleh kosong!'];
    } elseif (mb_strlen($nama_item) > 255) {
        $msg = ['type' => 'err', 'text' => 'Nama item terlalu panjang! Maksimal 255 karakter.'];
    } else {
        if ($item_id) {
            $pdo->prepare("UPDATE item SET nama_item = ? WHERE id_item = ?")
                ->execute([$nama_item, $item_id]);
            session_write_close();
            header('Location: item.php?msg=' . urlencode('Item berhasil diperbarui!'));
            exit;
        } else {
            $cek = $pdo->prepare("SELECT COUNT(*) FROM item WHERE nama_item = ?");
            $cek->execute([$nama_item]);
            if ((int)$cek->fetchColumn() > 0) {
                $msg = ['type' => 'err', 'text' => 'Item dengan nama tersebut sudah ada!'];
            } else {
                $pdo->prepare("INSERT INTO item (nama_item) VALUES (?)")
                    ->execute([$nama_item]);
                session_write_close();
                header('Location: item.php?msg=' . urlencode('Item berhasil ditambahkan!'));
                exit;
            }
        }
    }
}

// Pesan dari redirect (ditangani oleh toast di footer via ?msg=)
$msg = null;

// ================================================================
// LOAD DATA
// ================================================================
$search = sanitize($_GET['q'] ?? '');

$params   = [];
$whereSQL = '';
if ($search) {
    $whereSQL = "WHERE nama_item LIKE ?";
    $params[] = '%' . $search . '%';
}

$stmt = $pdo->prepare("
    SELECT id_item, nama_item, created_at
    FROM item
    $whereSQL
    ORDER BY id_item ASC
");
$stmt->execute($params);
$itemList = $stmt->fetchAll();

$totalItem = count($itemList);

// Ambil item yang sudah dipakai di custom penilaian beserta ta_komponen
$stmtPakai = $pdo->query("
    SELECT
        isi.id_item,
        GROUP_CONCAT(DISTINCT k.ta_komponen ORDER BY k.ta_komponen SEPARATOR ', ') AS tahun_ajaran_list
    FROM isi
    LEFT JOIN komponen k ON isi.id_komponen = k.id_komponen
    GROUP BY isi.id_item
");
// $dipakaiMap: id_item => string tahun ajaran
$dipakaiMap = [];
foreach ($stmtPakai->fetchAll() as $row) {
    $dipakaiMap[$row['id_item']] = $row['tahun_ajaran_list'];
}

$pageTitle = 'Tambah Point Penilaian';
require_once 'includes/header.php';
?>

<!-- notifikasi ditangani oleh toast di footer -->

<div class="data-table-card">

    <!-- ── Header ─────────────────────────────────────────────── -->
    <div class="card-header-custom mb-0" style="padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
        <div>
            <div class="card-title-custom">🗂 Tambah Point Penilaian</div>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                Bank item yang dapat dipilih di setiap indikator saat membuat <strong>Buat Pertanyaan Penilaian</strong>.
            </p>
        </div>
        <button onclick="openModal()" class="btn-primary-custom">+ Tambah Item</button>
    </div>

    <!-- ── Pencarian ──────────────────────────────────────────── -->
    <form method="GET" style="margin:16px 0 18px;">
        <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama item…"
                class="form-control-custom"
                style="max-width:320px;">
            <button type="submit" class="btn-primary-custom" style="padding:9px 18px;">Cari</button>
            <?php if ($search): ?>
                <a href="item.php" class="btn-cancel" style="padding:8px 13px;">✕ Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ── Konten ─────────────────────────────────────────────── -->
    <?php if (empty($itemList)): ?>
        <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
            <div style="font-size:46px;margin-bottom:14px;">📭</div>
            <p style="font-size:14px;">
                <?= $search
                    ? 'Tidak ada item yang cocok dengan "' . htmlspecialchars($search) . '".'
                    : 'Belum ada item. Tambahkan item pertama!' ?>
            </p>
            <?php if (!$search): ?>
                <button onclick="openModal()" class="btn-primary-custom" style="margin-top:12px;">+ Tambah Item Pertama</button>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div style="font-size:12px;color:#6b7280;margin-bottom:12px;">
            Menampilkan <strong><?= $totalItem ?></strong> item
            <?= $search ? ' · hasil pencarian: "' . htmlspecialchars($search) . '"' : '' ?>
            &nbsp;·&nbsp;
            <span style="color:#1a4731;font-weight:600;"><?= count($dipakaiMap) ?></span> sudah dipakai di custom penilaian
        </div>

        <table class="table table-hover" style="font-size:13px;width:100%;table-layout:fixed;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="width:45px;padding:10px 12px;color:#6b7280;font-weight:600;">No</th>
                    <th style="padding:10px 12px;color:#6b7280;font-weight:600;max-width:0;">Nama Item / Point Penilaian</th>
                    <th style="width:200px;padding:10px 12px;color:#6b7280;font-weight:600;text-align:center;">Status</th>
                    <th style="width:140px;padding:10px 12px;color:#6b7280;font-weight:600;text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itemList as $i => $item):
                    $itemId  = $item['id_item'];
                    $dipakai = isset($dipakaiMap[$itemId]);
                    $taList  = $dipakai ? $dipakaiMap[$itemId] : '';
                ?>
                    <tr style="border-bottom:1px solid #f3f4f6;transition:background .12s;"
                        onmouseover="this.style.background='#f9fafb'"
                        onmouseout="this.style.background=''">
                        <td style="padding:11px 12px;color:#9ca3af;vertical-align:top;"><?= $i + 1 ?></td>
                        <td style="padding:11px 12px;color:#1f2937;word-break:break-word;overflow-wrap:anywhere;white-space:normal;vertical-align:top;"><?= htmlspecialchars($item['nama_item']) ?></td>
                        <td style="padding:11px 12px;text-align:center;vertical-align:top;">
                            <?php if ($dipakai): ?>
                                <div class="inbox-wrap" style="position:relative;display:inline-block;">
                                    <button onclick="toggleInbox(this)"
                                        style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;">
                                        📅 Dipakai
                                        <span class="inbox-count" style="background:#1e40af;color:#fff;border-radius:50%;width:17px;height:17px;display:inline-flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0;">
                                            <?= count(array_filter(array_map('trim', explode(',', $taList)))) ?>
                                        </span>
                                        <svg class="inbox-arrow" style="width:10px;height:10px;transition:transform .2s;" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="#1e40af" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                    <div class="inbox-dropdown" style="display:none;position:absolute;top:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:200px;max-width:280px;z-index:999;overflow:hidden;">
                                        <div style="padding:8px 12px;background:#f8fafc;border-bottom:1px solid #e5e7eb;font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">
                                            Dipakai di Tahun Ajaran
                                        </div>
                                        <?php foreach (array_filter(array_map('trim', explode(',', $taList))) as $ta): ?>
                                        <div style="padding:7px 12px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #f3f4f6;font-size:12px;color:#1f2937;">
                                            <span style="color:#2563eb;">📌</span>
                                            <span><?= htmlspecialchars($ta) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span style="background:#f3f4f6;color:#9ca3af;font-size:11px;padding:3px 10px;border-radius:20px;">Belum dipakai</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:11px 12px;text-align:center;vertical-align:middle;white-space:nowrap;">
                            <div style="display:flex;gap:5px;justify-content:center;flex-wrap:nowrap;">
                                <button onclick='openModalEdit(<?= htmlspecialchars(json_encode([
                                                                    'id'   => $itemId,
                                                                    'nama' => $item['nama_item'],
                                                                ])) ?>)'
                                    class="btn-primary-custom btn-sm-custom btn-edit" style="white-space:nowrap;flex-shrink:0;">Edit</button>

                                <?php if (!$dipakai): ?>
                                    <button onclick="hapusItem(<?= $itemId ?>, '<?= htmlspecialchars(addslashes($item['nama_item'])) ?>')"
                                        class="btn-primary-custom btn-sm-custom btn-delete" style="white-space:nowrap;flex-shrink:0;">Hapus</button>
                                <?php else: ?>
                                    <button disabled title="Tidak bisa dihapus — sudah dipakai di custom penilaian"
                                        class="btn-primary-custom btn-sm-custom btn-delete"
                                        style="opacity:.35;cursor:not-allowed;white-space:nowrap;flex-shrink:0;">Hapus</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- ================================================================ -->
<!-- MODAL Tambah / Edit Item                                          -->
<!-- ================================================================ -->
<div id="modalItem" class="modal-overlay">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.18);">

        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong style="font-size:15px;color:#1a4731;" id="modalTitle">Tambah Item</strong>
                <div style="font-size:12px;color:#6b7280;margin-top:3px;">
                    Item ini akan tersedia sebagai pilihan di semua indikator Custom Penilaian.
                </div>
            </div>
            <button onclick="closeModal()"
                style="background:none;border:none;font-size:22px;cursor:pointer;color:#9ca3af;padding:4px 8px;border-radius:6px;transition:all .15s;"
                onmouseover="this.style.background='#f3f4f6';this.style.color='#374151'"
                onmouseout="this.style.background='none';this.style.color='#9ca3af'">×</button>
        </div>

        <form method="POST" style="padding:24px;" autocomplete="off">
            <input type="hidden" name="save_item" value="1">
            <input type="hidden" name="item_id" id="inputItemId" value="">

            <div style="margin-bottom:22px;">
                <label class="form-label-custom">Nama Item / Point Penilaian <span style="color:#dc2626;">*</span></label>
                <textarea name="nama_item" id="inputNamaItem" class="form-control-custom" rows="3" maxlength="255" oninput="updateCharCount()"
                    placeholder="cth: Persentase Kehadiran, Datang tepat waktu, Berpakaian seragam…"
                    required></textarea>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:5px;">
                    <small style="color:#9ca3af;font-size:11px;">Tulis secara jelas — item ini akan muncul sebagai pilihan saat menyusun custom penilaian.</small>
                    <small id="charCount" style="color:#9ca3af;font-size:11px;white-space:nowrap;margin-left:8px;">0 / 255</small>
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn-primary-custom">💾 Simpan</button>
                <button type="button" onclick="closeModal()" class="btn-cancel">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
    .modal-overlay {
        display: none !important;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .45);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex !important;
    }

    .alert-danger-custom {
        background: #fef2f2;
        border-left: 4px solid #dc2626;
        padding: 12px 16px;
        border-radius: 8px;
        color: #991b1b;
        font-size: 13px;
    }
</style>

<script>
    function updateCharCount() {
        var ta = document.getElementById('inputNamaItem');
        var cc = document.getElementById('charCount');
        if (ta && cc) {
            var len = ta.value.length;
            cc.textContent = len + ' / 255';
            cc.style.color = len > 230 ? '#dc2626' : '#9ca3af';
        }
    }

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Item';
        document.getElementById('inputItemId').value = '';
        document.getElementById('inputNamaItem').value = '';
        updateCharCount();
        document.getElementById('modalItem').classList.add('active');
        setTimeout(function() {
            document.getElementById('inputNamaItem').focus();
        }, 100);
    }

    function openModalEdit(data) {
        document.getElementById('modalTitle').textContent = 'Edit Item';
        document.getElementById('inputItemId').value = data.id;
        document.getElementById('inputNamaItem').value = data.nama;
        updateCharCount();
        document.getElementById('modalItem').classList.add('active');
        setTimeout(function() {
            document.getElementById('inputNamaItem').focus();
        }, 100);
    }

    function closeModal() {
        document.getElementById('modalItem').classList.remove('active');
    }

    function hapusItem(id, nama) {
        if (!confirm('Hapus item "' + nama + '"?')) return;
        window.location.href = 'item.php?action=delete&id=' + id;
    }

    document.getElementById('modalItem').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Inbox popover toggle
    function toggleInbox(btn) {
        const wrap = btn.closest('.inbox-wrap');
        const dropdown = wrap.querySelector('.inbox-dropdown');
        const arrow = btn.querySelector('.inbox-arrow');
        const isOpen = dropdown.style.display === 'block';

        // Tutup semua inbox lain dulu
        document.querySelectorAll('.inbox-dropdown').forEach(d => d.style.display = 'none');
        document.querySelectorAll('.inbox-arrow').forEach(a => a.style.transform = '');

        if (!isOpen) {
            dropdown.style.display = 'block';
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    // Tutup saat klik di luar
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.inbox-wrap')) {
            document.querySelectorAll('.inbox-dropdown').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.inbox-arrow').forEach(a => a.style.transform = '');
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>