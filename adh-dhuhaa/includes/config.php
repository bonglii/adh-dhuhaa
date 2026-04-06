<?php
// Mulai session sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Security headers ────────────────────────────────────────────────────────
// Cegah embedding di iframe (clickjacking)
header('X-Frame-Options: SAMEORIGIN');
// Cegah MIME sniffing
header('X-Content-Type-Options: nosniff');
// Sembunyikan referrer saat navigasi ke domain lain
header('Referrer-Policy: strict-origin-when-cross-origin');
// Hapus X-Powered-By yang mengekspos versi PHP
header_remove('X-Powered-By');
// SARAN-01 FIX: Content Security Policy untuk mencegah XSS yang lolos dari sanitize()
// unsafe-inline diperlukan untuk Bootstrap inline styles & script blok PHP yang ada
// fonts.googleapis.com untuk Google Fonts yang dipakai di login & cetak
header("Content-Security-Policy: default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; " .
    "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net cdnjs.cloudflare.com; " .
    "font-src 'self' fonts.gstatic.com; " .
    "img-src 'self' data:; " .
    "connect-src 'self'; " .
    "frame-ancestors 'none'");

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adh_dhuhaa');

// Informasi Sekolah
define('NAMA_SEKOLAH', 'SD IT QURANI ADH-DHUHAA');
define('YAYASAN', 'YAYASAN ADH-DHUHAA PANGKALPINANG');
define('ALAMAT', 'Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang Kota Pangkalpinang');
define('TELP', '(0717) 9116753');
define('NPSN', '70002294');
define('EMAIL', 'sditquraniadduha@gmail.com');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Jangan tampilkan detail error ke client — bisa mengandung hostname/credentials
    // Log ke server error log untuk keperluan debugging admin
    error_log('DB Connection Error: ' . $e->getMessage());
    die(json_encode(['error' => 'Koneksi database gagal. Hubungi administrator.']));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        // Simpan session sebelum redirect agar data tidak hilang
        // (konsisten dengan pola session_write_close() di seluruh file)
        session_write_close();
        header('Location: login.php');
        exit;
    }
    // Session idle timeout: logout otomatis setelah 2 jam tidak aktif
    $idleLimit = 7200; // detik (2 jam)
    if (isset($_SESSION['_last_active']) && (time() - $_SESSION['_last_active']) > $idleLimit) {
        session_unset();
        session_destroy();
        session_write_close();
        header('Location: login.php?msg=' . urlencode('Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.'));
        exit;
    }
    $_SESSION['_last_active'] = time();
    // WARN-01 FIX: Jika user wajib ganti password, paksa redirect ke halaman ganti password.
    // Cek basename agar tidak infinite redirect pada ganti_password.php itu sendiri.
    $currentPage = basename($_SERVER['PHP_SELF']);
    if (!empty($_SESSION['user']['must_change_password']) && $currentPage !== 'ganti_password.php') {
        session_write_close();
        header('Location: ganti_password.php');
        exit;
    }
}

function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ─── Helper fungsi untuk tabel tipe_guru ─────────────────────────────────────
// Tiga fungsi ini menggantikan array hardcode ['guru_quran'=>'Guru Qur\'an',...]
// yang sebelumnya tersebar di banyak file PHP.

/**
 * getTipeGuru — Ambil semua tipe guru dari tabel tipe_guru.
 * Hasil di-cache di $GLOBALS agar tidak query berulang dalam satu request.
 *
 * @param  PDO   $pdo   Koneksi database
 * @return array        ['guru_quran' => 'Guru Qur\'an', 'guru_kelas' => 'Guru Kelas', ...]
 */
function getTipeGuru(PDO $pdo): array
{
    if (!isset($GLOBALS['_cache_tipe_guru'])) {
        try {
            // Coba query tabel tipe_guru (versi baru dengan tabel mandiri)
            $rows = $pdo->query("SELECT kode, label FROM tipe_guru ORDER BY urutan")->fetchAll();
            $result = [];
            foreach ($rows as $row) {
                $result[$row['kode']] = $row['label'];
            }
            // Jika tabel kosong atau belum ada data, gunakan fallback
            if (empty($result)) {
                throw new Exception('tipe_guru kosong');
            }
            $GLOBALS['_cache_tipe_guru'] = $result;
        } catch (Exception $e) {
            // Fallback: jika tabel tipe_guru belum ada di database,
            // gunakan data default agar aplikasi tetap berjalan normal
            $GLOBALS['_cache_tipe_guru'] = [
                'guru_quran' => "Guru Qur'an",
                'guru_kelas' => 'Guru Kelas',
                'mapel'      => 'Guru Mapel',
                'gtk'        => 'GTK/Staff',
            ];
        }
    }
    return $GLOBALS['_cache_tipe_guru'];
}

/**
 * getTipeLabel — Ambil label tampil dari kode tipe guru.
 * Fallback ke kode itu sendiri jika tidak ditemukan.
 *
 * @param  PDO    $pdo   Koneksi database
 * @param  string $kode  Contoh: 'guru_quran'
 * @return string        Contoh: 'Guru Qur\'an'
 */
function getTipeLabel(PDO $pdo, string $kode): string
{
    return getTipeGuru($pdo)[$kode] ?? $kode;
}

/**
 * isValidTipe — Validasi apakah kode tipe ada di tabel tipe_guru.
 * Menggantikan in_array($tipe, ['guru_quran','guru_kelas','mapel','gtk']).
 *
 * @param  PDO    $pdo   Koneksi database
 * @param  string $kode  Kode yang akan divalidasi
 * @return bool          true jika valid
 */
function isValidTipe(PDO $pdo, string $kode): bool
{
    return array_key_exists($kode, getTipeGuru($pdo));
}
