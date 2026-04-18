<?php
/**
 * ranking.php — Halaman Ranking Guru per Tahun Ajaran
 *
 * - Tab per Tahun Ajaran (TA terbaru aktif default)
 * - Ranking = rata-rata SEMUA penilaian guru di TA tersebut
 * - Filter tipe guru & predikat, podium Top 3 ikut filter
 */

$pageTitle = 'Ranking Guru';
require_once 'includes/config.php';
requireLogin();

// ─── 1. Ambil semua TA yang pernah dipakai ──────────────────────────────────
$taList = $pdo->query("
    SELECT DISTINCT periode
    FROM penilaian
    WHERE periode IS NOT NULL AND periode != ''
    ORDER BY periode DESC
")->fetchAll(PDO::FETCH_COLUMN);

// TA aktif (dari query string atau default ke yang terbaru)
$activeTA = $_GET['ta'] ?? ($taList[0] ?? null);
if ($activeTA && !in_array($activeTA, $taList, true)) {
    $activeTA = $taList[0] ?? null;
}

// ─── 2. Query ranking untuk TA aktif ────────────────────────────────────────
// Logika: per guru, hitung rata-rata nilai dari SEMUA penilaiannya di TA tsb.
// Nilai per 1 penilaian = rata-rata persentase per indikator.
$ranked = [];
if ($activeTA) {
    $stmt = $pdo->prepare("
        SELECT
            g.id_guru,
            g.nama,
            g.jabatan,
            g.tipe,
            g.nrg,
            COUNT(DISTINCT p.id_penilaian) AS jml_penilaian,
            MAX(p.tanggal_penilaian)       AS last_penilaian,
            (
                SELECT p2.id_penilaian
                FROM penilaian p2
                WHERE p2.id_guru = g.id_guru
                  AND p2.periode = :ta2
                ORDER BY p2.tanggal_penilaian DESC, p2.id_penilaian DESC
                LIMIT 1
            ) AS last_id,
            ROUND(AVG(nilai_per_penilaian.pct), 1) AS nilai_akhir
        FROM guru g
        JOIN penilaian p
          ON p.id_guru = g.id_guru
         AND p.periode = :ta
        JOIN (
            /* Per penilaian: rata-rata persentase antar indikator */
            SELECT
                ind.id_penilaian,
                AVG(ind.ind_pct) AS pct
            FROM (
                SELECT
                    dp.id_penilaian,
                    s.nama_indikator,
                    SUM(dp.nilai) / NULLIF(COUNT(*) * 5, 0) * 100 AS ind_pct
                FROM hasil dp
                JOIN isi s
                  ON dp.id_item = s.id_item
                 AND s.id_komponen = (
                    SELECT id_komponen FROM penilaian WHERE id_penilaian = dp.id_penilaian
                 )
                GROUP BY dp.id_penilaian, s.nama_indikator
            ) ind
            GROUP BY ind.id_penilaian
        ) nilai_per_penilaian
          ON nilai_per_penilaian.id_penilaian = p.id_penilaian
        GROUP BY g.id_guru, g.nama, g.jabatan, g.tipe, g.nrg
        HAVING nilai_akhir IS NOT NULL
        ORDER BY nilai_akhir DESC, g.nama ASC
    ");
    $stmt->execute([':ta' => $activeTA, ':ta2' => $activeTA]);
    $rankingAll = $stmt->fetchAll();

    $rank = 1;
    foreach ($rankingAll as $r) {
        $r['rank']        = $rank++;
        $r['nilai_akhir'] = round($r['nilai_akhir'], 1);
        $ranked[]         = $r;
    }
}

// ─── 3. Label tipe guru (dinamis) ───────────────────────────────────────────
$tipeLabel = getTipeGuru($pdo);

// ─── 4. Helper ──────────────────────────────────────────────────────────────
function predikat($n)
{
    if ($n === null) return ['Belum Dinilai', '#6b7280'];
    if ($n >= 90)    return ['Sangat Baik Sekali', '#7c3aed'];
    if ($n >= 75)    return ['Sangat Baik',        '#16a34a'];
    if ($n >= 60)    return ['Baik',               '#2563eb'];
    if ($n >= 40)    return ['Cukup',              '#d97706'];
    return                  ['Kurang',             '#dc2626'];
}

function medalEmoji($rank)
{
    if ($rank === 1) return '🥇';
    if ($rank === 2) return '🥈';
    if ($rank === 3) return '🥉';
    return null;
}
?>

<?php require_once 'includes/header.php'; ?>

<style>
    /* ═══════════════════════════════════════════════════════════════
       RANKING PAGE — scoped styles
       ═══════════════════════════════════════════════════════════════ */

    /* Tab Tahun Ajaran ─────────────────────────────────────────── */
    .ta-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 20px;
        border-bottom: 2px solid #e5e7eb;
        overflow-x: auto;
    }

    .ta-tab {
        padding: 10px 18px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        white-space: nowrap;
        transition: all .15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .ta-tab:hover {
        color: var(--hijau);
        background: #f8fafc;
    }

    .ta-tab.active {
        color: var(--hijau);
        border-bottom-color: var(--hijau);
        background: var(--hijau-pale);
    }

    .ta-tab .ta-count {
        background: #e5e7eb;
        color: #374151;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 999px;
        font-weight: 700;
    }

    .ta-tab.active .ta-count {
        background: var(--hijau);
        color: #fff;
    }

    /* Podium ─────────────────────────────────────────────────────── */
    .podium-wrap {
        background: linear-gradient(135deg, #fafbfc 0%, #f0fdf4 100%);
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 24px 20px 28px;
        margin-bottom: 22px;
    }

    .podium-title {
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-align: center;
        margin-bottom: 20px;
    }

    .podium-row {
        display: flex;
        justify-content: center;
        align-items: flex-end;
        gap: 18px;
        flex-wrap: wrap;
    }

    .podium-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 0 1 180px;
        min-width: 150px;
    }

    .podium-medal {
        font-size: 30px;
        margin-bottom: 6px;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,.1));
    }

    .podium-card {
        background: #fff;
        border: 2px solid;
        border-radius: 12px;
        padding: 14px 16px 12px;
        text-align: center;
        width: 100%;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }

    .podium-name {
        font-size: 13px;
        font-weight: 700;
        color: #111;
        line-height: 1.3;
        margin-bottom: 4px;
        word-break: break-word;
    }

    .podium-jabatan {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 8px;
        min-height: 14px;
    }

    .podium-nilai {
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .podium-pred {
        font-size: 11px;
        font-weight: 600;
        margin-top: 2px;
    }

    .podium-rank {
        margin-top: 8px;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        padding: 6px 20px;
        border-radius: 8px;
        letter-spacing: .5px;
    }

    .podium-gold   { border-color: #f5c842; }
    .podium-silver { border-color: #c0c0c0; }
    .podium-bronze { border-color: #cd7f32; }
    .badge-gold    { background: #f5c842; }
    .badge-silver  { background: #c0c0c0; }
    .badge-bronze  { background: #cd7f32; }

    /* Tabel ranking ──────────────────────────────────────────────── */
    .rank-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .rank-table thead th {
        background: #f8fafc;
        color: #374151;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .3px;
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    .rank-table tbody td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .rank-table tbody tr {
        transition: background .12s ease;
    }

    .rank-table tbody tr:hover {
        background: #fafbfc;
    }

    .rank-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rank-num {
        font-weight: 700;
        color: #9ca3af;
        font-size: 14px;
    }

    .rank-medal {
        font-size: 20px;
        line-height: 1;
    }

    .guru-nama {
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .guru-jabatan {
        font-size: 11.5px;
        color: #6b7280;
        margin-top: 2px;
    }

    .nilai-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 220px;
    }

    .nilai-bar {
        flex: 1;
        height: 8px;
        background: #eef2f6;
        border-radius: 999px;
        overflow: hidden;
        min-width: 80px;
    }

    .nilai-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width .6s ease;
    }

    .nilai-angka {
        font-weight: 700;
        font-size: 14px;
        min-width: 48px;
        text-align: right;
    }

    .nilai-pred {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .jml-badge {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .empty-state {
        text-align: center;
        color: #9ca3af;
        padding: 48px 20px;
        font-size: 13px;
    }

    .empty-big {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .empty-big .emoji    { font-size: 48px; margin-bottom: 12px; }
    .empty-big .title    { font-size: 16px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .empty-big .subtitle { font-size: 13px; }

    /* Filter toolbar */
    .rank-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .rank-toolbar .form-control-custom {
        padding: 7px 12px;
        font-size: 13px;
    }

    /* Info bar TA aktif */
    .ta-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 14px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 14px;
        font-size: 12.5px;
        color: #6b7280;
        flex-wrap: wrap;
        gap: 8px;
    }

    .ta-info strong { color: #111827; }

    /* Responsive */
    @media (max-width: 640px) {
        .podium-item { flex: 1 1 100%; }
        .rank-table { font-size: 12px; }
        .rank-table thead th,
        .rank-table tbody td { padding: 10px 8px; }
        .nilai-wrap { min-width: 140px; }
        .ta-tab { padding: 8px 12px; font-size: 12px; }
    }
</style>

<div class="data-table-card">
    <div class="card-header-custom" style="flex-wrap:wrap;gap:10px;">
        <div class="card-title-custom">🏆 Ranking Kinerja Guru</div>
        <div class="rank-toolbar">
            <select id="filterTipe" class="form-control-custom" onchange="applyFilter()">
                <option value="">Semua Tipe</option>
                <?php foreach ($tipeLabel as $kode => $label): ?>
                    <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filterPredikat" class="form-control-custom" onchange="applyFilter()">
                <option value="">Semua Predikat</option>
                <option value="90">Sangat Baik Sekali (≥90%)</option>
                <option value="75">Sangat Baik (≥75%)</option>
                <option value="60">Baik (≥60%)</option>
                <option value="40">Cukup (≥40%)</option>
                <option value="0">Kurang (&lt;40%)</option>
            </select>
        </div>
    </div>

    <?php if (empty($taList)): ?>
        <!-- Belum ada TA sama sekali -->
        <div class="empty-big">
            <div class="emoji">📋</div>
            <div class="title">Belum ada data penilaian</div>
            <div class="subtitle">Mulai tambah penilaian untuk melihat ranking guru.</div>
            <a href="penilaian.php?action=add" class="btn-primary-custom" style="margin-top:16px;display:inline-block;">+ Tambah Penilaian</a>
        </div>
    <?php else: ?>

        <!-- ═══ Tab Tahun Ajaran ═══ -->
        <div class="ta-tabs" role="tablist">
            <?php
            $stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT id_guru) FROM penilaian WHERE periode = :ta");
            foreach ($taList as $ta):
                $stmtCount->execute([':ta' => $ta]);
                $jmlGuru = (int) $stmtCount->fetchColumn();
                $isActive = ($ta === $activeTA);
            ?>
                <a href="?ta=<?= urlencode($ta) ?>"
                   class="ta-tab <?= $isActive ? 'active' : '' ?>"
                   role="tab">
                    📅 TA <?= htmlspecialchars($ta) ?>
                    <span class="ta-count"><?= $jmlGuru ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ═══ Info bar TA aktif ═══ -->
        <?php
        $totalGuruTA = count($ranked);
        $avgTA = $totalGuruTA > 0 ? round(array_sum(array_column($ranked, 'nilai_akhir')) / $totalGuruTA, 1) : 0;

        $stmtJml = $pdo->prepare("SELECT COUNT(*) FROM penilaian WHERE periode = :ta");
        $stmtJml->execute([':ta' => $activeTA]);
        $totalPenilaianTA = (int) $stmtJml->fetchColumn();
        ?>
        <div class="ta-info">
            <div>
                Menampilkan <strong><?= $totalGuruTA ?> guru</strong> · <strong><?= $totalPenilaianTA ?> penilaian</strong> di TA <strong><?= htmlspecialchars($activeTA) ?></strong>
                <?php if ($totalGuruTA > 0): ?>
                    · Rata-rata: <strong><?= $avgTA ?>%</strong>
                <?php endif; ?>
            </div>
            <?php if ($totalGuruTA > 0): ?>
                <div style="font-size:11.5px;color:#9ca3af;">
                    Nilai = rata-rata semua penilaian guru di TA ini
                </div>
            <?php endif; ?>
        </div>

        <?php if ($totalGuruTA === 0): ?>
            <div class="empty-big">
                <div class="emoji">🔍</div>
                <div class="title">Belum ada guru dinilai di TA <?= htmlspecialchars($activeTA) ?></div>
                <div class="subtitle">Coba pilih TA lain atau tambahkan penilaian baru.</div>
            </div>
        <?php else: ?>

            <!-- ═══ Podium Top 3 ═══ -->
            <?php if (count($ranked) >= 1): ?>
            <div id="podiumSection" class="podium-wrap">
                <div class="podium-title">🏆 Podium Teratas · TA <?= htmlspecialchars($activeTA) ?></div>
                <div class="podium-row">
                    <?php
                    $podiumOrder   = [1, 0, 2];
                    $podiumVariant = [0 => 'gold', 1 => 'silver', 2 => 'bronze'];
                    $podiumMedal   = [0 => '🥇',   1 => '🥈',     2 => '🥉'];

                    foreach ($podiumOrder as $pIdx):
                        if (!isset($ranked[$pIdx])) continue;
                        $pr = $ranked[$pIdx];
                        [$pred, $col] = predikat($pr['nilai_akhir']);
                        $variant = $podiumVariant[$pIdx];
                    ?>
                        <div class="podium-item">
                            <div class="podium-medal"><?= $podiumMedal[$pIdx] ?></div>
                            <div class="podium-card podium-<?= $variant ?>">
                                <div class="podium-name"><?= htmlspecialchars($pr['nama']) ?></div>
                                <div class="podium-jabatan"><?= htmlspecialchars($pr['jabatan'] ?? '—') ?></div>
                                <div class="podium-nilai" style="color:<?= $col ?>;"><?= $pr['nilai_akhir'] ?>%</div>
                                <div class="podium-pred" style="color:<?= $col ?>;"><?= $pred ?></div>
                            </div>
                            <div class="podium-rank badge-<?= $variant ?>">#<?= $pIdx + 1 ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- ═══ Tabel ═══ -->
            <div style="overflow-x:auto;">
                <table class="rank-table" id="rankTable">
                    <thead>
                        <tr>
                            <th style="width:70px;">Rank</th>
                            <th>Guru</th>
                            <th>Tipe</th>
                            <th style="min-width:240px;">Nilai Rata-rata</th>
                            <th>Predikat</th>
                            <th style="text-align:center;">Jml Penilaian</th>
                            <th>Terakhir</th>
                            <th style="width:80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranked as $r):
                            [$pred, $col] = predikat($r['nilai_akhir']);
                            $medal = medalEmoji($r['rank']);
                            $badgeMap = ['guru_quran'=>'quran','guru_kelas'=>'kelas','mapel'=>'mapel','gtk'=>'gtk'];
                            $tipeKey  = $badgeMap[$r['tipe']] ?? 'gtk';
                        ?>
                            <tr data-tipe="<?= $r['tipe'] ?>"
                                data-nilai="<?= $r['nilai_akhir'] ?>"
                                data-nama="<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>"
                                data-jabatan="<?= htmlspecialchars($r['jabatan'] ?? '', ENT_QUOTES) ?>"
                                data-col="<?= $col ?>"
                                data-pred="<?= $pred ?>">
                                <td>
                                    <?php if ($medal): ?>
                                        <span class="rank-medal"><?= $medal ?></span>
                                    <?php else: ?>
                                        <span class="rank-num">#<?= $r['rank'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="guru-nama"><?= htmlspecialchars($r['nama']) ?></div>
                                    <?php if (!empty($r['jabatan'])): ?>
                                        <div class="guru-jabatan"><?= htmlspecialchars($r['jabatan']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-tipe badge-<?= $tipeKey ?>">
                                        <?= $tipeLabel[$r['tipe']] ?? $r['tipe'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="nilai-wrap">
                                        <div class="nilai-bar">
                                            <div class="nilai-bar-fill" style="width:<?= min($r['nilai_akhir'], 100) ?>%;background:<?= $col ?>;"></div>
                                        </div>
                                        <span class="nilai-angka" style="color:<?= $col ?>;"><?= $r['nilai_akhir'] ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="nilai-pred" style="background:<?= $col ?>15;color:<?= $col ?>;"><?= $pred ?></span>
                                </td>
                                <td style="text-align:center;">
                                    <span class="jml-badge" title="Jumlah penilaian di TA ini"><?= $r['jml_penilaian'] ?>×</span>
                                </td>
                                <td>
                                    <small style="color:#6b7280;"><?= $r['last_penilaian'] ? date('d/m/Y', strtotime($r['last_penilaian'])) : '-' ?></small>
                                </td>
                                <td>
                                    <?php if ($r['last_id']): ?>
                                        <a href="cetak.php?id=<?= $r['last_id'] ?>" class="btn-primary-custom btn-sm-custom btn-view" target="_blank">Cetak</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResultMsg" style="display:none;" class="empty-state">
                Tidak ada guru yang cocok dengan filter. Coba ubah atau reset filter.
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    function medalHtml(rank) {
        if (rank === 1) return '<span class="rank-medal">🥇</span>';
        if (rank === 2) return '<span class="rank-medal">🥈</span>';
        if (rank === 3) return '<span class="rank-medal">🥉</span>';
        return '<span class="rank-num">#' + rank + '</span>';
    }

    function applyFilter() {
        const tipe     = document.getElementById('filterTipe').value;
        const pred     = document.getElementById('filterPredikat').value;
        const table    = document.getElementById('rankTable');
        if (!table) return;

        const rows     = table.querySelectorAll('tbody tr');
        const podium   = document.getElementById('podiumSection');
        const noResult = document.getElementById('noResultMsg');
        const filterOn = tipe || pred !== '';

        let visibleRank = 1;
        const podiumData = [];

        rows.forEach(tr => {
            if (!tr.dataset.tipe) return;

            const rowTipe  = tr.dataset.tipe;
            const rowNilai = parseFloat(tr.dataset.nilai);

            let show = true;
            if (tipe && rowTipe !== tipe) show = false;
            if (pred !== '') {
                const p = parseInt(pred);
                if (p === 90 && rowNilai < 90)                          show = false;
                else if (p === 75 && (rowNilai < 75 || rowNilai >= 90)) show = false;
                else if (p === 60 && (rowNilai < 60 || rowNilai >= 75)) show = false;
                else if (p === 40 && (rowNilai < 40 || rowNilai >= 60)) show = false;
                else if (p === 0  && rowNilai >= 40)                    show = false;
            }

            tr.style.display = show ? '' : 'none';

            if (show) {
                const rankCell = tr.querySelector('td:first-child');
                if (rankCell) rankCell.innerHTML = medalHtml(visibleRank);

                if (visibleRank <= 3) {
                    podiumData.push({
                        rank:    visibleRank,
                        nama:    tr.dataset.nama,
                        jabatan: tr.dataset.jabatan || '—',
                        nilai:   rowNilai,
                        col:     tr.dataset.col,
                        pred:    tr.dataset.pred
                    });
                }
                visibleRank++;
            }
        });

        if (noResult) noResult.style.display = (visibleRank === 1 && filterOn) ? '' : 'none';

        if (!podium) return;

        if (!filterOn) {
            if (podium.dataset.rebuilt === '1') {
                window.location.reload();
                return;
            }
            podium.style.display = '';
            return;
        }

        if (podiumData.length === 0) {
            podium.style.display = 'none';
            return;
        }

        podium.style.display = '';
        podium.dataset.rebuilt = '1';

        const medals   = ['🥇','🥈','🥉'];
        const variants = ['gold','silver','bronze'];
        const order    = [1, 0, 2];

        let html = '<div class="podium-title">🏆 Podium Teratas (Filter Aktif)</div>';
        html += '<div class="podium-row">';
        order.forEach(idx => {
            if (!podiumData[idx]) return;
            const d = podiumData[idx];
            const v = variants[idx];
            html += `
                <div class="podium-item">
                    <div class="podium-medal">${medals[idx]}</div>
                    <div class="podium-card podium-${v}">
                        <div class="podium-name">${d.nama}</div>
                        <div class="podium-jabatan">${d.jabatan}</div>
                        <div class="podium-nilai" style="color:${d.col};">${d.nilai}%</div>
                        <div class="podium-pred" style="color:${d.col};">${d.pred}</div>
                    </div>
                    <div class="podium-rank badge-${v}">#${idx + 1}</div>
                </div>`;
        });
        html += '</div>';
        podium.innerHTML = html;
    }
</script>

<?php require_once 'includes/footer.php'; ?>
