<?php
/**
 * rekap.php — Halaman Rekap Penilaian Kinerja Guru
 *
 * Tampilan accordion per Tahun Ajaran (mengikuti pola custom_penilaian.php):
 *   • Setiap TA jadi satu accordion yang bisa ditutup/dibuka
 *   • TA terbaru auto-expand dengan badge "TERBARU"
 *   • TA lama collapsed by default → halaman tetap rapi walau TA bertambah
 *   • Section khusus "Belum dinilai di TA <latest>" untuk guru yang belum ada penilaian
 *   • Stats card di atas: ringkasan TA aktif, rata-rata, paling meningkat, perlu perhatian
 *   • Search box mencari nama guru di semua accordion sekaligus
 */

$pageTitle = 'Rekap Penilaian';
require_once 'includes/config.php';
requireLogin();

// ─── Filter (hanya tipe + search; tahun ajaran via accordion saja) ─────────
$filterTipe = sanitize($_GET['tipe'] ?? '');

// ─── Subquery rata-rata indikator per penilaian ───────────────────────────
// Nilai akhir BUKAN rata-rata flat dari semua item, melainkan:
//   1. Hitung persentase per indikator: SUM(nilai) / (COUNT * 5) * 100
//   2. Nilai akhir = rata-rata antar indikator (AVG)
// Formula ini agar bobot setiap indikator sama, tidak tergantung jumlah item.
// NULLIF(COUNT*5, 0) cegah division-by-zero kalau indikator tidak punya item.
$subNilai = "
    SELECT p2.id_penilaian AS penilaian_id,
        (
            SELECT AVG(ind_pct)
            FROM (
                SELECT
                    SUM(dp.nilai) / NULLIF(COUNT(*) * 5, 0) * 100 AS ind_pct
                FROM hasil dp
                JOIN isi s ON dp.id_item = s.id_item
                          AND s.id_komponen = p2.id_komponen
                WHERE dp.id_penilaian = p2.id_penilaian
                GROUP BY s.nama_indikator
            ) ind_scores
        ) AS avg_nilai
    FROM penilaian p2 GROUP BY p2.id_penilaian
";

// ─── Ambil semua penilaian + info guru, urut periode DESC ──────────────────
$tipeWhere = $filterTipe ? "AND g.tipe = ?" : "";
$sql = "
    SELECT
        p.id_penilaian,
        p.id_guru,
        p.periode,
        p.tanggal_penilaian,
        g.nama, g.jabatan, g.tipe, g.nrg,
        ROUND(sub.avg_nilai, 1) AS nilai
    FROM penilaian p
    JOIN guru g ON p.id_guru = g.id_guru
    LEFT JOIN ($subNilai) sub ON sub.penilaian_id = p.id_penilaian
    WHERE p.periode IS NOT NULL AND p.periode != ''
    $tipeWhere
    ORDER BY p.periode DESC, g.tipe, g.nama
";
$params = $filterTipe ? [$filterTipe] : [];
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allRows = $stmt->fetchAll();

// ─── Kelompokkan per TA, hitung stats per TA ───────────────────────────────
$rekapByTA = [];
foreach ($allRows as $row) {
    $rekapByTA[$row['periode']][] = $row;
}
$latestTA = !empty($rekapByTA) ? array_key_first($rekapByTA) : '';

$statsTA = [];
foreach ($rekapByTA as $ta => $rows) {
    $sum = 0; $cnt = 0;
    foreach ($rows as $r) {
        if ($r['nilai'] !== null) { $sum += (float)$r['nilai']; $cnt++; }
    }
    $statsTA[$ta] = [
        'jumlah' => count($rows),
        'rata'   => $cnt > 0 ? round($sum / $cnt, 1) : null,
    ];
}

