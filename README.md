# 🕌 Sistem Penilaian Kinerja GTK
## SD IT QURANI ADH-DHUHAA – Pangkalpinang

---

## 📋 Deskripsi

Aplikasi web sistem penilaian kinerja **Guru dan Tenaga Kependidikan (GTK)** berbasis PHP + MySQL.  
Dikonversi dari spreadsheet Excel menjadi sistem web yang terintegrasi, responsif, dan mudah dikelola.

---

## 🆕 Changelog Terbaru

### v1.2 — April 2026 (Bugfix & Dokumentasi Kode Lengkap)

#### 🐛 Bug yang Ditemukan & Diperbaiki

| # | File | Jenis Bug | Deskripsi | Status |
|---|------|-----------|-----------|--------|
| 1 | `guru.php` | **CSS class tidak terdefinisi** | Alert peringatan menggunakan class `alert-danger-custom` yang tidak ada di CSS `header.php`. Yang terdefinisi adalah `alert-error-custom` | ✅ Fixed |
| 2 | `guru.php` | **Double POST handler tanpa guard** | Dua blok `if (REQUEST_METHOD === POST)` berjalan berurutan tanpa guard. Saat form tipe disubmit, blok guru ikut ter-trigger dan menampilkan error "Nama wajib diisi" | ✅ Fixed |
| 3 | `cetak.php` | **`$subTotalsCustom` tidak pernah dihitung** | Variabel dipakai di template HTML tapi tidak diisi nilainya — berpotensi PHP Notice/undefined variable jika komponen custom aktif | ✅ Fixed |
| 4 | `dashboard.php` | **`$penilaianFinal` = `$totalPenilaian`** | Query identik karena tidak ada kolom `status`. "Draft" selalu 0. By-design tapi tidak terdokumentasi sehingga membingungkan | ✅ Documented |

#### 📝 Penambahan Komentar & Dokumentasi Kode

- ✅ Docblock `/** @param @return */` pada setiap fungsi PHP
- ✅ Section comment `// ─── ... ─` memisahkan blok logika utama
- ✅ Inline comment pada setiap baris kode yang memerlukan penjelasan
- ✅ Komentar arsitektur pada variabel/logika yang tidak intuitif
- ✅ Guard pada double POST handler diberi komentar alasan kebutuhan

---

### v1.1 — April 2026 (Bugfix Redirect Loop)
- Fix `ERR_TOO_MANY_REDIRECTS`: `session_write_close()` sebelum setiap `header('Location:')`

### v1.0 — April 2026 (Autocomplete & Dokumentasi Awal)
- `autocomplete="off"` di semua `<form>`
- Komentar awal semua file PHP
- `config.php` ditulis ulang dengan type-hint modern

---

## 🚀 Cara Instalasi

### 1. Persyaratan Sistem
| Komponen   | Versi Minimum |
|------------|--------------|
| PHP        | 7.4+ (disarankan 8.1+) |
| MySQL      | 5.7+ atau MariaDB 10.x |
| Web Server | Apache / Nginx / XAMPP / WAMP |

### 2. Setup Database
```sql
CREATE DATABASE adh_dhuhaa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql -u root -p adh_dhuhaa < adh_dhuhaa.sql
```
Atau via **phpMyAdmin**: buat database → tab Import → pilih `adh_dhuhaa.sql`.

### 3. Konfigurasi Database
Edit `includes/config.php`:
```php
define('DB_HOST', 'localhost');   // Host MySQL
define('DB_USER', 'root');        // Username MySQL
define('DB_PASS', '');            // Password MySQL
define('DB_NAME', 'adh_dhuhaa'); // Nama database
```

### 4. Lokasi File
```
XAMPP  → C:/xampp/htdocs/adh-dhuhaa/
WAMP   → C:/wamp/www/adh-dhuhaa/
Linux  → /var/www/html/adh-dhuhaa/
```

### 5. Akses
Buka: `http://localhost/adh-dhuhaa/`

---

## 🔐 Akun Default

| Username | Password   | Role           |
|----------|------------|----------------|
| `admin`  | `password` | Administrator  |
| `kepala` | `password` | Kepala Sekolah |

> ⚠️ Wajib ganti password setelah login pertama!

---

## 📁 Struktur File

