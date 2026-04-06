<?php
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
    $stmt = $pdo->prepare("INSERT INTO guru_history (aksi, guru_id, nama, nrg, tmt_guru, jabatan, status_kepegawaian, tipe, oleh, keterangan)
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
        $msg = 'Nama guru wajib diisi!';
    } else {
        if ($post_action === 'edit' && $post_id) {
            // Ambil data lama untuk keterangan perubahan
            $lama = $pdo->prepare("SELECT * FROM guru WHERE id=?");
            $lama->execute([$post_id]);
            $dataLama = $lama->fetch();

            $stmt = $pdo->prepare("UPDATE guru SET nama=?,nrg=?,tmt_guru=?,jabatan=?,status_kepegawaian=?,tipe=? WHERE id=?");
            $stmt->execute([$nama, $nrg, $tmt, $jabatan, $status, $tipe, $post_id]);

            $ket = 'Sebelum: ' . ($dataLama['nama'] ?? '') . ' | ' . ($dataLama['jabatan'] ?? '') . ' | ' . ($dataLama['tipe'] ?? '');
            catatHistory($pdo, 'edit', $post_id, $dataGuru, $oleh, $ket);
        } else {
            $stmt = $pdo->prepare("INSERT INTO guru (nama,nrg,tmt_guru,jabatan,status_kepegawaian,tipe) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$nama, $nrg, $tmt, $jabatan, $status, $tipe]);
            $newId = $pdo->lastInsertId();

            catatHistory($pdo, 'tambah', $newId, $dataGuru, $oleh);
        }
        // Simpan session sebelum redirect agar data tidak hilang
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('Data guru berhasil disimpan!'));
        exit;
    }
}

// ─── Handle POST: Hapus satu entri riwayat — dilindungi CSRF token ──────────
// BUG-QA-01 FIX: Diubah dari GET ke POST+CSRF agar konsisten dan tidak rentan CSRF.
if ($action === 'delete_history' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_tokens']['delete_history'] ?? '';
    unset($_SESSION['csrf_tokens']['delete_history']); // one-time use
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.') . '&tab=history');
        exit;
    }
    $del_id = (int)($_POST['del_id'] ?? 0);
    if ($del_id) {
        $pdo->prepare("DELETE FROM guru_history WHERE id=?")->execute([$del_id]);
    }
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: guru.php?msg=' . urlencode('Riwayat berhasil dihapus!') . '&tab=history');
    exit;
}

// ─── Handle POST: Reset (hapus semua) riwayat — dilindungi CSRF token ──────────
if ($action === 'reset_history' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    // BUG-05 FIX: Gunakan array keyed untuk mencegah race condition multi-tab
    $token_session = $_SESSION['csrf_tokens']['reset_history'] ?? '';
    unset($_SESSION['csrf_tokens']['reset_history']); // one-time use
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.') . '&tab=history');
        exit;
    }
    $pdo->exec("DELETE FROM guru_history");
    session_write_close();
    header('Location: guru.php?msg=' . urlencode('Semua riwayat berhasil direset!') . '&tab=history');
    exit;
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
        $msg = '⚠️ Kode dan Label tipe guru wajib diisi!';
    } else {
        if ($post_action === 'edit' && $post_id) {
            $cek = $pdo->prepare("SELECT id FROM tipe_guru WHERE kode=? AND id!=?");
            $cek->execute([$kode, $post_id]);
            if ($cek->fetch()) {
                $msg = '⚠️ Kode tipe sudah digunakan oleh tipe lain!';
            } else {
                $pdo->prepare("UPDATE tipe_guru SET kode=?,label=?,urutan=? WHERE id=?")->execute([$kode,$label,$urutan,$post_id]);
                unset($GLOBALS['_cache_tipe_guru']);
                session_write_close();
                header('Location: guru.php?msg=' . urlencode('Tipe guru berhasil diperbarui!') . '&tab=tipe');
                exit;
            }
        } else {
            $cek = $pdo->prepare("SELECT id FROM tipe_guru WHERE kode=?");
            $cek->execute([$kode]);
            if ($cek->fetch()) {
                $msg = '⚠️ Kode tipe sudah ada! Gunakan kode yang berbeda.';
            } else {
                $pdo->prepare("INSERT INTO tipe_guru (kode,label,urutan) VALUES (?,?,?)")->execute([$kode,$label,$urutan]);
                unset($GLOBALS['_cache_tipe_guru']);
                session_write_close();
                header('Location: guru.php?msg=' . urlencode('Tipe guru baru berhasil ditambahkan!') . '&tab=tipe');
                exit;
            }
        }
    }
}

