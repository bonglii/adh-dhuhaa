<?php
/**
 * includes/config.php — Konfigurasi Inti Aplikasi
 *
 * File ini di-require di hampir semua halaman PHP. Berisi:
 *  - Session bootstrapping
 *  - Konfigurasi koneksi database (PDO)
 *  - Konstanta informasi sekolah (tampil di kop raport)
 *  - Helper functions: autentikasi, sanitasi, JSON response
 *  - Helper tipe guru dinamis (cache di $GLOBALS)
 *  - Konstanta INDIKATOR_LIST (daftar indikator penilaian)
 */

// ─── Bootstrap session ───────────────────────────────────────────────────────
// Mulai session sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Konfigurasi Database ────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adh_dhuhaa');

// ─── Informasi Sekolah (dipakai di kop raport cetak.php) ─────────────────────
define('NAMA_SEKOLAH', 'SD IT QURANI ADH-DHUHAA');
define('YAYASAN', 'YAYASAN ADH-DHUHAA PANGKALPINANG');
define('ALAMAT', 'Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang Kota Pangkalpinang');
define('TELP', '(0717) 9116753');
define('NPSN', '70002294');
define('EMAIL', 'sditquraniadduha@gmail.com');

// ─── Inisialisasi PDO ────────────────────────────────────────────────────────
// ERRMODE_EXCEPTION: lempar PDOException saat query gagal, bukan silent fail
// FETCH_ASSOC: default hasil fetch pakai array asosiatif
// EMULATE_PREPARES=false: pakai real prepared statement server-side (lebih aman)
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
    die(json_encode(['error' => 'Koneksi database gagal: ' . $e->getMessage()]));
}

// ─── Helper: Autentikasi & Session ───────────────────────────────────────────

/**
 * isLoggedIn — Cek apakah user sudah login.
 *
 * @return bool true jika $_SESSION['user_id'] terisi
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * requireLogin — Redirect ke login.php jika user belum login.
 * Dipanggil di awal setiap halaman terproteksi.
 *
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * getCurrentUser — Ambil data user yang sedang login.
 *
 * @return array|null Array user data dari session, atau null jika belum login
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

// ─── Helper: Sanitasi & Response ─────────────────────────────────────────────

/**
 * sanitize — Bersihkan input user dari potensi XSS.
 * Urutan: trim whitespace → strip HTML tag → encode entitas HTML.
 *
 * @param  string $data  Input dari $_GET / $_POST
 * @return string        String yang aman untuk disimpan / ditampilkan
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * jsonResponse — Kirim respons JSON lalu hentikan eksekusi.
 * Dipakai oleh endpoint api_*.php.
 *
 * @param  mixed $data  Data yang akan di-encode JSON
 * @param  int   $code  HTTP status code (default 200)
 * @return never        Tidak pernah return (exit)
 */
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

/**
 * INDIKATOR_LIST — Daftar indikator penilaian yang berlaku untuk semua tipe guru.
 * Tidak lagi disimpan di database; dikelola langsung di sini.
 * Tambah atau hapus nama indikator di sini jika diperlukan.
 */
define('INDIKATOR_LIST', [
    'Disiplin',
    'Pelaksanaan Pembelajaran',
    'Kerjasama',
]);
