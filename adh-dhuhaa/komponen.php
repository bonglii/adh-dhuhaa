<?php
/**
 * komponen.php — Halaman Manajemen Komponen & Poin Penilaian
 *
 * Fitur:
 *  - Tambah, edit, hapus Indikator (kategori) penilaian per tipe guru
 *  - Tambah, edit, hapus Poin penilaian di bawah setiap indikator
 *  - Tampil tab per tipe guru: Guru Qur'an, Guru Kelas, Guru Mapel, GTK
 */

// ─── Inisialisasi: load konfigurasi & wajib login ────────────────────────────
require_once 'includes/config.php';
requireLogin();

// ─── Baca parameter aksi & ID dari URL ──────────────────────────────────────
$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// ================================================================
// HAPUS INDIKATOR — POST + CSRF (BUG-QA-01 FIX)
// ================================================================
if ($action === 'delete_komponen' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_tokens']['delete_komponen'] ?? '';
    unset($_SESSION['csrf_tokens']['delete_komponen']);
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        $rawTab = sanitize($_POST['tab'] ?? 'guru_quran');
        $safeTab = isValidTipe($pdo, $rawTab) ? $rawTab : 'guru_quran';
        header('Location: komponen.php?tab=' . $safeTab . '&msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.'));
        exit;
    }
    $del_id = (int)($_POST['del_id'] ?? 0);
    // Ambil tipe_guru dulu sebelum dihapus, untuk redirect ke tab yang benar
    $tipeStmt = $pdo->prepare("SELECT tipe_guru FROM komponen_penilaian WHERE id=?");
    $tipeStmt->execute([$del_id]);
    $rawTab = sanitize($_POST['tab'] ?? 'guru_quran');
    $tabAfterDelete = $tipeStmt->fetchColumn() ?: (isValidTipe($pdo, $rawTab) ? $rawTab : 'guru_quran');
    $pdo->prepare("DELETE FROM komponen_penilaian WHERE id=?")->execute([$del_id]);
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: komponen.php?tab=' . $tabAfterDelete . '&msg=' . urlencode('Indikator berhasil dihapus!'));
    exit;
}

// ================================================================
// HAPUS ITEM — POST + CSRF (BUG-QA-01 FIX)
// ================================================================
if ($action === 'delete_item' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_tokens']['delete_item'] ?? '';
    unset($_SESSION['csrf_tokens']['delete_item']);
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        $rawTab = sanitize($_POST['tab'] ?? 'guru_quran');
        $safeTab = isValidTipe($pdo, $rawTab) ? $rawTab : 'guru_quran';
        header('Location: komponen.php?tab=' . $safeTab . '&msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.'));
        exit;
    }
    $del_id = (int)($_POST['del_id'] ?? 0);
    // Ambil tipe_guru lewat join sebelum item dihapus
    $tipeStmt = $pdo->prepare("SELECT kp.tipe_guru FROM item i JOIN komponen_penilaian kp ON i.komponen_id=kp.id WHERE i.id=?");
    $tipeStmt->execute([$del_id]);
    $rawTab = sanitize($_POST['tab'] ?? 'guru_quran');
    $tabAfterDelete = $tipeStmt->fetchColumn() ?: (isValidTipe($pdo, $rawTab) ? $rawTab : 'guru_quran');
    $pdo->prepare("DELETE FROM item WHERE id=?")->execute([$del_id]);
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: komponen.php?tab=' . $tabAfterDelete . '&msg=' . urlencode('Poin penilaian berhasil dihapus!'));
    exit;
}

