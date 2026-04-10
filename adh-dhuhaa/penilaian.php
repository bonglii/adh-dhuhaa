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
    $pdo->prepare("DELETE FROM penilaian WHERE id_penilaian=?")->execute([$id]);
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil dihapus!'));
    exit;
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
    $pdo->prepare($dSQL)->execute($dParamsAll);
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil dihapus!'));
    exit;
}

if ($action === 'delete_selected' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
    if ($ids) {
        $pl = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("DELETE FROM penilaian WHERE id_penilaian IN ($pl)")->execute($ids);
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode(count($ids) . ' penilaian berhasil dihapus!'));
        exit;
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
                SUM(dp2.nilai) / NULLIF(COUNT(dp2.id_hasil) * 5, 0) * 100 AS ind_pct
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

function renderKomponenHtml(array $isiByInd, array $editDetail = []): string {
    if (empty($isiByInd)) {
        return '<div style="text-align:center;padding:30px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;">
            <p>Belum ada item pada custom penilaian ini.<br>
            <a href="custom_penilaian.php" target="_blank" style="color:#1a4731;">Atur custom penilaian ↗</a></p></div>';
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
    $katNo = 1;
    foreach ($isiByInd as $namaInd => $items) {
        $ic = $katIcons[$namaInd] ?? ['📌','#374151','#f9fafb'];
        $html .= '<div class="nilai-group" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
            <div class="nilai-kategori" style="background:'.$ic[2].';border-left:5px solid '.$ic[1].';padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;">
                <span style="font-size:20px;">'.$ic[0].'</span>
                <div><span style="font-size:11px;color:'.$ic[1].';font-weight:600;text-transform:uppercase;opacity:0.7;">Indikator '.$katNo++.'</span>
                <div style="font-size:14px;font-weight:700;color:'.$ic[1].';">'.htmlspecialchars($namaInd).'</div></div>
            </div>';
        foreach ($items as $item) {
            $html .= '<div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;overflow:hidden;align-items:flex-start;">
                <div class="nilai-item-label" style="word-break:break-all;overflow-wrap:anywhere;white-space:normal;flex:1;min-width:0;"><span class="nilai-item-num">'.htmlspecialchars($item['nomor_item']).'.</span> '.htmlspecialchars($item['nama_item']).'</div>
                <div class="nilai-radio-group">';
            for ($v=1;$v<=5;$v++) {
                $iid  = $item['id_item'];
                $savedV = $editDetail[$iid] ?? null;
                $chk  = $savedV !== null ? ($savedV == $v ? 'checked' : '') : ($v === 1 ? 'checked' : '');
                $html .= '<input type="radio" name="nilai['.$iid.']" id="v'.$iid.'_'.$v.'" value="'.$v.'" '.$chk.'>';
                $html .= '<label for="v'.$iid.'_'.$v.'">'.$v.'</label>';
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

    <form method="POST" action="penilaian.php?action=<?= $action ?>&id=<?= $id ?>" autocomplete="off">
        <input type="hidden" name="save_penilaian" value="1">
        <input type="hidden" name="action" value="<?= $action ?>">
        <?php if ($editPenilaian): ?>
        <input type="hidden" name="id_guru" value="<?= (int)$editPenilaian['id_guru'] ?>">
        <?php endif; ?>

        <?php
        $editTipeGuru      = $editPenilaian['tipe'] ?? '';
        $editTipeGuruLabel = $tipeLabels[$editTipeGuru] ?? $editTipeGuru;
        ?>

        <!-- LANGKAH 1: Nama Guru (utama) → Tipe Guru (otomatis) → Tahun Ajaran -->
        <div style="background:#f8fafc;border-radius:12px;padding:18px 20px;margin-bottom:22px;border-left:4px solid var(--hijau);">
            <div style="font-size:12px;font-weight:700;color:var(--hijau);text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">
                Langkah 1 — Pilih Guru Yang Dinilai
            </div>
            <div class="row g-3">

                <!-- NAMA GURU — dropdown utama, semua guru tampil -->
                <div class="col-md-6">
                    <div class="form-label-custom">Nama Guru <span style="color:#dc2626;">*</span></div>
                    <select name="id_guru" id="sel_guru" class="form-control-custom" required
                        onchange="onGuruChange(this.value)"
                        <?= $editPenilaian ? 'disabled' : '' ?>>
                        <option value="">-- Pilih Nama Guru --</option>
                        <?php foreach ($guruAll as $g): ?>
                            <option value="<?= (int)$g['id_guru'] ?>"
                                data-tipe="<?= htmlspecialchars($g['tipe']) ?>"
                                data-tipe-label="<?= htmlspecialchars($g['tipe_label'] ?? $g['tipe']) ?>"
                                <?= ($editPenilaian && $editPenilaian['id_guru'] == $g['id_guru']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama']) ?>
                                <?= $g['jabatan'] ? ' (' . $g['jabatan'] . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($editPenilaian): ?>
                        <small style="color:#9ca3af;font-size:11px;margin-top:3px;display:block;">(tidak bisa diubah saat edit)</small>
                    <?php endif; ?>
                </div>

                <!-- TIPE GURU — terisi otomatis dari pilihan guru -->
                <div class="col-md-3">
                    <div class="form-label-custom">Tipe Guru (otomatis)</div>
                    <input type="text" id="disp_tipe_guru" class="form-control-custom" readonly
                        style="background:#f3f4f6;color:#374151;font-weight:600;"
                        value="<?= htmlspecialchars($editTipeGuruLabel) ?>"
                        placeholder="Terisi otomatis">
                </div>

                <!-- TAHUN AJARAN — muncul setelah guru dipilih via AJAX -->
                <div class="col-md-3">
                    <div class="form-label-custom">Tahun Ajaran <span style="color:#dc2626;">*</span></div>
                    <select name="id_komponen" id="sel_komponen" class="form-control-custom" required
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
                            <option value="">-- Pilih guru dulu --</option>
                        <?php endif; ?>
                    </select>
                    <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">
                        <a href="custom_penilaian.php" target="_blank" style="color:var(--hijau);">Kelola Custom Penilaian ↗</a>
                    </small>
                </div>

            </div>
        </div>

        <!-- LANGKAH 2: Info Penilaian -->
        <div style="background:#f8fafc;border-radius:12px;padding:18px 20px;margin-bottom:22px;border-left:4px solid #6366f1;">
            <div style="font-size:12px;font-weight:700;color:#6366f1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px;">
                Langkah 2 — Informasi Penilaian
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

// ── Pilih Guru → auto-fill Tipe Guru + load Tahun Ajaran via AJAX ──────────
function onGuruChange(id_guru) {
    const selGuru  = document.getElementById('sel_guru');
    const selK     = document.getElementById('sel_komponen');
    const dispTipe = document.getElementById('disp_tipe_guru');

    const opt   = selGuru.options[selGuru.selectedIndex];
    const tipe  = opt ? (opt.dataset.tipe || '') : '';
    const label = opt ? (opt.dataset.tipeLabel || '') : '';

    dispTipe.value = label || (tipeLabels[tipe] || tipe);

    resetKomponenArea();
    hideDuplicateWarning();
    selK.innerHTML = '<option value="">Memuat...</option>';
    selK.disabled  = true;

    if (!tipe || !id_guru) {
        selK.innerHTML = '<option value="">-- Pilih guru dulu --</option>';
        return;
    }

    fetch('api_custom_komponen.php?tipe=' + encodeURIComponent(tipe))
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                selK.innerHTML = '<option value="">Belum ada tahun ajaran untuk tipe ini. Buat di Buat Pertanyaan Penilaian.</option>';
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
        })
        .catch(() => {
            selK.innerHTML = '<option value="">Gagal memuat data.</option>';
        });
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
    if (!id_komponen) { resetKomponenArea(); return; }

    // Cek duplikat: guru + tahun ajaran sudah pernah dinilai?
    const selGuru = document.getElementById('sel_guru');
    const id_guru = selGuru ? selGuru.value : '';
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
                area.innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;"><p>⚠️ Belum ada item. <a href="custom_penilaian.php" target="_blank" style="color:#1a4731;">Atur Buat Pertanyaan Penilaian ↗</a></p></div>';
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
            let html = `<div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border-radius:12px;padding:14px 18px;border-left:4px solid var(--hijau);">
                    <span style="font-size:18px;">📋</span>
                    <div><strong style="font-size:14px;color:var(--hijau);display:block;">Komponen Penilaian</strong>
                    <span style="font-size:11.5px;color:#6b7280;">Skala: 1=Kurang | 2=Cukup | 3=Baik | 4=Sangat Baik | 5=Sangat Baik Sekali</span></div>
                </div></div>`;
            let katNo = 1;
            Object.entries(grouped).forEach(([namaInd, items]) => {
                const s = katStyles[namaInd] || {icon:'📌', color:'#374151', bg:'#f9fafb'};
                html += `<div class="nilai-group" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <div class="nilai-kategori" style="background:${s.bg};border-left:5px solid ${s.color};padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;">
                        <span style="font-size:20px;">${s.icon}</span>
                        <div><span style="font-size:11px;color:${s.color};font-weight:600;text-transform:uppercase;opacity:0.7;">Indikator ${katNo++}</span>
                        <div style="font-size:14px;font-weight:700;color:${s.color};">${namaInd}</div></div>
                    </div>`;
                items.forEach(item => {
                    html += `<div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;overflow:hidden;align-items:flex-start;">
                        <div class="nilai-item-label" style="word-break:break-all;overflow-wrap:anywhere;white-space:normal;flex:1;min-width:0;">${item.nama_item}</div>
                        <div class="nilai-radio-group">`;
                    for (let v = 1; v <= 5; v++) {
                        const sv  = savedNilai[item.id_item];
                        const chk = isEditMode ? (sv == v ? 'checked' : '') : (v === 1 ? 'checked' : '');
                        html += `<input type="radio" name="nilai[${item.id_item}]" id="v${item.id_item}_${v}" value="${v}" ${chk}>
                                 <label for="v${item.id_item}_${v}">${v}</label>`;
                    }
                    html += `</div></div>`;
                });
                html += `</div>`;
            });
            area.innerHTML = html;
        })
        .catch(() => {
            area.innerHTML = '<div style="padding:20px;color:#dc2626;">⚠️ Gagal memuat data penilaian.<br><small style="color:#9ca3af;">Pastikan Buat Pertanyaan Penilaian sudah diatur. <a href="custom_penilaian.php" target="_blank" style="color:#1a4731;">Buka Buat Pertanyaan Penilaian ↗</a></small></div>';
        });
}

function resetKomponenArea() {
    document.getElementById('komponen-area').innerHTML =
        '<div style="text-align:center;padding:40px;color:#9ca3af;border:2px dashed #e5e7eb;border-radius:12px;"><div style="font-size:36px;margin-bottom:12px;">📋</div><p>Pilih nama guru dan tahun ajaran untuk menampilkan form penilaian.</p></div>';
}

// Edit mode: pastikan dropdown aktif
<?php if ($editPenilaian): ?>
document.getElementById('sel_komponen').disabled = false;
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
                            <?= $p['rata_nilai'] ?>%
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