// ─── Handle POST: Hapus tipe guru — dilindungi CSRF token ────────────────────
// BUG-QA-01 FIX: Diubah dari GET ke POST+CSRF.
if ($action === 'delete_tipe' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_tokens']['delete_tipe'] ?? '';
    unset($_SESSION['csrf_tokens']['delete_tipe']); // one-time use
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.') . '&tab=tipe');
        exit;
    }
    $del_id = (int)($_POST['del_id'] ?? 0);
    $cekGuru = $pdo->prepare("SELECT COUNT(*) as n FROM guru WHERE tipe=(SELECT kode FROM tipe_guru WHERE id=?)");
    $cekGuru->execute([$del_id]);
    $nGuru = $cekGuru->fetch()['n'];

    $cekKomp = $pdo->prepare("SELECT COUNT(*) as n FROM komponen_penilaian WHERE tipe_guru=(SELECT kode FROM tipe_guru WHERE id=?)");
    $cekKomp->execute([$del_id]);
    $nKomp = $cekKomp->fetch()['n'];

    if ($nGuru > 0 || $nKomp > 0) {
        $alasan = $nGuru > 0 ? "{$nGuru} data guru" : "{$nKomp} komponen penilaian";
        session_write_close();
        header('Location: guru.php?msg=' . urlencode("⚠️ Tipe tidak dapat dihapus karena masih digunakan oleh {$alasan}!") . '&tab=tipe');
        exit;
    }
    $pdo->prepare("DELETE FROM tipe_guru WHERE id=?")->execute([$del_id]);
    unset($GLOBALS['_cache_tipe_guru']);
    session_write_close();
    header('Location: guru.php?msg=' . urlencode('Tipe guru berhasil dihapus!') . '&tab=tipe');
    exit;
}

// ─── Handle POST: Hapus satu data guru — dilindungi CSRF token ───────────────
// BUG-QA-01 FIX: Diubah dari GET ke POST+CSRF.
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_dikirim = $_POST['csrf_token'] ?? '';
    $token_session = $_SESSION['csrf_tokens']['delete_guru'] ?? '';
    unset($_SESSION['csrf_tokens']['delete_guru']); // one-time use
    if (!$token_dikirim || !hash_equals($token_session, $token_dikirim)) {
        session_write_close();
        header('Location: guru.php?msg=' . urlencode('⚠️ Permintaan tidak valid! Silakan coba lagi.'));
        exit;
    }
    $del_id = (int)($_POST['del_id'] ?? 0);
    $row = $pdo->prepare("SELECT * FROM guru WHERE id=?");
    $row->execute([$del_id]);
    $dataHapus = $row->fetch();
    $oleh = $user['nama_lengkap'] ?? 'Admin';

    if ($dataHapus) {
        catatHistory($pdo, 'hapus', $del_id, $dataHapus, $oleh);
    }

    $pdo->prepare("DELETE FROM guru WHERE id=?")->execute([$del_id]);
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: guru.php?msg=' . urlencode('Data guru berhasil dihapus!'));
    exit;
}

if (isset($_GET['msg'])) $msg = sanitize($_GET['msg']);

// ─── CSRF token untuk reset_history ─────────────────────────────────────────
// BUG-05 FIX: Simpan di array keyed agar tidak tertimpa jika dua tab dibuka
$csrf_reset_history = bin2hex(random_bytes(16));
$_SESSION['csrf_tokens']['reset_history'] = $csrf_reset_history;

