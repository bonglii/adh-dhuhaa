# 🕌 Sistem Penilaian Kinerja GTK
## SD IT QURANI ADH-DHUHAA – Pangkalpinang

---

## 📋 Deskripsi

Aplikasi web sistem penilaian kinerja **Guru dan Tenaga Kependidikan (GTK)** berbasis PHP + MySQL.
Dikonversi dari spreadsheet Excel menjadi sistem web yang terintegrasi, responsif, dan mudah dikelola.

---

## 🆕 Changelog

### v1.3 — April 2026 (Testing Menyeluruh & Bugfix)

#### 🐛 Bug Ditemukan & Diperbaiki

| # | File | Bug | Dampak | Status |
|---|------|-----|--------|--------|
| 1 | `guru.php` | `catatHistory()` dipanggil di dalam `beginTransaction()` — jika history gagal, INSERT guru ikut di-rollback | Data guru **tidak tersimpan** sama sekali | ✅ Fixed |
| 2 | `guru.php` | `SELECT id FROM tipe_guru` — kolom `id` tidak ada, harusnya `id_tipe_guru` | PDOException saat edit/cek duplikat tipe guru | ✅ Fixed |
| 3 | `guru.php` | JS `data.id` harusnya `data.id_guru` di `openEdit()` | Edit guru selalu INSERT baru bukan UPDATE | ✅ Fixed |
| 4 | `guru.php` | JS `data.id` harusnya `data.id_tipe_guru` di `openEditTipe()` | Edit tipe selalu INSERT baru bukan UPDATE | ✅ Fixed |
| 5 | `guru.php` | `match` (PHP 8.0+) dan arrow `fn()` (PHP 7.4+) | Seluruh file gagal di-parse di PHP < 7.4 | ✅ Fixed |
| 6 | `guru.php` | `$msg` diset tanpa redirect — toast hanya baca dari URL `?msg=` | Pesan error validasi tidak pernah tampil | ✅ Fixed |
| 7 | `item.php` | `$msg = null` menimpa error validasi POST | "Nama kosong", "sudah ada" tidak pernah tampil | ✅ Fixed |
| 8 | `penilaian.php` | `$msg` dari POST tidak ditampilkan di HTML | User tidak tahu ada error saat simpan | ✅ Fixed |
| 9 | `penilaian.php` | DELETE tanpa try-catch | Halaman crash jika DELETE gagal | ✅ Fixed |

#### ✅ Hasil Pengujian Fitur

| Modul | Fitur | Status |
|-------|-------|--------|
| Login/Logout | Autentikasi bcrypt, redirect, error message | ✅ |
| Dashboard | Statistik guru & penilaian, akses cepat | ✅ |
| Guru – CRUD | Tambah, edit, hapus, filter, statistik per tipe | ✅ |
| Guru – History | Riwayat aksi, filter, hapus baris, reset semua | ✅ |
| Tipe Guru – CRUD | Tambah, edit, hapus (jika tidak dipakai) | ✅ |
| Item (Bank Soal) | Tambah, edit, hapus, status penggunaan | ✅ |
| Custom Penilaian | Buat template TA+tipe, tambah indikator+item | ✅ |
| Penilaian – CRUD | Tambah, edit, hapus satu/terpilih/semua | ✅ |
| Penilaian – Filter | Filter TA, tipe, nama guru | ✅ |
| Rekap | Nilai rata-rata, filter periode & tipe | ✅ |
| Ranking | Ranking nilai, podium top-3, filter | ✅ |
| Cetak | Raport PKG satu/semua, print/PDF | ✅ |
| API AJAX | Komponen per tipe, item per komponen | ✅ |

---

### v1.2 — April 2026 (Bugfix & Dokumentasi)

| # | File | Bug | Status |
|---|------|-----|--------|
| 1 | `guru.php` | CSS class tidak terdefinisi | ✅ Fixed |
| 2 | `guru.php` | Double POST handler tanpa guard | ✅ Fixed |
| 3 | `cetak.php` | `$subTotalsCustom` tidak dihitung | ✅ Fixed |
| 4 | `dashboard.php` | `$penilaianFinal` = `$totalPenilaian` (by-design) | ✅ Documented |

### v1.1 — April 2026
- Fix `ERR_TOO_MANY_REDIRECTS`: `session_write_close()` sebelum setiap redirect

### v1.0 — April 2026
- Rilis awal, konversi dari Excel ke web

---

## 🚀 Cara Instalasi

### 1. Persyaratan Sistem