```
adh-dhuhaa/
├── index.php           → Entry point: redirect ke login.php
├── login.php           → Autentikasi (session PHP + bcrypt)
├── logout.php          → Hancurkan session & redirect ke login
├── dashboard.php       → Dashboard: statistik & penilaian terbaru
├── guru.php            → CRUD guru + log riwayat + manajemen tipe
├── penilaian.php       → Form input penilaian kinerja per guru
├── komponen.php        → Manajemen indikator & poin penilaian
├── rekap.php           → Rekap semua penilaian + filter
├── ranking.php         → Ranking guru berdasarkan nilai kinerja
├── cetak.php           → Cetak raport PKG (print/PDF browser)
├── api_komponen.php    → Endpoint AJAX: komponen per tipe guru
├── adh_dhuhaa.sql      → Skema database & data awal
├── README.md           → Dokumentasi proyek (file ini)
└── includes/
    ├── config.php      → Koneksi DB, konstanta, helper functions
    ├── header.php      → Sidebar + navbar + Bootstrap 5 + CSS
    └── footer.php      → Penutup HTML + DataTables + JS helpers
```

---

## 📝 Dokumentasi Fungsi PHP

### `includes/config.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `isLoggedIn()` | — | `bool` | Cek apakah user sudah login via session |
| `requireLogin()` | — | `void` | Redirect ke `login.php` jika belum login |
| `getCurrentUser()` | — | `array\|null` | Ambil data user dari session |
| `sanitize($data)` | `string` | `string` | Trim + strip_tags + htmlspecialchars |
| `jsonResponse($data, $code)` | `mixed, int` | `never` | Kirim JSON response dan exit |
| `getTipeGuru(PDO $pdo)` | `PDO` | `array` | Ambil semua tipe dari `tipe_guru` (cached) |
| `getTipeLabel(PDO $pdo, $kode)` | `PDO, string` | `string` | Label tampil dari kode tipe |
| `isValidTipe(PDO $pdo, $kode)` | `PDO, string` | `bool` | Validasi kode tipe ke DB |

### `guru.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `catatHistory()` | `$pdo,$aksi,$guru_id,$data,$oleh,$ket` | `void` | Simpan log perubahan ke `guru_history` |

### `penilaian.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `getKomponen()` | `$pdo, $tipe` | `array` | Ambil semua item+kategori untuk tipe guru (JOIN) |

### `rekap.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `nilaiLabel()` | `float\|null $n` | `array` | Kembalikan `[label, warna_hex]` dari persentase |

### `ranking.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `predikat()` | `float\|null $n` | `array` | Kembalikan `[label, warna, dot_emoji]` |
| `medalEmoji()` | `int $rank` | `string\|null` | Emoji 🥇🥈🥉 untuk rank 1–3 |

### `cetak.php`

| Fungsi | Parameter | Return | Deskripsi |
|--------|-----------|--------|-----------|
| `getPredikat()` | `float $pct` | `array` | Kembalikan `[label, simbol_bintang]` |
| `tanggalIndonesia()` | `string $dateStr` | `string` | Format tanggal ke Bahasa Indonesia |

---

## ✨ Fitur Lengkap

### 🔐 Autentikasi
- Session PHP + redirect otomatis jika belum login
- Password bcrypt (`password_hash` / `password_verify`)
- `autocomplete="off"` mencegah browser menyimpan kredensial

### 👥 Manajemen Guru (`guru.php`)
- CRUD data guru dengan modal Bootstrap
- Tipe guru **dinamis** dari tabel `tipe_guru` (bukan hardcode)
- Log riwayat otomatis setiap aksi (tambah/edit/hapus)
- Filter & reset riwayat
- Manajemen tipe guru: tambah, edit, hapus (jika tidak dipakai)

### 📝 Penilaian Kinerja (`penilaian.php`)
- Komponen penilaian dimuat otomatis via AJAX sesuai tipe guru
- Input nilai skala 1–5 per poin dengan radio button
- Tambah kategori & poin kustom langsung dari form (tersimpan permanen ke DB)
- Hapus satu / pilih banyak / hapus semua
- Kalkulasi persentase dan predikat otomatis

### 📊 Komponen Penilaian (`komponen.php`)
- Tambah/edit/hapus indikator (kategori) per tipe guru
- Tambah/edit/hapus poin penilaian di bawah indikator
- Penomoran otomatis: `{urutan_indikator}.{urutan_poin}`
- Tab navigasi dinamis per tipe guru

