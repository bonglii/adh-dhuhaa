<?php
/**
 * penilaian.php — Halaman Penilaian Kinerja Guru
 *
 * Alur (FIXED):
 *  1. Pilih Nama Guru langsung (semua guru tampil, tipe guru terisi otomatis)
 *  2. Dropdown Tahun Ajaran muncul otomatis sesuai tipe guru yang bersangkutan
 *  3. Form penilaian auto-load dari tabel isi → item
 *  4. Nilai disimpan ke tabel hasil (id_item) — FIXED dari id_item
 */

require_once 'includes/config.php';
requireLogin();

$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM penilaian WHERE id_penilaian=?")->execute([$id]);
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil dihapus!'));
        exit;
    } catch (PDOException $e) {
        error_log('[penilaian.php] Gagal hapus penilaian id=' . $id . ': ' . $e->getMessage());
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode('⚠️ Gagal menghapus penilaian: ' . $e->getMessage()));
        exit;
    }
}

if ($action === 'delete_all') {
    // Wajib POST agar tidak bisa dipicu hanya dari URL/link
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        session_write_close();
        header('Location: penilaian.php');
        exit;
    }
    $dWhereAll  = ['1=1'];
    $dParamsAll = [];
    $dTA   = sanitize($_POST['filter_ta']   ?? '');
    $dTipe = sanitize($_POST['filter_tipe'] ?? '');
    $dGuru = (int)($_POST['filter_guru']    ?? 0);
    if ($dTA)   { $dWhereAll[] = 'periode = ?';              $dParamsAll[] = $dTA; }
    if ($dTipe) { $dWhereAll[] = 'id_guru IN (SELECT id_guru FROM guru WHERE tipe = ?)'; $dParamsAll[] = $dTipe; }
    if ($dGuru) { $dWhereAll[] = 'id_guru = ?';              $dParamsAll[] = $dGuru; }
    $dSQL = 'DELETE FROM penilaian WHERE ' . implode(' AND ', $dWhereAll);
    try {
        $pdo->prepare($dSQL)->execute($dParamsAll);
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil dihapus!'));
        exit;
    } catch (PDOException $e) {
        error_log('[penilaian.php] Gagal hapus semua penilaian: ' . $e->getMessage());
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode('⚠️ Gagal menghapus: ' . $e->getMessage()));
        exit;
    }
}

