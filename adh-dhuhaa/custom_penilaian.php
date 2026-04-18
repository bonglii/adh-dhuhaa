<?php

/**
 * custom_penilaian.php — Halaman Custom Penilaian
 *
 * Fitur:
 *  - Pilih tahun ajaran dan tipe guru → disimpan ke tabel komponen
 *  - Tambah indikator untuk custom penilaian
 *  - Pilih item dari bank soal (item) untuk setiap indikator → disimpan ke tabel isi
 *  - Kelola daftar custom penilaian yang sudah dibuat
 */

require_once 'includes/config.php';
requireLogin();

$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);
$openModalOnLoad = '';

// ================================================================
// HAPUS KOMPONEN (custom penilaian)
// ================================================================
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM komponen WHERE id_komponen = ?")->execute([$id]);
    session_write_close();
    header('Location: custom_penilaian.php?msg=' . urlencode('Custom penilaian berhasil dihapus!'));
    exit;
}

// ================================================================
// HAPUS ISI (indikator + item dari custom penilaian)
// ================================================================
if ($action === 'delete_isi') {
    $idKomponen = (int)($_GET['id_komponen'] ?? 0);
    $idItem     = (int)($_GET['id_item']     ?? 0);
    $namaInd    = $_GET['nama_indikator']   ?? '';
    if ($idKomponen && $idItem && $namaInd !== '') {
        $pdo->prepare("DELETE FROM isi WHERE id_komponen = ? AND id_item = ? AND nama_indikator = ?")
            ->execute([$idKomponen, $idItem, $namaInd]);
    }
    session_write_close();
    header('Location: custom_penilaian.php?action=edit&id=' . $idKomponen . '&msg=' . urlencode('Item berhasil dihapus dari indikator!'));
    exit;
}

// ================================================================
// SIMPAN CUSTOM PENILAIAN BARU (POST: step 1)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_komponen'])) {
    $ta       = sanitize($_POST['ta_komponen'] ?? '');
    $tipe     = sanitize($_POST['type_guru'] ?? '');

    if (!$ta || !$tipe || !isValidTipe($pdo, $tipe)) {
        $msg = 'Tahun ajaran dan tipe guru wajib diisi!';
    } else {
        // Cek duplikat: satu tahun ajaran + tipe guru hanya boleh satu komponen
        $cek = $pdo->prepare("SELECT id_komponen FROM komponen WHERE ta_komponen = ? AND type_guru = ?");
        $cek->execute([$ta, $tipe]);
        $existing = $cek->fetchColumn();
        if ($existing) {
            session_write_close();
            header('Location: custom_penilaian.php?action=edit&id=' . $existing . '&msg=' . urlencode('Custom penilaian untuk tahun ajaran & tipe guru ini sudah ada. Silakan edit yang sudah ada.'));
            exit;
        }
        $pdo->prepare("INSERT INTO komponen (ta_komponen, type_guru) VALUES (?, ?)")
            ->execute([$ta, $tipe]);
        $newId = $pdo->lastInsertId();
        session_write_close();
        header('Location: custom_penilaian.php?action=edit&id=' . $newId . '&msg=' . urlencode('Custom penilaian berhasil dibuat! Sekarang tambahkan indikator dan item.'));
        exit;
    }
}

// ================================================================
// SIMPAN INDIKATOR + ITEM KE TABEL ISI (POST: step 2)
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_isi'])) {
    $id_komponen    = (int)($_POST['id_komponen'] ?? 0);
    $nama_indikator = sanitize($_POST['nama_indikator'] ?? '');
    $urutan_isi     = (int)($_POST['urutan_isi'] ?? 0);
    $item_ids       = $_POST['item_ids'] ?? [];  // array of id_item

    // id_indikator tidak lagi dipakai (tabel indikator sudah dihapus)
    $id_indikator = null;

    if (!$id_komponen || !$nama_indikator) {
        $msg = 'Indikator wajib dipilih!';
        $openModalOnLoad = 'modalIndikator';
    } elseif (empty($item_ids)) {
        $msg = 'Pilih minimal satu item penilaian!';
        $openModalOnLoad = 'modalIndikator';
    } else {
        // Cek apakah indikator ini sudah ada di komponen ini
        $cekDuplikat = $pdo->prepare("SELECT COUNT(*) FROM isi WHERE id_komponen = ? AND nama_indikator = ?");
        $cekDuplikat->execute([$id_komponen, $nama_indikator]);
        if ((int)$cekDuplikat->fetchColumn() > 0) {
            $msg = 'Indikator "' . $nama_indikator . '" sudah ada! Hapus yang lama terlebih dahulu jika ingin menggantinya.';
            $openModalOnLoad = 'modalIndikator';
        } else {
            // Hitung urutan otomatis
            if (!$urutan_isi) {
                $stmt = $pdo->prepare("SELECT COALESCE(MAX(urutan_isi), 0) + 1 FROM isi WHERE id_komponen = ?");
                $stmt->execute([$id_komponen]);
                $urutan_isi = (int)$stmt->fetchColumn();
            }
            // Simpan setiap item (id_indikator sudah tidak dipakai)
            $ins = $pdo->prepare("INSERT INTO isi (id_komponen, nama_indikator, urutan_isi, id_item, nomor_item) VALUES (?, ?, ?, ?, ?)");
            foreach ($item_ids as $seq => $item_id) {
                $item_id = (int)$item_id;
                if (!$item_id) continue;
                $nomor = $urutan_isi . '.' . ($seq + 1);
                $ins->execute([$id_komponen, $nama_indikator, $urutan_isi, $item_id, $nomor]);
            }
            session_write_close();
            header('Location: custom_penilaian.php?action=edit&id=' . $id_komponen . '&msg=' . urlencode('Indikator dan item berhasil disimpan!'));
            exit;
        }
    }
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// ================================================================
// LOAD DATA
// ================================================================
$tipeLabels   = getTipeGuru($pdo);

