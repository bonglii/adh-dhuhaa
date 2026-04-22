<?php
/**
 * dashboard.php — Halaman Utama / Dashboard
 *
 * Menampilkan statistik ringkas (total guru, penilaian) dan
 * daftar 5 penilaian terbaru, serta akses cepat ke fitur utama.
 */

// ─── Set judul halaman & load header (sidebar + navbar) ─────────────────────
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

// ─── Query statistik utama ───────────────────────────────────────────────────
$totalGuru = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
$totalQuran = $pdo->query("SELECT COUNT(*) FROM guru WHERE tipe='guru_quran'")->fetchColumn();
$totalKelas = $pdo->query("SELECT COUNT(*) FROM guru WHERE tipe='guru_kelas'")->fetchColumn();
$totalPenilaian = $pdo->query("SELECT COUNT(*) FROM penilaian")->fetchColumn();
// Catatan: saat ini tidak ada kolom 'status' di tabel penilaian, sehingga
// $penilaianFinal = $totalPenilaian (semua penilaian dianggap final).
// Jika ingin membedakan draft vs final, tambahkan kolom status di tabel penilaian
// dan ubah query di bawah menjadi: WHERE status = 'final'
$penilaianFinal = $pdo->query("SELECT COUNT(*) FROM penilaian")->fetchColumn();

// ─── Penilaian terbaru (5 teratas untuk tabel di bagian bawah halaman) ─────
$recentPenilaian = $pdo->query("
    SELECT p.*, g.nama, g.jabatan, g.tipe
    FROM penilaian p JOIN guru g ON p.id_guru = g.id_guru
    ORDER BY p.created_at DESC LIMIT 5
")->fetchAll();
?>

<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5ee;">👩‍🏫</div>
            <div class="stat-number"><?= $totalGuru ?></div>
            <div class="stat-label">Total Guru & GTK</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;">📖</div>
            <div class="stat-number"><?= $totalQuran ?></div>
            <div class="stat-label">Guru Qur'an</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#dbeafe;">🏫</div>
            <div class="stat-number"><?= $totalKelas ?></div>
            <div class="stat-label">Guru Kelas</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon" style="background:#ede9fe;">🏷️</div>
            <div class="stat-number"><?= $totalGuru - $totalQuran - $totalKelas ?></div>
            <div class="stat-label">Guru Mapel & GTK</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="data-table-card">
            <div class="card-header-custom">
                <div class="card-title-custom">Penilaian Terbaru</div>
                <a href="penilaian.php" class="btn-primary-custom btn-sm-custom">Lihat Semua</a>
            </div>
            <table class="table table-hover" style="font-size:13.5px;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th>Nama Guru</th>
                        <th>Jabatan</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentPenilaian): foreach ($recentPenilaian as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['nama']) ?></td>
                                <td><small><?= htmlspecialchars($p['jabatan']) ?></small></td>
                                <td><small><?= htmlspecialchars($p['periode']) ?></small></td>
                                <td>
                                    <span style="font-size:12px;color:#16a34a;font-weight:600;">✓ Final</span>
                                </td>
                                <td>
                                    <a href="cetak.php?id=<?= $p['id_penilaian'] ?>&from=dashboard" class="btn-primary-custom btn-sm-custom btn-view" style="padding:5px 10px;font-size:11px;">Cetak</a>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada data penilaian</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="data-table-card">
            <div class="card-title-custom mb-4">Ringkasan</div>
            <div style="display:flex;flex-direction:column;gap:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--krem);border-radius:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:500;">Penilaian Final</div>
                        <div style="font-size:11px;color:var(--abu);">Sudah selesai</div>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#16a34a;"><?= $penilaianFinal ?></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--krem);border-radius:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:500;">Penilaian Draft</div>
                        <div style="font-size:11px;color:var(--abu);">Belum selesai</div>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#d97706;"><?= $totalPenilaian - $penilaianFinal ?></div>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px;background:var(--krem);border-radius:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:500;">Total Penilaian</div>
                        <div style="font-size:11px;color:var(--abu);">Semua periode</div>
                    </div>
                    <div style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#d97706;"><?= $totalPenilaian ?></div>
                </div>
            </div>

            <div style="margin-top:24px;">
                <div class="card-title-custom mb-3" style="font-size:14px;">Akses Cepat</div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="guru.php?action=add" class="btn-primary-custom" style="justify-content:center;">+ Tambah Guru</a>
                    <a href="penilaian.php?action=add" class="btn-primary-custom" style="justify-content:center;background:linear-gradient(135deg,#2563eb,#1d4ed8);">+ Tambah Penilaian</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>