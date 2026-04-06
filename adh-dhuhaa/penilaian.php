<?php
/**
 * penilaian.php — Halaman Penilaian Kinerja Guru
 *
 * Fitur:
 *  - Tambah & edit penilaian per guru dan per periode
 *  - Input nilai per komponen/indikator (skala 0-5)
 *  - Tambah kategori & item penilaian kustom langsung dari form
 *  - Hapus satu, pilih banyak, atau hapus semua data penilaian
 *  - Kalkulasi rata-rata otomatis dan predikat akhir
 *
 * Semua logika PHP diproses SEBELUM output HTML apapun.
 */

// ─── Inisialisasi: load konfigurasi & wajib login ────────────────────────────
require_once 'includes/config.php';
requireLogin();

// ─── Baca parameter aksi & ID dari URL ──────────────────────────────────────
$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

// ─── Handle GET: Hapus satu penilaian ────────────────────────────────────────
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM penilaian WHERE id=?")->execute([$id]);
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Penilaian berhasil dihapus!'));
    exit;
}

// ─── Handle GET: Hapus semua penilaian ───────────────────────────────────────
if ($action === 'delete_all') {
    $pdo->exec("DELETE FROM penilaian");
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Semua penilaian berhasil dihapus!'));
    exit;
}

// ─── Handle POST: Hapus penilaian yang dipilih via checkbox ──────────────────
if ($action === 'delete_selected' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi: pastikan semua ID adalah integer positif
    $ids = array_filter(array_map('intval', $_POST['selected_ids'] ?? []));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("DELETE FROM penilaian WHERE id IN ($placeholders)")->execute($ids);
        $jumlah = count($ids);
        // Simpan session sebelum redirect agar data tidak hilang
        session_write_close();
        header('Location: penilaian.php?msg=' . urlencode("$jumlah penilaian berhasil dihapus!"));
        exit;
    }
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: penilaian.php?msg=' . urlencode('Tidak ada data yang dipilih.'));
    exit;
}