// Filter dari GET
$filterTA   = sanitize($_GET['filter_ta']   ?? '');
$filterTipe = sanitize($_GET['filter_tipe'] ?? '');

// Ambil semua tahun ajaran untuk dropdown filter
$allTA = $pdo->query("SELECT DISTINCT ta_komponen FROM komponen ORDER BY ta_komponen DESC")->fetchAll(PDO::FETCH_COLUMN);

// Build query dengan filter
$filterWhere  = [];
$filterParams = [];
if ($filterTA) {
    $filterWhere[]  = "k.ta_komponen = ?";
    $filterParams[] = $filterTA;
}
if ($filterTipe) {
    $filterWhere[]  = "k.type_guru = ?";
    $filterParams[] = $filterTipe;
}
$whereSQL = $filterWhere ? 'WHERE ' . implode(' AND ', $filterWhere) : '';

$stmtKomp = $pdo->prepare("
    SELECT
        k.*,
        COUNT(DISTINCT i.id_item)          AS total_item,
        COUNT(DISTINCT i.nama_indikator)   AS total_indikator,
        (SELECT COUNT(DISTINCT p.id_guru) FROM penilaian p WHERE p.id_komponen = k.id_komponen) AS total_guru_dinilai,
        (SELECT COUNT(g.id_guru) FROM guru g WHERE g.tipe = k.type_guru) AS total_guru_tipe
    FROM komponen k
    LEFT JOIN isi i ON k.id_komponen = i.id_komponen
    $whereSQL
    GROUP BY k.id_komponen
    ORDER BY k.ta_komponen DESC, k.type_guru ASC
");
$stmtKomp->execute($filterParams);
$komponenList = $stmtKomp->fetchAll();

// Kelompokkan per Tahun Ajaran untuk tampilan accordion
$komponenByTA = [];
foreach ($komponenList as $k) {
    $komponenByTA[$k['ta_komponen']][] = $k;
}

// Statistik ringkasan
$statsTotalTA    = count($komponenByTA);
$statsTotalSkema = count($komponenList);
$statsTotalItem  = array_sum(array_column($komponenList, 'total_item'));

// Daftar indikator yang tersedia — hardcoded dari konstanta (tidak lagi dari tabel indikator)
$indikatorByTipe = null; // tidak dipakai lagi

// Mode edit: tampilkan detail satu komponen
$editKomponen   = null;
$isiList        = [];
$allItems       = [];
$indikatorTipe  = []; // indikator yang tersedia untuk tipe guru komponen ini
$usedItemIds    = []; // item yang sudah dipakai di indikator lain (dalam edit mode)

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM komponen WHERE id_komponen = ?");
    $stmt->execute([$id]);
    $editKomponen = $stmt->fetch();

    if ($editKomponen) {
        // Ambil isi (indikator + item) untuk komponen ini
        $stmt2 = $pdo->prepare("
            SELECT s.*, m.nama_item
            FROM isi s
            JOIN item m ON s.id_item = m.id_item
            WHERE s.id_komponen = ?
            ORDER BY s.urutan_isi, s.nomor_item
        ");
        $stmt2->execute([$id]);
        $rawIsi = $stmt2->fetchAll();

        // Kelompokkan per indikator
        foreach ($rawIsi as $row) {
            $isiList[$row['nama_indikator']][] = $row;
        }

        // Daftar indikator untuk komponen ini — sama untuk semua tipe guru
        $tipeKomponen  = $editKomponen['type_guru'];
        $indikatorTipe = INDIKATOR_LIST;

        // Indikator yang belum dipakai (belum ada di isiList)
        $indikatorTerpakai   = array_keys($isiList);
        $indikatorTersedia   = array_diff($indikatorTipe, $indikatorTerpakai);
    }

    // Semua item master untuk modal pilih
    $allItems = $pdo->query("SELECT * FROM item ORDER BY id_item ASC")->fetchAll();

    // Kumpulkan id_item yang sudah dipakai oleh indikator manapun di komponen ini
    $usedItemIds = [];
    foreach ($isiList as $namaInd => $items) {
        foreach ($items as $it) {
            $usedItemIds[] = (int)$it['id_item'];
        }
    }
}

$pageTitle = 'Buat Pertanyaan Penilaian';
require_once 'includes/header.php';
?>

<!-- notifikasi ditangani oleh toast di footer -->

<?php if ($action === 'edit' && $editKomponen): ?>
    <!-- ============================================================ -->
    <!-- MODE EDIT: Kelola indikator & item untuk satu komponen -->
    <!-- ============================================================ -->
    <div class="data-table-card" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:12px;">
            <a href="custom_penilaian.php"
                style="display:inline-flex;align-items:center;gap:8px;background:#f1f5f9;color:#374151;text-decoration:none;font-size:13px;font-weight:600;padding:8px 16px;border-radius:8px;border:1.5px solid #cbd5e1;transition:all .2s;"
                onmouseover="this.style.background='#e2e8f0';this.style.borderColor='#94a3b8';this.style.color='#111827';"
                onmouseout="this.style.background='#f1f5f9';this.style.borderColor='#cbd5e1';this.style.color='#374151';">
                ← Keluar dari Buat Pertanyaan Penilaian
            </a>
        </div>
        <div class="card-header-custom mb-3">
            <div>
                <div class="card-title-custom">✏️ Detail Pertanyaan Penilaian</div>
                <div style="margin-top:6px;display:flex;gap:12px;flex-wrap:wrap;">
                    <span style="background:#e8f5ee;color:#1a4731;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                        📅 <?= htmlspecialchars($editKomponen['ta_komponen']) ?>
                    </span>
                    <span style="background:#eff6ff;color:#1e40af;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                        👤 <?= htmlspecialchars($tipeLabels[$editKomponen['type_guru']] ?? $editKomponen['type_guru']) ?>
                    </span>
                </div>
            </div>
            <button type="button" onclick="openModalIndikator()" class="btn-primary-custom">
                + Tambah Indikator
            </button>
        </div>

        <?php
        $katIcons = [
            'Disiplin'                 => ['⏰', '#1a4731', '#e8f5ee'],
            'Pelaksanaan Pembelajaran' => ['📚', '#1e40af', '#eff6ff'],
            'Kerjasama'                => ['🤝', '#7c3aed', '#f5f3ff'],
        ];
        ?>

        <?php if (empty($isiList)): ?>
            <div style="text-align:center;padding:40px;color:#9ca3af;">
                <div style="font-size:36px;margin-bottom:12px;">📂</div>
                <p>Belum ada indikator. Tambahkan indikator dan pilih item penilaiannya.</p>
                <button type="button" onclick="openModalIndikator()" class="btn-primary-custom" style="margin-top:10px;">
                    + Tambah Indikator Pertama
                </button>
            </div>
        <?php else: ?>
            <?php
            $indIdx = 1;
            foreach ($isiList as $namaInd => $items):
                $ic = $katIcons[$namaInd] ?? ['📌', '#374151', '#f9fafb'];
            ?>
                <div style="border:1.5px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:18px;">
                    <div style="background:<?= $ic[2] ?>;border-left:5px solid <?= $ic[1] ?>;padding:12px 16px;display:flex;align-items:center;gap:12px;">
                        <span style="font-size:20px;"><?= $ic[0] ?></span>
                        <div style="flex:1;">
                            <div style="font-size:11px;color:<?= $ic[1] ?>;font-weight:600;text-transform:uppercase;opacity:.7;">Indikator <?= $indIdx++ ?></div>
                            <div style="font-size:15px;font-weight:700;color:<?= $ic[1] ?>"><?= htmlspecialchars($namaInd) ?></div>
                        </div>
                    </div>
                    <table style="width:100%;border-collapse:collapse;font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="padding:8px 14px;text-align:left;color:#6b7280;font-weight:600;width:80px;">No.</th>
                                <th style="padding:8px 14px;text-align:left;color:#6b7280;font-weight:600;">Item Penilaian</th>
                                <th style="padding:8px 14px;text-align:center;color:#6b7280;font-weight:600;width:80px;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr style="border-top:1px solid #f3f4f6;">
                                    <td style="padding:9px 14px;font-weight:600;color:<?= $ic[1] ?>;"><?= htmlspecialchars($it['nomor_item']) ?></td>
                                    <td style="padding:9px 14px;color:#374151;word-break:break-word;overflow-wrap:anywhere;white-space:normal;"><?= htmlspecialchars($it['nama_item']) ?></td>
                                    <td style="padding:9px 14px;text-align:center;">
                                        <button onclick="hapusIsi(<?= (int)$it['id_item'] ?>, <?= $id ?>, '<?= htmlspecialchars(addslashes($it['nama_indikator']), ENT_QUOTES) ?>')"
                                            class="btn-primary-custom btn-sm-custom btn-delete">🗑</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL: Tambah Indikator + Pilih Item -->
    <div id="modalIndikator" class="modal-overlay">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:680px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;">
            <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:#fff;z-index:10;">
                <div>
                    <strong style="font-size:15px;color:#1a4731;">Tambah Indikator & Pilih Item</strong>
                    <div style="font-size:12px;color:#6b7280;margin-top:2px;">Pilih indikator lalu centang item penilaian yang ingin dimasukkan</div>
                </div>
                <button onclick="closeModalIndikator()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px;line-height:1;transition:all .15s;" onmouseover="this.style.background='#f3f4f6';this.style.color='#374151'" onmouseout="this.style.background='none';this.style.color='#9ca3af'">×</button>
            </div>
            <form method="POST" style="padding:24px;" autocomplete="off">
                <input type="hidden" name="save_isi" value="1">
                <input type="hidden" name="id_komponen" value="<?= $id ?>">

                <!-- Pilih Indikator -->
                <div style="margin-bottom:18px;">
                    <label class="form-label-custom">Indikator</label>
                    <?php if (empty($indikatorTipe)): ?>
                        <div style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:13px;color:#991b1b;">
                            ⚠️ Belum ada indikator tersedia.
                        </div>
                    <?php elseif (empty($indikatorTersedia)): ?>
                        <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:10px;padding:12px 16px;font-size:13px;color:#92400e;">
                            ✅ Semua indikator sudah ditambahkan untuk komponen ini.
                        </div>
                    <?php else: ?>
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
                            <?php foreach ($indikatorTersedia as $namaInd):
                                $ic = $katIcons[$namaInd] ?? ['📌', '#374151', '#f9fafb'];
                            ?>
                                <label style="display:flex;align-items:center;gap:8px;padding:10px 12px;border:2px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.borderColor='<?= $ic[1] ?>';this.style.background='<?= $ic[2] ?>'"
                                    onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='#e5e7eb';this.style.background='#fff'}"
                                    class="indikator-option">
                                    <input type="radio" name="nama_indikator" value="<?= htmlspecialchars($namaInd) ?>"
                                        required style="accent-color:<?= $ic[1] ?>;"
                                        onchange="onIndikatorChange(this, '<?= $ic[1] ?>', '<?= $ic[2] ?>')">
                                    <span style="font-size:18px;"><?= $ic[0] ?></span>
                                    <span style="font-size:13px;font-weight:600;color:#374151;"><?= htmlspecialchars($namaInd) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pilih Item -->
                <div style="margin-bottom:18px;">
                    <label class="form-label-custom">Pilih Item Penilaian</label>
                    <div style="margin-bottom:8px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <input type="text" id="searchItem" placeholder="Cari item..." class="form-control-custom"
                            style="max-width:300px;" oninput="filterItems(this.value)">
                        <button type="button" onclick="pilihSemua()" class="btn-outline-custom">✓ Pilih Semua</button>
                        <button type="button" onclick="hapusSemua()" class="btn-danger-custom" style="padding:7px 14px;font-size:12px;">✕ Hapus Semua</button>
                    </div>
                    <div id="itemCheckboxArea" style="border:1px solid #e5e7eb;border-radius:10px;max-height:300px;overflow-y:auto;overflow-x:hidden;padding:8px;">
                        <?php foreach ($allItems as $item):
                            $isUsed = in_array((int)$item['id_item'], $usedItemIds);
                        ?>
                            <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 10px;cursor:<?= $isUsed ? 'not-allowed' : 'pointer' ?>;border-radius:6px;transition:background .15s;min-width:0;<?= $isUsed ? 'opacity:0.45;' : '' ?>"
                                <?= !$isUsed ? 'onmouseover="this.style.background=\'#f8fafc\'" onmouseout="this.style.background=\'\'"' : '' ?>
                                class="item-check-label">
                                <input type="checkbox" name="item_ids[]" value="<?= $item['id_item'] ?>"
                                    style="margin-top:2px;accent-color:#1a4731;width:16px;height:16px;flex-shrink:0;"
                                    <?= $isUsed ? 'disabled title="Item ini sudah digunakan di indikator lain"' : '' ?>>
                                <span style="font-size:13px;color:<?= $isUsed ? '#9ca3af' : '#374151' ?>;line-height:1.4;word-break:break-word;overflow-wrap:anywhere;flex:1;min-width:0;">
                                    <?= htmlspecialchars($item['nama_item']) ?>
                                    <?php if ($isUsed): ?>
                                        <span style="font-size:10px;background:#f3f4f6;color:#6b7280;padding:1px 6px;border-radius:4px;margin-left:4px;">sudah dipakai</span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                        <?php if (empty($allItems)): ?>
                            <div style="text-align:center;padding:20px;color:#9ca3af;font-size:13px;">
                                Belum ada item. <a href="item.php" target="_blank">Tambah item di sini</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div id="jumlahDipilih" style="margin-top:6px;font-size:12px;color:#1a4731;font-weight:600;"></div>
                </div>

                <div style="display:flex;gap:10px;">
                    <?php if (!empty($indikatorTipe) && !empty($indikatorTersedia)): ?>
                        <button type="submit" class="btn-primary-custom">💾 Simpan Indikator</button>
                    <?php endif; ?>
                    <button type="button" onclick="closeModalIndikator()" class="btn-cancel">Tutup</button>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- ============================================================ -->
    <!-- MODE DAFTAR: tampilkan semua custom penilaian -->
    <!-- ============================================================ -->
    <div class="data-table-card">
        <div class="card-header-custom mb-3">
            <div>
                <div class="card-title-custom">🎨 Buat Pertanyaan Penilaian</div>
                <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                    Buat skema penilaian per tahun ajaran dan tipe guru. Setiap skema bisa memiliki indikator dan item yang berbeda.
                </p>
            </div>
            <button onclick="openModalBaru()" class="btn-primary-custom">+ Buat Pertanyaan Penilaian</button>
        </div>

        <?php if (empty($komponenList) && !$filterTA && !$filterTipe): ?>
            <div style="text-align:center;padding:50px;color:#9ca3af;">
                <div style="font-size:42px;margin-bottom:14px;">🗂️</div>
                <p>Belum ada pertanyaan penilaian. Buat yang pertama!</p>
                <button onclick="openModalBaru()" class="btn-primary-custom" style="margin-top:10px;">+ Buat Sekarang</button>
            </div>
        <?php else: ?>
            <!-- Filter Bar -->
            <form method="GET" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:18px;padding:14px 16px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;">
                <input type="hidden" name="action" value="">
                <div style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:12px;font-weight:600;color:#6b7280;white-space:nowrap;">📅 Tahun Ajaran</label>
                    <select name="filter_ta" onchange="this.form.submit()"
                        style="font-size:13px;padding:7px 12px;border:1.5px solid #d1d5db;border-radius:8px;color:#374151;background:#fff;cursor:pointer;outline:none;">
                        <option value="">Semua TA</option>
                        <?php foreach ($allTA as $ta): ?>
                            <option value="<?= htmlspecialchars($ta) ?>" <?= $filterTA === $ta ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ta) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:12px;font-weight:600;color:#6b7280;white-space:nowrap;">👤 Tipe Guru</label>
                    <select name="filter_tipe" onchange="this.form.submit()"
                        style="font-size:13px;padding:7px 12px;border:1.5px solid #d1d5db;border-radius:8px;color:#374151;background:#fff;cursor:pointer;outline:none;">
                        <option value="">Semua Tipe</option>
                        <?php foreach ($tipeLabels as $kode => $label): ?>
                            <option value="<?= htmlspecialchars($kode) ?>" <?= $filterTipe === $kode ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($filterTA || $filterTipe): ?>
                    <a href="custom_penilaian.php"
                        style="font-size:12px;color:#dc2626;font-weight:600;text-decoration:none;padding:7px 12px;border:1.5px solid #fca5a5;border-radius:8px;background:#fff5f5;transition:all .15s;"
                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                        ✕ Reset Filter
                    </a>
                <?php endif; ?>
                <span style="font-size:12px;color:#9ca3af;margin-left:auto;display:flex;align-items:center;gap:10px;">
                    <button type="button" onclick="toggleAllAccordion(true)" style="background:none;border:none;color:#1a4731;font-weight:600;cursor:pointer;padding:4px 8px;font-size:12px;">⬇ Buka Semua</button>
                    <button type="button" onclick="toggleAllAccordion(false)" style="background:none;border:none;color:#6b7280;font-weight:600;cursor:pointer;padding:4px 8px;font-size:12px;">⬆ Tutup Semua</button>
                </span>
            </form>

            <?php if (empty($komponenList)): ?>
                <div style="text-align:center;padding:40px;color:#9ca3af;">
                    <div style="font-size:36px;margin-bottom:12px;">🔍</div>
                    <p style="font-size:13px;">Tidak ada pertanyaan penilaian yang sesuai filter.</p>
                    <a href="custom_penilaian.php" style="font-size:13px;color:#1a4731;font-weight:600;">Reset Filter</a>
                </div>
            <?php else: ?>
                <!-- Accordion per Tahun Ajaran — TA terbaru auto-expand -->
                <?php
                // TA terbaru = yang pertama di $komponenByTA (sudah diurut DESC)
                $firstTA = array_key_first($komponenByTA);
                ?>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach ($komponenByTA as $taLabel => $itemsInTA): ?>
                        <?php
                        $isOpen      = ($taLabel === $firstTA);
                        $totalItemTA = array_sum(array_column($itemsInTA, 'total_item'));
                        $accId       = 'acc_' . md5($taLabel);
                        ?>
                        <div class="ta-accordion" data-acc-id="<?= $accId ?>"
                            style="border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;background:#fff;">
                            <!-- Accordion Header -->
                            <button type="button" onclick="toggleAccordion('<?= $accId ?>')"
                                style="width:100%;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border:none;padding:16px 20px;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:14px;text-align:left;">
                                <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                                    <span class="acc-caret" style="font-size:14px;color:#1a4731;transition:transform .2s;<?= $isOpen ? 'transform:rotate(90deg);' : '' ?>">▶</span>
                                    <div style="display:flex;flex-direction:column;gap:2px;min-width:0;">
                                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <span style="font-size:16px;font-weight:700;color:#1a4731;">📅 <?= htmlspecialchars($taLabel) ?></span>
                                            <?php if ($taLabel === $firstTA): ?>
                                                <span style="background:#059669;color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;letter-spacing:.3px;">TERBARU</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size:12px;color:#6b7280;">
                                            <?= count($itemsInTA) ?> skema · <?= $totalItemTA ?> item penilaian
                                        </div>
                                    </div>
                                </div>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;">
                                    <?php foreach ($itemsInTA as $k): ?>
                                        <span style="background:#fff;border:1px solid #d1fae5;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;color:#065f46;">
                                            <?= htmlspecialchars($tipeLabels[$k['type_guru']] ?? $k['type_guru']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </button>

                            <!-- Accordion Body -->
                            <div class="acc-body" style="padding:16px 20px;background:#fff;<?= $isOpen ? '' : 'display:none;' ?>">
                                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
                                    <?php foreach ($itemsInTA as $k):
                                        $isDraft   = ((int)$k['total_item'] === 0);
                                        $pctDinilai = ((int)$k['total_guru_tipe'] > 0)
                                            ? round(((int)$k['total_guru_dinilai'] / (int)$k['total_guru_tipe']) * 100)
                                            : 0;
                                    ?>
                                        <div style="border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;background:#fafafa;">
                                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:10px;">
                                                <div style="font-size:14px;font-weight:700;color:#374151;">
                                                    👤 <?= htmlspecialchars($tipeLabels[$k['type_guru']] ?? $k['type_guru']) ?>
                                                </div>
                                                <?php if ($isDraft): ?>
                                                    <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;white-space:nowrap;">DRAFT</span>
                                                <?php else: ?>
                                                    <span style="background:#d1fae5;color:#065f46;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;white-space:nowrap;">SIAP</span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Stats ringkas -->
                                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
                                                <span style="background:#f3f4f6;color:#374151;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:600;">
                                                    📊 <?= (int)$k['total_indikator'] ?> indikator
                                                </span>
                                                <span style="background:#f3f4f6;color:#374151;padding:3px 9px;border-radius:10px;font-size:11px;font-weight:600;">
                                                    📋 <?= (int)$k['total_item'] ?> item
                                                </span>
                                            </div>

                                            <!-- Progress penilaian -->
                                            <?php if ((int)$k['total_guru_tipe'] > 0): ?>
                                                <div style="margin-bottom:12px;">
                                                    <div style="display:flex;justify-content:space-between;align-items:baseline;font-size:11px;color:#6b7280;margin-bottom:4px;">
                                                        <span>Progres penilaian</span>
                                                        <span><strong style="color:#1a4731;"><?= (int)$k['total_guru_dinilai'] ?></strong>/<?= (int)$k['total_guru_tipe'] ?> guru</span>
                                                    </div>
                                                    <div style="height:6px;background:#e5e7eb;border-radius:3px;overflow:hidden;">
                                                        <div style="height:100%;width:<?= $pctDinilai ?>%;background:linear-gradient(90deg,#059669,#10b981);border-radius:3px;"></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Tombol aksi -->
                                            <div style="display:flex;gap:8px;">
                                                <a href="custom_penilaian.php?action=edit&id=<?= $k['id_komponen'] ?>"
                                                    class="btn-primary-custom btn-sm-custom btn-edit" style="flex:1;text-align:center;text-decoration:none;">
                                                    ✏️ Kelola
                                                </a>
                                                <button onclick="hapusKomponen(<?= $k['id_komponen'] ?>)"
                                                    class="btn-primary-custom btn-sm-custom btn-delete">🗑</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL: Buat Custom Penilaian Baru -->
    <div id="modalBaru" class="modal-overlay">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:500px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong style="font-size:15px;color:#1a4731;">Buat Pertanyaan Penilaian Baru</strong>
                    <div style="font-size:12px;color:#6b7280;margin-top:2px;">Tentukan tahun ajaran dan tipe guru</div>
                </div>
                <button onclick="closeModalBaru()" style="background:none;border:none;font-size:22px;cursor:pointer;color:#9ca3af;padding:4px;border-radius:6px;line-height:1;transition:all .15s;" onmouseover="this.style.background='#f3f4f6';this.style.color='#374151'" onmouseout="this.style.background='none';this.style.color='#9ca3af'">×</button>
            </div>
            <form method="POST" style="padding:24px;" autocomplete="off" id="formBaru">
                <input type="hidden" name="save_komponen" value="1">
                <input type="hidden" name="ta_komponen" id="hiddenTA">
                <div style="margin-bottom:18px;">
                    <label class="form-label-custom">Tahun Ajaran <span style="color:#dc2626;">*</span></label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <select id="taDari" class="form-control-custom" required onchange="updateHiddenTA()">
                            <?php
                            $currentYear = (int)date('Y');
                            for ($y = $currentYear - 5; $y <= $currentYear + 2; $y++):
                            ?>
                                <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                        <span style="font-weight:600;color:#6b7280;white-space:nowrap;">s/d</span>
                        <select id="taSampai" class="form-control-custom" required onchange="updateHiddenTA()">
                            <?php
                            for ($y = $currentYear - 5; $y <= $currentYear + 2; $y++):
                            ?>
                                <option value="<?= $y ?>" <?= $y == $currentYear + 1 ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">
                        Pilih tahun awal dan tahun akhir periode penilaian.
                    </small>
                </div>

                <div style="margin-bottom:24px;">
                    <label class="form-label-custom">Tipe Guru</label>
                    <select name="type_guru" class="form-control-custom" required>
                        <option value="">-- Pilih Tipe Guru --</option>
                        <?php foreach ($tipeLabels as $kode => $label): ?>
                            <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn-primary-custom" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;">
                        <span style="font-size:18px;">▶️</span>
                        <span>Lanjut Atur Indikator</span>
                    </button>
                    <button type="button" onclick="closeModalBaru()" class="btn-cancel">Batal</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<style>
    .modal-overlay {
        display: none !important;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex !important;
    }

    .alert-warning-custom {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        padding: 12px 16px;
        border-radius: 8px;
        color: #92400e;
        font-size: 13px;
    }

    .alert-custom.alert-warning-custom {
        background: #fffbeb;
        color: #92400e;
    }
</style>

<script>
    // === Modal Baru ===
    function openModalBaru() {
        var m = document.getElementById('modalBaru');
        if (!m) return;
        m.classList.add('active');
        setTimeout(function() {
            var el = document.getElementById('inputTA');
            if (el) el.focus();
            updateHiddenTA();
        }, 100);
    }

    function closeModalBaru() {
        var m = document.getElementById('modalBaru');
        if (m) m.classList.remove('active');
    }

    function updateHiddenTA() {
        const dari = document.getElementById('taDari').value;
        const sampai = document.getElementById('taSampai').value;
        document.getElementById('hiddenTA').value = dari + '/' + sampai;
    }

    // === Modal Indikator ===
    function openModalIndikator() {
        var m = document.getElementById('modalIndikator');
        if (!m) return;
        m.classList.add('active');
        updateJumlahDipilih();
    }

    function closeModalIndikator() {
        var m = document.getElementById('modalIndikator');
        if (m) m.classList.remove('active');
    }

    // Pasang event backdrop-click setelah DOM siap
    document.addEventListener('DOMContentLoaded', function() {
        var mBaru = document.getElementById('modalBaru');
        if (mBaru) mBaru.addEventListener('click', function(e) {
            if (e.target === this) closeModalBaru();
        });
        var mInd = document.getElementById('modalIndikator');
        if (mInd) mInd.addEventListener('click', function(e) {
            if (e.target === this) closeModalIndikator();
        });
        // Pasang listener checkbox
        document.querySelectorAll('#itemCheckboxArea input[type=checkbox]').forEach(function(cb) {
            cb.addEventListener('change', updateJumlahDipilih);
        });
    });

    // Highlight label indikator saat dipilih
    function onIndikatorChange(radio, color, bg) {
        // Reset semua label
        document.querySelectorAll('.indikator-option').forEach(lbl => {
            lbl.style.borderColor = '#e5e7eb';
            lbl.style.background = '#fff';
        });
        // Highlight yang dipilih
        const lbl = radio.closest('.indikator-option');
        if (lbl) {
            lbl.style.borderColor = color;
            lbl.style.background = bg;
        }
    }

    // === Filter item ===
    function filterItems(q) {
        q = q.toLowerCase();
        document.querySelectorAll('.item-check-label').forEach(label => {
            const txt = label.querySelector('span').textContent.toLowerCase();
            label.style.display = txt.includes(q) ? '' : 'none';
        });
    }

    function pilihSemua() {
        document.querySelectorAll('#itemCheckboxArea input[type=checkbox]').forEach(cb => {
            const lbl = cb.closest('.item-check-label');
            if (!lbl || lbl.style.display === 'none') return;
            cb.checked = true;
        });
        updateJumlahDipilih();
    }

    function hapusSemua() {
        document.querySelectorAll('#itemCheckboxArea input[type=checkbox]').forEach(cb => {
            const lbl = cb.closest('.item-check-label');
            if (!lbl || lbl.style.display === 'none') return;
            cb.checked = false;
        });
        updateJumlahDipilih();
    }

    function updateJumlahDipilih() {
        let n = 0;
        document.querySelectorAll('#itemCheckboxArea input[type=checkbox]:checked').forEach(cb => {
            const lbl = cb.closest('.item-check-label');
            if (!lbl || lbl.style.display === 'none') return;
            n++;
        });
        const el = document.getElementById('jumlahDipilih');
        if (el) el.textContent = n > 0 ? `✓ ${n} item dipilih` : '';
    }

    // === Auto-open modal setelah error validasi (POST) ===
    <?php if ($openModalOnLoad): ?>
        window.addEventListener('DOMContentLoaded', function() {
            var modalId = '<?= htmlspecialchars($openModalOnLoad) ?>';
            var el = document.getElementById(modalId);
            if (el) el.classList.add('active');
        });
    <?php endif; ?>

    function hapusIsi(idItem, idKomponen, namaInd) {
        if (!confirm('Hapus item ini dari indikator?')) return;
        var url = 'custom_penilaian.php?action=delete_isi'
                + '&id_komponen=' + idKomponen
                + '&id_item=' + idItem
                + '&nama_indikator=' + encodeURIComponent(namaInd);
        window.location.href = url;
    }

    function hapusKomponen(id) {
        if (!confirm('Hapus seluruh custom penilaian ini beserta semua indikator dan item-nya?')) return;
        window.location.href = 'custom_penilaian.php?action=delete&id=' + id;
    }

    // ── Accordion grup Tahun Ajaran ─────────────────────────────
    function toggleAccordion(accId) {
        const wrap = document.querySelector('[data-acc-id="' + accId + '"]');
        if (!wrap) return;
        const body  = wrap.querySelector('.acc-body');
        const caret = wrap.querySelector('.acc-caret');
        if (!body) return;
        const isHidden = body.style.display === 'none';
        body.style.display    = isHidden ? 'block' : 'none';
        if (caret) caret.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
    }

    function toggleAllAccordion(open) {
        document.querySelectorAll('.ta-accordion').forEach(wrap => {
            const body  = wrap.querySelector('.acc-body');
            const caret = wrap.querySelector('.acc-caret');
            if (body)  body.style.display    = open ? 'block' : 'none';
            if (caret) caret.style.transform = open ? 'rotate(90deg)' : 'rotate(0deg)';
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>