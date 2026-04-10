<?php
/**
 * ranking.php — Halaman Ranking Guru Berdasarkan Nilai Kinerja
 *
 * Menampilkan peringkat semua guru yang sudah memiliki penilaian,
 * diurutkan dari nilai tertinggi ke terendah.
 * Mendukung filter per tipe guru dan tampilkan predikat otomatis.
 */

// ─── Set judul halaman & load header (sidebar + navbar) ─────────────────────
$pageTitle = 'Ranking Guru';
require_once 'includes/config.php';
requireLogin();

// ─── Query semua guru yang sudah punya penilaian, nilai terbaru per guru ─────
$rankingAll = $pdo->query("
    SELECT
        g.id_guru, g.nama, g.jabatan, g.tipe, g.nrg,
        COUNT(p.id_penilaian) as jml_penilaian,
        p_last.tanggal_penilaian as last_penilaian,
        p_last.id_penilaian as last_id,
        (
            SELECT ROUND(AVG(ind_pct), 1)
            FROM (
                SELECT
                    SUM(dp2.nilai) / NULLIF(COUNT(dp2.id_hasil) * 5, 0) * 100 AS ind_pct
                FROM hasil dp2
                JOIN isi s ON dp2.id_item = s.id_item
                          AND s.id_komponen = p_last.id_komponen
                WHERE dp2.id_penilaian = p_last.id_penilaian
                GROUP BY s.nama_indikator
            ) ind_scores
        ) as nilai_akhir
    FROM guru g
    LEFT JOIN penilaian p ON p.id_guru = g.id_guru
    LEFT JOIN penilaian p_last ON p_last.id_penilaian = (
        SELECT id_penilaian FROM penilaian
        WHERE id_guru = g.id_guru
        ORDER BY tanggal_penilaian DESC, id_penilaian DESC
        LIMIT 1
    )
    GROUP BY g.id_guru, p_last.id_penilaian, p_last.tanggal_penilaian
    HAVING nilai_akhir IS NOT NULL
    ORDER BY nilai_akhir DESC
")->fetchAll();

// Beri nomor ranking
$ranked = [];
$rank = 1;
foreach ($rankingAll as $r) {
    $r['rank'] = $rank++;
    $r['nilai_akhir'] = round($r['nilai_akhir'], 1);
    $ranked[] = $r;
}

// Statistik ringkas
$total   = count($ranked);
$belumDinilai = $pdo->query("
    SELECT COUNT(*) FROM guru g
    WHERE NOT EXISTS (SELECT 1 FROM penilaian WHERE id_guru = g.id_guru)
")->fetchColumn();

$avgAll  = $total > 0 ? round(array_sum(array_column($ranked, 'nilai_akhir')) / $total, 1) : 0;
$tertinggi = $total > 0 ? $ranked[0] : null;
$terendah  = $total > 0 ? $ranked[$total - 1] : null;

// Ambil label tipe guru dari database (dinamis)
$tipeLabel = getTipeGuru($pdo);

function predikat($n)
{
    if ($n === null) return ['Belum Dinilai', '#6b7280', '⬜'];
    if ($n >= 90)    return ['Sangat Baik Sekali', '#7c3aed', '🟣'];
    if ($n >= 75)    return ['Sangat Baik', '#16a34a', '🟢'];
    if ($n >= 60)    return ['Baik', '#2563eb', '🔵'];
    if ($n >= 40)    return ['Cukup', '#d97706', '🟡'];
    return ['Kurang', '#dc2626', '🔴'];
}

function medalEmoji($rank)
{
    if ($rank === 1) return '🥇';
    if ($rank === 2) return '🥈';
    if ($rank === 3) return '🥉';
    return null;
}
?>

<!-- Filter + Tabel Ranking -->
<?php require_once 'includes/header.php'; ?>

<!-- Kartu Statistik -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px;">
    <div style="background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e5e7eb;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:#e8f5ee;display:flex;align-items:center;justify-content:center;font-size:20px;">🏆</div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#1a4731;"><?= $total ?></div>
            <div style="font-size:12px;color:#6b7280;">Guru Terperingkat</div>
        </div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e5e7eb;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:20px;">📊</div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#1e40af;"><?= $avgAll ?>%</div>
            <div style="font-size:12px;color:#6b7280;">Rata-rata Semua</div>
        </div>
    </div>
    <?php if ($tertinggi): [$pr, $col] = predikat($tertinggi['nilai_akhir']); ?>
        <div style="background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e5e7eb;display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#fef9ec;display:flex;align-items:center;justify-content:center;font-size:20px;">🥇</div>
            <div style="min-width:0;">
                <div style="font-size:14px;font-weight:700;color:#78350f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;"><?= htmlspecialchars($tertinggi['nama']) ?></div>
                <div style="font-size:12px;color:#6b7280;"><?= $tertinggi['nilai_akhir'] ?>% — Tertinggi</div>
            </div>
        </div>
    <?php endif; ?>
    <div style="background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e5e7eb;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:#fef3c7;display:flex;align-items:center;justify-content:center;font-size:20px;">⏳</div>
        <div>
            <div style="font-size:22px;font-weight:700;color:#d97706;"><?= $belumDinilai ?></div>
            <div style="font-size:12px;color:#6b7280;">Belum Dinilai</div>
        </div>
    </div>
</div>

<div class="data-table-card">
    <div class="card-header-custom" style="flex-wrap:wrap;gap:10px;">
        <div class="card-title-custom">🏆 Ranking Kinerja Guru</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <select id="filterTipe" class="form-control-custom" style="padding:7px 12px;font-size:13px;" onchange="applyFilter()">
                <option value="">Semua Tipe</option>
                <?php foreach ($tipeLabel as $kode => $label): ?>
                    <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filterPredikat" class="form-control-custom" style="padding:7px 12px;font-size:13px;" onchange="applyFilter()">
                <option value="">Semua Predikat</option>
                <option value="90">Sangat Baik Sekali (≥90%)</option>
                <option value="75">Sangat Baik (≥75%)</option>
                <option value="60">Baik (≥60%)</option>
                <option value="40">Cukup (≥40%)</option>
                <option value="0">Kurang (&lt;40%)</option>
            </select>
        </div>
    </div>

    <!-- Top 3 Podium -->
    <?php if (count($ranked) >= 1): ?>
        <div id="podiumSection" style="background:linear-gradient(135deg,#f9fafb,#f0fdf4);border-radius:14px;padding:20px 24px;margin-bottom:20px;border:1px solid #e5e7eb;">
            <div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:16px;text-align:center;text-transform:uppercase;letter-spacing:0.5px;">🏆 Podium Teratas</div>
            <div style="display:flex;justify-content:center;align-items:flex-end;gap:16px;flex-wrap:wrap;">
                <?php
                $podiumOrder = [1, 0, 2]; // tampilkan #2, #1, #3
                $podiumHeights = [1 => 110, 0 => 130, 2 => 90];
                $podiumColors  = [1 => '#c0c0c0', 0 => '#f5c842', 2 => '#cd7f32'];
                $podiumBg      = [1 => '#f8fafc', 0 => '#fef9ec', 2 => '#fdf6ee'];
                foreach ($podiumOrder as $pIdx):
                    if (!isset($ranked[$pIdx])) continue;
                    $pr = $ranked[$pIdx];
                    [$pred, $col] = predikat($pr['nilai_akhir']);
                    $medal = ['🥇', '🥈', '🥉'][$pIdx];
                    $ht = $podiumHeights[$pIdx];
                    $pc = $podiumColors[$pIdx];
                    $pb = $podiumBg[$pIdx];
                ?>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;flex:0 0 auto;">
                        <div style="font-size:24px;"><?= $medal ?></div>
                        <div style="background:<?= $pb ?>;border:2px solid <?= $pc ?>;border-radius:12px;padding:10px 14px;text-align:center;max-width:140px;">
                            <div style="font-size:13px;font-weight:700;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;"><?= htmlspecialchars($pr['nama']) ?></div>
                            <div style="font-size:11px;color:#6b7280;margin-top:2px;"><?= htmlspecialchars($pr['jabatan'] ?? '') ?></div>
                            <div style="font-size:20px;font-weight:800;color:<?= $col ?>;margin-top:4px;"><?= $pr['nilai_akhir'] ?>%</div>
                            <div style="font-size:11px;color:<?= $col ?>;font-weight:600;"><?= $pred ?></div>
                        </div>
                        <div style="background:<?= $pc ?>;color:#fff;font-weight:800;font-size:13px;padding:6px 18px;border-radius:0 0 8px 8px;min-height:<?= $ht / 3 ?>px;display:flex;align-items:center;justify-content:center;">
                            #<?= $pIdx + 1 ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabel Lengkap -->
    <div style="overflow-x:auto;">
        <table class="table table-hover" id="rankTable" style="font-size:13px;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="width:60px;">Rank</th>
                    <th>Nama Guru</th>
                    <th>Jabatan</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th style="min-width:180px;">Nilai Akhir</th>
                    <th>Predikat</th>
                    <th>Tgl Penilaian</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranked as $r):
                    [$pred, $col, $dot] = predikat($r['nilai_akhir']);
                    $medal = medalEmoji($r['rank']);
                    $badgeMap = ['guru_quran'=>'quran','guru_kelas'=>'kelas','mapel'=>'mapel','gtk'=>'gtk'];
                    $tipeKey  = $badgeMap[$r['tipe']] ?? 'gtk';
                ?>
                    <tr data-tipe="<?= $r['tipe'] ?>" data-nilai="<?= $r['nilai_akhir'] ?>" data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>" data-jabatan="<?= htmlspecialchars($r['jabatan'] ?? '', ENT_QUOTES) ?>" data-col="<?= $col ?>" data-pred="<?= $pred ?>">
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <?php if ($medal): ?>
                                    <span style="font-size:18px;"><?= $medal ?></span>
                                <?php else: ?>
                                    <span style="font-weight:700;color:#9ca3af;font-size:14px;">#<?= $r['rank'] ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
                        <td><small style="color:#6b7280;"><?= htmlspecialchars($r['jabatan'] ?? '') ?></small></td>
                        <td>
                            <span class="badge-tipe badge-<?= $tipeKey ?>">
                                <?= $tipeLabel[$r['tipe']] ?? $r['tipe'] ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-size:11.5px;font-weight:600;color:#16a34a;">✓ Sudah Dinilai</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="flex:1;height:10px;background:#e5e7eb;border-radius:5px;overflow:hidden;min-width:80px;">
                                    <div style="height:100%;width:<?= min($r['nilai_akhir'], 100) ?>%;background:<?= $col ?>;border-radius:5px;transition:width 0.6s ease;"></div>
                                </div>
                                <span style="font-weight:700;color:<?= $col ?>;min-width:42px;text-align:right;"><?= $r['nilai_akhir'] ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span style="font-size:11.5px;font-weight:600;color:<?= $col ?>;"><?= $dot ?> <?= $pred ?></span>
                        </td>
                        <td><small><?= $r['last_penilaian'] ? date('d/m/Y', strtotime($r['last_penilaian'])) : '-' ?></small></td>
                        <td>
                            <?php if ($r['last_id']): ?>
                                <a href="cetak.php?id=<?= $r['last_id'] ?>" class="btn-primary-custom btn-sm-custom btn-view" target="_blank">Cetak</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($ranked)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center;color:#9ca3af;padding:40px;">Belum ada data penilaian.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($belumDinilai > 0): ?>
        <div style="margin-top:16px;padding:12px 16px;background:#fef3c7;border-radius:10px;border-left:4px solid #f59e0b;font-size:13px;color:#92400e;">
            ⚠️ <strong><?= $belumDinilai ?> guru</strong> belum memiliki data penilaian dan tidak ditampilkan dalam ranking.
            <a href="penilaian.php?action=add" style="color:#1a4731;font-weight:600;margin-left:6px;">+ Tambah Penilaian</a>
        </div>
    <?php endif; ?>
</div>

<script>
    function medalHtml(rank) {
        if (rank === 1) return '<span style="font-size:18px;">🥇</span>';
        if (rank === 2) return '<span style="font-size:18px;">🥈</span>';
        if (rank === 3) return '<span style="font-size:18px;">🥉</span>';
        return '<span style="font-weight:700;color:#9ca3af;font-size:14px;">#' + rank + '</span>';
    }

    function applyFilter() {
        const tipe     = document.getElementById('filterTipe').value;
        const pred     = document.getElementById('filterPredikat').value;
        const rows     = document.querySelectorAll('#rankTable tbody tr');
        const podium   = document.getElementById('podiumSection');
        const filterOn = tipe || pred !== '';

        let visibleRank = 1;
        const podiumData = []; // kumpulkan top-3 dari filter aktif

        rows.forEach(tr => {
            const rowTipe  = tr.dataset.tipe;
            const rowNilai = parseFloat(tr.dataset.nilai);

            let show = true;
            if (tipe && rowTipe !== tipe) show = false;
            if (pred !== '') {
                const p = parseInt(pred);
                if (p === 90 && rowNilai < 90)                    show = false;
                else if (p === 75 && (rowNilai < 75 || rowNilai >= 90)) show = false;
                else if (p === 60 && (rowNilai < 60 || rowNilai >= 75)) show = false;
                else if (p === 40 && (rowNilai < 40 || rowNilai >= 60)) show = false;
                else if (p === 0  && rowNilai >= 40)               show = false;
            }

            tr.style.display = show ? '' : 'none';

            if (show) {
                // Update nomor rank sesuai urutan yang terlihat
                const rankCell = tr.querySelector('td:first-child div');
                if (rankCell) rankCell.innerHTML = medalHtml(visibleRank);

                // Kumpulkan data untuk podium
                if (visibleRank <= 3) {
                    podiumData.push({
                        rank:  visibleRank,
                        nama:  tr.dataset.nama,
                        jabatan: tr.dataset.jabatan,
                        nilai: rowNilai,
                        col:   tr.dataset.col,
                        pred:  tr.dataset.pred
                    });
                }
                visibleRank++;
            }
        });

        // Tampilkan / sembunyikan podium
        if (!podium) return;
        if (!filterOn) {
            podium.style.display = ''; // kembalikan podium global
            return;
        }

        // Rebuild podium sesuai filter
        if (podiumData.length === 0) { podium.style.display = 'none'; return; }
        podium.style.display = '';

        const medals  = ['🥇','🥈','🥉'];
        const heights = [130, 110, 90];
        const pColors = ['#f5c842','#c0c0c0','#cd7f32'];
        const pBg     = ['#fef9ec','#f8fafc','#fdf6ee'];
        // urutan tampil: #2, #1, #3
        const order = [1, 0, 2];

        let html = '<div style="font-size:13px;font-weight:600;color:#374151;margin-bottom:16px;text-align:center;text-transform:uppercase;letter-spacing:0.5px;">🏆 Podium Teratas</div>';
        html += '<div style="display:flex;justify-content:center;align-items:flex-end;gap:16px;flex-wrap:wrap;">';
        order.forEach(idx => {
            if (!podiumData[idx]) return;
            const d  = podiumData[idx];
            const pc = pColors[idx];
            const pb = pBg[idx];
            html += `<div style="display:flex;flex-direction:column;align-items:center;gap:6px;flex:0 0 auto;">
                <div style="font-size:24px;">${medals[idx]}</div>
                <div style="background:${pb};border:2px solid ${pc};border-radius:12px;padding:10px 14px;text-align:center;max-width:140px;">
                    <div style="font-size:13px;font-weight:700;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;">${d.nama}</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">${d.jabatan}</div>
                    <div style="font-size:20px;font-weight:800;color:${d.col};margin-top:4px;">${d.nilai}%</div>
                    <div style="font-size:11px;color:${d.col};font-weight:600;">${d.pred}</div>
                </div>
                <div style="background:${pc};color:#fff;font-weight:800;font-size:13px;padding:6px 18px;border-radius:0 0 8px 8px;display:flex;align-items:center;justify-content:center;">
                    #${idx + 1}
                </div>
            </div>`;
        });
        html += '</div>';
        podium.innerHTML = html;
    }
</script>

<?php require_once 'includes/footer.php'; ?>