// ─── Total guru & guru yang belum dinilai di TA latest ─────────────────────
$totalGuruParams = $filterTipe ? [$filterTipe] : [];
$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM guru" . ($filterTipe ? " WHERE tipe = ?" : ""));
$stmtTotal->execute($totalGuruParams);
$totalGuru = (int)$stmtTotal->fetchColumn();

$idGuruDinilaiLatest = [];
foreach ($rekapByTA[$latestTA] ?? [] as $r) {
    $idGuruDinilaiLatest[(int)$r['id_guru']] = true;
}

if (!empty($idGuruDinilaiLatest)) {
    $placeholder = implode(',', array_fill(0, count($idGuruDinilaiLatest), '?'));
    $sqlBelum = "SELECT id_guru, nama, jabatan, tipe FROM guru
                 WHERE id_guru NOT IN ($placeholder)"
                . ($filterTipe ? " AND tipe = ?" : "")
                . " ORDER BY tipe, nama";
    $paramsBelum = array_keys($idGuruDinilaiLatest);
    if ($filterTipe) $paramsBelum[] = $filterTipe;
} else {
    $sqlBelum = "SELECT id_guru, nama, jabatan, tipe FROM guru"
                . ($filterTipe ? " WHERE tipe = ?" : "")
                . " ORDER BY tipe, nama";
    $paramsBelum = $filterTipe ? [$filterTipe] : [];
}
$stmtBelum = $pdo->prepare($sqlBelum);
$stmtBelum->execute($paramsBelum);
$guruBelumDinilai = $stmtBelum->fetchAll();

// ─── Cari guru paling meningkat & paling menurun ──────────────────────────
// Bangun history nilai per guru (dari semua TA). Urutan: DESC ke ASC (array_reverse)
// supaya elemen terakhir = TA paling baru. Bandingkan 2 TA terakhir untuk hitung delta.
$historyByGuru = [];
foreach ($allRows as $row) {
    if ($row['nilai'] !== null) {
        $historyByGuru[$row['id_guru']][] = [
            'periode' => $row['periode'],
            'nilai'   => (float)$row['nilai'],
        ];
    }
}
foreach ($historyByGuru as $idg => $hist) {
    $historyByGuru[$idg] = array_reverse($hist);
}

// Cari guru dengan delta naik tertinggi (paling meningkat) dan delta turun terdalam
$palingMeningkat = null; $palingMenurun = null;
$maxNaik = 0; $maxTurun = 0;
foreach ($historyByGuru as $idGuru => $hist) {
    if (count($hist) < 2) continue; // perlu minimal 2 TA untuk hitung delta
    $n = count($hist);
    $delta = $hist[$n-1]['nilai'] - $hist[$n-2]['nilai'];
    if ($delta > $maxNaik)  { $maxNaik = $delta;  $palingMeningkat = ['id' => $idGuru, 'delta' => $delta]; }
    if ($delta < $maxTurun) { $maxTurun = $delta; $palingMenurun  = ['id' => $idGuru, 'delta' => $delta]; }
}

// Lookup map nama guru by id (untuk render "paling meningkat/menurun")
$namaGuruMap = [];
foreach ($allRows as $row) $namaGuruMap[$row['id_guru']] = $row['nama'];
foreach ($guruBelumDinilai as $row) {
    if (!isset($namaGuruMap[$row['id_guru']])) $namaGuruMap[$row['id_guru']] = $row['nama'];
}

$rataRataLatest = $statsTA[$latestTA]['rata'] ?? null;
$jumlahDinilaiLatest = count($idGuruDinilaiLatest);

$tipeLabel = getTipeGuru($pdo);