if ($action === 'delete_selected' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
    if ($ids) {
        $pl = implode(',', array_fill(0, count($ids), '?'));
        try {
            $pdo->prepare("DELETE FROM penilaian WHERE id_penilaian IN ($pl)")->execute($ids);
            session_write_close();
            header('Location: penilaian.php?msg=' . urlencode(count($ids) . ' penilaian berhasil dihapus!'));
            exit;
        } catch (PDOException $e) {
            error_log('[penilaian.php] Gagal hapus penilaian terpilih: ' . $e->getMessage());
            session_write_close();
            header('Location: penilaian.php?msg=' . urlencode('⚠️ Gagal menghapus: ' . $e->getMessage()));
            exit;
        }
    }
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

// ================================================================
// SIMPAN PENILAIAN — FIXED: simpan ke id_item bukan id_item
// ================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_penilaian'])) {
    $id_guru     = (int)($_POST['id_guru'] ?? 0);
    $id_komponen = (int)($_POST['id_komponen'] ?? 0);
    $tgl         = $_POST['tanggal_penilaian'] ?? '';
    $penilai     = sanitize($_POST['penilai'] ?? '');
    $jab_pen     = sanitize($_POST['jabatan_penilai'] ?? '');
    $catatan     = sanitize($_POST['catatan'] ?? '');
    $nilai_data  = $_POST['nilai'] ?? [];  // key = id_item

    $periode = '';
    if ($id_komponen) {
        $stmt = $pdo->prepare("SELECT ta_komponen FROM komponen WHERE id_komponen = ?");
        $stmt->execute([$id_komponen]);
        $periode = $stmt->fetchColumn() ?: '';
    }

    if (!$id_guru || !$id_komponen || !$tgl) {
        $msg = 'Data tidak lengkap! Guru, tahun ajaran, dan tanggal wajib diisi.';
    } else {
        $pdo->beginTransaction();
        try {
            if ($action === 'edit' && $id) {
                $stmt = $pdo->prepare("UPDATE penilaian SET id_guru=?,id_komponen=?,periode=?,tanggal_penilaian=?,penilai=?,jabatan_penilai=?,catatan=? WHERE id_penilaian=?");
                $stmt->execute([$id_guru, $id_komponen, $periode, $tgl, $penilai, $jab_pen, $catatan, $id]);
                $pdo->prepare("DELETE FROM hasil WHERE id_penilaian=?")->execute([$id]);
                $pen_id = $id;
            } else {
                $stmt = $pdo->prepare("INSERT INTO penilaian (id_guru,id_komponen,periode,tanggal_penilaian,penilai,jabatan_penilai,catatan) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$id_guru, $id_komponen, $periode, $tgl, $penilai, $jab_pen, $catatan]);
                $pen_id = $pdo->lastInsertId();
            }
            // FIXED: simpan ke kolom id_item
            if ($nilai_data) {
                $ins = $pdo->prepare("INSERT INTO hasil (id_penilaian, id_item, nilai) VALUES (?,?,?)");
                foreach ($nilai_data as $iid => $nilai) {
                    $ins->execute([$pen_id, (int)$iid, (int)$nilai]);
                }
            }
            $pdo->commit();
            session_write_close();
            header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil disimpan!'));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// ================================================================
// FILTER — ambil dari GET, default kosong (tampilkan semua)
// ================================================================
$filterTA   = sanitize($_GET['filter_ta']   ?? '');
$filterTipe = sanitize($_GET['filter_tipe'] ?? '');
$filterGuru = (int)($_GET['filter_guru']    ?? 0);

// Daftar tahun ajaran yang tersedia (untuk dropdown filter)
$listTA = $pdo->query("
    SELECT DISTINCT ta_komponen FROM komponen ORDER BY ta_komponen DESC
")->fetchAll(PDO::FETCH_COLUMN);

// Bangun query dengan kondisi filter dinamis
$where  = ['1=1'];
$params = [];

if ($filterTA !== '') {
    $where[]  = 'p.periode = ?';
    $params[] = $filterTA;
}
if ($filterTipe !== '') {
    $where[]  = 'g.tipe = ?';
    $params[] = $filterTipe;
}
if ($filterGuru > 0) {
    $where[]  = 'p.id_guru = ?';
    $params[] = $filterGuru;
}

$whereSQL = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT p.*, g.nama, g.jabatan, g.tipe,
    (
        SELECT ROUND(AVG(ind_pct), 1)
        FROM (
            SELECT
                SUM(dp2.nilai) / NULLIF(COUNT(*) * 5, 0) * 100 AS ind_pct
            FROM hasil dp2
            JOIN isi s ON dp2.id_item = s.id_item
                      AND s.id_komponen = p.id_komponen
            WHERE dp2.id_penilaian = p.id_penilaian
            GROUP BY s.nama_indikator
        ) ind_scores
    ) as rata_nilai
    FROM penilaian p JOIN guru g ON p.id_guru = g.id_guru
    WHERE $whereSQL
    ORDER BY p.created_at DESC
");
$stmt->execute($params);
$penilaianList = $stmt->fetchAll();

// Semua guru (tidak digroup) untuk dropdown utama
$guruAll = $pdo->query("
    SELECT g.id_guru, g.nama, g.jabatan, g.tipe, tg.label AS tipe_label
    FROM guru g LEFT JOIN tipe_guru tg ON g.tipe = tg.kode
    ORDER BY tg.urutan, g.nama
")->fetchAll();

// Dikelompokkan per tipe — dipakai JS untuk filter dropdown guru saat tipe dipilih
$guruByTipe = [];
foreach ($guruAll as $g) {
    $guruByTipe[$g['tipe']][] = [
        'id_guru' => (int)$g['id_guru'],
        'nama'    => $g['nama'],
        'jabatan' => $g['jabatan'],
    ];
}

$tipeLabels = getTipeGuru($pdo);

// Ambil semua penilaian yang sudah ada (untuk cek duplikat guru+tahun ajaran)
$existingPenilaian = $pdo->query("
    SELECT p.id_guru, p.id_komponen, k.ta_komponen, g.nama as nama_guru
    FROM penilaian p
    JOIN komponen k ON p.id_komponen = k.id_komponen
    JOIN guru g ON p.id_guru = g.id_guru
")->fetchAll();
$existingMap = [];
foreach ($existingPenilaian as $ep) {
    $existingMap[$ep['id_guru']][] = [
        'id_komponen' => $ep['id_komponen'],
        'ta_komponen' => $ep['ta_komponen'],
    ];
}

$editPenilaian = null;
$editDetail    = [];

if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT p.*, g.tipe FROM penilaian p JOIN guru g ON p.id_guru=g.id_guru WHERE p.id_penilaian=?");
    $stmt->execute([$id]);
    $editPenilaian = $stmt->fetch();
    // FIXED: baca id_item
    $rows = $pdo->prepare("SELECT id_item, nilai FROM hasil WHERE id_penilaian=?");
    $rows->execute([$id]);
    foreach ($rows->fetchAll() as $r) $editDetail[$r['id_item']] = $r['nilai'];
}

/**
 * renderKomponenHtml — Render HTML form penilaian server-side.
 *
 * Dipakai di mode edit (ketika penilaian sudah ada dan perlu pre-load).
 * Mode tambah baru pakai render client-side via AJAX (lihat loadFormPenilaian di JS).
 *
 * @param  array $isiByInd    Item penilaian dikelompokkan per nama indikator.
 *                            Struktur: ['Disiplin' => [item1, item2], 'Kerjasama' => [...]]
 * @param  array $editDetail  Map id_item => nilai, dipakai untuk prefill radio button
 * @return string             HTML form penilaian siap echo
 */
function renderKomponenHtml(array $isiByInd, array $editDetail = []): string {
    if (empty($isiByInd)) {
        return '<div style="text-align:center;padding:30px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;">
            <p>Belum ada item pada custom penilaian ini.<br>
            <a href="custom_penilaian.php" style="color:#1a4731;">Atur custom penilaian</a></p></div>';
    }
    $katIcons = [
        'Disiplin'                 => ['⏰','#1a4731','#e8f5ee'],
        'Pelaksanaan Pembelajaran' => ['📚','#1e40af','#eff6ff'],
        'Kerjasama'                => ['🤝','#7c3aed','#f5f3ff'],
    ];
    $html = '<div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border-radius:12px;padding:14px 18px;border-left:4px solid var(--hijau);">
            <span style="font-size:18px;">📋</span>
            <div><strong style="font-size:14px;color:var(--hijau);display:block;">Komponen Penilaian</strong>
            <span style="font-size:11.5px;color:#6b7280;">Skala: 1=Kurang | 2=Cukup | 3=Baik | 4=Sangat Baik | 5=Sangat Baik Sekali</span></div>
        </div></div>';
    $skalaLabels = [1=>'Kurang',2=>'Cukup',3=>'Baik',4=>'Sangat Baik',5=>'Sangat Baik Sekali'];
    $katNo = 1;
    foreach ($isiByInd as $namaInd => $items) {
        $ic = $katIcons[$namaInd] ?? ['📌','#374151','#f9fafb'];
        $indId = 'ind_'.$katNo;
        $html .= '<div class="nilai-group" data-indikator="'.htmlspecialchars($namaInd).'" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
            <div class="nilai-kategori" style="background:'.$ic[2].';border-left:5px solid '.$ic[1].';padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;flex-wrap:wrap;">
                <span style="font-size:20px;">'.$ic[0].'</span>
                <div style="flex:1;min-width:160px;"><span style="font-size:11px;color:'.$ic[1].';font-weight:600;text-transform:uppercase;opacity:0.7;">Indikator '.$katNo.' <span class="ind-progress" style="font-weight:500;opacity:0.8;"></span></span>
                <div style="font-size:14px;font-weight:700;color:'.$ic[1].';">'.htmlspecialchars($namaInd).'</div></div>
                <div class="set-semua-wrap" style="display:flex;gap:4px;align-items:center;font-size:11px;color:#6b7280;">
                    <span style="margin-right:4px;">Set semua:</span>';
        for ($sv=1;$sv<=5;$sv++) {
            $html .= '<button type="button" class="btn-set-semua" data-val="'.$sv.'" title="Set semua item indikator ini ke '.$sv.' ('.$skalaLabels[$sv].')" style="width:26px;height:26px;border:1px solid '.$ic[1].';background:#fff;color:'.$ic[1].';border-radius:6px;font-weight:600;cursor:pointer;font-size:11px;">'.$sv.'</button>';
        }
        $html .= '</div></div>';
        $katNo++;
        foreach ($items as $item) {
            $html .= '<div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;overflow:hidden;align-items:flex-start;">
                <div class="nilai-item-label" style="word-break:break-all;overflow-wrap:anywhere;white-space:normal;flex:1;min-width:0;"><span class="nilai-item-num">'.htmlspecialchars($item['nomor_item']).'.</span> '.htmlspecialchars($item['nama_item']).'</div>
                <div class="nilai-radio-group">';
            for ($v=1;$v<=5;$v++) {
                $iid  = $item['id_item'];
                $savedV = $editDetail[$iid] ?? null;
                $chk  = ($savedV !== null && $savedV == $v) ? 'checked' : '';
                $html .= '<input type="radio" name="nilai['.$iid.']" id="v'.$iid.'_'.$v.'" value="'.$v.'" '.$chk.'>';
                $html .= '<label for="v'.$iid.'_'.$v.'" title="'.$v.' = '.$skalaLabels[$v].'">'.$v.'</label>';
            }
            $html .= '</div></div>';
        }
        $html .= '</div>';
    }
    return $html;
}

$pageTitle = 'Penilaian Kinerja Guru';
require_once 'includes/header.php';
?>

<!-- notifikasi ditangani oleh toast di footer -->

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="data-table-card">
    <div class="card-header-custom mb-4">
        <div class="card-title-custom"><?= $action==='edit'?'✏️ Edit':'➕ Tambah'?> Penilaian Kinerja Guru</div>
        <a href="penilaian.php" class="btn-back">← Kembali</a>
    </div>

    <?php if ($msg): ?>
    <div style="background:#fef2f2;border-left:4px solid #dc2626;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b;font-size:13px;display:flex;align-items:center;gap:8px;">
        <span style="font-size:16px;">⚠️</span>
        <span><?= htmlspecialchars($msg) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="penilaian.php?action=<?= $action ?>&id=<?= $id ?>" autocomplete="off">
        <style>
            /* Dropdown yang terkunci menunggu dependensi terisi dulu */
            .form-locked:disabled {
                background: #f3f4f6 !important;
                color: #9ca3af !important;
                cursor: not-allowed !important;
                opacity: 0.85;
                border-style: dashed !important;
            }
        </style>
        <input type="hidden" name="save_penilaian" value="1">
        <input type="hidden" name="action" value="<?= $action ?>">
        <?php if ($editPenilaian): ?>
        <input type="hidden" name="id_guru" value="<?= (int)$editPenilaian['id_guru'] ?>">
        <?php endif; ?>

        <?php
        // ─── Prefill dari rekap.php: ?action=add&id_guru=X&tipe=Y ───────
        // Saat user klik "Nilai" di rekap, isi otomatis Tipe Guru & Nama Guru.
        $prefillIdGuru = ($action === 'add' && !$editPenilaian) ? (int)($_GET['id_guru'] ?? 0) : 0;
        $prefillTipe   = ($action === 'add' && !$editPenilaian) ? sanitize($_GET['tipe'] ?? '') : '';
        // Validasi: tipe harus valid, dan id_guru harus benar-benar bertipe itu
        if ($prefillIdGuru && $prefillTipe) {
            $stmtCk = $pdo->prepare("SELECT 1 FROM guru WHERE id_guru = ? AND tipe = ?");
            $stmtCk->execute([$prefillIdGuru, $prefillTipe]);
            if (!$stmtCk->fetchColumn()) {
                $prefillIdGuru = 0;
                $prefillTipe   = '';
            }
        }

        $editTipeGuru      = $editPenilaian['tipe'] ?? $prefillTipe;
        $editTipeGuruLabel = $tipeLabels[$editTipeGuru] ?? $editTipeGuru;
        ?>

        <!-- LANGKAH 1: Tipe Guru → Tahun Ajaran → Nama Guru -->
        <div style="background:#f8fafc;border-radius:12px;padding:18px 20px;margin-bottom:22px;border-left:4px solid var(--hijau);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;gap:12px;flex-wrap:wrap;">
                <div style="font-size:12px;font-weight:700;color:var(--hijau);text-transform:uppercase;letter-spacing:.5px;">
                    Langkah 1 dari 3 — Pilih Guru Yang Dinilai
                </div>
                <a href="custom_penilaian.php"
                    style="font-size:11px;color:var(--hijau);text-decoration:none;background:#fff;border:1px solid #d1fae5;padding:4px 10px;border-radius:6px;font-weight:500;">
                    Kelola Custom Penilaian
                </a>
            </div>
            <div class="row g-3">

                <!-- TIPE GURU — dipilih pertama, mempersempit daftar guru -->
                <div class="col-md-3">
                    <div class="form-label-custom">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:var(--hijau);color:#fff;border-radius:50%;font-size:10px;font-weight:700;margin-right:6px;vertical-align:middle;">1</span>
                        Tipe Guru <span style="color:#dc2626;">*</span>
                    </div>
                    <select id="sel_tipe" class="form-control-custom" required
                        onchange="onTipeChange(this.value)"
                        <?= $editPenilaian ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Tipe Guru --</option>
                        <?php foreach ($tipeLabels as $kode => $label): ?>
                            <option value="<?= htmlspecialchars($kode) ?>"
                                <?= ($editTipeGuru === $kode) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($editPenilaian): ?>
                        <small style="color:#9ca3af;font-size:11px;margin-top:3px;display:block;">(tidak bisa diubah saat edit)</small>
                    <?php endif; ?>
                </div>

                <!-- TAHUN AJARAN — muncul setelah tipe dipilih via AJAX -->
                <div class="col-md-3">
                    <div class="form-label-custom">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:var(--hijau);color:#fff;border-radius:50%;font-size:10px;font-weight:700;margin-right:6px;vertical-align:middle;">2</span>
                        Tahun Ajaran <span style="color:#dc2626;">*</span>
                    </div>
                    <select name="id_komponen" id="sel_komponen" class="form-control-custom form-locked" required
                        onchange="onKomponenChange(this.value)"
                        <?= $editPenilaian ? '' : 'disabled' ?>>
                        <?php if ($editPenilaian && $editPenilaian['id_komponen']): ?>
                            <?php
                            $ks = $pdo->prepare("SELECT id_komponen, ta_komponen FROM komponen WHERE type_guru = ? ORDER BY id_komponen DESC");
                            $ks->execute([$editTipeGuru]);
                            echo '<option value="">-- Pilih Tahun Ajaran --</option>';
                            foreach ($ks->fetchAll() as $k):
                            ?>
                            <option value="<?= $k['id_komponen'] ?>"
                                <?= $k['id_komponen'] == $editPenilaian['id_komponen'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['ta_komponen']) ?>
                            </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">-- Pilih tipe dulu --</option>
                        <?php endif; ?>
                    </select>
                    <small id="hint_komponen" style="color:#9ca3af;font-size:11px;margin-top:4px;display:<?= $editPenilaian ? 'none' : 'block' ?>;">
                        Pilih tipe guru terlebih dahulu
                    </small>
                </div>

                <!-- NAMA GURU — terfilter otomatis sesuai tipe yang dipilih -->
                <div class="col-md-6">
                    <div class="form-label-custom">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:var(--hijau);color:#fff;border-radius:50%;font-size:10px;font-weight:700;margin-right:6px;vertical-align:middle;">3</span>
                        Nama Guru <span style="color:#dc2626;">*</span>
                    </div>
                    <select name="id_guru" id="sel_guru" class="form-control-custom form-locked" required
                        onchange="onGuruChange(this.value)"
                        <?= $editPenilaian ? 'disabled' : '' ?>>
                        <?php if ($editPenilaian): ?>
                            <?php foreach ($guruAll as $g): if ($g['tipe'] !== $editTipeGuru) continue; ?>
                                <option value="<?= (int)$g['id_guru'] ?>"
                                    <?= ($editPenilaian['id_guru'] == $g['id_guru']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['nama']) ?>
                                    <?= $g['jabatan'] ? ' (' . $g['jabatan'] . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">-- Pilih tipe dulu --</option>
                        <?php endif; ?>
                    </select>
                    <small id="hint_guru" style="color:#9ca3af;font-size:11px;margin-top:4px;display:<?= $editPenilaian ? 'none' : 'block' ?>;">
                        Daftar guru akan muncul setelah tipe guru dipilih
                    </small>
                </div>

            </div>
        </div>

        <!-- LANGKAH 2: Info Penilaian -->
        <div style="background:#f8fafc;border-radius:12px;padding:18px 20px;margin-bottom:22px;border-left:4px solid #6366f1;">
            <div style="font-size:12px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">
                Langkah 2 dari 3 — Informasi Penilaian
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="form-label-custom">Tanggal Penilaian</div>
                    <input type="date" name="tanggal_penilaian" class="form-control-custom"
                        value="<?= $editPenilaian['tanggal_penilaian'] ?? date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-5">
                    <div class="form-label-custom">Nama Penilai</div>
                    <input type="text" name="penilai" class="form-control-custom" readonly
                        style="background:#f3f4f6;color:#555;"
                        value="<?= htmlspecialchars($editPenilaian['penilai'] ?? 'Hasyim Ashari, S.T') ?>">
                </div>
                <div class="col-md-4">
                    <div class="form-label-custom">Jabatan Penilai</div>
                    <input type="text" name="jabatan_penilai" class="form-control-custom" readonly
                        style="background:#f3f4f6;color:#555;"
                        value="<?= htmlspecialchars($editPenilaian['jabatan_penilai'] ?? 'Kepala Sekolah') ?>">
                </div>
            </div>
        </div>

        <!-- PANEL PROGRESS — sticky, auto-update saat radio berubah -->
        <div id="progressPanel" style="display:none;position:sticky;top:0;z-index:20;background:#fff;border:1.5px solid #d1fae5;border-radius:12px;padding:12px 18px;margin-bottom:14px;box-shadow:0 4px 12px rgba(26,71,49,0.08);">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div style="flex:1;min-width:200px;">
                    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px;font-size:12px;color:#374151;">
                        <span>Progres Pengisian: <strong id="progFilled" style="color:var(--hijau);">0</strong> / <span id="progTotal">0</span> item</span>
                        <span id="progPercentWrap" style="font-size:11px;color:#6b7280;">(<span id="progPercent">0</span>%)</span>
                    </div>
                    <div style="height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                        <div id="progBar" style="height:100%;width:0%;background:linear-gradient(90deg,var(--hijau),var(--hijau-muda));border-radius:4px;transition:width 0.3s ease;"></div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;padding-left:14px;border-left:1.5px solid #e5e7eb;">
                    <div>
                        <div style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;font-weight:600;">Nilai Sementara</div>
                        <div style="font-size:20px;font-weight:700;line-height:1.1;" id="progNilaiWrap"><span id="progNilai" style="color:#9ca3af;">—</span><span style="font-size:13px;color:#6b7280;font-weight:500;" id="progPredikat"></span></div>
                    </div>
                    <button type="button" id="btnJumpUnfilled" onclick="jumpToUnfilled()" style="display:none;padding:6px 12px;background:#fef3c7;color:#92400e;border:1px solid #fbbf24;border-radius:8px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">↓ Ke item belum terisi</button>
                </div>
            </div>
        </div>

        <!-- LANGKAH 3: Form Penilaian (auto-load setelah pilih tahun ajaran) -->
        <div id="komponen-area">
            <?php
            if ($editPenilaian && $editPenilaian['id_komponen']) {
                $isiStmt = $pdo->prepare("
                    SELECT s.*, m.nama_item
                    FROM isi s JOIN item m ON s.id_item = m.id_item
                    WHERE s.id_komponen = ? ORDER BY s.urutan_isi, s.nomor_item
                ");
                $isiStmt->execute([$editPenilaian['id_komponen']]);
                $isiByInd = [];
                foreach ($isiStmt->fetchAll() as $row) $isiByInd[$row['nama_indikator']][] = $row;
                echo renderKomponenHtml($isiByInd, $editDetail);
            } else {
                echo '<div id="placeholder-komponen" style="text-align:center;padding:40px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;">
                    <div style="font-size:36px;margin-bottom:12px;">📋</div>
                    <p>Pilih nama guru dan tahun ajaran untuk menampilkan form penilaian.</p></div>';
            }
            ?>
        </div>

        <div class="row g-3 mt-2">
            <div class="col-12">
                <div class="form-label-custom">Catatan / Rekomendasi</div>
                <textarea name="catatan" class="form-control-custom" rows="4"
                    placeholder="Tuliskan catatan evaluasi..."><?= htmlspecialchars($editPenilaian['catatan']??'') ?></textarea>
            </div>
        </div>
        <div class="d-flex gap-3 mt-4">
            <button type="submit" class="btn-primary-custom">💾 Simpan Penilaian</button>
            <a href="penilaian.php" class="btn-cancel">Batal</a>
        </div>
    </form>
</div>

<script>
const savedNilai = <?= json_encode($editDetail ?: new stdClass()) ?>;
const isEditMode = <?= $action==='edit' ? 'true' : 'false' ?>;
const tipeLabels = <?= json_encode($tipeLabels) ?>;
const existingPenilaian = <?= json_encode($existingMap) ?>;
const guruByTipe = <?= json_encode($guruByTipe) ?>;

// ── Pilih Tipe Guru → filter daftar guru + load Tahun Ajaran via AJAX ──────────
function onTipeChange(tipe) {
    const selGuru = document.getElementById('sel_guru');
    const selK    = document.getElementById('sel_komponen');
    const hintK   = document.getElementById('hint_komponen');
    const hintG   = document.getElementById('hint_guru');

    resetKomponenArea();
    hideDuplicateWarning();

    // Reset dropdown guru sesuai tipe
    if (!tipe) {
        selGuru.innerHTML = '<option value="">-- Pilih tipe dulu --</option>';
        selGuru.disabled  = true;
        selK.innerHTML    = '<option value="">-- Pilih tipe dulu --</option>';
        selK.disabled     = true;
        if (hintK) { hintK.textContent = 'Pilih tipe guru terlebih dahulu'; hintK.style.display = 'block'; }
        if (hintG) { hintG.textContent = 'Daftar guru akan muncul setelah tipe guru dipilih'; hintG.style.display = 'block'; }
        return;
    }

    const list = guruByTipe[tipe] || [];
    if (!list.length) {
        selGuru.innerHTML = '<option value="">Belum ada guru untuk tipe ini.</option>';
        selGuru.disabled  = true;
        if (hintG) { hintG.textContent = 'Belum ada data guru untuk tipe ini.'; hintG.style.display = 'block'; }
    } else {
        let html = '<option value="">-- Pilih Nama Guru --</option>';
        list.forEach(g => {
            const jab = g.jabatan ? ' (' + g.jabatan + ')' : '';
            html += '<option value="' + g.id_guru + '">' + escapeHtml(g.nama) + escapeHtml(jab) + '</option>';
        });
        selGuru.innerHTML = html;
        selGuru.disabled  = false;
        if (hintG) hintG.style.display = 'none';
    }

    // Load Tahun Ajaran untuk tipe ini
    selK.innerHTML = '<option value="">Memuat...</option>';
    selK.disabled  = true;
    if (hintK) { hintK.textContent = 'Memuat daftar tahun ajaran...'; hintK.style.display = 'block'; }

    fetch('api_custom_komponen.php?tipe=' + encodeURIComponent(tipe))
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                selK.innerHTML = '<option value="">Belum ada tahun ajaran untuk tipe ini.</option>';
                if (hintK) { hintK.textContent = 'Belum ada data. Buat di Kelola Custom Penilaian ↗'; hintK.style.display = 'block'; }
                return;
            }
            selK.innerHTML = '<option value="">-- Pilih Tahun Ajaran --</option>';
            data.forEach(k => {
                const o       = document.createElement('option');
                o.value       = k.id_komponen;
                o.textContent = k.ta_komponen;
                selK.appendChild(o);
            });
            selK.disabled = false;
            if (hintK) hintK.style.display = 'none';
        })
        .catch(() => {
            selK.innerHTML = '<option value="">Gagal memuat data.</option>';
            if (hintK) { hintK.textContent = 'Gagal memuat. Coba muat ulang halaman.'; hintK.style.display = 'block'; }
        });
}

// ── Pilih Guru → cek duplikat (kalau tahun ajaran sudah terisi) ──────────
function onGuruChange(id_guru) {
    hideDuplicateWarning();
    resetKomponenArea();
    const id_komponen = document.getElementById('sel_komponen').value;
    if (id_guru && id_komponen) checkDuplicateAndLoad(id_guru, id_komponen);
}

function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

function showDuplicateWarning(ta) {
    let el = document.getElementById('dupWarning');
    if (!el) {
        el = document.createElement('div');
        el.id = 'dupWarning';
        const selKWrap = document.getElementById('sel_komponen').closest('.col-md-3');
        selKWrap.appendChild(el);
    }
    el.innerHTML = `<div style="margin-top:8px;display:flex;align-items:flex-start;gap:10px;background:#fffbeb;border:1.5px solid #f59e0b;border-radius:10px;padding:10px 14px;">
        <span style="font-size:18px;flex-shrink:0;">⚠️</span>
        <div>
            <div style="font-size:12px;font-weight:700;color:#92400e;">Guru sudah pernah dinilai di tahun ajaran <strong>${ta}</strong>.</div>
            <div style="font-size:11px;color:#78350f;margin-top:2px;">Penilaian baru tetap bisa dibuat. Klik tombol di bawah untuk melanjutkan.</div>
            <button type="button" onclick="dismissAndLoad()" style="margin-top:6px;padding:4px 12px;background:#f59e0b;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;">▶ Tetap Lanjutkan</button>
        </div>
    </div>`;
    el.style.display = '';
}

function hideDuplicateWarning() {
    const el = document.getElementById('dupWarning');
    if (el) el.style.display = 'none';
}

function dismissAndLoad() {
    hideDuplicateWarning();
    const id_komponen = document.getElementById('sel_komponen').value;
    if (id_komponen) loadFormPenilaian(id_komponen);
}

// ── Pilih Tahun Ajaran → cek duplikat lalu tampilkan form penilaian ──────────
function onKomponenChange(id_komponen) {
    hideDuplicateWarning();

    // ─── Update marker "✓ sudah dinilai" di setiap option dropdown Nama Guru ───
    // Berjalan setiap kali user ganti tahun ajaran, supaya marker selalu
    // mencerminkan tahun ajaran yang aktif sekarang.
    updateGuruMarkers(id_komponen);

    if (!id_komponen) { resetKomponenArea(); return; }

    const selGuru = document.getElementById('sel_guru');
    const id_guru = selGuru ? selGuru.value : '';

    // Kalau guru belum dipilih, diam saja — tunggu guru dipilih
    if (!id_guru) { resetKomponenArea(); return; }

    checkDuplicateAndLoad(id_guru, id_komponen);
}

/**
 * Tempel/lepas marker "✓ sudah dinilai di TA ini" pada setiap opsi dropdown
 * Nama Guru, berdasarkan tahun ajaran (komponen) yang sedang dipilih.
 *
 * @param {string|number} komponenId  id_komponen yang sedang dipilih (kosong = lepas semua marker)
 */
function updateGuruMarkers(komponenId) {
    const selGuru = document.getElementById('sel_guru');
    if (!selGuru) return;
    const MARKER = '  •  ✓ sudah dinilai di TA ini';

    Array.from(selGuru.options).forEach(opt => {
        // Buang marker lama (kalau ada) supaya tidak menumpuk
        let txt = opt.text;
        if (txt.endsWith(MARKER)) {
            txt = txt.slice(0, -MARKER.length);
        }
        // Tambah marker kalau guru ini punya penilaian di komponen yang dipilih
        const idGuru = opt.value;
        if (komponenId && idGuru && existingPenilaian[idGuru]) {
            const sudah = existingPenilaian[idGuru].some(
                p => String(p.id_komponen) === String(komponenId)
            );
            if (sudah) txt += MARKER;
        }
        opt.text = txt;
    });
}

// Cek duplikat guru+TA; kalau duplikat tampil warning, kalau tidak langsung load form
function checkDuplicateAndLoad(id_guru, id_komponen) {
    if (!isEditMode && id_guru && existingPenilaian[id_guru]) {
        const found = existingPenilaian[id_guru].find(p => String(p.id_komponen) === String(id_komponen));
        if (found) {
            showDuplicateWarning(found.ta_komponen);
            resetKomponenArea();
            return;
        }
    }
    loadFormPenilaian(id_komponen);
}

function loadFormPenilaian(id_komponen) {
    const area = document.getElementById('komponen-area');
    area.innerHTML = '<div style="text-align:center;padding:30px;color:#6b7280;"><span style="font-size:24px;">⏳</span><p>Memuat form penilaian...</p></div>';

    fetch('api_custom_komponen.php?id_komponen=' + encodeURIComponent(id_komponen))
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                area.innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;"><p>⚠️ Belum ada item. <a href="custom_penilaian.php" style="color:#1a4731;">Atur Buat Pertanyaan Penilaian</a></p></div>';
                return;
            }
            const grouped = {};
            data.forEach(r => {
                if (!grouped[r.nama_indikator]) grouped[r.nama_indikator] = [];
                grouped[r.nama_indikator].push(r);
            });
            const katStyles = {
                'Disiplin':                 {icon:'⏰', color:'#1a4731', bg:'#e8f5ee'},
                'Pelaksanaan Pembelajaran': {icon:'📚', color:'#1e40af', bg:'#eff6ff'},
                'Kerjasama':               {icon:'🤝', color:'#7c3aed', bg:'#f5f3ff'},
            };
            const skalaLabels = {1:'Kurang',2:'Cukup',3:'Baik',4:'Sangat Baik',5:'Sangat Baik Sekali'};
            let html = `<div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border-radius:12px;padding:14px 18px;border-left:4px solid var(--hijau);">
                    <span style="font-size:18px;">📋</span>
                    <div><strong style="font-size:14px;color:var(--hijau);display:block;">Komponen Penilaian</strong>
                    <span style="font-size:11.5px;color:#6b7280;">Skala: 1=Kurang | 2=Cukup | 3=Baik | 4=Sangat Baik | 5=Sangat Baik Sekali</span></div>
                </div></div>`;
            let katNo = 1;
            Object.entries(grouped).forEach(([namaInd, items]) => {
                const s = katStyles[namaInd] || {icon:'📌', color:'#374151', bg:'#f9fafb'};
                const setSemuaBtns = [1,2,3,4,5].map(sv =>
                    `<button type="button" class="btn-set-semua" data-val="${sv}" title="Set semua item indikator ini ke ${sv} (${skalaLabels[sv]})" style="width:26px;height:26px;border:1px solid ${s.color};background:#fff;color:${s.color};border-radius:6px;font-weight:600;cursor:pointer;font-size:11px;">${sv}</button>`
                ).join('');
                html += `<div class="nilai-group" data-indikator="${namaInd.replace(/"/g,'&quot;')}" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <div class="nilai-kategori" style="background:${s.bg};border-left:5px solid ${s.color};padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;flex-wrap:wrap;">
                        <span style="font-size:20px;">${s.icon}</span>
                        <div style="flex:1;min-width:160px;"><span style="font-size:11px;color:${s.color};font-weight:600;text-transform:uppercase;opacity:0.7;">Indikator ${katNo++} <span class="ind-progress" style="font-weight:500;opacity:0.8;"></span></span>
                        <div style="font-size:14px;font-weight:700;color:${s.color};">${namaInd}</div></div>
                        <div class="set-semua-wrap" style="display:flex;gap:4px;align-items:center;font-size:11px;color:#6b7280;">
                            <span style="margin-right:4px;">Set semua:</span>${setSemuaBtns}
                        </div>
                    </div>`;
                items.forEach(item => {
                    html += `<div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;overflow:hidden;align-items:flex-start;">
                        <div class="nilai-item-label" style="word-break:break-all;overflow-wrap:anywhere;white-space:normal;flex:1;min-width:0;">${item.nama_item}</div>
                        <div class="nilai-radio-group">`;
                    for (let v = 1; v <= 5; v++) {
                        const sv  = savedNilai[item.id_item];
                        const chk = (sv !== undefined && sv == v) ? 'checked' : '';
                        html += `<input type="radio" name="nilai[${item.id_item}]" id="v${item.id_item}_${v}" value="${v}" ${chk}>
                                 <label for="v${item.id_item}_${v}" title="${v} = ${skalaLabels[v]}">${v}</label>`;
                    }
                    html += `</div></div>`;
                });
                html += `</div>`;
            });
            area.innerHTML = html;
            initProgressTracking();
        })
        .catch(() => {
            area.innerHTML = '<div style="padding:20px;color:#dc2626;">⚠️ Gagal memuat data penilaian.<br><small style="color:#9ca3af;">Pastikan Buat Pertanyaan Penilaian sudah diatur. <a href="custom_penilaian.php" style="color:#1a4731;">Buka Buat Pertanyaan Penilaian</a></small></div>';
        });
}

function resetKomponenArea() {
    document.getElementById('komponen-area').innerHTML =
        '<div style="text-align:center;padding:40px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;"><div style="font-size:36px;margin-bottom:12px;">📋</div><p>Pilih nama guru dan tahun ajaran untuk menampilkan form penilaian.</p></div>';
    document.getElementById('progressPanel').style.display = 'none';
}

// ── Progress tracking: update live saat radio berubah ───────────────────────
function initProgressTracking() {
    const panel = document.getElementById('progressPanel');
    const area  = document.getElementById('komponen-area');
    const groups = area.querySelectorAll('.nilai-group');
    if (!groups.length) { panel.style.display = 'none'; return; }
    panel.style.display = '';

    // Pasang listener pada setiap radio
    area.querySelectorAll('input[type=radio][name^="nilai["]').forEach(r => {
        r.addEventListener('change', updateProgress);
    });

    // Pasang listener pada tombol "Set semua"
    area.querySelectorAll('.btn-set-semua').forEach(btn => {
        btn.addEventListener('click', function() {
            const val = this.dataset.val;
            const group = this.closest('.nilai-group');
            group.querySelectorAll('.nilai-item').forEach(item => {
                const radio = item.querySelector(`input[type=radio][value="${val}"]`);
                if (radio) radio.checked = true;
            });
            updateProgress();
        });
        // Hover: highlight tombol sesuai warna skala
        btn.addEventListener('mouseenter', function() {
            const warna = {1:'#dc2626',2:'#d97706',3:'#2563eb',4:'#16a34a',5:'#7c3aed'}[this.dataset.val];
            this.style.background = warna; this.style.color = '#fff'; this.style.borderColor = warna;
        });
        btn.addEventListener('mouseleave', function() {
            this.style.background = '#fff';
            const kat = this.closest('.nilai-kategori');
            const warna = kat ? kat.style.borderLeftColor : '#374151';
            this.style.color = warna; this.style.borderColor = warna;
        });
    });

    updateProgress();
}

function predikatLabel(pct) {
    if (pct >= 90) return {label:'Sangat Baik Sekali', color:'#7c3aed'};
    if (pct >= 75) return {label:'Sangat Baik',        color:'#16a34a'};
    if (pct >= 60) return {label:'Baik',               color:'#2563eb'};
    if (pct >= 40) return {label:'Cukup',              color:'#d97706'};
    return           {label:'Kurang',             color:'#dc2626'};
}

function updateProgress() {
    const area = document.getElementById('komponen-area');
    const groups = area.querySelectorAll('.nilai-group');
    let totalItems = 0, totalFilled = 0;
    const indPercentages = [];

    groups.forEach(g => {
        const items = g.querySelectorAll('.nilai-item');
        let gSum = 0, gFilled = 0;
        items.forEach(it => {
            totalItems++;
            const sel = it.querySelector('input[type=radio]:checked');
            if (sel) {
                totalFilled++;
                gFilled++;
                gSum += parseInt(sel.value, 10);
            }
        });
        // Update label progres per indikator (di header kategori)
        const indProg = g.querySelector('.ind-progress');
        if (indProg) {
            indProg.textContent = `· ${gFilled}/${items.length}`;
            indProg.style.opacity = (gFilled === items.length && items.length > 0) ? '1' : '0.6';
        }
        // Hitung persen indikator (hanya dari item terisi, mengikuti rumus SQL)
        if (gFilled > 0) {
            indPercentages.push((gSum / (gFilled * 5)) * 100);
        }
    });

    // Rata-rata antar indikator (mencocokkan formula AVG(ind_pct) di SQL)
    const overallPct = indPercentages.length
        ? indPercentages.reduce((a,b)=>a+b, 0) / indPercentages.length
        : 0;

    const pctFilled = totalItems ? (totalFilled / totalItems * 100) : 0;
    document.getElementById('progFilled').textContent  = totalFilled;
    document.getElementById('progTotal').textContent   = totalItems;
    document.getElementById('progPercent').textContent = pctFilled.toFixed(0);
    document.getElementById('progBar').style.width     = pctFilled + '%';

    const nilaiEl    = document.getElementById('progNilai');
    const predikatEl = document.getElementById('progPredikat');
    if (totalFilled > 0) {
        const p = predikatLabel(overallPct);
        nilaiEl.textContent = overallPct.toFixed(1);
        nilaiEl.style.color = p.color;
        predikatEl.textContent = ' · ' + p.label;
        predikatEl.style.color = p.color;
    } else {
        nilaiEl.textContent = '—';
        nilaiEl.style.color = '#9ca3af';
        predikatEl.textContent = '';
    }

    // Tombol "Ke item belum terisi" muncul kalau ada yang kosong
    document.getElementById('btnJumpUnfilled').style.display =
        (totalFilled > 0 && totalFilled < totalItems) ? '' : 'none';
}

function jumpToUnfilled() {
    const area = document.getElementById('komponen-area');
    const items = area.querySelectorAll('.nilai-item');
    for (const it of items) {
        if (!it.querySelector('input[type=radio]:checked')) {
            it.scrollIntoView({behavior:'smooth', block:'center'});
            // Kedipkan sebentar agar jelas
            const orig = it.style.background;
            it.style.transition = 'background 0.4s';
            it.style.background = '#fef3c7';
            setTimeout(() => { it.style.background = orig; }, 1200);
            return;
        }
    }
}

// ── Validasi sebelum submit: cek semua item terisi ─────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action*="penilaian.php?action="]');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        const area = document.getElementById('komponen-area');
        if (!area) return;
        const items = area.querySelectorAll('.nilai-item');
        if (!items.length) return; // Form belum ter-load, biarkan server yang validasi
        let missing = 0;
        items.forEach(it => { if (!it.querySelector('input[type=radio]:checked')) missing++; });
        if (missing > 0) {
            e.preventDefault();
            if (confirm(`⚠️ Masih ada ${missing} item yang belum diisi.\n\nKlik OK untuk loncat ke item pertama yang belum terisi,\natau Cancel untuk tetap menyimpan penilaian parsial.`)) {
                jumpToUnfilled();
            } else {
                // User menolak — tetap simpan (server akan menangani item kosong dengan benar)
                form.submit();
            }
        }
    });
});