// BUG-QA-01 FIX: Token untuk hapus tunggal (guru, riwayat, tipe) via POST+CSRF
$csrf_delete_guru    = bin2hex(random_bytes(16));
$csrf_delete_history = bin2hex(random_bytes(16));
$csrf_delete_tipe    = bin2hex(random_bytes(16));
$_SESSION['csrf_tokens']['delete_guru']    = $csrf_delete_guru;
$_SESSION['csrf_tokens']['delete_history'] = $csrf_delete_history;
$_SESSION['csrf_tokens']['delete_tipe']    = $csrf_delete_tipe;

$guruList = $pdo->query("SELECT * FROM guru ORDER BY tipe, nama")->fetchAll();

// ─── SARAN-02: Server-side pagination riwayat ─────────────────────────────────
// Sebelumnya: hard limit 200 baris tanpa navigasi halaman
// Sekarang: 50 baris per halaman dengan prev/next, filter tetap berjalan di JS
$historyPerPage = 50;
$historyPage    = max(1, (int)($_GET['hpage'] ?? 1));
$historyTotal   = (int)$pdo->query("SELECT COUNT(*) FROM guru_history")->fetchColumn();
$historyPages   = max(1, (int)ceil($historyTotal / $historyPerPage));
$historyPage    = min($historyPage, $historyPages); // clamp ke halaman valid
$historyOffset  = ($historyPage - 1) * $historyPerPage;

$stmtHistory = $pdo->prepare("SELECT * FROM guru_history ORDER BY waktu DESC LIMIT ? OFFSET ?");
$stmtHistory->execute([$historyPerPage, $historyOffset]);
$historyList = $stmtHistory->fetchAll();

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
           (SELECT COUNT(*) FROM komponen_penilaian kp WHERE kp.tipe_guru = t.kode) AS jumlah_komponen
    FROM tipe_guru t ORDER BY t.urutan, t.id
")->fetchAll();

// Hitung urutan berikutnya otomatis (max urutan + 1)
$maxUrutan = $pdo->query("SELECT COALESCE(MAX(urutan),0) FROM tipe_guru")->fetchColumn();
$nextUrutan = (int)$maxUrutan + 1;

$pageTitle = 'Data Guru & GTK';
require_once 'includes/header.php';
?>

<?php if ($msg): ?>
    <?php $isWarn = str_starts_with($msg, '⚠️'); ?>
    <div class="alert-custom <?= $isWarn ? 'alert-error-custom' : 'alert-success-custom' ?> mb-4">
        <?= $isWarn ? '' : '✓ ' ?><?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Tab Navigation -->
<div style="display:flex;gap:0;margin-bottom:24px;border-bottom:2px solid #e5e7eb;">
    <button id="tab-data" onclick="switchTab('data')"
        style="padding:10px 24px;font-size:13.5px;font-weight:600;border:none;background:none;cursor:pointer;color:var(--hijau);border-bottom:3px solid var(--hijau);margin-bottom:-2px;transition:all .2s;">
        👥 Data Guru & GTK
    </button>
    <button id="tab-history" onclick="switchTab('history')"
        style="padding:10px 24px;font-size:13.5px;font-weight:600;border:none;background:none;cursor:pointer;color:#6b7280;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s;">
        🕓 Riwayat Perubahan
        <?php if ($historyTotal > 0): ?>
            <span id="history-badge" style="background:#ef4444;color:#fff;font-size:10px;padding:2px 6px;border-radius:99px;margin-left:4px;"><?= $historyTotal ?></span>
        <?php endif; ?>
    </button>
    <button id="tab-tipe" onclick="switchTab('tipe')"
        style="padding:10px 24px;font-size:13.5px;font-weight:600;border:none;background:none;cursor:pointer;color:#6b7280;border-bottom:3px solid transparent;margin-bottom:-2px;transition:all .2s;">
        🏷️ Tipe Guru
        <span style="background:#6b7280;color:#fff;font-size:10px;padding:2px 6px;border-radius:99px;margin-left:4px;"><?= count($tipeFullList) ?></span>
    </button>
</div>