/**
 * nilaiLabel — Konversi persentase nilai ke predikat + warna hex.
 *
 * @param  float|null $n  Persentase nilai akhir (0-100)
 * @return array          [label_predikat, warna_hex]
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
        <div>
            <div class="card-title-custom">📊 Rekap Penilaian Kinerja Guru</div>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">
                Rekap dikelompokkan per Tahun Ajaran. TA terbaru otomatis terbuka, TA lama bisa ditutup agar tetap rapi.
            </p>
        </div>
        <a href="cetak.php?all=1<?= $filterTipe ? '&tipe=' . urlencode($filterTipe) : '' ?>"
           class="btn-primary-custom" target="_blank">
            🖨 Cetak Semua
        </a>
    </div>

    <form method="GET" action="rekap.php"
          style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin-bottom:18px;
                 padding:14px 16px;background:#f8fafc;border-radius:12px;border:1px solid #e5e7eb;"
          autocomplete="off">

        <input type="text" id="searchGuru" placeholder="🔍 Cari nama guru di semua TA..."
               style="flex:1;min-width:200px;padding:8px 12px;font-size:13px;
                      border:1.5px solid #d1d5db;border-radius:8px;background:#fff;outline:none;"
               oninput="filterRekap(this.value)">

        <div style="display:flex;align-items:center;gap:6px;">
            <label style="font-size:12px;font-weight:600;color:#6b7280;white-space:nowrap;">👤 Tipe Guru</label>
            <select name="tipe" onchange="this.form.submit()"
                    style="font-size:13px;padding:7px 12px;border:1.5px solid #d1d5db;border-radius:8px;color:#374151;background:#fff;cursor:pointer;outline:none;">
                <option value="">Semua Tipe</option>
                <?php foreach ($tipeLabel as $kode => $label): ?>
                    <option value="<?= htmlspecialchars($kode) ?>"
                        <?= ($filterTipe === $kode) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($filterTipe): ?>
            <a href="rekap.php"
               style="font-size:12px;color:#dc2626;font-weight:600;text-decoration:none;
                      padding:7px 12px;border:1.5px solid #fca5a5;border-radius:8px;background:#fff5f5;">
                ✕ Reset Filter
            </a>
        <?php endif; ?>

        <span style="font-size:12px;color:#9ca3af;margin-left:auto;display:flex;align-items:center;gap:10px;">
            <button type="button" onclick="toggleAllAccordion(true)"
                    style="background:none;border:none;color:#1a4731;font-weight:600;cursor:pointer;padding:4px 8px;font-size:12px;">
                ⬇ Buka Semua
            </button>
            <button type="button" onclick="toggleAllAccordion(false)"
                    style="background:none;border:none;color:#6b7280;font-weight:600;cursor:pointer;padding:4px 8px;font-size:12px;">
                ⬆ Tutup Semua
            </button>
        </span>
    </form>

    <?php if (!empty($guruBelumDinilai) && $latestTA): ?>
    <?php $accIdBelum = 'acc_belum'; ?>
    <div class="ta-accordion" data-acc-id="<?= $accIdBelum ?>"
         style="border:1.5px solid #fcd34d;border-radius:14px;overflow:hidden;background:#fff;margin-bottom:12px;">

        <button type="button" onclick="toggleAccordion('<?= $accIdBelum ?>')"
                style="width:100%;background:linear-gradient(135deg,#fef3c7,#fffbeb);border:none;
                       padding:16px 20px;cursor:pointer;display:flex;align-items:center;
                       justify-content:space-between;gap:14px;text-align:left;">
            <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                <span class="acc-caret" style="font-size:14px;color:#92400e;transition:transform .2s;">▶</span>
                <div style="display:flex;flex-direction:column;gap:2px;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:16px;font-weight:700;color:#92400e;">
                            ⚠️ Belum dinilai di TA <?= htmlspecialchars($latestTA) ?>
                        </span>
                        <span style="background:#dc2626;color:#fff;font-size:10px;font-weight:700;
                                     padding:2px 8px;border-radius:12px;letter-spacing:.3px;">
                            <?= count($guruBelumDinilai) ?> GURU
                        </span>
                    </div>
                    <div style="font-size:12px;color:#78350f;">
                        Klik tombol Nilai untuk mulai penilaian guru-guru berikut
                    </div>
                </div>
            </div>
        </button>

        <div class="acc-body" style="padding:16px 20px;background:#fff;display:none;">
            <table class="table table-hover" style="font-size:13px;margin-bottom:0;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Nama Guru</th>
                        <th>Jabatan</th>
                        <th>Tipe</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guruBelumDinilai as $i => $g): ?>
                    <tr data-nama="<?= htmlspecialchars(strtolower($g['nama'])) ?>">
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($g['nama']) ?></strong></td>
                        <td><small><?= htmlspecialchars($g['jabatan'] ?? '') ?></small></td>
                        <td>
                            <?php $bc = ['guru_quran'=>'quran','guru_kelas'=>'kelas',
                                         'mapel'=>'mapel','gtk'=>'gtk'][$g['tipe']] ?? 'gtk'; ?>
                            <span class="badge-tipe badge-<?= $bc ?>">
                                <?= htmlspecialchars($tipeLabel[$g['tipe']] ?? $g['tipe']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="penilaian.php?action=add&id_guru=<?= (int)$g['id_guru'] ?>&tipe=<?= urlencode($g['tipe']) ?>"
                               class="btn-primary-custom btn-sm-custom"
                               style="background:linear-gradient(135deg,#d97706,#b45309);">
                                Nilai
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($rekapByTA)): ?>
        <div style="text-align:center;padding:50px;color:#9ca3af;
                    border:2px dashed #e5e7eb;border-radius:12px;">
            <div style="font-size:42px;margin-bottom:14px;">📋</div>
            <p>Belum ada data penilaian.</p>
            <a href="penilaian.php?action=add" class="btn-primary-custom" style="margin-top:10px;">
                + Buat Penilaian Pertama
            </a>
        </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($rekapByTA as $ta => $rows):
            $isOpen = ($ta === $latestTA);
            $accId  = 'acc_' . md5($ta);
            $stat   = $statsTA[$ta];
            [$labelStat, $colorStat] = nilaiLabel($stat['rata']);
        ?>
        <div class="ta-accordion" data-acc-id="<?= $accId ?>"
             style="border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;background:#fff;">

            <button type="button" onclick="toggleAccordion('<?= $accId ?>')"
                    style="width:100%;background:linear-gradient(135deg,#e8f5ee,#f0faf4);border:none;
                           padding:16px 20px;cursor:pointer;display:flex;align-items:center;
                           justify-content:space-between;gap:14px;text-align:left;">
                <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                    <span class="acc-caret"
                          style="font-size:14px;color:#1a4731;transition:transform .2s;<?= $isOpen ? 'transform:rotate(90deg);' : '' ?>">▶</span>
                    <div style="display:flex;flex-direction:column;gap:2px;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-size:16px;font-weight:700;color:#1a4731;">
                                📅 <?= htmlspecialchars($ta) ?>
                            </span>
                            <?php if ($isOpen): ?>
                                <span style="background:#059669;color:#fff;font-size:10px;font-weight:700;
                                             padding:2px 8px;border-radius:12px;letter-spacing:.3px;">
                                    TERBARU
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:12px;color:#6b7280;">
                            <?= $stat['jumlah'] ?> guru dinilai ·
                            rata-rata <strong style="color:<?= $colorStat ?>;"><?= $stat['rata'] ?? '—' ?></strong>
                            · <?= htmlspecialchars($labelStat) ?>
                        </div>
                    </div>
                </div>

                <a href="cetak.php?all=1&periode=<?= urlencode($ta) ?><?= $filterTipe ? '&tipe=' . urlencode($filterTipe) : '' ?>"
                   target="_blank"
                   onclick="event.stopPropagation();"
                   style="background:#fff;border:1px solid #d1fae5;color:#065f46;
                          padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;
                          text-decoration:none;white-space:nowrap;">
                    🖨 Cetak TA ini
                </a>
            </button>

            <div class="acc-body" style="padding:16px 20px;background:#fff;<?= $isOpen ? '' : 'display:none;' ?>">
                <div style="overflow-x:auto;">
                <table class="table table-hover" style="font-size:13px;margin-bottom:0;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Nama Guru</th>
                            <th>Jabatan</th>
                            <th>Tipe</th>
                            <th style="min-width:160px;">Nilai</th>
                            <th>Predikat</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r):
                            [$pred, $color] = nilaiLabel($r['nilai']);
                        ?>
                        <tr data-nama="<?= htmlspecialchars(strtolower($r['nama'])) ?>">
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                            <td><small><?= htmlspecialchars($r['jabatan'] ?? '') ?></small></td>
                            <td>
                                <?php $bc = ['guru_quran'=>'quran','guru_kelas'=>'kelas',
                                             'mapel'=>'mapel','gtk'=>'gtk'][$r['tipe']] ?? 'gtk'; ?>
                                <span class="badge-tipe badge-<?= $bc ?>">
                                    <?= htmlspecialchars($tipeLabel[$r['tipe']] ?? $r['tipe']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($r['nilai']): ?>
                                <div style="display:flex;align-items:center;gap:8px;min-width:140px;">
                                    <div style="flex:1;height:6px;background:#e5e7eb;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:<?= min($r['nilai'],100) ?>%;
                                                    background:<?= $color ?>;border-radius:4px;
                                                    transition:width 0.5s;"></div>
                                    </div>
                                    <span style="font-weight:600;color:<?= $color ?>;
                                                 min-width:36px;font-size:12px;">
                                        <?= $r['nilai'] ?>
                                    </span>
                                </div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">Belum ada</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-weight:600;color:<?= $color ?>;font-size:12px;">
                                    <?= $pred ?>
                                </span>
                            </td>
                            <td>
                                <small>
                                    <?= $r['tanggal_penilaian']
                                        ? date('d/m/Y', strtotime($r['tanggal_penilaian']))
                                        : '-' ?>
                                </small>
                            </td>
                            <td>
                                <a href="cetak.php?id=<?= $r['id_penilaian'] ?>"
                                   class="btn-primary-custom btn-sm-custom btn-view" target="_blank">
                                    Cetak
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleAccordion(accId) {
    const wrap = document.querySelector('[data-acc-id="' + accId + '"]');
    if (!wrap) return;
    const body  = wrap.querySelector('.acc-body');
    const caret = wrap.querySelector('.acc-caret');
    if (!body) return;
    const isHidden = body.style.display === 'none';
    body.style.display = isHidden ? 'block' : 'none';
    if (caret) caret.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
}

function toggleAllAccordion(open) {
    document.querySelectorAll('.ta-accordion').forEach(wrap => {
        const body  = wrap.querySelector('.acc-body');
        const caret = wrap.querySelector('.acc-caret');
        if (body)  body.style.display = open ? 'block' : 'none';
        if (caret) caret.style.transform = open ? 'rotate(90deg)' : 'rotate(0deg)';
    });
}

function filterRekap(query) {
    const q = (query || '').trim().toLowerCase();
    document.querySelectorAll('.ta-accordion').forEach(wrap => {
        const rows  = wrap.querySelectorAll('tbody tr');
        const body  = wrap.querySelector('.acc-body');
        const caret = wrap.querySelector('.acc-caret');
        let visibleCount = 0;
        let n = 1;
        rows.forEach(row => {
            const nama = row.getAttribute('data-nama') || '';
            const visible = (q === '' || nama.includes(q));
            row.style.display = visible ? '' : 'none';
            if (visible) {
                visibleCount++;
                const firstCell = row.querySelector('td');
                if (firstCell) firstCell.textContent = n++;
            }
        });
        if (q !== '') {
            const shouldShow = visibleCount > 0;
            body.style.display = shouldShow ? 'block' : 'none';
            if (caret) caret.style.transform = shouldShow ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