// ================================================================
// SIMPAN / UPDATE INDIKATOR
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_komponen'])) {
    $tipe_guru     = sanitize($_POST['tipe_guru'] ?? '');
    $nama_kategori = sanitize($_POST['nama_kategori'] ?? '');
    $komp_id       = (int)($_POST['komponen_id'] ?? 0);

    // Validasi tipe_guru terhadap tabel tipe_guru di database
    if (!isValidTipe($pdo, $tipe_guru) || !$nama_kategori) {
        $msg = 'Data tidak lengkap!';
    } else {
        if ($komp_id) {
            // Edit: hanya nama & tipe yang bisa diubah, urutan tetap
            $pdo->prepare("UPDATE komponen_penilaian SET tipe_guru=?, nama_kategori=? WHERE id=?")
                ->execute([$tipe_guru, $nama_kategori, $komp_id]);
            $msg = 'Indikator berhasil diperbarui!';
        } else {
            // Tambah baru: urutan otomatis = jumlah indikator tipe ini + 1
            $maxUrutan = $pdo->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 FROM komponen_penilaian WHERE tipe_guru=?");
            $maxUrutan->execute([$tipe_guru]);
            $urutan = (int)$maxUrutan->fetchColumn();
            $pdo->prepare("INSERT INTO komponen_penilaian (tipe_guru, nama_kategori, urutan) VALUES (?,?,?)")
                ->execute([$tipe_guru, $nama_kategori, $urutan]);
            $msg = 'Indikator berhasil ditambahkan!';
        }
        // Simpan session sebelum redirect agar data tidak hilang
        session_write_close();
        header('Location: komponen.php?tab=' . $tipe_guru . '&msg=' . urlencode($msg));
        exit;
    }
}

// ================================================================
// SIMPAN / UPDATE ITEM (POIN PENILAIAN)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $komponen_id = (int)($_POST['komponen_id'] ?? 0);
    $nama_item   = sanitize($_POST['nama_item'] ?? '');
    $item_id     = (int)($_POST['item_id'] ?? 0);

    if (!$komponen_id || !$nama_item) {
        $msg = 'Data tidak lengkap!';
    } else {
        if ($item_id) {
            // Edit: hanya nama_item yang bisa diubah, nomor & urutan tetap otomatis
            $pdo->prepare("UPDATE item SET nama_item=? WHERE id=?")
                ->execute([$nama_item, $item_id]);
            $msg = 'Poin penilaian berhasil diperbarui!';
        } else {
            // Tambah baru: urutan = jumlah item dalam komponen ini + 1
            $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM item WHERE komponen_id=?");
            $cntStmt->execute([$komponen_id]);
            $urutan = (int)$cntStmt->fetchColumn() + 1;

            // Ambil urutan indikator untuk prefix nomor (cth: indikator urutan=2 → nomor "2.3")
            $kompUrutan = $pdo->prepare("SELECT urutan FROM komponen_penilaian WHERE id=?");
            $kompUrutan->execute([$komponen_id]);
            $prefix = (int)$kompUrutan->fetchColumn();

            $nomor_item = $prefix . '.' . $urutan;

            $pdo->prepare("INSERT INTO item (komponen_id, nomor_item, nama_item, urutan) VALUES (?,?,?,?)")
                ->execute([$komponen_id, $nomor_item, $nama_item, $urutan]);
            $msg = 'Poin penilaian berhasil ditambahkan!';
        }
        // Cari tipe_guru dari komponen_id untuk redirect ke tab yang benar
        $tipe_row = $pdo->prepare("SELECT tipe_guru FROM komponen_penilaian WHERE id=?");
        $tipe_row->execute([$komponen_id]);
        $tipe_guru = $tipe_row->fetchColumn() ?: 'guru_kelas';
        // Simpan session sebelum redirect agar data tidak hilang
        session_write_close();
        header('Location: komponen.php?tab=' . $tipe_guru . '&msg=' . urlencode($msg));
        exit;
    }
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// BUG-QA-01 FIX: Token CSRF untuk hapus indikator dan item via POST
$csrf_delete_komponen = bin2hex(random_bytes(16));
$csrf_delete_item     = bin2hex(random_bytes(16));
$_SESSION['csrf_tokens']['delete_komponen'] = $csrf_delete_komponen;
$_SESSION['csrf_tokens']['delete_item']     = $csrf_delete_item;

// ================================================================
// LOAD DATA
// ================================================================
$tabActive = sanitize($_GET['tab'] ?? 'guru_quran');

// Ambil semua komponen dengan items-nya
$allKomponen = $pdo->query("SELECT * FROM komponen_penilaian ORDER BY tipe_guru, urutan")->fetchAll();
$allItems    = $pdo->query("SELECT * FROM item ORDER BY komponen_id, urutan")->fetchAll();

