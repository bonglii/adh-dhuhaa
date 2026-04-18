<?php
// ─── Tampilkan semua error agar mudah didiagnosis ─────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

/**
 * guru.php — Halaman Manajemen Data Guru & GTK
 *
 * Fitur:
 *  - Tampil daftar guru berdasarkan tipe (Guru Qur'an, Guru Kelas, Mapel, GTK)
 *  - Tambah, edit, hapus data guru
 *  - Riwayat perubahan (history log) setiap aksi CRUD
 *  - Filter & pencarian pada riwayat
 *  - Manajemen Tipe Guru (tab ketiga): tambah, edit, hapus tipe secara dinamis
 */

// ─── Inisialisasi: load konfigurasi & pastikan user sudah login ──────────────
require_once 'includes/config.php';
requireLogin();
$user = getCurrentUser();

// ─── Baca parameter dari URL ─────────────────────────────────────────────────
$msg    = '';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

/**
 * catatHistory — Simpan catatan perubahan data guru ke tabel guru_history.
 *
 * @param PDO    $pdo        Koneksi database
 * @param string $aksi       Jenis aksi: 'tambah' | 'edit' | 'hapus'
 * @param int    $guru_id    ID guru yang bersangkutan
 * @param array  $data       Data guru baru (array asosiatif)
 * @param string $oleh       Nama pengguna yang melakukan aksi
 * @param string $keterangan Catatan opsional (contoh: data sebelum diedit)
 */
function catatHistory($pdo, $aksi, $guru_id, $data, $oleh, $keterangan = '')
{
    $stmt = $pdo->prepare("INSERT INTO guru_history (aksi, id_guru, nama, nrg, tmt_guru, jabatan, status_kepegawaian, tipe, oleh, keterangan)
        VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $aksi,
        $guru_id,
        $data['nama']               ?? '',
        $data['nrg']                ?? null,
        $data['tmt_guru']           ?: null,
        $data['jabatan']            ?? null,
        $data['status_kepegawaian'] ?? null,
        $data['tipe']               ?? null,
        $oleh,
        $keterangan
    ]);
}

// ─── Handle POST: Tambah atau Edit data guru ─────────────────────────────────
// Guard: pastikan bukan submission dari form tipe guru (ditangani blok terpisah di bawah)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') !== 'tipe') {
    // Baca dan sanitasi semua field yang dikirim dari form modal
    $post_action = sanitize($_POST['action'] ?? 'add');
    $post_id = (int)($_POST['id'] ?? 0);
    $nama    = sanitize($_POST['nama'] ?? '');
    $nrg     = sanitize($_POST['nrg'] ?? '');
    $tmt     = $_POST['tmt_guru'] ?: null;
    $jabatan = sanitize($_POST['jabatan'] ?? '');
    $status  = sanitize($_POST['status_kepegawaian'] ?? '');
    $tipe    = sanitize($_POST['tipe'] ?? 'guru_kelas');
    // Validasi tipe terhadap tabel tipe_guru (bukan hardcode)
    if (!isValidTipe($pdo, $tipe)) $tipe = 'guru_kelas';
    $oleh    = $user['nama_lengkap'] ?? 'Admin';

    $dataGuru = [
        'nama'               => $nama,
        'nrg'                => $nrg,
        'tmt_guru'           => $tmt,
        'jabatan'            => $jabatan,
        'status_kepegawaian' => $status,
        'tipe'               => $tipe,
    ];

    if (!$nama) {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Nama guru wajib diisi!'));
        exit;
    }

    try {
        if ($post_action === 'edit' && $post_id) {
            // Ambil data lama untuk keterangan perubahan
            $lama = $pdo->prepare("SELECT * FROM guru WHERE id_guru=?");
            $lama->execute([$post_id]);
            $dataLama = $lama->fetch();

            if (!$dataLama) {
                session_write_close();
                header('Location: guru.php?msg=' . urlencode('⚠️ Data guru tidak ditemukan!'));
                exit;
            }

            $stmt = $pdo->prepare("UPDATE guru SET nama=?,nrg=?,tmt_guru=?,jabatan=?,status_kepegawaian=?,tipe=? WHERE id_guru=?");
            $stmt->execute([$nama, $nrg, $tmt, $jabatan, $status, $tipe, $post_id]);

            $ket = 'Sebelum: ' . ($dataLama['nama'] ?? '') . ' | ' . ($dataLama['jabatan'] ?? '') . ' | ' . ($dataLama['tipe'] ?? '');
            try {
                catatHistory($pdo, 'edit', $post_id, $dataGuru, $oleh, $ket);
            } catch (PDOException $eHistory) {
                error_log('[guru.php] catatHistory edit gagal (guru tetap diperbarui): ' . $eHistory->getMessage());
            }

            session_write_close();
            header('Location: guru.php?msg=' . urlencode('Data guru berhasil diperbarui!'));
            exit;

        } else {
            // ── INSERT guru langsung (auto-commit) — tidak pakai transaction
            // agar catatHistory yang gagal tidak membatalkan INSERT guru ──
            $stmt = $pdo->prepare("INSERT INTO guru (nama,nrg,tmt_guru,jabatan,status_kepegawaian,tipe) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nama, $nrg, $tmt, $jabatan, $status, $tipe]);
            $newId = (int)$pdo->lastInsertId();

            // catatHistory di try-catch tersendiri:
            // kalau gagal, guru tetap tersimpan & error dicatat di log
            try {
                catatHistory($pdo, 'tambah', $newId, $dataGuru, $oleh);
            } catch (PDOException $eHistory) {
                error_log('[guru.php] catatHistory gagal (guru tetap tersimpan): ' . $eHistory->getMessage());
            }

            session_write_close();
            header('Location: guru.php?msg=' . urlencode('Data guru berhasil disimpan!'));
            exit;
        }

    } catch (PDOException $e) {
        // Redirect dengan pesan error agar toast system di footer menampilkannya
        $errMsg = $e->getMessage();
        error_log('[guru.php] PDOException saat simpan guru: ' . $errMsg);

        if (strpos($errMsg, 'fk_guru_tipe') !== false || strpos(strtolower($errMsg), 'foreign key') !== false) {
            $errTampil = 'Tipe guru "' . $tipe . '" tidak terdaftar. Silakan tambah tipe tersebut di tab Tipe Guru terlebih dahulu.';
        } elseif (strpos(strtolower($errMsg), 'duplicate') !== false) {
            $errTampil = 'Data guru dengan NRG tersebut sudah ada.';
        } else {
            $errTampil = 'Terjadi kesalahan database: ' . $errMsg;
        }

        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Gagal menyimpan: ' . $errTampil));
        exit;
    }
}