// Jika mode edit dengan data sudah ter-render dari PHP, aktifkan progress tracking
<?php if ($editPenilaian && $editPenilaian['id_komponen']): ?>
document.addEventListener('DOMContentLoaded', initProgressTracking);
<?php endif; ?>

// Edit mode: pastikan dropdown aktif
<?php if ($editPenilaian): ?>
document.getElementById('sel_komponen').disabled = false;
<?php endif; ?>

// ─── Auto-prefill saat datang dari rekap.php (?id_guru=X&tipe=Y) ───────────
<?php if ($prefillIdGuru && $prefillTipe): ?>
document.addEventListener('DOMContentLoaded', function () {
    const tipe    = <?= json_encode($prefillTipe) ?>;
    const idGuru  = <?= (int)$prefillIdGuru ?>;
    const selTipe = document.getElementById('sel_tipe');
    const selGuru = document.getElementById('sel_guru');
    if (!selTipe || !selGuru) return;

    // 1. Set tipe (sudah ter-selected dari PHP, tapi pastikan value-nya benar)
    selTipe.value = tipe;
    // 2. Trigger cascade — populate guru list (sync) + load tahun ajaran (AJAX)
    onTipeChange(tipe);
    // 3. Set guru terpilih (list sudah terisi karena step 2 sync)
    selGuru.value = String(idGuru);
    // 4. Beri visual feedback agar user tahu ini auto-isi
    selGuru.style.boxShadow = '0 0 0 3px rgba(217,119,6,0.25)';
    setTimeout(() => { selGuru.style.boxShadow = ''; }, 1800);
    // 5. Geser hint user ke pilih tahun ajaran
    const hintK = document.getElementById('hint_komponen');
    if (hintK) {
        hintK.textContent = '👆 Tinggal pilih tahun ajaran untuk memulai penilaian';
        hintK.style.color = '#d97706';
        hintK.style.fontWeight = '600';
    }
});
<?php endif; ?>
</script>