<!-- Panel: Data Guru -->
<div id="panel-data">
    <div class="data-table-card">
        <div class="card-header-custom">
            <div class="card-title-custom">Daftar Guru & GTK</div>
            <button class="btn-primary-custom" onclick="openModal()">+ Tambah Guru</button>
        </div>

        <table class="table table-hover datatable" style="font-size:13px;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th>No</th>
                    <th>Nama Guru</th>
                    <th>NRG</th>
                    <th>TMT</th>
                    <th>Jabatan</th>
                    <th>Tipe</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($guruList as $i => $g):
                    $t = $tipeLabel[$g['tipe']] ?? ['label' => $g['tipe'], 'class' => ''];
                ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($g['nama']) ?></strong></td>
                        <td><small><?= htmlspecialchars($g['nrg'] ?? '-') ?></small></td>
                        <td><small><?= $g['tmt_guru'] ? date('d/m/Y', strtotime($g['tmt_guru'])) : '-' ?></small></td>
                        <td><?= htmlspecialchars($g['jabatan'] ?? '-') ?></td>
                        <td><span class="badge-tipe <?= htmlspecialchars($t['class']) ?>"><?= htmlspecialchars($t['label']) ?></span></td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button class="btn-primary-custom btn-sm-custom btn-edit" onclick='openEdit(<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'>Edit</button>
                                <button class="btn-primary-custom btn-sm-custom btn-delete" onclick="confirmDeleteGuru(<?= $g['id'] ?>, '<?= htmlspecialchars(addslashes($g['nama'])) ?>')">Hapus</button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Panel: Riwayat History -->