// ─── Handle GET: Hapus satu entri riwayat ────────────────────────────────────
if ($action === 'delete_history' && $id) {
    try {
        $pdo->prepare("DELETE FROM guru_history WHERE id_guru_history=?")->execute([$id]);
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Riwayat berhasil dihapus!') . '&tab=history');
        exit;
    } catch (PDOException $e) {
        error_log('[guru.php] PDOException saat hapus history id=' . $id . ': ' . $e->getMessage());
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Gagal menghapus riwayat.') . '&tab=history');
        exit;
    }
}

// ─── Handle GET: Reset (hapus semua) riwayat ─────────────────────────────────
if ($action === 'reset_history') {
    try {
        $pdo->exec("DELETE FROM guru_history");
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Semua riwayat berhasil direset!') . '&tab=history');
        exit;
    } catch (PDOException $e) {
        error_log('[guru.php] PDOException saat reset history: ' . $e->getMessage());
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Gagal mereset riwayat.') . '&tab=history');
        exit;
    }
}

// ─── Handle POST: Tambah / Edit tipe guru ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'tipe') {
    $post_action = sanitize($_POST['action'] ?? 'add');
    $post_id     = (int)($_POST['id'] ?? 0);
    $kode        = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', trim($_POST['kode'] ?? '')));
    $label       = sanitize($_POST['label'] ?? '');
    $urutan      = (int)($_POST['urutan'] ?? 0);

    // Untuk action add: selalu gunakan max+1 agar urutan konsisten
    if ($post_action === 'add') {
        $urutan = (int)$pdo->query("SELECT COALESCE(MAX(urutan),0)+1 FROM tipe_guru")->fetchColumn();
    }

    if (!$kode || !$label) {
        // Redirect agar toast system di footer bisa menampilkan pesan
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Kode dan Label tipe guru wajib diisi!') . '&tab=tipe');
        exit;
    }

    try {
        if ($post_action === 'edit' && $post_id) {
            // Cek apakah kode sudah dipakai tipe lain (gunakan id_tipe_guru, bukan id)
            $cek = $pdo->prepare("SELECT id_tipe_guru FROM tipe_guru WHERE kode=? AND id_tipe_guru!=?");
            $cek->execute([$kode, $post_id]);
            if ($cek->fetch()) {
                session_write_close();
                header('Location: guru.php?msg=' . urlencode('⚠️ Kode tipe sudah digunakan oleh tipe lain!') . '&tab=tipe');
                exit;
            }
            $pdo->prepare("UPDATE tipe_guru SET kode=?,label=?,urutan=? WHERE id_tipe_guru=?")->execute([$kode, $label, $urutan, $post_id]);
            unset($GLOBALS['_cache_tipe_guru']);
            session_write_close();
            header('Location: guru.php?msg=' . urlencode('Tipe guru berhasil diperbarui!') . '&tab=tipe');
            exit;

        } else {
            // Cek apakah kode sudah ada (gunakan id_tipe_guru, bukan id)
            $cek = $pdo->prepare("SELECT id_tipe_guru FROM tipe_guru WHERE kode=?");
            $cek->execute([$kode]);
            if ($cek->fetch()) {
                session_write_close();
                header('Location: guru.php?msg=' . urlencode('⚠️ Kode tipe sudah ada! Gunakan kode yang berbeda.') . '&tab=tipe');
                exit;
            }
            $pdo->prepare("INSERT INTO tipe_guru (kode,label,urutan) VALUES (?,?,?)")->execute([$kode, $label, $urutan]);
            unset($GLOBALS['_cache_tipe_guru']);
            session_write_close();
            header('Location: guru.php?msg=' . urlencode('Tipe guru baru berhasil ditambahkan!') . '&tab=tipe');
            exit;
        }

    } catch (PDOException $e) {
        $errMsg = $e->getMessage();
        error_log('[guru.php] PDOException saat simpan tipe guru: ' . $errMsg);
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Gagal menyimpan tipe guru: ' . $errMsg) . '&tab=tipe');
        exit;
    }
}

// ─── Handle GET: Hapus tipe guru ─────────────────────────────────────────────
if ($action === 'delete_tipe' && $id) {
    try {
        $cekGuru = $pdo->prepare("SELECT COUNT(*) as n FROM guru WHERE tipe=(SELECT kode FROM tipe_guru WHERE id_tipe_guru=?)");
        $cekGuru->execute([$id]);
        $nGuru = $cekGuru->fetch()['n'];

        $cekKomp = $pdo->prepare("SELECT COUNT(*) as n FROM komponen WHERE type_guru=(SELECT kode FROM tipe_guru WHERE id_tipe_guru=?)");
        $cekKomp->execute([$id]);
        $nKomp = $cekKomp->fetch()['n'];

        if ($nGuru > 0 || $nKomp > 0) {
            $alasan = $nGuru > 0 ? "{$nGuru} data guru" : "{$nKomp} komponen penilaian";
            session_write_close();
            header('Location: guru.php?msg=' . urlencode("⚠️ Tipe tidak dapat dihapus karena masih digunakan oleh {$alasan}!") . '&tab=tipe');
            exit;
        }

        $pdo->prepare("DELETE FROM tipe_guru WHERE id_tipe_guru=?")->execute([$id]);
        unset($GLOBALS['_cache_tipe_guru']);
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Tipe guru berhasil dihapus!') . '&tab=tipe');
        exit;

    } catch (PDOException $e) {
        error_log('[guru.php] PDOException saat hapus tipe guru id=' . $id . ': ' . $e->getMessage());
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Gagal menghapus tipe guru: ' . $e->getMessage()) . '&tab=tipe');
        exit;
    }
}