<?php else: ?>
<div class="data-table-card">
    <div class="card-header-custom">
        <div class="card-title-custom">📝 Daftar Penilaian Kinerja</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <button id="btnHapusTerpilih" onclick="hapusTerpilih()" class="btn-danger-custom" style="display:none;">
                🗑 Hapus Terpilih (<span id="jumlahTerpilih">0</span>)
            </button>
            <button onclick="hapusSemua()" class="btn-danger-custom">🗑 Hapus Semua</button>
            <a href="penilaian.php?action=add" class="btn-primary-custom">+ Tambah Penilaian</a>
        </div>
    </div>

    <!-- PANEL FILTER -->
    <form method="GET" action="penilaian.php" autocomplete="off" id="filterForm"
          style="background:#f8fafc;border-radius:10px;padding:14px 18px;margin-bottom:18px;border:1px solid #e5e7eb;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:160px;">
            <div class="form-label-custom" style="margin-bottom:4px;">📅 Tahun Ajaran</div>
            <select name="filter_ta" class="form-control-custom" style="width:100%;" onchange="document.getElementById('filterForm').submit()">
                <option value="">— Semua Tahun —</option>
                <?php foreach ($listTA as $ta): ?>
                    <option value="<?= htmlspecialchars($ta) ?>"
                        <?= ($filterTA === $ta) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ta) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:1;min-width:150px;">
            <div class="form-label-custom" style="margin-bottom:4px;">👤 Tipe Guru</div>
            <select name="filter_tipe" class="form-control-custom" style="width:100%;" onchange="document.getElementById('filterForm').submit()">
                <option value="">— Semua Tipe —</option>
                <?php foreach ($tipeLabels as $kode => $label): ?>
                    <option value="<?= htmlspecialchars($kode) ?>"
                        <?= ($filterTipe === $kode) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex:2;min-width:180px;">
            <div class="form-label-custom" style="margin-bottom:4px;">🔍 Nama Guru</div>
            <select name="filter_guru" class="form-control-custom" style="width:100%;" onchange="document.getElementById('filterForm').submit()">
                <option value="0">— Semua Guru —</option>
                <?php foreach ($guruAll as $g): ?>
                    <option value="<?= (int)$g['id_guru'] ?>"
                        <?= ($filterGuru === (int)$g['id_guru']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['nama']) ?>
                        <?= $g['jabatan'] ? ' ('.$g['jabatan'].')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
            <?php if ($filterTA !== '' || $filterTipe !== '' || $filterGuru > 0): ?>
                <a href="penilaian.php" class="btn-cancel" style="white-space:nowrap;">✕ Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <?php
    $totalAll = $pdo->query("SELECT COUNT(*) FROM penilaian")->fetchColumn();
    $totalFiltered = count($penilaianList);
    $adaFilter = ($filterTA !== '' || $filterTipe !== '' || $filterGuru > 0);
    ?>
    <?php if ($adaFilter): ?>
    <div style="font-size:12px;color:#6b7280;margin-bottom:10px;padding:0 2px;">
        Menampilkan <strong style="color:#1a4731;"><?= $totalFiltered ?></strong> dari
        <strong><?= $totalAll ?></strong> total penilaian
        <?php if ($filterTA): ?> &middot; Tahun Ajaran: <strong><?= htmlspecialchars($filterTA) ?></strong><?php endif; ?>
        <?php if ($filterTipe): ?> &middot; Tipe: <strong><?= htmlspecialchars($tipeLabels[$filterTipe] ?? $filterTipe) ?></strong><?php endif; ?>
        <?php if ($filterGuru): ?>
            <?php
            $namaGF = '';
            foreach ($guruAll as $g) { if ((int)$g['id_guru'] === $filterGuru) { $namaGF = $g['nama']; break; } }
            ?>
            &middot; Guru: <strong><?= htmlspecialchars($namaGF) ?></strong>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <form id="formHapusTerpilih" method="POST" action="penilaian.php?action=delete_selected" autocomplete="off">
    <style>
    #tblPenilaian thead th { background:#f1f5f9; position:sticky; top:0; z-index:1; font-size:12px; font-weight:600; color:#475569; padding:10px 12px; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
    #tblPenilaian tbody tr:hover { background:#f8fafc; }
    #tblPenilaian tbody td { padding:10px 12px; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
    #tblPenilaian { border-collapse:collapse; }
    </style>
    <div style="overflow-x:auto;">
    <table class="table table-hover" id="tblPenilaian" style="font-size:13px;width:100%;min-width:700px;">
        <thead style="background:#f8fafc;">
            <tr>
                <th style="width:36px;"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                <th>No</th><th>Nama Guru</th><th>Tipe Guru</th><th>Jabatan</th><th>Tahun Ajaran</th><th>Tgl Penilaian</th><th>Nilai Rata²</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($penilaianList)): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:#9ca3af;">
                <div style="font-size:32px;margin-bottom:8px;">📋</div>
                <div>Tidak ada data penilaian<?= $adaFilter ? ' untuk filter yang dipilih' : '' ?>.</div>
                <?php if ($adaFilter): ?><div style="margin-top:6px;"><a href="penilaian.php" style="color:var(--hijau);">Tampilkan semua penilaian</a></div><?php endif; ?>
            </td></tr>
            <?php else: ?>
            <?php foreach ($penilaianList as $i => $p): ?>
            <tr>
                <td><input type="checkbox" name="selected_ids[]" value="<?= $p['id_penilaian'] ?>" class="row-check" onchange="updateTerpilih()"></td>
                <td><?= $i+1 ?></td>
                <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                <td><span style="background:#eff6ff;color:#1e40af;padding:2px 9px;border-radius:12px;font-size:11px;font-weight:600;"><?= htmlspecialchars($tipeLabels[$p['tipe']] ?? $p['tipe']) ?></span></td>
                <td><small><?= htmlspecialchars($p['jabatan']??'') ?></small></td>
                <td><small><?= htmlspecialchars($p['periode']) ?></small></td>
                <td><?= date('d/m/Y', strtotime($p['tanggal_penilaian'])) ?></td>
                <td>
                    <?php if ($p['rata_nilai']): ?>
                        <span style="font-weight:600;color:<?= $p['rata_nilai']>=75?'#16a34a':($p['rata_nilai']>=50?'#d97706':'#dc2626') ?>">
                            <?= $p['rata_nilai'] ?>
                        </span>
                    <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:5px;flex-wrap:wrap;">
                        <a href="penilaian.php?action=edit&id=<?= $p['id_penilaian'] ?>" class="btn-primary-custom btn-sm-custom btn-edit">Edit</a>
                        <a href="cetak.php?id=<?= $p['id_penilaian'] ?>" class="btn-primary-custom btn-sm-custom btn-view" target="_blank">Cetak</a>
                        <button class="btn-primary-custom btn-sm-custom btn-delete" onclick="confirmDelete(<?= $p['id_penilaian'] ?>,'penilaian.php')">Hapus</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div><!-- end overflow-x -->
    </form>