| Komponen   | Versi Minimum | Catatan |
|------------|--------------|---------|
| PHP        | 7.0+         | Disarankan 8.1+ |
| MySQL      | 5.7+         | MariaDB 10.x juga didukung |
| Web Server | Apache / Nginx / XAMPP / WAMP | |

### 2. Setup Database

```sql
CREATE DATABASE adh_dhuhaa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql -u root -p adh_dhuhaa < "adh_dhuhaa (4).sql"
```

Atau via **phpMyAdmin**: buat database → Import → pilih file SQL.

### 3. Konfigurasi

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adh_dhuhaa');
```

### 4. Lokasi File

```
XAMPP  → C:/xampp/htdocs/adh-dhuhaa/
WAMP   → C:/wamp/www/adh-dhuhaa/
Linux  → /var/www/html/adh-dhuhaa/
```

### 5. Akses

`http://localhost/adh-dhuhaa/`

---

## 🔐 Akun Default

| Username | Password   | Role           |
|----------|------------|----------------|
| `admin`  | `password` | Administrator  |
| `kepala` | `password` | Kepala Sekolah |

> ⚠️ Ganti password setelah login pertama!

---

## 📁 Struktur File

```
adh-dhuhaa/
├── index.php                → Entry point → redirect ke login.php
├── login.php                → Autentikasi bcrypt + session
├── logout.php               → Hancurkan session → redirect login
├── dashboard.php            → Statistik & penilaian terbaru
├── guru.php                 → CRUD guru + history + manajemen tipe
├── penilaian.php            → Input & kelola penilaian kinerja
├── item.php                 → Bank soal item penilaian (master)
├── custom_penilaian.php     → Template penilaian per TA & tipe guru
├── komponen.php             → Redirect ke custom_penilaian.php (deprecated)
├── rekap.php                → Rekap nilai semua guru
├── ranking.php              → Ranking guru berdasarkan nilai
├── cetak.php                → Cetak raport PKG (print/PDF)
├── api_custom_komponen.php  → AJAX: komponen & item per tipe/TA
├── api_komponen.php         → AJAX: item per tipe (legacy)
├── adh_dhuhaa (4).sql       → Skema & data awal database
├── README.md                → Dokumentasi (file ini)
└── includes/
    ├── config.php           → Koneksi DB + helper functions
    ├── header.php           → Sidebar + navbar + Bootstrap 5 + CSS
    └── footer.php           → Penutup HTML + DataTables + toast JS
```

---

## 📝 Dokumentasi Fungsi PHP

### `includes/config.php`

| Fungsi | Return | Deskripsi |
|--------|--------|-----------|
| `isLoggedIn()` | `bool` | Cek `$_SESSION['user_id']` |
| `requireLogin()` | `void` | Redirect login jika belum login |
| `getCurrentUser()` | `array\|null` | Ambil `$_SESSION['user']` |
| `sanitize($data)` | `string` | trim + strip_tags + htmlspecialchars |
| `jsonResponse($data, $code)` | `never` | JSON response + exit |
| `getTipeGuru(PDO)` | `array` | Ambil tipe dari DB, di-cache ke `$GLOBALS` |
| `getTipeLabel(PDO, $kode)` | `string` | Label dari kode tipe |
| `isValidTipe(PDO, $kode)` | `bool` | Validasi kode tipe ke DB |

### `guru.php`

| Fungsi | Deskripsi |
|--------|-----------|
| `catatHistory($pdo, $aksi, $guru_id, $data, $oleh, $ket)` | INSERT ke `guru_history`. Dipanggil dalam try-catch **terpisah** dari operasi utama agar kegagalan history tidak membatalkan INSERT/UPDATE/DELETE guru |

### `penilaian.php`

| Fungsi | Deskripsi |
|--------|-----------|
| `renderKomponenHtml($isiByInd, $editDetail)` | Render HTML form penilaian server-side (untuk mode edit) |

### `rekap.php`

| Fungsi | Deskripsi |
|--------|-----------|
| `nilaiLabel($n)` | `[label, warna_hex]` dari persentase |

### `ranking.php`

| Fungsi | Deskripsi |
|--------|-----------|
| `predikat($n)` | `[label, warna, dot_emoji]` dari nilai |
| `medalEmoji($rank)` | Emoji 🥇🥈🥉 untuk rank 1–3 |

### `cetak.php`

| Fungsi | Deskripsi |
|--------|-----------|
| `getPredikat($pct)` | `[label, simbol_bintang]` dari persentase |
| `tanggalIndonesia($dateStr)` | Format tanggal ke Bahasa Indonesia |

---

## 🗄️ Skema Database