// ─── Handle GET: Hapus satu data guru ────────────────────────────────────────
if ($action === 'delete' && $id) {
    $row = $pdo->prepare("SELECT * FROM guru WHERE id_guru=?");
    $row->execute([$id]);
    $dataHapus = $row->fetch();
    $oleh = $user['nama_lengkap'] ?? 'Admin';

    if ($dataHapus) {
        try {
            // ── DELETE guru dulu (auto-commit), history terpisah ──
            $pdo->prepare("DELETE FROM guru WHERE id_guru=?")->execute([$id]);
        } catch (PDOException $e) {
            $errMsg = $e->getMessage();
            error_log('[guru.php] PDOException saat hapus guru id=' . $id . ': ' . $errMsg);

            if (strpos(strtolower($errMsg), 'foreign key') !== false) {
                $errTampil = 'Guru ini masih memiliki data penilaian. Hapus data penilaiannya terlebih dahulu.';
            } else {
                $errTampil = 'Terjadi kesalahan database: ' . $errMsg;
            }

            session_write_close();
            header('Location: guru.php?msg=' . urlencode('⚠️ Gagal menghapus: ' . $errTampil));
            exit;
        }

        // catatHistory terpisah — kalau gagal, guru tetap sudah terhapus
        try {
            catatHistory($pdo, 'hapus', $id, $dataHapus, $oleh);
        } catch (PDOException $eHistory) {
            error_log('[guru.php] catatHistory hapus gagal (guru tetap terhapus): ' . $eHistory->getMessage());
        }

        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Data guru berhasil dihapus!'));
        exit;

    } else {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Data guru tidak ditemukan!'));
        exit;
    }
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// Filter tipe guru untuk tab data
$filterTipeGuru = sanitize($_GET['filter_tipe_guru'] ?? '');

if ($filterTipeGuru) {
    $stmtGuru = $pdo->prepare("SELECT * FROM guru WHERE tipe = ? ORDER BY nama");
    $stmtGuru->execute([$filterTipeGuru]);
    $guruList = $stmtGuru->fetchAll();
} else {
    $guruList = $pdo->query("SELECT * FROM guru ORDER BY tipe, nama")->fetchAll();
}

// Ambil history (200 terakhir)
$historyList = $pdo->query("SELECT * FROM guru_history ORDER BY waktu DESC LIMIT 200")->fetchAll();

// Ambil tipe guru dari database (dinamis, bukan hardcode)
$tipeList = getTipeGuru($pdo);
$tipeBadgeClass = [
    'guru_quran' => 'badge-quran',
    'guru_kelas' => 'badge-kelas',
    'mapel'      => 'badge-mapel',
    'gtk'        => 'badge-gtk',
];
$tipeLabel = [];
foreach ($tipeList as $kode => $label) {
    $tipeLabel[$kode] = [
        'label' => $label,
        'class' => $tipeBadgeClass[$kode] ?? 'badge-gtk',
    ];
}

// Ambil daftar tipe lengkap (dengan jumlah pemakaian) untuk tab Tipe Guru
$tipeFullList = $pdo->query("
    SELECT t.*,
           (SELECT COUNT(*) FROM guru g WHERE g.tipe = t.kode) AS jumlah_guru,
           (SELECT COUNT(*) FROM komponen k WHERE k.type_guru = t.kode) AS jumlah_komponen
    FROM tipe_guru t ORDER BY t.urutan, t.id_tipe_guru
")->fetchAll();

// Hitung urutan berikutnya otomatis (max urutan + 1)
$maxUrutan = $pdo->query("SELECT COALESCE(MAX(urutan),0) FROM tipe_guru")->fetchColumn();
$nextUrutan = (int)$maxUrutan + 1;

$pageTitle = 'Data Guru & GTK';
require_once 'includes/header.php';
?>


<!-- notifikasi ditangani oleh toast di footer -->

<style>
    /* ═══════════════════════════════════════════════════════════════
       GURU PAGE — scoped styles
       ═══════════════════════════════════════════════════════════════ */

    .guru-tabs {
        display: flex;
        gap: 2px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
        overflow-x: auto;
    }

    .guru-tab {
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 600;
        border: none;
        background: transparent;
        cursor: pointer;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
        transition: all .15s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .guru-tab:hover { color: var(--hijau); background: #f8fafc; }
    .guru-tab.active {
        color: var(--hijau);
        border-bottom-color: var(--hijau);
        background: var(--hijau-pale);
    }

    .guru-tab-badge {
        background: #e5e7eb;
        color: #374151;
        font-size: 10.5px;
        padding: 2px 7px;
        border-radius: 999px;
        font-weight: 700;
    }

    .guru-tab.active .guru-tab-badge { background: var(--hijau); color: #fff; }
    .guru-tab-badge.danger { background: #ef4444; color: #fff; }

    /* Filter chips */
    .filter-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-bottom: 16px;
    }

    .filter-chips-label {
        font-size: 12px;
        font-weight: 600;
        color: #6b7280;
        margin-right: 4px;
    }

    .chip {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid #d1d5db;
        background: #fff;
        color: #374151;
        cursor: pointer;
        transition: all .15s ease;
    }

    .chip:hover { border-color: var(--hijau); color: var(--hijau); }
    .chip.active { background: var(--hijau); border-color: var(--hijau); color: #fff; }
    .chip-count { opacity: 0.85; font-weight: 700; }

    /* Search box */
    .guru-search {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        font-size: 12.5px;
        color: #374151;
        width: 200px;
    }

    .guru-search:focus-within { border-color: var(--hijau); }
    .guru-search input {
        border: none;
        outline: none;
        background: transparent;
        flex: 1;
        font-size: 12.5px;
        color: #374151;
    }

    /* Stat cards */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid;
        border-radius: 12px;
        padding: 14px 18px;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,.05);
    }

    .stat-value { font-size: 22px; font-weight: 700; line-height: 1.1; }
    .stat-label { font-size: 11.5px; margin-top: 4px; font-weight: 500; }

    .stat-green  { border-left-color: #16a34a; }
    .stat-green  .stat-value { color: #15803d; }
    .stat-green  .stat-label { color: #166534; }

    .stat-yellow { border-left-color: #ca8a04; }
    .stat-yellow .stat-value { color: #854d0e; }
    .stat-yellow .stat-label { color: #92400e; }

    .stat-red    { border-left-color: #dc2626; }
    .stat-red    .stat-value { color: #b91c1c; }
    .stat-red    .stat-label { color: #991b1b; }

    .stat-blue   { border-left-color: #0284c7; }
    .stat-blue   .stat-value { color: #0369a1; }
    .stat-blue   .stat-label { color: #075985; }

    /* Tabel */
    .guru-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 13px;
    }

    .guru-table thead th {
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

    .guru-table tbody td {
        padding: 11px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .guru-table tbody tr { transition: background .12s ease; }
    .guru-table tbody tr:hover { background: #fafbfc; }
    .guru-table tbody tr:last-child td { border-bottom: none; }

    /* Guru name + meta */
    .guru-name { font-weight: 600; color: #111827; line-height: 1.3; }
    .guru-meta {
        display: flex;
        gap: 10px;
        margin-top: 3px;
        font-size: 11px;
        color: #9ca3af;
        flex-wrap: wrap;
    }
    .guru-meta span { display: inline-flex; align-items: center; gap: 3px; }

    .row-actions { display: inline-flex; gap: 6px; }

    .user-avatar {
        background: #e0f2fe;
        color: #0369a1;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .aksi-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .aksi-tambah { background: #dcfce7; color: #15803d; }
    .aksi-edit   { background: #fef9c3; color: #854d0e; }
    .aksi-hapus  { background: #fee2e2; color: #b91c1c; }

    .count-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
    }

    .count-pill.muted  { color: #d1d5db; font-size: 12px; padding: 0; }
    .count-pill.blue   { background: #dbeafe; color: #1d4ed8; }
    .count-pill.purple { background: #f3e8ff; color: #7c3aed; }

    .btn-reset-all {
        padding: 7px 14px;
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-reset-all:hover { background: #fecaca; }

    .btn-icon {
        background: #fee2e2;
        border: none;
        border-radius: 6px;
        padding: 5px 9px;
        cursor: pointer;
        color: #b91c1c;
        font-size: 13px;
    }

    .btn-icon:hover { background: #fecaca; }

    .btn-disabled {
        padding: 5px 10px;
        font-size: 12px;
        border-radius: 7px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        color: #d1d5db;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .empty-state .emoji    { font-size: 48px; margin-bottom: 12px; }
    .empty-state .title    { font-size: 14px; font-weight: 600; color: #374151; }
    .empty-state .subtitle { font-size: 12px; margin-top: 4px; }

    .info-footer {
        margin-top: 16px;
        padding: 14px 16px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        font-size: 12.5px;
        color: #78350f;
        line-height: 1.7;
    }

    .info-footer-title {
        font-weight: 700;
        margin-bottom: 6px;
        font-size: 13px;
    }

    .info-footer code {
        background: #fef3c7;
        padding: 1px 5px;
        border-radius: 4px;
    }

    .result-info {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 10px;
    }

    @media (max-width: 640px) {
        .guru-table { font-size: 12px; }
        .guru-table thead th,
        .guru-table tbody td { padding: 9px 8px; }
        .guru-tab { padding: 8px 14px; font-size: 12px; }
    }
</style>

<!-- ═══════════════════════════════════════════════════════════════
     Tab Navigation
     ═══════════════════════════════════════════════════════════════ -->
<?php $allGuruCountGlobal = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn(); ?>
<div class="guru-tabs" role="tablist">
    <button id="tab-data" onclick="switchTab('data')" class="guru-tab active">
        👥 Data Guru & GTK
        <span class="guru-tab-badge"><?= $allGuruCountGlobal ?></span>
    </button>
    <button id="tab-history" onclick="switchTab('history')" class="guru-tab">
        🕓 Riwayat Perubahan
        <?php if (count($historyList) > 0): ?>
            <span id="history-badge" class="guru-tab-badge danger"><?= count($historyList) ?></span>
        <?php endif; ?>
    </button>
    <button id="tab-tipe" onclick="switchTab('tipe')" class="guru-tab">
        🏷️ Tipe Guru
        <span class="guru-tab-badge"><?= count($tipeFullList) ?></span>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     PANEL: DATA GURU
     ═══════════════════════════════════════════════════════════════ -->
<div id="panel-data">
    <div class="data-table-card">
        <div class="card-header-custom" style="flex-wrap:wrap;gap:10px;">
            <div class="card-title-custom">Daftar Guru & GTK</div>
            <button class="btn-primary-custom" onclick="openModal()">+ Tambah Guru</button>
        </div>

        <?php
        // Hitung jumlah guru per tipe
        $jumlahPerTipe = [];
        foreach ($tipeList as $kode => $label) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM guru WHERE tipe = ?");
            $stmt->execute([$kode]);
            $jumlahPerTipe[$kode] = (int)$stmt->fetchColumn();
        }
        ?>

        <!-- Filter chips — client-side, instant -->
        <div class="filter-chips">
            <span class="filter-chips-label">Filter Tipe:</span>
            <button type="button"
                class="chip <?= $filterTipeGuru === '' ? 'active' : '' ?>"
                onclick="filterByTipe(this, '')">
                Semua <span class="chip-count">(<?= $allGuruCountGlobal ?>)</span>
            </button>
            <?php foreach ($tipeList as $kode => $label):
                $isActive = $filterTipeGuru === $kode;
                $jml = $jumlahPerTipe[$kode] ?? 0;
            ?>
                <button type="button"
                    class="chip <?= $isActive ? 'active' : '' ?>"
                    onclick="filterByTipe(this, '<?= htmlspecialchars($kode) ?>')">
                    <?= htmlspecialchars($label) ?> <span class="chip-count">(<?= $jml ?>)</span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="result-info">
            Menampilkan <strong id="guruVisibleCount" style="color:var(--hijau);"><?= count($guruList) ?></strong> guru
            <?php if ($filterTipeGuru): ?>
                tipe <strong><?= htmlspecialchars($tipeList[$filterTipeGuru] ?? $filterTipeGuru) ?></strong>
            <?php endif; ?>
        </div>

        <div style="overflow-x:auto;">
            <table class="guru-table" id="guruTable">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Guru</th>
                        <th>Jabatan</th>
                        <th style="width:130px;">Tipe</th>
                        <th style="width:120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guruList as $i => $g):
                        $t = $tipeLabel[$g['tipe']] ?? ['label' => $g['tipe'], 'class' => ''];
                    ?>
                        <tr data-tipe="<?= htmlspecialchars($g['tipe']) ?>">
                            <td style="color:#9ca3af;"><?= $i + 1 ?></td>
                            <td>
                                <div class="guru-name"><?= htmlspecialchars($g['nama']) ?></div>
                                <div class="guru-meta">
                                    <?php if (!empty($g['nrg'])): ?>
                                        <span title="NRG">🆔 <?= htmlspecialchars($g['nrg']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($g['tmt_guru'])): ?>
                                        <span title="TMT Guru">📅 <?= date('d/m/Y', strtotime($g['tmt_guru'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($g['jabatan'] ?? '-') ?></td>
                            <td><span class="badge-tipe <?= htmlspecialchars($t['class']) ?>"><?= htmlspecialchars($t['label']) ?></span></td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-primary-custom btn-sm-custom btn-edit" onclick='openEdit(<?= json_encode($g) ?>)'>Edit</button>
                                    <button class="btn-primary-custom btn-sm-custom btn-delete" onclick="confirmDelete(<?= $g['id_guru'] ?>,'guru.php')">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($guruList)): ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <div class="emoji">👥</div>
                                <div class="title">Belum ada data guru</div>
                                <div class="subtitle">Klik <strong>+ Tambah Guru</strong> untuk memulai.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     PANEL: RIWAYAT
     ═══════════════════════════════════════════════════════════════ -->
<div id="panel-history" style="display:none;">
    <div class="data-table-card">
        <div class="card-header-custom" style="align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="card-title-custom">Riwayat Perubahan Data Guru</div>
                <p style="font-size:12px;color:#6b7280;margin:4px 0 0;">Catatan semua aktivitas penambahan, perubahan, dan penghapusan data guru.</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <select id="filterAksi" onchange="filterHistory()" class="form-control-custom" style="padding:7px 12px;font-size:12.5px;">
                    <option value="">Semua Aksi</option>
                    <option value="tambah">✅ Ditambahkan</option>
                    <option value="edit">✏️ Diedit</option>
                    <option value="hapus">🗑️ Dihapus</option>
                </select>
                <div class="guru-search">
                    <span>🔍</span>
                    <input type="text" id="searchHistory" autocomplete="off" onkeyup="filterHistory()" placeholder="Cari nama guru...">
                </div>
                <?php if (!empty($historyList)): ?>
                    <button onclick="confirmResetHistory()" class="btn-reset-all">
                        🗑️ Reset Semua
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php
        $cTambah = 0; $cEdit = 0; $cHapus = 0;
        foreach ($historyList as $h) {
            if ($h['aksi'] === 'tambah') $cTambah++;
            elseif ($h['aksi'] === 'edit') $cEdit++;
            elseif ($h['aksi'] === 'hapus') $cHapus++;
        }
        ?>
        <div class="stat-grid">
            <div class="stat-card stat-green">
                <div class="stat-value"><?= $cTambah ?></div>
                <div class="stat-label">✅ Ditambahkan</div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="stat-value"><?= $cEdit ?></div>
                <div class="stat-label">✏️ Diedit</div>
            </div>
            <div class="stat-card stat-red">
                <div class="stat-value"><?= $cHapus ?></div>
                <div class="stat-label">🗑️ Dihapus</div>
            </div>
            <div class="stat-card stat-blue">
                <div class="stat-value"><?= count($historyList) ?></div>
                <div class="stat-label">📋 Total Riwayat</div>
            </div>
        </div>

        <?php if (empty($historyList)): ?>
            <div class="empty-state">
                <div class="emoji">📋</div>
                <div class="title">Belum ada riwayat perubahan</div>
                <div class="subtitle">Setiap penambahan, pengeditan, atau penghapusan data guru akan tercatat di sini.</div>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="guru-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Aksi</th>
                            <th>Guru</th>
                            <th>Jabatan</th>
                            <th style="width:95px;">Tipe</th>
                            <th>Oleh</th>
                            <th style="width:140px;">Waktu</th>
                            <th>Keterangan</th>
                            <th style="width:50px;text-align:center;">—</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <?php foreach ($historyList as $i => $h):
                            $t = $tipeLabel[$h['tipe'] ?? ''] ?? ['label' => ($h['tipe'] ?? '-'), 'class' => ''];
                            $aksiClass = 'aksi-tambah';
                            $aksiLabel = '✅ Ditambah';
                            if ($h['aksi'] === 'edit')  { $aksiClass = 'aksi-edit';  $aksiLabel = '✏️ Diedit'; }
                            elseif ($h['aksi'] === 'hapus') { $aksiClass = 'aksi-hapus'; $aksiLabel = '🗑️ Dihapus'; }
                        ?>
                            <tr class="history-row" data-aksi="<?= $h['aksi'] ?>" data-nama="<?= strtolower(htmlspecialchars($h['nama'])) ?>">
                                <td>
                                    <span class="aksi-badge <?= $aksiClass ?>"><?= $aksiLabel ?></span>
                                </td>
                                <td>
                                    <div class="guru-name" style="font-size:12.5px;"><?= htmlspecialchars($h['nama']) ?></div>
                                    <?php if (!empty($h['nrg'])): ?>
                                        <div class="guru-meta"><span>🆔 <?= htmlspecialchars($h['nrg']) ?></span></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:12px;"><?= htmlspecialchars($h['jabatan'] ?? '-') ?></td>
                                <td>
                                    <?php if ($h['tipe']): ?>
                                        <span class="badge-tipe <?= htmlspecialchars($t['class']) ?>" style="font-size:10px;"><?= htmlspecialchars($t['label']) ?></span>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:6px;">
                                        <span class="user-avatar"><?= strtoupper(substr($h['oleh'], 0, 1)) ?></span>
                                        <span style="font-size:12px;"><?= htmlspecialchars($h['oleh']) ?></span>
                                    </span>
                                </td>
                                <td style="color:#6b7280;white-space:nowrap;font-size:11.5px;">
                                    <?= date('d/m/Y', strtotime($h['waktu'])) ?><br>
                                    <small style="color:#9ca3af;"><?= date('H:i:s', strtotime($h['waktu'])) ?></small>
                                </td>
                                <td style="font-size:11.5px;color:#6b7280;max-width:220px;word-break:break-word;">
                                    <?= $h['keterangan'] ? htmlspecialchars($h['keterangan']) : '<span style="color:#d1d5db;">—</span>' ?>
                                </td>
                                <td style="text-align:center;">
                                    <button onclick="confirmDeleteHistory(<?= $h['id_guru_history'] ?>, '<?= htmlspecialchars(addslashes($h['nama'])) ?>')"
                                        title="Hapus riwayat ini" class="btn-icon">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 4px;font-size:11.5px;color:#9ca3af;">
                Menampilkan <?= count($historyList) ?> riwayat terakhir.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════
     PANEL: TIPE GURU
     ═══════════════════════════════════════════════════════════════ -->
<div id="panel-tipe" style="display:none;">
    <div class="data-table-card">
        <div class="card-header-custom" style="flex-wrap:wrap;gap:10px;">
            <div>
                <div class="card-title-custom">Manajemen Tipe Guru</div>
                <p style="font-size:12.5px;color:#6b7280;margin:4px 0 0;">
                    Kelola kategori tipe guru yang dipakai di seluruh modul.
                </p>
            </div>
            <button class="btn-primary-custom" onclick="openModalTipe()">+ Tambah Tipe</button>
        </div>

        <div class="stat-grid">
            <div class="stat-card stat-green">
                <div class="stat-value"><?= count($tipeFullList) ?></div>
                <div class="stat-label">🏷️ Total Tipe</div>
            </div>
            <div class="stat-card stat-blue">
                <div class="stat-value"><?= array_sum(array_column($tipeFullList, 'jumlah_guru')) ?></div>
                <div class="stat-label">👥 Total Guru</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="guru-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Tipe</th>
                        <th style="width:130px;">Kode</th>
                        <th style="width:80px;text-align:center;">Urutan</th>
                        <th style="width:100px;text-align:center;">Guru</th>
                        <th style="width:120px;text-align:center;">Komponen</th>
                        <th style="width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tipeFullList)): ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <div class="emoji">🏷️</div>
                                <div class="title">Belum ada tipe guru</div>
                                <div class="subtitle">Klik <strong>+ Tambah Tipe</strong> untuk memulai.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $badgePool = [
                            ['bg'=>'#dcfce7','color'=>'#15803d','border'=>'#86efac'],
                            ['bg'=>'#dbeafe','color'=>'#1d4ed8','border'=>'#93c5fd'],
                            ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047'],
                            ['bg'=>'#f3e8ff','color'=>'#7c3aed','border'=>'#c4b5fd'],
                            ['bg'=>'#fee2e2','color'=>'#b91c1c','border'=>'#fca5a5'],
                            ['bg'=>'#ffedd5','color'=>'#c2410c','border'=>'#fdba74'],
                            ['bg'=>'#e0f2fe','color'=>'#0369a1','border'=>'#7dd3fc'],
                        ];
                        foreach ($tipeFullList as $i => $t):
                            $bc = $badgePool[$i % count($badgePool)];
                        ?>
                            <tr>
                                <td style="color:#9ca3af;"><?= $i + 1 ?></td>
                                <td>
                                    <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:11.5px;font-weight:600;background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>;border:1px solid <?= $bc['border'] ?>;">
                                        <?= htmlspecialchars($t['label']) ?>
                                    </span>
                                </td>
                                <td>
                                    <code style="background:#f3f4f6;padding:3px 8px;border-radius:6px;font-size:11.5px;color:#374151;">
                                        <?= htmlspecialchars($t['kode']) ?>
                                    </code>
                                </td>
                                <td style="text-align:center;">
                                    <span style="background:#f3f4f6;padding:3px 10px;border-radius:6px;font-size:12px;">
                                        <?= $t['urutan'] ?>
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($t['jumlah_guru'] > 0): ?>
                                        <span class="count-pill blue"><?= $t['jumlah_guru'] ?></span>
                                    <?php else: ?>
                                        <span class="count-pill muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($t['jumlah_komponen'] > 0): ?>
                                        <span class="count-pill purple"><?= $t['jumlah_komponen'] ?></span>
                                    <?php else: ?>
                                        <span class="count-pill muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-primary-custom btn-sm-custom btn-edit"
                                            onclick='openEditTipe(<?= json_encode($t) ?>)'>
                                            Edit
                                        </button>
                                        <?php if ($t['jumlah_guru'] == 0 && $t['jumlah_komponen'] == 0): ?>
                                            <button class="btn-primary-custom btn-sm-custom btn-delete"
                                                onclick="confirmHapusTipe(<?= $t['id_tipe_guru'] ?>, '<?= htmlspecialchars(addslashes($t['label'])) ?>')">
                                                Hapus
                                            </button>
                                        <?php else: ?>
                                            <button disabled title="Masih digunakan, tidak bisa dihapus" class="btn-disabled">
                                                Hapus
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="info-footer">
            <div class="info-footer-title">ℹ️ Catatan Penting</div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div>🔒 Tipe yang sudah dipakai guru atau komponen penilaian <strong>tidak dapat dihapus</strong>.</div>
                <div>🔤 <strong>Kode</strong> hanya boleh mengandung huruf kecil, angka, dan underscore — contoh: <code>guru_kelas</code>.</div>
                <div>🔢 <strong>Urutan</strong> ditetapkan otomatis; dapat diubah lewat <em>Edit</em>. Angka kecil = tampil lebih dulu.</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Tipe client-side (instan) -->
<script>
    function filterByTipe(btn, tipe) {
        document.querySelectorAll('#panel-data .chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#guruTable tbody tr');
        let no = 1;
        let visible = 0;
        rows.forEach(tr => {
            if (!tr.dataset.tipe) return;
            const show = !tipe || tr.dataset.tipe === tipe;
            tr.style.display = show ? '' : 'none';
            if (show) {
                const numCell = tr.querySelector('td:first-child');
                if (numCell) numCell.textContent = no++;
                visible++;
            }
        });

        const info = document.getElementById('guruVisibleCount');
        if (info) info.textContent = visible;
    }
</script>


<!-- Modal Tambah/Edit Tipe Guru -->
<div class="modal fade" id="tipeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tipeModalTitle">Tambah Tipe Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="guru.php" autocomplete="off">
                <input type="hidden" name="form_type" value="tipe">
                <input type="hidden" name="action" id="tipeFormAction" value="add">
                <input type="hidden" name="id" id="tipeFormId" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-label-custom">Kode Tipe <span style="color:#ef4444;">*</span></div>
                            <input type="text" name="kode" id="ft_kode" class="form-control-custom"
                                placeholder="Contoh: guru_kelas"
                                pattern="[a-zA-Z0-9_]+"
                                title="Hanya huruf, angka, dan underscore"
                                required>
                            <div style="font-size:12px;color:#374151;background:#eff6ff;border:1px solid #bfdbfe;border-radius:7px;padding:7px 10px;margin-top:6px;display:flex;align-items:flex-start;gap:6px;">
                                <span style="font-size:14px;flex-shrink:0;">✏️</span>
                                <span>Gunakan <strong>huruf kecil, angka, dan underscore</strong> saja. Contoh: <code style="background:#dbeafe;padding:1px 5px;border-radius:4px;">guru_kelas</code>, <code style="background:#dbeafe;padding:1px 5px;border-radius:4px;">gtk</code></span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-label-custom">Label Tampil <span style="color:#ef4444;">*</span></div>
                            <input type="text" name="label" id="ft_label" class="form-control-custom"
                                placeholder="Contoh: Guru Kelas" required
                                oninput="document.getElementById('tipe-preview').textContent=this.value||'Label Tipe'">
                            <div style="font-size:12px;color:#374151;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:7px 10px;margin-top:6px;display:flex;align-items:flex-start;gap:6px;">
                                <span style="font-size:14px;flex-shrink:0;">🏷️</span>
                                <span>Nama ini yang akan tampil di <strong>dropdown pemilihan</strong> dan <strong>badge</strong> pada tabel guru.</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-label-custom">Urutan Tampil</div>
                            <input type="number" name="urutan" id="ft_urutan" class="form-control-custom"
                                placeholder="0" min="0" value="<?= $nextUrutan ?>"
                                style="background:#f3f4f6;cursor:not-allowed;" readonly>
                            <div id="ft_urutan_note" style="font-size:12px;color:#374151;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:7px 10px;margin-top:6px;display:flex;align-items:flex-start;gap:6px;">
                                <span style="font-size:14px;flex-shrink:0;">ℹ️</span>
                                <span><strong>Ditetapkan otomatis.</strong> Urutan ini menentukan posisi tampil di dropdown — angka kecil muncul lebih dulu. Bisa diubah melalui tombol <em>Edit</em> setelah tipe disimpan.</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-label-custom">Preview Badge</div>
                            <div style="padding:12px 16px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                                <span id="tipe-preview" style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:12.5px;font-weight:600;background:#dcfce7;color:#15803d;border:1px solid #86efac;">
                                    Label Tipe
                                </span>
                                <span style="font-size:11.5px;color:#9ca3af;">tampilan di tabel & dropdown</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">Simpan Tipe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="guruModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Form tambah/edit guru: autocomplete="off" mencegah browser mengisi otomatis field sensitif -->
            <form method="POST" action="guru.php" autocomplete="off">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="formId" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="form-label-custom">Nama Lengkap</div>
                            <input type="text" name="nama" autocomplete="off" id="f_nama" class="form-control-custom" required>
                        </div>
                        <div class="col-md-4">
                            <div class="form-label-custom">Tipe</div>
                            <select name="tipe" id="f_tipe" class="form-control-custom">
                                <?php foreach ($tipeList as $kode => $label): ?>
                                    <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-label-custom">NRG</div>
                            <input type="text" name="nrg" autocomplete="off" id="f_nrg" class="form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <div class="form-label-custom">TMT Sebagai Guru</div>
                            <input type="date" name="tmt_guru" id="f_tmt" class="form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <div class="form-label-custom">Jabatan</div>
                            <input type="text" name="jabatan" autocomplete="off" id="f_jabatan" class="form-control-custom">
                        </div>
                        <div class="col-md-6">
                            <div class="form-label-custom">Status Kepegawaian</div>
                            <input type="text" name="status_kepegawaian" autocomplete="off" id="f_status" class="form-control-custom">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-custom">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let modal, tipeModal;
    document.addEventListener('DOMContentLoaded', function() {
        modal = new bootstrap.Modal(document.getElementById('guruModal'));
        tipeModal = new bootstrap.Modal(document.getElementById('tipeModal'));
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || (window.location.hash === '#history' ? 'history' : 'data');
        switchTab(tab);
    });

    function switchTab(tab) {
        const tabs = ['data', 'history', 'tipe'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-' + t);
            const panel = document.getElementById('panel-' + t);
            if (!btn || !panel) return;
            const isActive = t === tab;
            panel.style.display = isActive ? '' : 'none';
            btn.classList.toggle('active', isActive);
        });

        if (tab === 'history') {
            const badge = document.getElementById('history-badge');
            if (badge) badge.style.display = 'none';
            localStorage.setItem('history_seen', '<?= count($historyList) ?>');
        }
    }

    // Sembunyikan badge jika sudah pernah dilihat dengan jumlah riwayat yang sama
    (function() {
        const badge = document.getElementById('history-badge');
        if (badge) {
            const seen = localStorage.getItem('history_seen');
            if (seen === '<?= count($historyList) ?>') badge.style.display = 'none';
        }
    })();

    function filterHistory() {
        const aksi = document.getElementById('filterAksi').value.toLowerCase();
        const cari = document.getElementById('searchHistory').value.toLowerCase();
        const rows = document.querySelectorAll('#historyBody .history-row');
        rows.forEach(row => {
            const cocokAksi = !aksi || row.dataset.aksi === aksi;
            const cocokNama = !cari || row.dataset.nama.includes(cari);
            row.style.display = cocokAksi && cocokNama ? '' : 'none';
        });
    }

    function openModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Data Guru';
        document.getElementById('formAction').value = 'add';
        document.getElementById('formId').value = '';
        document.getElementById('f_nama').value = '';
        document.getElementById('f_nrg').value = '';
        document.getElementById('f_tmt').value = '';
        document.getElementById('f_jabatan').value = '';
        document.getElementById('f_status').value = '';
        document.getElementById('f_tipe').value = 'guru_kelas';
        modal.show();
    }

    function openEdit(data) {
        document.getElementById('modalTitle').textContent = 'Edit Data Guru';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('formId').value = data.id_guru;
        document.getElementById('f_nama').value = data.nama || '';
        document.getElementById('f_nrg').value = data.nrg || '';
        document.getElementById('f_tmt').value = data.tmt_guru || '';
        document.getElementById('f_jabatan').value = data.jabatan || '';
        document.getElementById('f_status').value = data.status_kepegawaian || '';
        document.getElementById('f_tipe').value = data.tipe || 'guru_kelas';
        modal.show();
    }

    function confirmDeleteHistory(id, nama) {
        document.getElementById('confirmMsg').textContent = `Hapus riwayat untuk "${nama}"? Tindakan ini tidak bisa dibatalkan.`;
        document.getElementById('confirmBtn').onclick = function() {
            window.location.href = 'guru.php?action=delete_history&id=' + id;
        };
        const m = new bootstrap.Modal(document.getElementById('confirmModal'));
        m.show();
    }

    function confirmResetHistory() {
        document.getElementById('confirmMsg').textContent = 'Reset semua riwayat perubahan? Seluruh catatan akan dihapus permanen dan tidak bisa dikembalikan.';
        document.getElementById('confirmBtn').onclick = function() {
            window.location.href = 'guru.php?action=reset_history';
        };
        const m = new bootstrap.Modal(document.getElementById('confirmModal'));
        m.show();
    }

    // ── Fungsi Tipe Guru ──────────────────────────────────────────────────────
    function openModalTipe() {
        document.getElementById('tipeModalTitle').textContent = 'Tambah Tipe Guru';
        document.getElementById('tipeFormAction').value = 'add';
        document.getElementById('tipeFormId').value = '';
        document.getElementById('ft_kode').value = '';
        document.getElementById('ft_kode').readOnly = false;
        document.getElementById('ft_label').value = '';
        document.getElementById('ft_urutan').value = '<?= $nextUrutan ?>';
        document.getElementById('ft_urutan').readOnly = true;
        document.getElementById('ft_urutan').style.background = '#f3f4f6';
        document.getElementById('ft_urutan').style.cursor = 'not-allowed';
        document.getElementById('ft_urutan_note').innerHTML = '<span style="font-size:14px;flex-shrink:0;">ℹ️</span><span><strong>Ditetapkan otomatis.</strong> Urutan ini menentukan posisi tampil di dropdown — angka kecil muncul lebih dulu. Bisa diubah melalui tombol <em>Edit</em> setelah tipe disimpan.</span>';
        document.getElementById('tipe-preview').textContent = 'Label Tipe';
        tipeModal.show();
    }

    function openEditTipe(data) {
        document.getElementById('tipeModalTitle').textContent = 'Edit Tipe Guru';
        document.getElementById('tipeFormAction').value = 'edit';
        document.getElementById('tipeFormId').value = data.id_tipe_guru;
        document.getElementById('ft_kode').value = data.kode || '';
        document.getElementById('ft_kode').readOnly = (data.jumlah_guru > 0 || data.jumlah_komponen > 0);
        document.getElementById('ft_label').value = data.label || '';
        document.getElementById('ft_urutan').value = data.urutan || '0';
        document.getElementById('ft_urutan').readOnly = false;
        document.getElementById('ft_urutan').style.background = '';
        document.getElementById('ft_urutan').style.cursor = '';
        document.getElementById('ft_urutan_note').innerHTML = '<span style="font-size:14px;flex-shrink:0;">✏️</span><span>Angka <strong>lebih kecil</strong> tampil lebih dulu di dropdown. Ubah sesuai kebutuhan urutan tampil.</span>';
        document.getElementById('tipe-preview').textContent = data.label || 'Label Tipe';
        tipeModal.show();
    }

    function confirmHapusTipe(id, label) {
        document.getElementById('confirmMsg').textContent = `Hapus tipe "${label}"? Tindakan ini tidak bisa dibatalkan.`;
        document.getElementById('confirmBtn').onclick = function() {
            window.location.href = 'guru.php?action=delete_tipe&id=' + id;
        };
        const m = new bootstrap.Modal(document.getElementById('confirmModal'));
        m.show();
    }
</script>

<!-- Modal Konfirmasi Hapus/Reset -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:16px;border:none;box-shadow:0 20px 60px rgba(0,0,0,.15);">
            <div class="modal-body" style="padding:32px 28px 20px;text-align:center;">
                <div style="font-size:44px;margin-bottom:14px;">⚠️</div>
                <div style="font-size:15px;font-weight:700;color:#111827;margin-bottom:8px;">Konfirmasi Hapus</div>
                <div id="confirmMsg" style="font-size:13px;color:#6b7280;line-height:1.6;"></div>
            </div>
            <div class="modal-footer" style="border:none;padding:0 28px 24px;gap:8px;justify-content:center;">
                <button type="button" class="btn-cancel btn-sm-custom" data-bs-dismiss="modal"
                    style="border-radius:8px;padding:8px 20px;font-size:13px;">Batal</button>
                <button type="button" id="confirmBtn"
                    class="btn-danger-custom btn-sm-custom">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>