// Kelompokkan items per komponen
$itemsByKomponen = [];
foreach ($allItems as $it) {
    $itemsByKomponen[$it['komponen_id']][] = $it;
}

// Data edit (untuk modal)
$editKomponen = null;
$editItem     = null;
$modalKomponenId = (int)($_GET['komponen_id'] ?? 0); // untuk modal tambah item

if ($action === 'edit_komponen' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM komponen_penilaian WHERE id=?");
    $stmt->execute([$id]);
    $editKomponen = $stmt->fetch();
    if ($editKomponen) $tabActive = $editKomponen['tipe_guru'];
}
if ($action === 'edit_item' && $id) {
    $stmt = $pdo->prepare("SELECT i.*, kp.tipe_guru FROM item i JOIN komponen_penilaian kp ON i.komponen_id=kp.id WHERE i.id=?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
    if ($editItem) $tabActive = $editItem['tipe_guru'];
}

// Ambil daftar tipe guru dari database (dinamis)
$tipeLabels = getTipeGuru($pdo);

$katIcons = [
    'Disiplin'                 => ['⏰', '#1a4731', '#e8f5ee'],
    'Pelaksanaan Pembelajaran' => ['📚', '#1e40af', '#eff6ff'],
    'Kerjasama'                => ['🤝', '#7c3aed', '#f5f3ff'],
    'Administrasi'             => ['🗂️', '#b45309', '#fef3c7'],
    'Kinerja'                  => ['📊', '#0f766e', '#f0fdfa'],
    'Pelayanan'                => ['🌟', '#be185d', '#fdf2f8'],
];

$pageTitle = 'Kelola Komponen Penilaian';
require_once 'includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert-custom alert-success-custom mb-4">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="data-table-card">
    <div class="card-header-custom mb-3">
        <div>
            <div class="card-title-custom">⚙️ Kelola Komponen Penilaian</div>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                Atur <strong>indikator</strong> (kategori) dan <strong>poin penilaian</strong> (item) untuk setiap tipe guru.
            </p>
        </div>
        <button onclick="openModalKomponen()" class="btn-primary-custom">+ Tambah Indikator</button>
    </div>

    <!-- TAB NAVIGASI -->
    <div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid #e5e7eb;padding-bottom:0;">
        <?php foreach ($tipeLabels as $tipe => $label): ?>
            <a href="komponen.php?tab=<?= $tipe ?>"
               style="padding:9px 18px;border-radius:8px 8px 0 0;font-size:13px;font-weight:600;text-decoration:none;
                      transition:all .2s;border:1.5px solid <?= $tabActive === $tipe ? 'var(--hijau)' : '#e5e7eb' ?>;
                      border-bottom:none;
                      background:<?= $tabActive === $tipe ? 'var(--hijau)' : '#f9fafb' ?>;
                      color:<?= $tabActive === $tipe ? '#fff' : '#374151' ?>;">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- KONTEN TAB -->
    <?php
    $komponenTab = array_filter($allKomponen, fn($k) => $k['tipe_guru'] === $tabActive);
    if (empty($komponenTab)):
    ?>
        <div style="text-align:center;padding:40px;color:#9ca3af;">
            <div style="font-size:36px;margin-bottom:12px;">📭</div>
            <p>Belum ada indikator untuk tipe ini.</p>
            <button onclick="openModalKomponen('<?= $tabActive ?>')" class="btn-primary-custom" style="margin-top:8px;">+ Tambah Indikator Pertama</button>
        </div>
    <?php else: ?>
        <?php foreach ($komponenTab as $komp):
            $ic = $katIcons[$komp['nama_kategori']] ?? ['📌', '#374151', '#f9fafb'];
            $items = $itemsByKomponen[$komp['id']] ?? [];
        ?>
            <div class="nilai-group" style="border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px;">
            <!-- Header Indikator -->
            <div style="background:<?= $ic[2] ?>;border-left:5px solid <?= $ic[1] ?>;padding:12px 16px;display:flex;align-items:center;gap:12px;">
                <span style="font-size:22px;"><?= $ic[0] ?></span>
                <div style="flex:1;">
                    <div style="font-size:11px;color:<?= $ic[1] ?>;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;opacity:.7;">
                        Indikator · Urutan <?= (int)$komp['urutan'] ?>
                        <?php if (!empty($komp['is_tambahan'])): ?>
                            &nbsp;<span style="background:#fef3c7;color:#92400e;padding:1px 7px;border-radius:4px;font-size:10px;font-weight:600;">Tambahan</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:15px;font-weight:700;color:<?= $ic[1] ?>;"><?= htmlspecialchars($komp['nama_kategori']) ?></div>
                </div>
                <div style="display:flex;gap:6px;">
                    <button onclick="openModalKomponen(null, <?= htmlspecialchars(json_encode($komp)) ?>)"
                        class="btn-primary-custom btn-sm-custom btn-edit">✏️ Edit</button>
                    <button onclick="openModalItem(<?= $komp['id'] ?>, '<?= htmlspecialchars($komp['nama_kategori']) ?>')"
                        class="btn-primary-custom btn-sm-custom" style="background:#0f766e;font-size:11px;">+ Poin</button>
                    <button onclick="confirmDeleteKomponen(<?= $komp['id'] ?>, '<?= htmlspecialchars(addslashes($komp['nama_kategori'])) ?>', '<?= $tabActive ?>')"
                        class="btn-primary-custom btn-sm-custom btn-delete">🗑</button>
                </div>
            </div>

            <!-- Daftar Item -->
            <?php if (empty($items)): ?>
                <div style="padding:14px 20px;color:#92400e;font-size:13px;font-weight:700;background:#fef3c7;border-radius:8px;margin:8px 12px;border-left:4px solid #f59e0b;">
                    &#9888; Pastikan Setelah Indikator Ditambah, Tambahkan Juga Point Agar Dapat Dinilai Di Penilaian
                </div>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="padding:8px 14px;text-align:left;color:#6b7280;font-weight:600;width:80px;">No.</th>
                            <th style="padding:8px 14px;text-align:left;color:#6b7280;font-weight:600;">Poin Penilaian</th>
                            <th style="padding:8px 14px;text-align:center;color:#6b7280;font-weight:600;width:70px;">Urutan</th>
                            <th style="padding:8px 14px;text-align:center;color:#6b7280;font-weight:600;width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr style="border-top:1px solid #f3f4f6;">
                            <td style="padding:9px 14px;font-weight:600;color:<?= $ic[1] ?>;"><?= htmlspecialchars($item['nomor_item']) ?></td>
                            <td style="padding:9px 14px;color:#374151;"><?= htmlspecialchars($item['nama_item']) ?></td>
                            <td style="padding:9px 14px;text-align:center;color:#6b7280;"><?= (int)$item['urutan'] ?></td>
                            <td style="padding:9px 14px;text-align:center;">
                                <div style="display:flex;gap:5px;justify-content:center;">
                                    <button onclick="openModalEditItem(<?= htmlspecialchars(json_encode($item)) ?>, '<?= htmlspecialchars($komp['nama_kategori']) ?>')"
                                        class="btn-primary-custom btn-sm-custom btn-edit">Edit</button>
                                    <button onclick="confirmDeleteItem(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['nama_item'])) ?>', '<?= $tabActive ?>')"
                                        class="btn-primary-custom btn-sm-custom btn-delete">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="padding:12px 20px;color:#166534;font-size:13px;font-weight:700;background:#dcfce7;border-radius:8px;margin:8px 12px 12px;border-left:4px solid #16a34a;">
                    &#10003; Point Sudah Ditambahkan Dan Sudah Muncul Di Penilaian Nanti
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ============================================================ -->
<!-- MODAL: Tambah / Edit Indikator -->
<!-- ============================================================ -->
<div id="modalKomponen" class="modal-overlay">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <strong style="font-size:15px;color:#1a4731;" id="titleModalKomponen">Tambah Indikator</strong>
            <button onclick="closeModal('modalKomponen')" style="background:none;border:none;font-size:22px;cursor:pointer;color:#6b7280;">×</button>
        </div>
        <!-- Form indikator: autocomplete="off" mencegah browser mengisi otomatis field teks -->
        <form method="POST" style="padding:24px;" autocomplete="off">
            <input type="hidden" name="save_komponen" value="1">
            <input type="hidden" name="komponen_id" id="inputKomponenId" value="">

            <div style="margin-bottom:16px;">
                <label class="form-label-custom">Tipe Guru</label>
                <select name="tipe_guru" id="inputTipeGuru" class="form-control-custom" required>
                    <?php foreach ($tipeLabels as $kode => $label): ?>
                        <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom:6px;">
                <label class="form-label-custom">Nama Indikator</label>
                <input type="text" name="nama_kategori" id="inputNamaKategori" class="form-control-custom"
                    placeholder="cth: Disiplin, Kerjasama, Pelaksanaan Pembelajaran" required>
            </div>
            <small style="color:#9ca3af;font-size:11px;display:block;margin-bottom:18px;">
                Urutan nomor indikator dibuat otomatis oleh sistem
            </small>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn-primary-custom">💾 Simpan Indikator</button>
                <button type="button" onclick="closeModal('modalKomponen')" class="btn btn-light">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: Tambah / Edit Item (Poin Penilaian) -->
<!-- ============================================================ -->
<div id="modalItem" class="modal-overlay">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:520px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong style="font-size:15px;color:#1a4731;" id="titleModalItem">Tambah Poin Penilaian</strong>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;" id="subtitleModalItem"></div>
            </div>
            <button onclick="closeModal('modalItem')" style="background:none;border:none;font-size:22px;cursor:pointer;color:#6b7280;">×</button>
        </div>
        <!-- Form poin penilaian: autocomplete="off" mencegah browser mengisi otomatis field teks -->
        <form method="POST" style="padding:24px;" autocomplete="off">
            <input type="hidden" name="save_item" value="1">
            <input type="hidden" name="item_id" id="inputItemId" value="">
            <input type="hidden" name="komponen_id" id="inputItemKomponenId" value="">

            <!-- Nomor ditampilkan hanya saat edit (readonly, tidak dikirim) -->
            <div id="nomorDisplay" style="display:none;margin-bottom:14px;">
                <label class="form-label-custom">Nomor</label>
                <div style="padding:9px 12px;background:#f3f4f6;border-radius:8px;font-size:13px;
                            color:#6b7280;border:1px solid #e5e7eb;" id="nomorValue">—</div>
                <small style="color:#9ca3af;font-size:11px;">Nomor dibuat otomatis oleh sistem</small>
            </div>

            <div style="margin-bottom:20px;">
                <label class="form-label-custom">Nama Poin Penilaian</label>
                <textarea name="nama_item" id="inputNamaItem" class="form-control-custom" rows="3"
                    placeholder="cth: Persentase Kehadiran" required></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn-primary-custom">💾 Simpan Poin</button>
                <button type="button" onclick="closeModal('modalItem')" class="btn btn-light">Batal</button>
            </div>
        </form>
    </div>
</div>
<style>
    .modal-overlay { display: none !important; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex !important; }
</style>
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // ── Modal Indikator: buka untuk tambah atau edit ────────────────────────
    function openModalKomponen(defaultTipe = null, data = null) {
        document.getElementById('inputKomponenId').value   = data ? data.id : '';
        document.getElementById('inputNamaKategori').value = data ? data.nama_kategori : '';
        document.getElementById('titleModalKomponen').textContent = data ? 'Edit Indikator' : 'Tambah Indikator';

        const sel  = document.getElementById('inputTipeGuru');
        const tipe = data ? data.tipe_guru : (defaultTipe || '<?= $tabActive ?>');
        for (let opt of sel.options) opt.selected = (opt.value === tipe);

        openModal('modalKomponen');
    }

    // ── Modal Poin: buka untuk tambah poin baru ─────────────────────────────
    function openModalItem(komponenId, namaKategori) {
        document.getElementById('inputItemId').value          = '';
        document.getElementById('inputItemKomponenId').value  = komponenId;
        document.getElementById('inputNamaItem').value        = '';
        document.getElementById('titleModalItem').textContent = 'Tambah Poin Penilaian';
        document.getElementById('subtitleModalItem').textContent = 'Indikator: ' + namaKategori;
        // Sembunyikan tampilan nomor saat tambah baru
        document.getElementById('nomorDisplay').style.display = 'none';
        openModal('modalItem');
    }

    // ── Modal Poin: buka untuk edit poin yang sudah ada ─────────────────────
    function openModalEditItem(data, namaKategori) {
        document.getElementById('inputItemId').value          = data.id;
        document.getElementById('inputItemKomponenId').value  = data.komponen_id;
        document.getElementById('inputNamaItem').value        = data.nama_item;
        document.getElementById('titleModalItem').textContent = 'Edit Poin Penilaian';
        document.getElementById('subtitleModalItem').textContent = 'Indikator: ' + namaKategori;
        // Tampilkan nomor (readonly) saat edit
        document.getElementById('nomorDisplay').style.display = 'block';
        document.getElementById('nomorValue').textContent     = data.nomor_item;
        openModal('modalItem');
    }

    // Tutup modal jika klik overlay di luar kotak
    ['modalKomponen', 'modalItem'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) closeModal(id);
        });
    });

    // Buka modal otomatis jika ada parameter action edit dari URL
    <?php if ($editKomponen): ?>
    openModalKomponen(null, <?= json_encode($editKomponen, JSON_HEX_TAG | JSON_HEX_APOS) ?>);
    <?php endif; ?>
    <?php if ($editItem): ?>
    openModalEditItem(<?= json_encode($editItem, JSON_HEX_TAG | JSON_HEX_APOS) ?>, '<?= htmlspecialchars($editItem['nama_kategori'] ?? '') ?>');
    <?php endif; ?>
    <?php if ($action === 'add_item' && $modalKomponenId): ?>
    openModalItem(<?= $modalKomponenId ?>, '');
    <?php endif; ?>

    // BUG-QA-01 FIX: Hapus indikator dan item via POST+CSRF
    function confirmDeleteKomponen(id, nama, tab) {
        document.getElementById('konfirmasiMsg').textContent =
            `Hapus indikator "${nama}"? Semua poin di dalamnya juga akan terhapus.`;
        document.getElementById('konfirmasiBtn').onclick = function() {
            document.getElementById('hapusKomponenId').value  = id;
            document.getElementById('hapusKomponenTab').value = tab;
            document.getElementById('formHapusKomponen').submit();
        };
        const m = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
        m.show();
    }
    function confirmDeleteItem(id, nama, tab) {
        document.getElementById('konfirmasiMsg').textContent =
            `Hapus poin "${nama}"? Tindakan ini tidak bisa dibatalkan.`;
        document.getElementById('konfirmasiBtn').onclick = function() {
            document.getElementById('hapusItemId').value  = id;
            document.getElementById('hapusItemTab').value = tab;
            document.getElementById('formHapusItem').submit();
        };
        const m = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
        m.show();
    }