### 📈 Rekap & Ranking
- **`rekap.php`**: nilai rata-rata semua guru, filter tipe & periode, progress bar
- **`ranking.php`**: podium top-3, filter tipe & predikat, progress bar
- Predikat + warna otomatis

### 🖨️ Cetak Raport (`cetak.php`)
- Kop surat resmi + tabel penilaian + predikat + TTD
- Mode cetak satu (`?id=N`) atau cetak semua (`?all=1&periode=...&tipe=...`)
- Export PDF via Print browser (Ctrl+P → Save as PDF)

---

## 🗄️ Skema Database

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Akun login (username, password bcrypt, role) |
| `tipe_guru` | Daftar tipe guru: kode, label, urutan |
| `guru` | Data guru & GTK |
| `guru_history` | Log riwayat perubahan data guru |
| `komponen_penilaian` | Indikator/kategori penilaian per tipe guru |
| `item` | Poin penilaian di bawah indikator |
| `penilaian` | Header penilaian per guru per periode |
| `detail_penilaian` | Nilai per item untuk setiap record penilaian |

---

## 📊 Skala Penilaian

| Nilai | Keterangan   |
|-------|-------------|
| 1     | Kurang      |
| 2     | Cukup       |
| 3     | Baik        |
| 4     | Sangat Baik |
| 5     | Istimewa    |

## 🏆 Predikat Akhir

| Persentase | Predikat            |
|------------|---------------------|
| ≥ 90%      | Sangat Baik Sekali  |
| 75–89%     | Sangat Baik         |
| 60–74%     | Baik                |
| 40–59%     | Cukup               |
| < 40%      | Kurang              |

---

## 🔒 Keamanan

| Fitur | Implementasi |
|-------|-------------|
| SQL Injection | PDO Prepared Statements di **semua** query |
| XSS | `htmlspecialchars()` pada semua output; `sanitize()` pada semua input |
| Autentikasi | Session PHP + `requireLogin()` di setiap halaman |
| Password | Bcrypt via `password_hash()` / `password_verify()` |
| Autocomplete | `autocomplete="off"` di semua `<form>` |
| Validasi tipe | `isValidTipe()` — tidak bergantung pada hardcode array |

---

## 🛠️ Teknologi

| Layer     | Teknologi |
|-----------|-----------|
| Backend   | PHP 7.4+ (PDO, Session) |
| Database  | MySQL / MariaDB |
| Frontend  | Bootstrap 5, DataTables 1.13 |
| Font      | Google Fonts (Playfair Display, DM Sans) |
| Icons     | SVG inline |
| Print/PDF | Browser Print (Ctrl+P) |

---

## 🔧 Panduan Pengembangan

### Menambah Tipe Guru Baru
1. Masuk menu **Data Guru → tab Tipe Guru → + Tambah Tipe**
2. Isi kode (misal `btq`), label (misal `Guru BTQ`), urutan
3. Tambah komponen penilaian di menu **Komponen Penilaian → tab tipe baru**

### Membedakan Draft vs Final
Saat ini semua penilaian dianggap final (tidak ada kolom `status`):
1. Tambah kolom: `ALTER TABLE penilaian ADD status ENUM('draft','final') DEFAULT 'draft';`
2. Update `$penilaianFinal` di `dashboard.php`: `WHERE status = 'final'`
3. Tambah tombol "Finalisasi" di `penilaian.php`

### Cache Tipe Guru
`getTipeGuru()` meng-cache hasil ke `$GLOBALS['_cache_tipe_guru']`.  
Setelah modifikasi tabel `tipe_guru`, cache sudah di-reset otomatis via:
```php
unset($GLOBALS['_cache_tipe_guru']);
```
Ini sudah diterapkan di semua handler CRUD tipe di `guru.php`.

### API Komponen (AJAX)
```
GET api_komponen.php?tipe={kode_tipe}
```
Mengembalikan JSON array item penilaian. Return `[]` jika tipe tidak valid.

---

## 📞 Informasi Sekolah

**SD IT QURANI ADH-DHUHAA**  
Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang  
Kota Pangkalpinang, Provinsi Kepulauan Bangka Belitung  
NPSN: `70002294` | Telp: `(0717) 9116753`  
Email: `sditquraniadduha@gmail.com`