</div>
<script>
function toggleAll(m){document.querySelectorAll('.row-check').forEach(cb=>cb.checked=m.checked);updateTerpilih();}
function updateTerpilih(){
    const n=document.querySelectorAll('.row-check:checked').length;
    document.getElementById('jumlahTerpilih').textContent=n;
    document.getElementById('btnHapusTerpilih').style.display=n>0?'':'none';
    const all=document.querySelectorAll('.row-check'),m=document.getElementById('checkAll');
    if(m)m.checked=all.length>0&&n===all.length;
}
function hapusTerpilih(){const n=document.querySelectorAll('.row-check:checked').length;if(!n)return;if(!confirm('Hapus '+n+' penilaian?'))return;document.getElementById('formHapusTerpilih').submit();}
function hapusSemua(){
    const total = <?= json_encode(count($penilaianList)) ?>;
    if(!confirm('Hapus ' + total + ' penilaian yang sedang ditampilkan?')) return;
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = 'penilaian.php?action=delete_all';
    [
        ['filter_ta',   <?= json_encode($filterTA) ?>],
        ['filter_tipe', <?= json_encode($filterTipe) ?>],
        ['filter_guru', <?= json_encode($filterGuru) ?>],
    ].forEach(([name, val]) => {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = name; i.value = val;
        f.appendChild(i);
    });
    document.body.appendChild(f);
    f.submit();
}
</script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