</script>

<!-- BUG-QA-01 FIX: Modal konfirmasi hapus -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-body" style="padding:32px 28px 20px;text-align:center;">
                <div style="font-size:44px;margin-bottom:14px;">⚠️</div>
                <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:8px;">Konfirmasi Hapus</div>
                <div id="konfirmasiMsg" style="font-size:13px;color:#6b7280;line-height:1.6;"></div>
            </div>
            <div class="modal-footer" style="border:none;padding:0 28px 24px;gap:8px;justify-content:center;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="border-radius:8px;padding:8px 20px;font-size:13px;">Batal</button>
                <button type="button" id="konfirmasiBtn"
                    style="background:#dc2626;color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form tersembunyi hapus indikator — POST + CSRF -->
<form id="formHapusKomponen" method="POST" action="komponen.php?action=delete_komponen" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_delete_komponen ?? '') ?>">
    <input type="hidden" name="del_id" id="hapusKomponenId" value="">
    <input type="hidden" name="tab"    id="hapusKomponenTab" value="">
</form>
<!-- Form tersembunyi hapus item/poin — POST + CSRF -->
<form id="formHapusItem" method="POST" action="komponen.php?action=delete_item" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_delete_item ?? '') ?>">
    <input type="hidden" name="del_id" id="hapusItemId" value="">
    <input type="hidden" name="tab"    id="hapusItemTab" value="">
</form>

<?php require_once 'includes/footer.php'; ?>
