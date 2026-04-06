<?php
/**
 * rekap.php — Halaman Rekap Penilaian Kinerja Guru
 *
 * Menampilkan daftar semua guru beserta rata-rata nilai akhir penilaian.
 * Mendukung filter berdasarkan tipe guru dan periode penilaian.
 */

$pageTitle = 'Rekap Penilaian';
require_once 'includes/config.php';
requireLogin();

// ─── Ambil daftar periode unik untuk dropdown filter ───────────────────────
// GROUP BY periode (label) saja agar tidak muncul duplikat meski periode_awal/akhir sedikit beda
$periodeList = $pdo->query("
    SELECT periode, MIN(periode_awal) AS periode_awal, MAX(periode_akhir) AS periode_akhir
    FROM penilaian
    WHERE periode IS NOT NULL AND periode != ''
    GROUP BY periode
    ORDER BY MIN(periode_awal) DESC
")->fetchAll();

// ─── Baca parameter filter dari GET ────────────────────────────────────────
// sanitize() mencegah XSS jika nilai filter di-echo ke HTML tanpa escaping tambahan
$filterPeriode = sanitize($_GET['periode'] ?? '');

// ─── Query rekap utama dengan opsional filter tipe & periode ──────────────
// Saat filter periode aktif  → INNER JOIN: hanya guru yang sudah dinilai di periode itu
// Tanpa filter periode       → LEFT JOIN : semua guru ditampilkan (termasuk belum dinilai)
$params = [];

// ─── WARN-06 FIX: Optimasi subquery nilai ────────────────────────────────────
// Sebelumnya: 2 correlated subquery per baris penilaian (SUM + COUNT)
// Sekarang: satu agregasi GROUP BY dalam derived table — satu pass untuk semua
$subNilai = "
    SELECT
        penilaian_id,
        COALESCE(SUM(nilai), 0)                           AS total_nilai,
        COALESCE(COUNT(id),  0)                           AS total_item,
        ROUND(
            COALESCE(SUM(nilai), 0)
            / NULLIF(COALESCE(COUNT(id), 0) * 5, 0)
            * 100
        , 1)                                              AS avg_nilai
    FROM detail_penilaian
    GROUP BY penilaian_id
";

$filterTipe = sanitize($_GET['tipe'] ?? '');

if ($filterPeriode) {
    // Filter periode aktif: hanya tampilkan guru yang punya penilaian di periode ini
    $joinType = 'INNER JOIN';
    $joinCond = "p.guru_id = g.id AND p.periode = ?";
    $params[] = $filterPeriode;
} else {
    // Tanpa filter: tampilkan semua guru, nilai dari semua penilaian
    $joinType = 'LEFT JOIN';
    $joinCond = "p.guru_id = g.id";
}

// Filter tipe di WHERE luar (berlaku untuk kedua mode join)
$tipeWhere = $filterTipe ? "WHERE g.tipe = ?" : "";
if ($filterTipe) $params[] = $filterTipe;

$sql = "
    SELECT
        g.id, g.nama, g.jabatan, g.tipe, g.nrg,
        COUNT(p.id)                         AS jml_penilaian,
        ROUND(AVG(sub.avg_nilai), 1)        AS avg_final,
        MAX(p.tanggal_penilaian)            AS last_penilaian,
        MAX(p.id)                           AS last_penilaian_id
    FROM guru g
    $joinType penilaian p ON $joinCond
    LEFT JOIN ($subNilai) sub ON sub.penilaian_id = p.id
    $tipeWhere
    GROUP BY g.id, g.nama, g.jabatan, g.tipe, g.nrg
    ORDER BY g.tipe, g.nama
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rekap = $stmt->fetchAll();

// ─── SARAN-06: Export CSV ─────────────────────────────────────────────────────
// Dipanggil saat ada parameter ?export=csv — pakai data $rekap yang sudah di-fetch
// agar tidak perlu query ulang. Keluar sebelum output HTML.
if (($_GET['export'] ?? '') === 'csv') {
    // Nama file mencerminkan filter aktif untuk mudah diidentifikasi
    $filePart = 'rekap';
    if ($filterPeriode) $filePart .= '_' . preg_replace('/[^a-zA-Z0-9_]/', '-', $filterPeriode);
    if ($filterTipe)    $filePart .= '_' . $filterTipe;
    $filename = $filePart . '_' . date('Ymd') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    // BOM UTF-8 agar Excel bisa baca karakter Indonesia (ä, ñ, dsb.) dengan benar
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');

    // Header kolom
    fputcsv($out, [
        'No', 'Nama Guru', 'NRG', 'Jabatan', 'Tipe',
        'Jumlah Penilaian', 'Nilai Akhir (%)', 'Predikat', 'Terakhir Dinilai'
    ]);

    // Data baris
    foreach ($rekap as $i => $r) {
        [$pred] = nilaiLabel($r['avg_final']);
        fputcsv($out, [
            $i + 1,
            $r['nama'],
            $r['nrg'] ?? '',
            $r['jabatan'] ?? '',
            $tipeLabel[$r['tipe']] ?? $r['tipe'],
            $r['jml_penilaian'] ?: 0,
            $r['avg_final'] !== null ? $r['avg_final'] . '%' : 'Belum dinilai',
            $pred,
            $r['last_penilaian'] ? date('d/m/Y', strtotime($r['last_penilaian'])) : '-',
        ]);
    }

    fclose($out);
    exit;
}

// ─── Label tipe guru ────────────────────────────────────────────────────────
// Ambil label tipe guru dari database (dinamis)
$tipeLabel = getTipeGuru($pdo);

/**
 * Mengembalikan [label predikat, warna hex] berdasarkan nilai persentase.
 *
 * @param float|null $n  Nilai persentase (0–100), null jika belum dinilai
 * @return array         [string $label, string $color]
 */
function nilaiLabel($n): array
{
    if ($n === null) return ['Belum Dinilai', '#6b7280'];
    if ($n >= 90)   return ['Sangat Baik Sekali', '#7c3aed'];
    if ($n >= 75)   return ['Sangat Baik', '#16a34a'];
    if ($n >= 60)   return ['Baik', '#2563eb'];
    if ($n >= 40)   return ['Cukup', '#d97706'];
    return ['Kurang', '#dc2626'];
}
?>

<?php require_once 'includes/header.php'; ?>

<div class="data-table-card">
    <div class="card-header-custom">
        <div class="card-title-custom">Rekap Penilaian Kinerja Semua Guru</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <!-- SARAN-06: Tombol Export CSV — menggunakan filter aktif yang sama -->
            <a href="rekap.php?export=csv<?= $filterPeriode ? '&periode=' . urlencode($filterPeriode) : '' ?><?= $filterTipe ? '&tipe=' . urlencode($filterTipe) : '' ?>"
               class="btn-primary-custom" style="background:linear-gradient(135deg,#0f766e,#0d9488);">
                📊 Export CSV
            </a>
            <!-- Tombol Cetak Semua — kirim filter periode & tipe jika ada -->
            <a href="cetak.php?all=1<?= $filterPeriode ? '&periode=' . urlencode($filterPeriode) : '' ?><?= $filterTipe ? '&tipe=' . urlencode($filterTipe) : '' ?>"
               class="btn-primary-custom" target="_blank">
                🖨 Cetak Semua
            </a>
        </div>
    </div>

    <!-- ─── Filter Tipe + Periode ──────────────────────────────────────── -->
    <!-- Form filter tipe & periode: autocomplete="off" mencegah isian tersimpan di browser -->
    <form method="GET" action="rekap.php" style="display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;" autocomplete="off">
        <select name="tipe" id="filterTipe" class="form-control-custom"
                style="padding:8px 12px;font-size:13px;width:auto;" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            <?php foreach ($tipeLabel as $kode => $label): ?>
                <option value="<?= htmlspecialchars($kode) ?>"
                    <?= ($filterTipe === $kode) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="periode" class="form-control-custom"
                style="padding:8px 12px;font-size:13px;width:auto;" onchange="this.form.submit()">
            <option value="">Semua Periode</option>
            <?php foreach ($periodeList as $p): ?>
                <option value="<?= htmlspecialchars($p['periode']) ?>"
                    <?= $filterPeriode === $p['periode'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['periode']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($filterPeriode || $filterTipe): ?>
            <a href="rekap.php" class="btn-primary-custom" style="background:#6b7280;padding:8px 14px;font-size:13px;">
                ✕ Reset
            </a>
        <?php endif; ?>
    </form>

    <!-- ─── Tabel Rekap ─────────────────────────────────────────────────── -->
    <table class="table table-hover" id="rekapTable" style="font-size:13px;">
        <thead style="background:#f8fafc;">
            <tr>
                <th>No</th>
                <th>Nama Guru</th>
                <th>Jabatan</th>
                <th>Tipe</th>
                <th>Jml Penilaian</th>
                <th>Nilai Akhir</th>
                <th>Predikat</th>
                <th>Terakhir Dinilai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekap as $i => $r):
                [$pred, $color] = nilaiLabel($r['avg_final']);
            ?>
            <tr data-tipe="<?= $r['tipe'] ?>">
                <td><?= $i + 1 ?></td>
                <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                <td><small><?= htmlspecialchars($r['jabatan'] ?? '') ?></small></td>
                <td>
<?php $bc = ['guru_quran'=>'quran','guru_kelas'=>'kelas','mapel'=>'mapel','gtk'=>'gtk'][$r['tipe']] ?? 'gtk'; ?>
                    <span class="badge-tipe badge-<?= $bc ?>">
                        <?= htmlspecialchars($tipeLabel[$r['tipe']] ?? $r['tipe']) ?>
                    </span>
                </td>
                <td><?= $r['jml_penilaian'] ?: '-' ?></td>
                <td>
                    <?php if ($r['avg_final']): ?>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:8px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                                <div style="height:100%;width:<?= min($r['avg_final'],100) ?>%;background:<?= $color ?>;border-radius:4px;transition:width 0.5s;"></div>
                            </div>
                            <span style="font-weight:600;color:<?= $color ?>;min-width:36px;"><?= $r['avg_final'] ?>%</span>
                        </div>
                    <?php else: ?>
                        <span class="text-muted" style="font-size:12px;">Belum ada</span>
                    <?php endif; ?>
                </td>
                <td><span style="font-weight:600;color:<?= $color ?>;font-size:12px;"><?= $pred ?></span></td>
                <td><small><?= $r['last_penilaian'] ? date('d/m/Y', strtotime($r['last_penilaian'])) : '-' ?></small></td>
                <td>
                    <?php if ($r['last_penilaian_id']): ?>
                        <a href="cetak.php?id=<?= $r['last_penilaian_id'] ?>"
                           class="btn-primary-custom btn-sm-custom btn-view" target="_blank">Cetak</a>
                    <?php else: ?>
                        <a href="penilaian.php?action=add"
                           class="btn-primary-custom btn-sm-custom"
                           style="background:linear-gradient(135deg,#d97706,#b45309);">Nilai</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Filter tipe & periode kini keduanya diproses server-side — tidak perlu JS tambahan
</script>

<?php require_once 'includes/footer.php'; ?>