| Tabel | PK | Deskripsi |
|-------|----|-----------|
| `users` | `id_users` | Akun login |
| `tipe_guru` | `id_tipe_guru` | Daftar tipe guru (kode, label, urutan) |
| `guru` | `id_guru` | Data guru & GTK, FK → `tipe_guru.kode` |
| `guru_history` | `id_guru_history` | Log riwayat perubahan guru |
| `item` | `id_item` | Bank soal poin penilaian |
| `komponen` | `id_komponen` | Template penilaian (TA + tipe guru) |
| `isi` | `id_isi` | Mapping komponen ↔ indikator ↔ item |
| `penilaian` | `id_penilaian` | Header penilaian per guru per periode |
| `hasil` | `id_hasil` | Nilai per item per penilaian |

### Alur Data

```
tipe_guru ──< guru ──< penilaian ──< hasil
                              │
komponen ──< isi ──< item ───┘ (via id_item)
    │
    └──< penilaian (via id_komponen)
```

---

## 📊 Skala Penilaian & Predikat

| Nilai | Keterangan       | | Persentase | Predikat           |
|-------|-----------------|---|------------|--------------------|
| 1     | Kurang          | | ≥ 90%      | Sangat Baik Sekali |
| 2     | Cukup           | | 75–89%     | Sangat Baik        |
| 3     | Baik            | | 60–74%     | Baik               |
| 4     | Sangat Baik     | | 40–59%     | Cukup              |
| 5     | Sangat Baik Sekali | | < 40%   | Kurang             |

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|-------------|
| SQL Injection | PDO Prepared Statements di semua query |
| XSS | `htmlspecialchars()` semua output; `sanitize()` semua input |
| Autentikasi | Session PHP + `requireLogin()` di setiap halaman |
| Password | Bcrypt via `password_hash()` / `password_verify()` |
| Autocomplete | `autocomplete="off"` di semua form |
| Validasi tipe | `isValidTipe()` query ke DB, tidak hardcode |
| Error handling | try-catch + redirect di semua operasi DB |

---

## 🛠️ Teknologi

| Layer     | Teknologi |
|-----------|-----------|
| Backend   | PHP 7.0+ (PDO, Session) |
| Database  | MySQL 5.7+ / MariaDB |
| Frontend  | Bootstrap 5, DataTables 1.13 |
| Font      | Google Fonts (Playfair Display, DM Sans) |
| Print/PDF | Browser Print (Ctrl+P → Save as PDF) |

---

## 🔧 Panduan Pengembangan

### Alur Lengkap Penilaian Baru

```
1. [Tambah Point Penilaian]     → Tambah item ke bank soal
2. [Buat Pertanyaan Penilaian]  → Buat template TA + Tipe + item per indikator
3. [Penilaian Kinerja]          → Pilih guru → TA → isi nilai → simpan
4. [Rekap / Ranking]            → Lihat hasil
5. [Cetak]                      → Export PDF
```

### Menambah Tipe Guru Baru

1. **Data Guru → tab Tipe Guru → + Tambah Tipe**
2. Isi kode (contoh: `btq`), label (`Guru BTQ`), urutan
3. Tambah item di **Tambah Point Penilaian**
4. Buat template di **Buat Pertanyaan Penilaian**

### Prinsip Error Handling

Semua operasi DB mengikuti pola:

```php
try {
    // operasi DB
    session_write_close();
    header('Location: halaman.php?msg=' . urlencode('Berhasil!'));
    exit;
} catch (PDOException $e) {
    error_log('[file.php] Error: ' . $e->getMessage());
    session_write_close();
    header('Location: halaman.php?msg=' . urlencode('⚠️ Gagal: ' . $e->getMessage()));
    exit;
}
```

Toast notification di `footer.php` membaca `?msg=` dari URL dan menampilkan pesan sukses/error otomatis.

### Cache Tipe Guru

`getTipeGuru()` cache ke `$GLOBALS['_cache_tipe_guru']`. Reset otomatis setelah CRUD tipe:
```php
unset($GLOBALS['_cache_tipe_guru']);
```

### Konfigurasi Production

Matikan debug mode di `guru.php`:
```php
// Komentari baris ini di production:
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// ini_set('log_errors', '1');  // ← ini boleh tetap aktif untuk log server
```

---

## 📞 Informasi Sekolah

**SD IT QURANI ADH-DHUHAA**
Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang
Kota Pangkalpinang, Provinsi Kepulauan Bangka Belitung
NPSN: `70002294` | Telp: `(0717) 9116753`
Email: `sditquraniadduha@gmail.com`