// ─── Handle POST: Simpan / update penilaian baru ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guru_id      = (int)($_POST['guru_id'] ?? 0);
    $periode_awal = $_POST['periode_awal'] ?? '';
    $periode_akhir = $_POST['periode_akhir'] ?? '';
    $tgl          = $_POST['tanggal_penilaian'] ?? '';
    $penilai      = sanitize($_POST['penilai'] ?? '');
    $jab_pen      = sanitize($_POST['jabatan_penilai'] ?? '');
    $catatan      = sanitize($_POST['catatan'] ?? '');
    $nilai_data   = $_POST['nilai'] ?? [];
    $custom_kat   = $_POST['custom_kategori'] ?? [];
    $custom_item  = $_POST['custom_item'] ?? [];
    $custom_nilai = $_POST['custom_nilai'] ?? [];

    // Buat label periode otomatis dari tanggal awal-akhir
    $periode = '';
    if ($periode_awal && $periode_akhir) {
        $bulan_id = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                     '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                     '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        [$y1,$m1,$d1] = explode('-', $periode_awal);
        [$y2,$m2,$d2] = explode('-', $periode_akhir);
        $periode = ($bulan_id[$m1] ?? $m1).' '.$y1.' s.d '.($bulan_id[$m2] ?? $m2).' '.$y2;
    }

    // Ambil tipe guru untuk menyimpan kategori tambahan ke tabel yang tepat
    $guru_tipe = 'guru_kelas';
    if ($guru_id) {
        $gs = $pdo->prepare("SELECT tipe FROM guru WHERE id=?");
        $gs->execute([$guru_id]);
        $guru_tipe = $gs->fetchColumn() ?: 'guru_kelas';
    }

    if (!$guru_id || !$periode_awal || !$periode_akhir || !$tgl) {
        $msg = 'Data tidak lengkap! Guru, periode, dan tanggal wajib diisi.';
    } else {
        $pdo->beginTransaction();
        try {
            if ($action === 'edit' && $id) {
                // Update data penilaian yang sudah ada
                $stmt = $pdo->prepare("UPDATE penilaian SET guru_id=?,periode=?,periode_awal=?,periode_akhir=?,tanggal_penilaian=?,penilai=?,jabatan_penilai=?,catatan=? WHERE id=?");
                $stmt->execute([$guru_id, $periode, $periode_awal, $periode_akhir, $tgl, $penilai, $jab_pen, $catatan, $id]);
                $pdo->prepare("DELETE FROM detail_penilaian WHERE penilaian_id=?")->execute([$id]);
                $pen_id = $id;
            } else {
                // Tambah penilaian baru
                $stmt = $pdo->prepare("INSERT INTO penilaian (guru_id,periode,periode_awal,periode_akhir,tanggal_penilaian,penilai,jabatan_penilai,catatan) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$guru_id, $periode, $periode_awal, $periode_akhir, $tgl, $penilai, $jab_pen, $catatan]);
                $pen_id = $pdo->lastInsertId();
            }
            if ($nilai_data) {
                $ins = $pdo->prepare("INSERT INTO detail_penilaian (penilaian_id,item_id,nilai) VALUES (?,?,?)");
                foreach ($nilai_data as $komp_id => $nilai) {
                    $ins->execute([$pen_id, (int)$komp_id, (int)$nilai]);
                }
            }
            // ============================================================
            // Simpan kategori tambahan ke komponen_penilaian + item
            // ============================================================
            if ($custom_item) {
                // Kelompokkan item berdasarkan kategori
                $grouped_custom = [];
                foreach ($custom_item as $idx => $nm) {
                    $nm = trim($nm);
                    if ($nm === '') continue;
                    $kat = trim($custom_kat[$idx] ?? 'Lainnya');
                    $val = (int)($custom_nilai[$idx] ?? 1);
                    $grouped_custom[$kat][] = ['nama' => $nm, 'nilai' => $val];
                }

                $ins_det_c = $pdo->prepare("INSERT INTO detail_penilaian (penilaian_id, item_id, nilai) VALUES (?,?,?)");

                foreach ($grouped_custom as $kat => $items_custom) {
                    // Cari komponen yang sudah ada untuk tipe guru ini
                    $ck_komp = $pdo->prepare("SELECT id, urutan FROM komponen_penilaian WHERE tipe_guru=? AND nama_kategori=?");
                    $ck_komp->execute([$guru_tipe, $kat]);
                    $komp_row = $ck_komp->fetch();

                    if ($komp_row) {
                        $komp_id     = $komp_row['id'];
                        $komp_urutan = $komp_row['urutan'];
                    } else {
                        // Buat indikator baru di tabel komponen_penilaian
                        $max_u = $pdo->prepare("SELECT COALESCE(MAX(urutan), 0) + 1 FROM komponen_penilaian WHERE tipe_guru=?");
                        $max_u->execute([$guru_tipe]);
                        $komp_urutan = (int)$max_u->fetchColumn();
                        $pdo->prepare("INSERT INTO komponen_penilaian (tipe_guru, nama_kategori, urutan, is_tambahan) VALUES (?,?,?,1)")
                            ->execute([$guru_tipe, $kat, $komp_urutan]);
                        $komp_id = (int)$pdo->lastInsertId();
                    }

                    // Simpan setiap poin ke tabel item & catat nilainya
                    $ck_item  = $pdo->prepare("SELECT id FROM item WHERE komponen_id=? AND nama_item=?");
                    $ins_item = $pdo->prepare("INSERT INTO item (komponen_id, nomor_item, nama_item, urutan) VALUES (?,?,?,?)");
                    $cnt_item = $pdo->prepare("SELECT COUNT(*) FROM item WHERE komponen_id=?");

                    foreach ($items_custom as $it) {
                        // Cek duplikat item
                        $ck_item->execute([$komp_id, $it['nama']]);
                        $item_id = $ck_item->fetchColumn();

                        if (!$item_id) {
                            $cnt_item->execute([$komp_id]);
                            $item_seq  = (int)$cnt_item->fetchColumn() + 1;
                            $nomor_otomatis = $komp_urutan . '.' . $item_seq;
                            $ins_item->execute([$komp_id, $nomor_otomatis, $it['nama'], $item_seq]);
                            $item_id = (int)$pdo->lastInsertId();
                        }

                        $ins_det_c->execute([$pen_id, $item_id, $it['nilai']]);
                    }
                }
            }
            $pdo->commit();
            // Simpan session sebelum redirect agar data tidak hilang
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

$penilaianList = $pdo->query("
    SELECT p.*, g.nama, g.jabatan, g.tipe,
    (
        SELECT ROUND(
            (SELECT COALESCE(SUM(dp2.nilai),0) FROM detail_penilaian dp2 WHERE dp2.penilaian_id = p.id)
            /
            NULLIF(
                (SELECT COALESCE(COUNT(dp3.id),0) FROM detail_penilaian dp3 WHERE dp3.penilaian_id = p.id) * 5
            , 0)
            * 100
        , 1)
    ) as rata_nilai
    FROM penilaian p JOIN guru g ON p.guru_id = g.id
    ORDER BY p.created_at DESC
")->fetchAll();

// JOIN tipe_guru untuk mendapat label tampil dan urutan yang benar
$guruAll = $pdo->query("
    SELECT g.id, g.nama, g.jabatan, g.tipe, tg.label AS tipe_label
    FROM guru g
    LEFT JOIN tipe_guru tg ON g.tipe = tg.kode
    ORDER BY tg.urutan, g.nama
")->fetchAll();

$editPenilaian = null;
$editDetail = [];
$editCustom = [];
if (($action === 'edit' || $action === 'view') && $id) {
    $stmt = $pdo->prepare("SELECT p.*, g.tipe FROM penilaian p JOIN guru g ON p.guru_id=g.id WHERE p.id=?");
    $stmt->execute([$id]);
    $editPenilaian = $stmt->fetch();
    $rows = $pdo->prepare("SELECT item_id, nilai FROM detail_penilaian WHERE penilaian_id=?");
    $rows->execute([$id]);
    foreach ($rows->fetchAll() as $r) $editDetail[$r['item_id']] = $r['nilai'];
    $editCustom = [];
}

function getKomponen($pdo, $tipe)
{
    $stmt = $pdo->prepare("
        SELECT 
            i.id,
            i.komponen_id,
            i.nomor_item,
            i.nama_item,
            i.urutan,
            kp.nama_kategori AS kategori,
            kp.urutan AS urutan_kategori
        FROM item i
        JOIN komponen_penilaian kp ON i.komponen_id = kp.id
        WHERE kp.tipe_guru = ?
        ORDER BY kp.urutan, i.urutan
    ");
    $stmt->execute([$tipe]);    
    return $stmt->fetchAll();
}

$pageTitle = 'Penilaian Kinerja Guru';
require_once 'includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert-custom alert-success-custom mb-4">✓ <?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'):
    $tipeGuru = $editPenilaian['tipe'] ?? 'guru_kelas';
    $komponen = getKomponen($pdo, $tipeGuru);
    $kategoris = array_unique(array_column($komponen, 'kategori'));
?>
    <div class="data-table-card">
        <div class="card-header-custom mb-4">
            <div class="card-title-custom"><?= $action === 'edit' ? 'Edit' : 'Tambah' ?> Penilaian Kinerja Guru</div>
            <a href="penilaian.php" class="btn-primary-custom" style="background:#6b7280;">← Kembali</a>
        </div>
        <form method="POST" autocomplete="off">
            <input type="hidden" name="action" value="<?= $action ?>">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-label-custom">Guru Yang Dinilai</div>
                    <select name="guru_id" id="sel_guru" class="form-control-custom" onchange="loadKomponen(this.value)" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($guruAll as $g): ?>
                            <option value="<?= (int)$g['id'] ?>" data-tipe="<?= htmlspecialchars($g['tipe']) ?>"
                                <?= ($editPenilaian && $editPenilaian['guru_id'] == $g['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama']) ?> (<?= htmlspecialchars($g['jabatan'] ?? '') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="form-label-custom">Periode Penilaian</div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input type="date" name="periode_awal" class="form-control-custom"
                            value="<?= htmlspecialchars($editPenilaian['periode_awal'] ?? '') ?>"
                            required style="flex:1;" title="Tanggal mulai periode">
                        <span style="color:#6b7280;font-size:13px;white-space:nowrap;">s.d</span>
                        <input type="date" name="periode_akhir" class="form-control-custom"
                            value="<?= htmlspecialchars($editPenilaian['periode_akhir'] ?? '') ?>"
                            required style="flex:1;" title="Tanggal akhir periode">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-label-custom">Tanggal Penilaian</div>
                    <input type="date" name="tanggal_penilaian" class="form-control-custom" value="<?= $editPenilaian['tanggal_penilaian'] ?? date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-5">
                    <div class="form-label-custom">Nama Penilai</div>
                    <input type="text" name="penilai" class="form-control-custom" readonly
                        style="background:#f3f4f6;color:#555;cursor:not-allowed;"
                        value="<?= htmlspecialchars($editPenilaian['penilai'] ?? 'Hasyim Ashari, S.T') ?>">
                </div>
                <div class="col-md-4">
                    <div class="form-label-custom">Jabatan Penilai</div>
                    <input type="text" name="jabatan_penilai" class="form-control-custom" readonly
                        style="background:#f3f4f6;color:#555;cursor:not-allowed;"
                        value="<?= htmlspecialchars($editPenilaian['jabatan_penilai'] ?? 'Kepala Sekolah') ?>">
                </div>

            </div>

            <div id="komponen-area">
                <?php if ($komponen): ?>
                    <div style="margin-bottom:20px;">
                        <div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border-radius:12px;padding:14px 18px;border-left:4px solid var(--hijau);">
                            <span style="font-size:18px;">📋</span>
                            <div>
                                <strong style="font-size:14px;color:var(--hijau);display:block;">Komponen Penilaian</strong>
                                <span style="font-size:11.5px;color:#6b7280;">Skala: 1 = Kurang &nbsp;|&nbsp; 2 = Cukup &nbsp;|&nbsp; 3 = Baik &nbsp;|&nbsp; 4 = Sangat Baik &nbsp;|&nbsp; 5 = Sangat Baik Sekali</span>
                            </div>
                        </div>
                    </div>
                    <?php
                    $katIcons = ['Disiplin' => ['⏰', '#1a4731', '#e8f5ee'], 'Pelaksanaan Pembelajaran' => ['📚', '#1e40af', '#eff6ff'], 'Kerjasama' => ['🤝', '#7c3aed', '#f5f3ff'], 'Kinerja' => ['📊', '#0f766e', '#f0fdfa'], 'Administrasi' => ['🗂️', '#b45309', '#fef3c7'], 'Pelayanan' => ['🌟', '#be185d', '#fdf2f8']];
                    $katNo = 1;
                    foreach ($kategoris as $kat):
                        $items = array_filter($komponen, fn($k) => $k['kategori'] === $kat);
                        $ic = $katIcons[$kat] ?? ['📌', '#374151', '#f9fafb'];
                    ?>
                        <div class="nilai-group" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                            <div class="nilai-kategori" style="background:<?= $ic[2] ?>;border-left:5px solid <?= $ic[1] ?>;padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;">
                                <span style="font-size:20px;"><?= $ic[0] ?></span>
                                <div>
                                    <span style="font-size:11px;color:<?= $ic[1] ?>;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;opacity:0.7;">Kategori <?= $katNo++ ?></span>
                                    <div style="font-size:14px;font-weight:700;color:<?= $ic[1] ?>;"><?= htmlspecialchars($kat) ?></div>
                                </div>
                            </div>
                            <?php foreach ($items as $item): ?>
                                <div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;">
                                    <div class="nilai-item-label">
                                        <span class="nilai-item-num"><?= $item['nomor_item'] ?>.</span>
                                        <?= htmlspecialchars($item['nama_item']) ?>
                                    </div>
                                    <div class="nilai-radio-group">
                                        <?php for ($v = 1; $v <= 5; $v++):
                                            $checked = isset($editDetail[$item['id']]) && $editDetail[$item['id']] == $v ? 'checked' : '';
                                            if (!$checked && !$editPenilaian && $v === 1) $checked = 'checked';
                                        ?>
                                            <input type="radio" name="nilai[<?= $item['id'] ?>]" id="v<?= $item['id'] . '_' . $v ?>" value="<?= $v ?>" <?= $checked ?>>
                                            <label for="v<?= $item['id'] . '_' . $v ?>"><?= $v ?></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- ===== KOMPONEN PENILAIAN CUSTOM ===== -->
            <div style="margin-top:28px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span style="font-size:14px;">➕</span>
                        <strong style="font-size:13.5px;color:var(--hijau);">Komponen Penilaian Tambahan (Opsional)</strong>
                    </div>
                    <button type="button" onclick="tambahKategoriCustom()" class="btn-primary-custom" style="font-size:12px;padding:7px 14px;background:#0f766e;">
                        + Tambah Kategori Baru
                    </button>
                </div>
                <div id="custom-area">
                    <?php foreach ($editCustom as $cIdx => $ci): ?>
                        <div class="custom-kategori-block" style="border:1.5px dashed #c9a84c;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#fffdf5;">
                            <div style="background:#fef9ec;border-left:4px solid #c9a84c;padding:12px 16px;display:flex;align-items:center;gap:10px;">
                                <span style="font-size:16px;">✳️</span>
                                <div style="flex:1;">
                                    <span style="font-size:10px;font-weight:600;color:#92400e;text-transform:uppercase;letter-spacing:0.5px;">Kategori Tambahan</span>
                                    <input type="text" class="form-control-custom" style="font-weight:700;font-size:13.5px;color:#78350f;border:none;background:transparent;padding:2px 0;margin:0;height:auto;"
                                        placeholder="cth: Prestasi, Program Khusus, dll"
                                        name="custom_kategori[<?= $cIdx ?>]"
                                        value="<?= htmlspecialchars($ci['kategori']) ?>">
                                </div>
                                <button type="button" onclick="hapusKategori(this)" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:20px;line-height:1;padding:0 4px;" title="Hapus kategori ini">×</button>
                            </div>
                            <div class="custom-items" style="padding:12px 16px 4px;">
                                <div class="custom-item-row" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                                    <input type="text" class="form-control-custom" style="flex:1;"
                                        placeholder="Nama penilaian..."
                                        name="custom_item[<?= $cIdx ?>]"
                                        value="<?= htmlspecialchars($ci['nama_item']) ?>">
                                    <div class="nilai-radio-group" style="flex-shrink:0;">
                                        <?php for ($v = 1; $v <= 5; $v++): $checked = $ci['nilai'] == $v ? 'checked' : ''; ?>
                                            <input type="radio" name="custom_nilai[<?= $cIdx ?>]" id="cv<?= $cIdx . '_' . $v ?>" value="<?= $v ?>" <?= $checked ?>>
                                            <label for="cv<?= $cIdx . '_' . $v ?>"><?= $v ?></label>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="button" onclick="hapusItem(this)" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:16px;line-height:1;" title="Hapus item">×</button>
                                </div>
                            </div>
                            <div style="padding:4px 16px 12px;">
                                <button type="button" onclick="tambahItemCustom(this)" style="background:none;border:none;cursor:pointer;color:var(--hijau);font-size:12px;font-weight:600;padding:4px 0;">
                                    + Tambah Penilaian
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p style="font-size:12px;color:#888;margin-top:8px;">💡 Gunakan fitur ini untuk menambah kategori penilaian di luar komponen standar, misalnya: Prestasi Khusus, Program Ramadhan, dll.</p>
            </div>
            <!-- ===== END CUSTOM ===== -->

            <div class="row g-3 mt-2">
                <div class="col-12">
                    <div class="form-label-custom">Catatan / Rekomendasi</div>
                    <textarea name="catatan" class="form-control-custom" rows="4" placeholder="Tuliskan catatan evaluasi..."><?= htmlspecialchars($editPenilaian['catatan'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn-primary-custom">💾 Simpan Penilaian</button>
                <a href="penilaian.php" class="btn btn-light">Batal</a>
            </div>
        </form>
    </div>

    <script>
        // Data nilai tersimpan (untuk mode edit) — diinjek dari PHP
        const savedNilai = <?= json_encode($editDetail ?: new stdClass()) ?>;

        function loadKomponen(guru_id) {
            if (!guru_id) return;
            const sel = document.getElementById('sel_guru');
            const tipe = sel.options[sel.selectedIndex].getAttribute('data-tipe');
            if (!tipe) return;
            fetch('api_komponen.php?tipe=' + tipe)
                .then(r => r.json())
                .then(data => {
                    const area = document.getElementById('komponen-area');
                    if (!data.length) {
                        area.innerHTML = '<p class="text-muted">Tidak ada komponen penilaian untuk tipe ini. Silakan tambahkan di menu <a href="komponen.php?tab=' + tipe + '">Komponen Penilaian</a> terlebih dahulu.</p>';
                        return;
                    }
                    const kategoris = [...new Set(data.map(k => k.kategori))];
                    const katStyles = {
                        'Disiplin':                  { icon: '⏰', color: '#1a4731', bg: '#e8f5ee' },
                        'Pelaksanaan Pembelajaran':   { icon: '📚', color: '#1e40af', bg: '#eff6ff' },
                        'Kerjasama':                 { icon: '🤝', color: '#7c3aed', bg: '#f5f3ff' },
                        'Kinerja':                   { icon: '📊', color: '#0f766e', bg: '#f0fdfa' },
                        'Administrasi':              { icon: '🗂️', color: '#b45309', bg: '#fef3c7' },
                        'Pelayanan':                 { icon: '🌟', color: '#be185d', bg: '#fdf2f8' },
                    };
                    let html = `<div style="margin-bottom:20px;">
                <div style="display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border-radius:12px;padding:14px 18px;border-left:4px solid var(--hijau);">
                    <span style="font-size:18px;">📋</span>
                    <div>
                        <strong style="font-size:14px;color:var(--hijau);display:block;">Komponen Penilaian</strong>
                        <span style="font-size:11.5px;color:#6b7280;">Skala: 1 = Kurang &nbsp;|&nbsp; 2 = Cukup &nbsp;|&nbsp; 3 = Baik &nbsp;|&nbsp; 4 = Sangat Baik &nbsp;|&nbsp; 5 = Sangat Baik Sekali</span>
                    </div>
                </div></div>`;
                    let katNo = 1;
                    kategoris.forEach(kat => {
                        const items = data.filter(k => k.kategori === kat);
                        const s = katStyles[kat] || {
                            icon: '📌',
                            color: '#374151',
                            bg: '#f9fafb'
                        };
                        html += `<div class="nilai-group" style="margin-bottom:20px;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">
                    <div class="nilai-kategori" style="background:${s.bg};border-left:5px solid ${s.color};padding:12px 18px;display:flex;align-items:center;gap:10px;margin:0;border-radius:0;">
                        <span style="font-size:20px;">${s.icon}</span>
                        <div>
                            <span style="font-size:11px;color:${s.color};font-weight:600;text-transform:uppercase;letter-spacing:0.5px;opacity:0.7;">Kategori ${katNo++}</span>
                            <div style="font-size:14px;font-weight:700;color:${s.color};">${kat}</div>
                        </div>
                    </div>`;
                        items.forEach(item => {
                            html += `<div class="nilai-item" style="border-bottom:1px solid #f3f4f6;padding:10px 18px;"><div class="nilai-item-label"><span class="nilai-item-num">${item.nomor_item}.</span> ${item.nama_item}</div><div class="nilai-radio-group">`;
                            for (let v = 1; v <= 5; v++) {
                                // Gunakan nilai tersimpan jika ada (mode edit), default ke 1
                                const checked = (savedNilai[item.id] == v) ? 'checked' : ((!savedNilai[item.id] && v===1) ? 'checked' : '');
                                html += `<input type="radio" name="nilai[${item.id}]" id="v${item.id}_${v}" value="${v}" ${checked}><label for="v${item.id}_${v}">${v}</label>`;
                            }
                            html += `</div></div>`;
                        });
                        html += `</div>`;
                    });
                    area.innerHTML = html;
                });
        }

        let customIdx = 90000;

        function tambahKategoriCustom() {
            const area = document.getElementById('custom-area');
            const block = document.createElement('div');
            block.className = 'custom-kategori-block';
            block.style.cssText = 'border:1.5px dashed #c9a84c;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#fffdf5;';
            block.innerHTML = `
        <div style="background:#fef9ec;border-left:4px solid #c9a84c;padding:12px 16px;display:flex;align-items:center;gap:10px;">
            <span style="font-size:16px;">✳️</span>
            <div style="flex:1;">
                <span style="font-size:10px;font-weight:600;color:#92400e;text-transform:uppercase;letter-spacing:0.5px;">Kategori Tambahan</span>
                <input type="text" class="form-control-custom" style="font-weight:700;font-size:13.5px;color:#78350f;border:none;background:transparent;padding:2px 0;margin:0;height:auto;"
                    placeholder="cth: Prestasi, Program Khusus, dll"
                    data-kat-name="1">
            </div>
            <button type="button" onclick="hapusKategori(this)" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:20px;line-height:1;padding:0 4px;" title="Hapus kategori ini">×</button>
        </div>
        <div class="custom-items" style="padding:12px 16px 4px;"></div>
        <div style="padding:4px 16px 12px;">
            <button type="button" onclick="tambahItemCustom(this)" style="background:none;border:none;cursor:pointer;color:var(--hijau);font-size:12px;font-weight:600;padding:4px 0;">
                + Tambah Penilaian
            </button>
        </div>`;
            area.appendChild(block);
            const katInput = block.querySelector('[data-kat-name]');
            tambahItemCustom(block.querySelector('button[onclick^="tambahItem"]'));
            katInput.focus();
        }

        function tambahItemCustom(btn) {
            const block = btn.closest('.custom-kategori-block');
            const itemsDiv = block.querySelector('.custom-items');
            const uid = customIdx++;
            // Sync kategori name from block header input to hidden fields
            const katInput = block.querySelector('[data-kat-name]');
            const row = document.createElement('div');
            row.className = 'custom-item-row';
            row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;';
            let radios = '';
            for (let v = 1; v <= 5; v++) {
                radios += `<input type="radio" name="custom_nilai[${uid}]" id="r${uid}_${v}" value="${v}" ${v===1?'checked':''}><label for="r${uid}_${v}">${v}</label>`;
            }
            row.innerHTML = `
        <input type="hidden" class="kat-ref" name="custom_kategori[${uid}]" value="${katInput ? katInput.value : ''}">
        <input type="text" class="form-control-custom" style="flex:1;"
            placeholder="Nama penilaian..."
            name="custom_item[${uid}]">
        <div class="nilai-radio-group" style="flex-shrink:0;">${radios}</div>
        <button type="button" onclick="hapusItem(this)" style="background:none;border:none;cursor:pointer;color:#dc2626;font-size:16px;line-height:1;" title="Hapus item">×</button>`;
            itemsDiv.appendChild(row);
            // Update kat hidden inputs on kat name change
            if (katInput) {
                katInput.addEventListener('input', function() {
                    block.querySelectorAll('.kat-ref').forEach(h => h.value = this.value);
                });
            }
            row.querySelector(`input[name="custom_item[${uid}]"]`).focus();
        }

        function hapusItem(btn) {
            const row = btn.closest('.custom-item-row');
            const block = row.closest('.custom-kategori-block');
            row.remove();
            if (!block.querySelector('.custom-item-row')) hapusKategori(block.querySelector('[onclick="hapusKategori(this)"]'));
        }

        function hapusKategori(btn) {
            btn.closest('.custom-kategori-block').remove();
        }

        // Auto-load komponen saat halaman pertama kali dibuka (mode add & edit)
        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('sel_guru');
            if (sel && sel.value) {
                loadKomponen(sel.value);
            }
            // Pastikan radio value terpilih saat loadKomponen selesai (mode edit)
            sel && sel.addEventListener('change', function() {
                // Reset custom area jika ganti guru
                document.getElementById('custom-area').innerHTML = '';
            });
        });
    </script>

<?php else: ?>

    <div class="data-table-card">
        <div class="card-header-custom">
            <div class="card-title-custom">Daftar Penilaian Kinerja</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <button id="btnHapusTerpilih" onclick="hapusTerpilih()"
                    class="btn-primary-custom" style="background:#dc2626;display:none;">
                    🗑 Hapus Terpilih (<span id="jumlahTerpilih">0</span>)
                </button>
                <button onclick="hapusSemua()"
                    class="btn-primary-custom" style="background:#7f1d1d;">
                    🗑 Hapus Semua
                </button>
                <a href="penilaian.php?action=add" class="btn-primary-custom">+ Tambah Penilaian</a>
            </div>
        </div>

        <!-- Form hapus terpilih: autocomplete="off" agar browser tidak menyimpan isian checkbox -->
        <form id="formHapusTerpilih" method="POST" action="penilaian.php?action=delete_selected" autocomplete="off">
        <table class="table table-hover datatable" style="font-size:13px;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="width:36px;"><input type="checkbox" id="checkAll" title="Pilih semua" onchange="toggleAll(this)"></th>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>Jabatan</th>
                    <th>Periode</th>
                    <th>Tgl Penilaian</th>
                    <th>Nilai Rata²</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($penilaianList as $i => $p): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_ids[]" value="<?= $p['id'] ?>" class="row-check" onchange="updateTerpilih()"></td>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($p['nama']) ?></strong></td>
                        <td><small><?= htmlspecialchars($p['jabatan'] ?? '') ?></small></td>
                        <td><small><?= htmlspecialchars($p['periode']) ?></small></td>
                        <td><?= date('d/m/Y', strtotime($p['tanggal_penilaian'])) ?></td>
                        <td>
                            <?php if ($p['rata_nilai']): ?>
                                <span style="font-weight:600;color:<?= ($p['rata_nilai'] ?? 0) >= 75 ? '#16a34a' : (($p['rata_nilai'] ?? 0) >= 50 ? '#d97706' : '#dc2626') ?>">
                                    <?= $p['rata_nilai'] ?>%
                                </span>
                            <?php else: ?><span class="text-muted">-</span><?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                                <a href="penilaian.php?action=edit&id=<?= $p['id'] ?>" class="btn-primary-custom btn-sm-custom btn-edit">Edit</a>
                                <a href="cetak.php?id=<?= $p['id'] ?>" class="btn-primary-custom btn-sm-custom btn-view" target="_blank">Cetak</a>
                                <button class="btn-primary-custom btn-sm-custom btn-delete" onclick="confirmDelete(<?= $p['id'] ?>,'penilaian.php')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </form>
    </div>

    <script>
    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
        updateTerpilih();
    }
    function updateTerpilih() {
        const checked = document.querySelectorAll('.row-check:checked');
        const btn     = document.getElementById('btnHapusTerpilih');
        const count   = document.getElementById('jumlahTerpilih');
        const master  = document.getElementById('checkAll');
        const all     = document.querySelectorAll('.row-check');
        count.textContent = checked.length;
        btn.style.display = checked.length > 0 ? '' : 'none';
        if (master) master.checked = all.length > 0 && checked.length === all.length;
    }
    function hapusTerpilih() {
        const n = document.querySelectorAll('.row-check:checked').length;
        if (!n) return;
        if (!confirm('Hapus ' + n + ' penilaian yang dipilih? Tindakan ini tidak bisa dibatalkan.')) return;
        document.getElementById('formHapusTerpilih').submit();
    }
    function hapusSemua() {
        if (!confirm('Hapus SEMUA penilaian? Seluruh data nilai akan hilang permanen dan tidak bisa dikembalikan!')) return;
        window.location.href = 'penilaian.php?action=delete_all';
    }
    </script>

<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>