<div id="panel-history" style="display:none;">
    <div class="data-table-card">
        <div class="card-header-custom" style="align-items:flex-start;flex-wrap:wrap;gap:12px;">
            <div>
                <div class="card-title-custom">Riwayat Perubahan Data Guru</div>
                <p style="font-size:12px;color:#6b7280;margin:4px 0 0;">Catatan semua aktivitas penambahan, perubahan, dan penghapusan data guru.</p>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <select id="filterAksi" onchange="filterHistory()" style="padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12.5px;color:#374151;background:#fff;cursor:pointer;">
                    <option value="">Semua Aksi</option>
                    <option value="tambah">✅ Ditambahkan</option>
                    <option value="edit">✏️ Diedit</option>
                    <option value="hapus">🗑️ Dihapus</option>
                </select>
                <input type="text" id="searchHistory" autocomplete="off" onkeyup="filterHistory()" placeholder="🔍 Cari nama guru..."
                    style="padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12.5px;width:180px;">
                <?php if (!empty($historyList)): ?>
                    <button onclick="confirmResetHistory()"
                        style="padding:7px 14px;background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:5px;">
                        🗑️ Reset Semua
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik ringkas — query langsung dari DB agar akurat meski history terpaginasi -->
        <?php
        $statsRow = $pdo->query("
            SELECT
                SUM(aksi = 'tambah') AS c_tambah,
                SUM(aksi = 'edit')   AS c_edit,
                SUM(aksi = 'hapus')  AS c_hapus
            FROM guru_history
        ")->fetch();
        $cTambah = (int)($statsRow['c_tambah'] ?? 0);
        $cEdit   = (int)($statsRow['c_edit']   ?? 0);
        $cHapus  = (int)($statsRow['c_hapus']  ?? 0);
        ?>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <div style="flex:1;min-width:120px;background:#dcfce7;border-radius:10px;padding:14px 18px;border-left:4px solid #16a34a;">
                <div style="font-size:22px;font-weight:700;color:#15803d;"><?= $cTambah ?></div>
                <div style="font-size:11.5px;color:#166534;margin-top:2px;">✅ Ditambahkan</div>
            </div>
            <div style="flex:1;min-width:120px;background:#fef9c3;border-radius:10px;padding:14px 18px;border-left:4px solid #ca8a04;">
                <div style="font-size:22px;font-weight:700;color:#854d0e;"><?= $cEdit ?></div>
                <div style="font-size:11.5px;color:#92400e;margin-top:2px;">✏️ Diedit</div>
            </div>
            <div style="flex:1;min-width:120px;background:#fee2e2;border-radius:10px;padding:14px 18px;border-left:4px solid #dc2626;">
                <div style="font-size:22px;font-weight:700;color:#b91c1c;"><?= $cHapus ?></div>
                <div style="font-size:11.5px;color:#991b1b;margin-top:2px;">🗑️ Dihapus</div>
            </div>
            <div style="flex:1;min-width:120px;background:#f0f9ff;border-radius:10px;padding:14px 18px;border-left:4px solid #0284c7;">
                <div style="font-size:22px;font-weight:700;color:#0369a1;"><?= $historyTotal ?></div>
                <div style="font-size:11.5px;color:#075985;margin-top:2px;">📋 Total Riwayat</div>
            </div>
        </div>

        <?php if (empty($historyList)): ?>
            <div style="text-align:center;padding:60px 20px;color:#9ca3af;">
                <div style="font-size:48px;margin-bottom:12px;">📋</div>
                <div style="font-size:14px;font-weight:600;">Belum ada riwayat perubahan</div>
                <div style="font-size:12px;margin-top:4px;">Setiap penambahan, pengeditan, atau penghapusan data guru akan tercatat di sini.</div>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="table table-hover" style="font-size:12.5px;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="width:40px;">No</th>
                            <th style="width:115px;">Aksi</th>
                            <th>Nama Guru</th>
                            <th>Jabatan</th>
                            <th style="width:95px;">Tipe</th>
                            <th>Oleh</th>
                            <th style="width:140px;">Waktu</th>
                            <th>Keterangan</th>
                            <th style="width:50px;text-align:center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                        <?php foreach ($historyList as $i => $h):
                            $t = $tipeLabel[$h['tipe'] ?? ''] ?? ['label' => ($h['tipe'] ?? '-'), 'class' => ''];
                            $aksiStyle = match ($h['aksi']) {
                                'tambah' => ['label' => '✅ Ditambah', 'bg' => '#dcfce7', 'color' => '#15803d'],
                                'edit'   => ['label' => '✏️ Diedit',  'bg' => '#fef9c3', 'color' => '#854d0e'],
                                'hapus'  => ['label' => '🗑️ Dihapus', 'bg' => '#fee2e2', 'color' => '#b91c1c'],
                                default  => ['label' => $h['aksi'],   'bg' => '#f3f4f6', 'color' => '#374151'],
                            };
                        ?>
                            <tr class="history-row" data-aksi="<?= $h['aksi'] ?>" data-nama="<?= strtolower(htmlspecialchars($h['nama'])) ?>">
                                <td style="color:#9ca3af;"><?= $i + 1 ?></td>
                                <td>
                                    <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:<?= $aksiStyle['bg'] ?>;color:<?= $aksiStyle['color'] ?>;">
                                        <?= $aksiStyle['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($h['nama']) ?></strong>
                                    <?php if ($h['nrg']): ?><br><small style="color:#9ca3af;"><?= htmlspecialchars($h['nrg']) ?></small><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($h['jabatan'] ?? '-') ?></td>
                                <td>
                                    <?php if ($h['tipe']): ?>
                                        <span class="badge-tipe <?= htmlspecialchars($t['class']) ?>" style="font-size:10px;"><?= htmlspecialchars($t['label']) ?></span>
                                        <?php else: ?>-<?php endif; ?>
                                </td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:5px;">
                                        <span style="background:#e0f2fe;color:#0369a1;width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;">
                                            <?= strtoupper(substr($h['oleh'], 0, 1)) ?>
                                        </span>
                                        <span><?= htmlspecialchars($h['oleh']) ?></span>
                                    </span>
                                </td>
                                <td style="color:#6b7280;white-space:nowrap;">
                                    <?= date('d/m/Y', strtotime($h['waktu'])) ?><br>
                                    <small style="color:#9ca3af;"><?= date('H:i:s', strtotime($h['waktu'])) ?></small>
                                </td>
                                <td style="font-size:11.5px;color:#6b7280;max-width:200px;word-break:break-word;">
                                    <?= $h['keterangan'] ? htmlspecialchars($h['keterangan']) : '<span style="color:#d1d5db;">—</span>' ?>
                                </td>
                                <td style="text-align:center;">
                                    <button onclick="confirmDeleteHistory(<?= $h['id'] ?>, '<?= htmlspecialchars(addslashes($h['nama'])) ?>')"
                                        title="Hapus riwayat ini"
                                        style="background:#fee2e2;border:none;border-radius:6px;padding:4px 8px;cursor:pointer;color:#b91c1c;font-size:13px;">
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- SARAN-02: Pagination server-side untuk riwayat -->
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 4px;flex-wrap:wrap;gap:8px;">
                <div style="font-size:11.5px;color:#9ca3af;">
                    Menampilkan <?= number_format($historyOffset + 1) ?>–<?= number_format(min($historyOffset + $historyPerPage, $historyTotal)) ?>
                    dari <?= number_format($historyTotal) ?> riwayat
                    <?= ($historyPages > 1) ? "(halaman $historyPage dari $historyPages)" : '' ?>
                </div>
                <?php if ($historyPages > 1): ?>
                    <div style="display:flex;gap:4px;align-items:center;">
                        <?php if ($historyPage > 1): ?>
                            <a href="guru.php?tab=history&hpage=1"
                               style="padding:5px 9px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;background:#fff;">«</a>
                            <a href="guru.php?tab=history&hpage=<?= $historyPage - 1 ?>"
                               style="padding:5px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;background:#fff;">‹ Sebelumnya</a>
                        <?php endif; ?>

                        <?php
                        // Tampilkan maksimal 5 nomor halaman di sekitar halaman aktif
                        $pageStart = max(1, $historyPage - 2);
                        $pageEnd   = min($historyPages, $historyPage + 2);
                        for ($pg = $pageStart; $pg <= $pageEnd; $pg++):
                            $isActive = $pg === $historyPage;
                        ?>
                            <a href="guru.php?tab=history&hpage=<?= $pg ?>"
                               style="padding:5px 9px;border:1px solid <?= $isActive ? 'var(--hijau)' : '#e5e7eb' ?>;border-radius:6px;font-size:12px;
                                      color:<?= $isActive ? '#fff' : '#374151' ?>;text-decoration:none;
                                      background:<?= $isActive ? 'var(--hijau)' : '#fff' ?>;font-weight:<?= $isActive ? '600' : '400' ?>;">
                                <?= $pg ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($historyPage < $historyPages): ?>
                            <a href="guru.php?tab=history&hpage=<?= $historyPage + 1 ?>"
                               style="padding:5px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;background:#fff;">Berikutnya ›</a>
                            <a href="guru.php?tab=history&hpage=<?= $historyPages ?>"
                               style="padding:5px 9px;border:1px solid #e5e7eb;border-radius:6px;font-size:12px;color:#374151;text-decoration:none;background:#fff;">»</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Panel: Tipe Guru -->
<div id="panel-tipe" style="display:none;">
    <div class="data-table-card">
        <div class="card-header-custom">
            <div>
                <div class="card-title-custom">Manajemen Tipe Guru</div>
                <p style="font-size:12.5px;color:#6b7280;margin:4px 0 0;">
                    Kelola kategori tipe guru yang dipakai di seluruh modul (Data Guru, Komponen Penilaian, Penilaian Kinerja).
                </p>
            </div>
            <button class="btn-primary-custom" onclick="openModalTipe()">+ Tambah Tipe</button>
        </div>

        <!-- Ringkasan -->
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px;">
            <div style="flex:1;min-width:130px;background:#f0fdf4;border-radius:10px;padding:14px 18px;border-left:4px solid #16a34a;">
                <div style="font-size:22px;font-weight:700;color:#15803d;"><?= count($tipeFullList) ?></div>
                <div style="font-size:11.5px;color:#166534;margin-top:2px;">🏷️ Total Tipe</div>
            </div>
            <div style="flex:1;min-width:130px;background:#eff6ff;border-radius:10px;padding:14px 18px;border-left:4px solid #3b82f6;">
                <div style="font-size:22px;font-weight:700;color:#1d4ed8;"><?= array_sum(array_column($tipeFullList, 'jumlah_guru')) ?></div>
                <div style="font-size:11.5px;color:#1e40af;margin-top:2px;">👥 Total Guru</div>
            </div>
        </div>

        <!-- Tabel Tipe -->
        <div style="overflow-x:auto;">
            <table class="table table-hover" style="font-size:13px;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Kode</th>
                        <th>Label</th>
                        <th style="width:80px;text-align:center;">Urutan</th>
                        <th style="width:110px;text-align:center;">Jml Guru</th>
                        <th style="width:130px;text-align:center;">Jml Komponen</th>
                        <th style="width:150px;">Preview Badge</th>
                        <th style="width:130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tipeFullList)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:#9ca3af;">
                                <div style="font-size:36px;margin-bottom:8px;">🏷️</div>
                                Belum ada tipe guru. Klik <strong>+ Tambah Tipe</strong> untuk memulai.
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
                                    <code style="background:#f3f4f6;padding:3px 8px;border-radius:6px;font-size:12px;color:#374151;">
                                        <?= htmlspecialchars($t['kode']) ?>
                                    </code>
                                </td>
                                <td><strong><?= htmlspecialchars($t['label']) ?></strong></td>
                                <td style="text-align:center;">
                                    <span style="background:#f3f4f6;padding:3px 10px;border-radius:6px;font-size:12px;">
                                        <?= $t['urutan'] ?>
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($t['jumlah_guru'] > 0): ?>
                                        <span style="background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                            <?= $t['jumlah_guru'] ?> guru
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#d1d5db;font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if ($t['jumlah_komponen'] > 0): ?>
                                        <span style="background:#f3e8ff;color:#7c3aed;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                            <?= $t['jumlah_komponen'] ?> komponen
                                        </span>
                                    <?php else: ?>
                                        <span style="color:#d1d5db;font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="display:inline-block;padding:4px 12px;border-radius:20px;font-size:11.5px;font-weight:600;
                                        background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>;border:1px solid <?= $bc['border'] ?>;">
                                        <?= htmlspecialchars($t['label']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn-primary-custom btn-sm-custom btn-edit"
                                            onclick='openEditTipe(<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_TAG) ?>)'>
                                            Edit
                                        </button>
                                        <?php if ($t['jumlah_guru'] == 0 && $t['jumlah_komponen'] == 0): ?>
                                            <button class="btn-primary-custom btn-sm-custom btn-delete"
                                                onclick="confirmHapusTipe(<?= $t['id'] ?>, '<?= htmlspecialchars(addslashes($t['label'])) ?>')">
                                                Hapus
                                            </button>
                                        <?php else: ?>
                                            <button disabled title="Masih digunakan, tidak bisa dihapus"
                                                style="padding:5px 10px;font-size:12px;border-radius:7px;border:1px solid #e5e7eb;background:#f9fafb;color:#d1d5db;cursor:not-allowed;">
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

        <!-- Keterangan -->
        <div style="margin-top:16px;padding:14px 16px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;font-size:12.5px;color:#78350f;line-height:1.8;">
            <div style="font-weight:700;margin-bottom:6px;font-size:13px;">ℹ️ Catatan Penting</div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <div>🔒 Tipe yang sudah dipakai guru atau komponen penilaian <strong>tidak dapat dihapus</strong>.</div>
                <div>🔤 <strong>Kode</strong> hanya boleh mengandung huruf kecil, angka, dan underscore — contoh: <code style="background:#fef3c7;padding:1px 5px;border-radius:4px;">guru_kelas</code>.</div>
                <div>🔢 <strong>Urutan</strong> ditetapkan otomatis saat menambah; dapat diubah melalui tombol <em>Edit</em>. Angka kecil = tampil lebih dulu.</div>
            </div>
        </div>
    </div>
</div>

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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
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
            btn.style.color = isActive ? 'var(--hijau)' : '#6b7280';
            btn.style.borderBottom = isActive ? '3px solid var(--hijau)' : '3px solid transparent';
        });

        if (tab === 'history') {
            const badge = document.getElementById('history-badge');
            if (badge) badge.style.display = 'none';
            localStorage.setItem('history_seen', '<?= $historyTotal ?>');
        }
        if (tab === 'tipe') {
            const tipeBadge = document.querySelector('#tab-tipe span');
            if (tipeBadge) tipeBadge.style.background = 'var(--hijau)';
        }
    }

    // Sembunyikan badge jika sudah pernah dilihat dengan jumlah riwayat yang sama
    (function() {
        const badge = document.getElementById('history-badge');
        if (badge) {
            const seen = localStorage.getItem('history_seen');
            if (seen === '<?= $historyTotal ?>') badge.style.display = 'none';
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
        document.getElementById('formId').value = data.id;
        document.getElementById('f_nama').value = data.nama || '';
        document.getElementById('f_nrg').value = data.nrg || '';
        document.getElementById('f_tmt').value = data.tmt_guru || '';
        document.getElementById('f_jabatan').value = data.jabatan || '';
        document.getElementById('f_status').value = data.status_kepegawaian || '';
        document.getElementById('f_tipe').value = data.tipe || 'guru_kelas';
        modal.show();
    }

    // BUG-QA-01 FIX: hapus guru tunggal via POST+CSRF
    function confirmDeleteGuru(id, nama) {
        document.getElementById('confirmMsg').textContent = `Hapus data guru "${nama}"? Tindakan ini tidak bisa dibatalkan.`;
        document.getElementById('confirmBtn').onclick = function() {
            document.getElementById('hapusGuruId').value = id;
            document.getElementById('formHapusGuru').submit();
        };
        const m = new bootstrap.Modal(document.getElementById('confirmModal'));
        m.show();
    }

    function confirmDeleteHistory(id, nama) {
        document.getElementById('confirmBtn').onclick = function() {
            // BUG-QA-01 FIX: submit POST form+CSRF, bukan GET redirect
            document.getElementById('hapusHistoryId').value = id;
            document.getElementById('formHapusHistory').submit();
        };
        const m = new bootstrap.Modal(document.getElementById('confirmModal'));
        m.show();
    }

    function confirmResetHistory() {
        document.getElementById('confirmMsg').textContent = 'Reset semua riwayat perubahan? Seluruh catatan akan dihapus permanen dan tidak bisa dikembalikan.';
        document.getElementById('confirmBtn').onclick = function() {
            // Kirim via POST form + CSRF token (bukan GET)
            document.getElementById('formResetHistory').submit();
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
        document.getElementById('tipeFormId').value = data.id;
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
            // BUG-QA-01 FIX: submit POST form+CSRF, bukan GET redirect
            document.getElementById('hapusTipeId').value = id;
            document.getElementById('formHapusTipe').submit();
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
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal"
                    style="border-radius:8px;padding:8px 20px;font-size:13px;">Batal</button>
                <button type="button" id="confirmBtn"
                    style="background:#dc2626;color:#fff;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form tersembunyi untuk Reset History — POST + CSRF token -->
<form id="formResetHistory" method="POST" action="guru.php?action=reset_history" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_reset_history ?? '') ?>">
</form>

<!-- BUG-QA-01 FIX: Form-form tersembunyi hapus tunggal — POST + CSRF token -->
<form id="formHapusGuru" method="POST" action="guru.php?action=delete" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_delete_guru ?? '') ?>">
    <input type="hidden" name="del_id" id="hapusGuruId" value="">
</form>
<form id="formHapusHistory" method="POST" action="guru.php?action=delete_history" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_delete_history ?? '') ?>">
    <input type="hidden" name="del_id" id="hapusHistoryId" value="">
</form>
<form id="formHapusTipe" method="POST" action="guru.php?action=delete_tipe" style="display:none;" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_delete_tipe ?? '') ?>">
    <input type="hidden" name="del_id" id="hapusTipeId" value="">
</form>

<?php require_once 'includes/footer.php'; ?>