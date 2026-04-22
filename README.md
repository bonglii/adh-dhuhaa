# 🕌 Sistem Penilaian Kinerja Guru & Tenaga Kependidikan

**SD IT QURANI ADH-DHUHAA** – Pangkalpinang, Kepulauan Bangka Belitung

Aplikasi web berbasis PHP + MySQL untuk mengelola penilaian kinerja guru dan tenaga kependidikan (GTK) sekolah. Menggantikan sistem lama berbasis spreadsheet Excel dengan antarmuka terpusat yang multi-user, rapi, dan bisa dicetak langsung sebagai raport.

---

## 📑 Daftar Isi

1. [Fitur Utama](#-fitur-utama)
2. [Teknologi](#-teknologi)
3. [Instalasi](#-instalasi)
4. [Konfigurasi](#-konfigurasi)
5. [Panduan Pengguna](#-panduan-pengguna)
6. [Arsitektur Aplikasi](#-arsitektur-aplikasi)
7. [Skema Database](#-skema-database)
8. [Rumus Perhitungan Nilai](#-rumus-perhitungan-nilai)
9. [API Endpoints](#-api-endpoints)
10. [Keamanan](#-keamanan)
11. [Dokumentasi Fungsi PHP](#-dokumentasi-fungsi-php)
12. [Troubleshooting](#-troubleshooting)
13. [Changelog](#-changelog)
14. [Informasi Sekolah](#-informasi-sekolah)

---

## ✨ Fitur Utama

### 🔐 Autentikasi & Manajemen Session

- Login dengan hash **bcrypt** (`password_hash` / `password_verify`)
- Session PHP dengan `requireLogin()` di setiap halaman terproteksi
- Logout menghancurkan seluruh session data

### 👥 Manajemen Guru & GTK

- CRUD lengkap data guru (nama, NRG, TMT, jabatan, status kepegawaian, tipe)
- Filter client-side per tipe (chip instan tanpa reload)
- **Riwayat Perubahan**: log semua aksi tambah/edit/hapus ke tabel `guru_history` beserta catatan sebelum-sesudah
- **Manajemen Tipe Guru** dinamis: tambah/edit/hapus tipe baru tanpa menyentuh kode

### 🏷️ Bank Poin Penilaian (Item Master)

- Tambah/edit/hapus item penilaian (max 255 karakter)
- Status pemakaian: menampilkan tahun ajaran mana saja item dipakai
- Cegah duplikat (unique nama item) dan cegah hapus item yang sudah dipakai

### 📋 Buat Pertanyaan Penilaian (Custom)

- Buat skema penilaian per kombinasi **Tahun Ajaran + Tipe Guru**
- Tambah indikator (dari daftar `INDIKATOR_LIST`) + pilih item dari bank
- Accordion per TA, TA terbaru auto-expand, TA lama collapsed
- Progress bar: berapa guru sudah dinilai dari total guru dengan tipe ini

### ✍️ Penilaian Kinerja

- Alur 3 langkah: pilih Tipe Guru → Tahun Ajaran → Nama Guru
- Form penilaian auto-load via AJAX sesuai skema yang dipilih
- **Progress panel sticky**: hitung nilai live saat user mengisi radio button
- **Tombol "Set semua"** per indikator untuk percepat pengisian
- Deteksi duplikat (guru + TA sama) dengan warning non-blocking
- Prefill otomatis dari halaman Rekap (bagi guru yang belum dinilai di TA latest)
- Bulk action: hapus satu / hapus terpilih (checkbox) / hapus semua (sesuai filter)

### 📊 Rekap

- Tampilan accordion per Tahun Ajaran, TA terbaru otomatis terbuka
- Nilai rata-rata dihitung **per indikator** dulu, baru rata-rata antar indikator (bukan flat sum)
- Section khusus "Belum dinilai di TA latest" beserta tombol cepat "Nilai"
- Search nama guru lintas semua TA
- Cetak langsung per TA atau cetak semua

### 🏆 Ranking

- Ranking per Tahun Ajaran (tab), TA terbaru aktif default
- **Podium Top 3** dengan medali emas/perak/perunggu
- Filter tipe guru & filter predikat, podium ikut tersesuai filter
- Tombol cetak raport penilaian terakhir setiap guru

### 🖨️ Cetak Raport PKG

- Cetak satu penilaian (`?id=`) atau cetak semua (`?all=1`) dengan filter TA dan tipe
- Format raport lengkap: kop sekolah, identitas guru, tabel nilai per indikator, total, predikat bintang, catatan, area tanda tangan
- Menggunakan **Browser Print** (Ctrl+P → Save as PDF), tidak perlu library PDF
- Satu halaman per penilaian saat cetak semua (dengan `page-break-before: always`)

---

## 🛠️ Teknologi

| Layer          | Teknologi / Library                                  | Versi                  |
| -------------- | ---------------------------------------------------- | ---------------------- |
| Runtime        | PHP (PDO, Session)                                   | 7.4+ (disarankan 8.1+) |
| Database       | MySQL / MariaDB                                      | 5.7+ / 10.x            |
| Web Server     | Apache / Nginx (XAMPP / Laragon / WAMP / LAMP)       | —                      |
| CSS Framework  | Bootstrap                                            | 5.3.0 (CDN)            |
| Table Plugin   | DataTables                                           | 1.13.6 (CDN)           |
| JS Library     | jQuery                                               | 3.7.0 (CDN)            |
| Font           | Google Fonts: Playfair Display, DM Sans              | —                      |
| AJAX           | Native `fetch()` API                                 | —                      |
| Cetak / PDF    | Native browser print dialog (`window.print()`)       | —                      |

Tidak ada dependency yang perlu di-install via Composer atau npm — semua library frontend di-load via CDN.

---

## 🚀 Instalasi

### Persyaratan Sistem

| Komponen   | Minimum   | Disarankan | Catatan                             |
| ---------- | --------- | ---------- | ----------------------------------- |
| PHP        | 7.4       | 8.1+       | Butuh PDO extension + mysqli aktif  |
| MySQL      | 5.7       | 8.0+       | MariaDB 10.x juga OK                |
| Web Server | Apache 2  | Apache 2.4 | atau Nginx, XAMPP, Laragon, WAMP    |
| RAM        | 512 MB    | 2 GB+      |                                     |
| Disk       | 100 MB    | 500 MB     |                                     |

### Langkah Instalasi

**1. Ekstrak proyek**

Letakkan folder `adh-dhuhaa/` di lokasi web root:

```
XAMPP   → C:\xampp\htdocs\adh-dhuhaa\
Laragon → C:\laragon\www\adh-dhuhaa\
WAMP    → C:\wamp64\www\adh-dhuhaa\
Linux   → /var/www/html/adh-dhuhaa/
macOS   → /Library/WebServer/Documents/adh-dhuhaa/
```

**2. Buat database**

Via terminal MySQL:

```sql
CREATE DATABASE adh_dhuhaa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**3. Import skema**

Via terminal:

```bash
mysql -u root -p adh_dhuhaa < adh_dhuhaa.sql
```

Atau via **phpMyAdmin**:
1. Buka `http://localhost/phpmyadmin`
2. Klik database `adh_dhuhaa`
3. Tab **Import** → pilih file `adh_dhuhaa.sql` → klik **Kirim**

**4. Edit konfigurasi database**

Buka file `includes/config.php` lalu sesuaikan:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // ganti sesuai user MySQL kamu
define('DB_PASS', '');          // isi kalau MySQL pakai password
define('DB_NAME', 'adh_dhuhaa');
```

**5. Nyalakan Apache & MySQL**

Dari XAMPP/Laragon Control Panel, pastikan lampu hijau pada Apache dan MySQL.

**6. Akses aplikasi**

Buka browser ke: `http://localhost/adh-dhuhaa/`

### Akun Default

| Username | Password   | Keterangan          |
| -------- | ---------- | ------------------- |
| `admin`  | `password` | Administrator penuh |
| `kepala` | `password` | Kepala Sekolah      |

> ⚠️ **WAJIB ganti password setelah login pertama!** Edit hash di tabel `users` menggunakan `password_hash('passwordBaru', PASSWORD_BCRYPT)`.

---

## ⚙️ Konfigurasi

Seluruh konfigurasi ada di `includes/config.php`:

### Koneksi Database

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adh_dhuhaa');
```

### Informasi Sekolah (tampil di kop surat raport)

```php
define('NAMA_SEKOLAH', 'SD IT QURANI ADH-DHUHAA');
define('YAYASAN',      'YAYASAN ADH-DHUHAA PANGKALPINANG');
define('ALAMAT',       'Jl. Melati I No. 257 ...');
define('TELP',         '(0717) 9116753');
define('NPSN',         '70002294');
define('EMAIL',        'sditquraniadduha@gmail.com');
```

### Daftar Indikator Penilaian

Disimpan sebagai konstanta di `config.php` (bukan di tabel database):

```php
define('INDIKATOR_LIST', [
    'Disiplin',
    'Pelaksanaan Pembelajaran',
    'Kerjasama',
]);
```

Untuk menambah/mengubah indikator, edit array ini lalu buat ulang skema di **Buat Pertanyaan Penilaian**.

### Mode Production

File `guru.php` di baris awal memiliki debug flag yang aktif:

```php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
```

Untuk production: **komentari dua baris pertama** (biarkan `log_errors` aktif agar error tetap tercatat di server log):

```php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
ini_set('log_errors', '1');
```

---

## 📖 Panduan Pengguna

### Alur Penilaian End-to-End (Pertama Kali Setup)

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. DATA GURU                                                    │
│    Tambahkan semua guru & GTK, tentukan Tipe (Qur'an / Kelas /  │
│    Mapel / GTK atau tipe custom)                                │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 2. TAMBAH POIN PENILAIAN (Bank Item)                            │
│    Tambahkan butir-butir penilaian yang akan dipakai — misal:   │
│    "Datang tepat waktu", "Menyusun RPP", "Komunikasi dengan     │
│    orang tua", dll.                                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 3. BUAT PERTANYAAN PENILAIAN                                    │
│    Untuk setiap kombinasi (Tahun Ajaran + Tipe Guru), pilih     │
│    indikator + item yang berlaku. Satu skema = satu template    │
│    penilaian.                                                   │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 4. PENILAIAN KINERJA                                            │
│    Pilih Tipe → TA → Guru, isi radio 1–5 tiap item, simpan.     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ 5. REKAP / RANKING / CETAK                                      │
│    Lihat hasil, urutkan, cetak raport PDF.                      │
└─────────────────────────────────────────────────────────────────┘
```

### Menambah Tipe Guru Baru (misalnya "Guru BTQ")

1. **Data Guru** → tab **Tipe Guru** → **+ Tambah Tipe**
2. Isi kode (misal: `btq`), label (`Guru BTQ`), urutan otomatis
3. Masuk **Data Guru** lagi → **+ Tambah Guru**, pilih tipe `Guru BTQ`
4. **Tambah Poin Penilaian** — tambah item yang relevan untuk BTQ
5. **Buat Pertanyaan Penilaian** — buat skema TA 2025/2026 + tipe Guru BTQ, pilih indikator dan item
6. Lanjut ke **Penilaian Kinerja**, guru BTQ sudah muncul di dropdown

### Workflow Penilaian Rutin (Bulanan/Semester)

1. Kepala Sekolah / Operator login
2. Buka **Rekap** — lihat section "Belum dinilai di TA [latest]"
3. Klik tombol **"Nilai"** pada guru → langsung masuk form dengan tipe & guru terisi otomatis
4. Pilih tahun ajaran → form muncul → isi radio button
5. **Simpan** → toast success muncul, kembali ke daftar
6. Setelah semua guru dinilai, buka **Ranking** untuk lihat urutan
7. **Cetak Semua** dari Rekap untuk cetak raport semua guru dalam satu PDF

---

## 🏛️ Arsitektur Aplikasi

### Struktur Folder

```
adh-dhuhaa/
├── index.php                   # Entry point → redirect ke login
├── login.php                   # Form login + autentikasi bcrypt
├── logout.php                  # Destroy session
├── dashboard.php               # Statistik ringkas & penilaian terbaru
├── guru.php                    # CRUD guru + tab Riwayat + tab Tipe Guru
├── item.php                    # Bank poin penilaian
├── custom_penilaian.php        # Template penilaian per TA + tipe
├── komponen.php                # Redirect deprecated → custom_penilaian
├── penilaian.php               # Form input penilaian kinerja
├── rekap.php                   # Rekap nilai accordion per TA
├── ranking.php                 # Ranking guru per TA + podium
├── cetak.php                   # Cetak raport PKG
├── api_custom_komponen.php     # AJAX: list komponen + isi
├── api_komponen.php            # AJAX: item per tipe (legacy)
├── adh_dhuhaa.sql              # Skema & data awal
├── README.md                   # File ini
└── includes/
    ├── config.php              # Koneksi DB + helper functions + konstanta
    ├── header.php              # Sidebar + navbar + Bootstrap CSS + CSS global
    └── footer.php              # Penutup HTML + DataTables JS + toast
```

### Pola MVC Sederhana (Monolithic)

Aplikasi mengikuti pola **procedural PHP** dengan pemisahan ringan:

- **Controller-like (bagian atas setiap file)**: logika PHP untuk handle GET/POST, query database, redirect dengan `?msg=`
- **View (bagian bawah setiap file)**: HTML template dengan `<?= ?>` untuk escape output
- **Shared**: `includes/config.php` untuk fungsi bersama, `includes/header.php` dan `includes/footer.php` untuk layout konsisten

Setiap halaman PHP self-contained — tidak ada router, tidak ada framework. Ini disengaja agar mudah di-edit oleh operator sekolah yang tidak terlalu familiar dengan arsitektur modern.

### Flow Toast Notification

Sistem notifikasi menggunakan pola **PRG (Post/Redirect/Get)**:

```
POST operation → database update → session_write_close()
              → header('Location: page.php?msg=' . urlencode('Berhasil!'))
              → exit
              ↓
GET page.php?msg=Berhasil! → footer.php JavaScript auto-detect
              → showToast('Berhasil!', 'success')
              → bersihkan ?msg= dari URL via history.replaceState
```

Pesan yang mengandung kata "gagal", "error", "tidak bisa" → toast merah.  
Pesan yang mengandung "tidak ada", "sudah ada", "⚠" → toast kuning.  
Selain itu → toast hijau.

---

## 🗄️ Skema Database

### Daftar Tabel

| Tabel          | Primary Key                                   | Jumlah Kolom | Fungsi                                            |
| -------------- | --------------------------------------------- | ------------ | ------------------------------------------------- |
| `users`        | `id_users`                                    | 6            | Akun login (username, password hash, role)        |
| `tipe_guru`    | `id_tipe_guru` (UNIQUE: `kode`)               | 4            | Daftar tipe guru dinamis (kode, label, urutan)    |
| `guru`         | `id_guru`                                     | 9            | Data guru & GTK, FK `tipe` → `tipe_guru.kode`     |
| `guru_history` | `id_guru_history`                             | 12           | Log riwayat CRUD guru (aksi, data, oleh, waktu)   |
| `item`         | `id_item`                                     | 3            | Bank poin penilaian                               |
| `komponen`     | `id_komponen`                                 | 3            | Skema penilaian (TA + tipe guru)                  |
| `isi`          | Composite: `id_komponen` + `id_item`          | 5            | Mapping: skema ↔ indikator ↔ item                 |
| `penilaian`    | `id_penilaian`                                | 10           | Header penilaian (guru, skema, tanggal, penilai)  |
| `hasil`        | Composite: `id_penilaian` + `id_item`         | 3            | Nilai 1–5 per item per penilaian                  |

> **Catatan:** Tabel `isi` dan `hasil` menggunakan **composite key** (dua kolom jadi kunci), bukan auto-increment. Hapus/insert harus memperhatikan kombinasi kedua kolom.

### Relasi Antar Tabel

```
                ┌───────────┐
                │ tipe_guru │
                │  kode PK  │
                └─────┬─────┘
                      │  1:N
                      ▼
┌────────────┐   ┌─────────┐   ┌──────────────┐
│guru_history│◀──│  guru   │──▶│  penilaian   │
│   oleh     │   │id_guru  │   │id_penilaian  │
└────────────┘   └─────────┘   └──────┬───────┘
                                       │
                                       │ 1:N
                                       ▼
                              ┌──────────────────┐
                              │      hasil       │
                              │  id_penilaian +  │
                              │     id_item      │
                              └────────┬─────────┘
                                       │ N:1
                                       ▼
                              ┌─────────┐
                              │  item   │◀────┐
                              │id_item  │     │
                              └─────────┘     │  N:1
                                              │
                                        ┌─────┴──┐   ┌──────────┐
                                        │  isi   │──▶│ komponen │
                                        │(id_kom+│   │id_komponen│
                                        │id_item)│   └──────────┘
                                        └────────┘
```

### Detail Kolom Tabel Utama

**`guru`**
| Kolom                | Tipe         | Keterangan                             |
| -------------------- | ------------ | -------------------------------------- |
| `id_guru`            | INT AUTO     | Primary Key                            |
| `nama`               | VARCHAR(100) | Nama lengkap                           |
| `nrg`                | VARCHAR(50)  | Nomor Registrasi Guru (opsional)       |
| `tmt_guru`           | DATE         | Tanggal Mulai Tugas                    |
| `jabatan`            | VARCHAR(100) | Jabatan fungsional                     |
| `status_kepegawaian` | VARCHAR(100) | Status (PNS, Honor, Yayasan, dll.)     |
| `tipe`               | VARCHAR(20)  | FK → `tipe_guru.kode`                  |
| `created_at`         | TIMESTAMP    | Auto fill saat INSERT                  |
| `updated_at`         | TIMESTAMP    | Auto update saat UPDATE                |

**`penilaian`**
| Kolom                | Tipe         | Keterangan                                |
| -------------------- | ------------ | ----------------------------------------- |
| `id_penilaian`       | INT AUTO     | Primary Key                               |
| `id_guru`            | INT          | FK → `guru`                               |
| `id_komponen`        | INT          | FK → `komponen` (skema yang dipakai)      |
| `periode`            | VARCHAR      | TA snapshot (di-copy dari `komponen`)     |
| `tanggal_penilaian`  | DATE         | Tanggal pelaksanaan penilaian             |
| `penilai`            | VARCHAR(100) | Nama penilai (default: Hasyim Ashari)     |
| `jabatan_penilai`    | VARCHAR(100) | Jabatan (default: Kepala Sekolah)         |
| `catatan`            | TEXT         | Catatan/rekomendasi opsional              |
| `created_at`         | TIMESTAMP    | Auto fill                                 |
| `updated_at`         | TIMESTAMP    | Auto update                               |

**`hasil`** (composite PK: `id_penilaian` + `id_item`)
| Kolom          | Tipe | Keterangan            |
| -------------- | ---- | --------------------- |
| `id_penilaian` | INT  | FK → `penilaian`      |
| `id_item`      | INT  | FK → `item`           |
| `nilai`        | INT  | Rentang 1–5           |

---

## 🧮 Rumus Perhitungan Nilai

### Skala Input (per Item)

| Nilai | Label Kualitatif     |
| ----- | -------------------- |
| `1`   | Kurang               |
| `2`   | Cukup                |
| `3`   | Baik                 |
| `4`   | Sangat Baik          |
| `5`   | Sangat Baik Sekali   |

### Formula Nilai Akhir

Nilai akhir **bukan** rata-rata flat dari semua item, melainkan:

```
Langkah 1: Untuk setiap indikator, hitung persentase per indikator
           pct_ind = (SUM(nilai_item_ind) / (COUNT(item_ind) × 5)) × 100

Langkah 2: Nilai akhir = rata-rata antar indikator
           nilai_akhir = AVG(pct_ind_1, pct_ind_2, ...)
```

**Alasan**: agar setiap indikator memiliki bobot sama tanpa tergantung jumlah item. Kalau indikator "Disiplin" punya 3 item dan "Kerjasama" punya 10 item, tanpa formula ini "Kerjasama" akan mendominasi nilai akhir.

**Contoh perhitungan**:

```
Indikator Disiplin (3 item):       nilai = 5, 5, 4 → (14/15)×100 = 93.33%
Indikator Pembelajaran (5 item):   nilai = 4, 4, 5, 4, 3 → (20/25)×100 = 80.00%
Indikator Kerjasama (2 item):      nilai = 5, 4 → (9/10)×100 = 90.00%

Nilai Akhir = (93.33 + 80.00 + 90.00) / 3 = 87.78 → "Sangat Baik"
```

### Predikat dari Nilai Akhir

| Persentase   | Predikat              | Bintang    |
| ------------ | --------------------- | ---------- |
| ≥ 90         | Sangat Baik Sekali    | ⭐⭐⭐⭐⭐ |
| 75–89        | Sangat Baik           | ⭐⭐⭐⭐   |
| 60–74        | Baik                  | ⭐⭐⭐     |
| 40–59        | Cukup                 | ⭐⭐       |
| < 40         | Kurang                | ⭐         |

---

## 🔌 API Endpoints

Dua endpoint internal untuk AJAX dari form penilaian dan custom_penilaian:

### `GET api_komponen.php?tipe={kode_tipe}`

Ambil daftar item penilaian untuk tipe guru tertentu (legacy, dipakai minimal).

**Response** (JSON array):
```json
[
  {"id_item": 1, "kategori": "Disiplin", "urutan_kategori": 1, "nama_item": "Datang tepat waktu"}
]
```

### `GET api_custom_komponen.php?tipe={kode_tipe}`

List komponen (skema TA) untuk satu tipe guru.

**Response**:
```json
[
  {"id_komponen": 5, "ta_komponen": "2024/2025"},
  {"id_komponen": 3, "ta_komponen": "2023/2024"}
]
```

### `GET api_custom_komponen.php?id_komponen={id}`

List isi (indikator + item) untuk satu komponen.

**Response**:
```json
[
  {
    "id_komponen": 5,
    "nama_indikator": "Disiplin",
    "urutan_isi": 1,
    "id_item": 12,
    "nomor_item": "1.1",
    "nama_item": "Datang tepat waktu"
  }
]
```

Semua endpoint memerlukan session login (`requireLogin()`). Respons `[]` jika parameter tidak valid atau tipe tidak terdaftar di tabel `tipe_guru`.

---

## 🔒 Keamanan

| Ancaman             | Mitigasi                                                       |
| ------------------- | -------------------------------------------------------------- |
| SQL Injection       | **Semua query** pakai PDO prepared statement (placeholder `?`) |
| XSS (stored)        | `htmlspecialchars()` di semua output `<?= $var ?>`             |
| XSS (reflected)     | Escape `?msg=` di footer.php `showToast()` sejak v1.4          |
| CSRF                | Aksi destruktif (delete_all) hanya via POST, tidak via GET     |
| Session hijacking   | `session_start()` default, HTTPS di production dianjurkan      |
| Password bruteforce | Bcrypt hashing (~100ms per verify, cukup tahan bruteforce)     |
| Input validation    | `sanitize()` + `isValidTipe()` untuk semua input user          |
| Autocomplete        | `autocomplete="off"` di form sensitif                          |
| Error exposure      | `display_errors = 0` di file API (agar JSON tidak korup)       |

### Rekomendasi Tambahan untuk Production

1. **Aktifkan HTTPS** — install sertifikat SSL (Let's Encrypt gratis)
2. **Buat user MySQL khusus** dengan privilege terbatas — jangan pakai `root`
3. **Matikan `display_errors`** di `php.ini` production
4. **Backup database rutin** — cron mysqldump harian
5. **Batasi akses `includes/config.php`** via `.htaccess`:
   ```apache
   <Files "config.php">
       Order Allow,Deny
       Deny from all
   </Files>
   ```

---

## 📚 Dokumentasi Fungsi PHP

### `includes/config.php`

| Fungsi                            | Return         | Deskripsi                                             |
| --------------------------------- | -------------- | ----------------------------------------------------- |
| `isLoggedIn()`                    | `bool`         | Cek keberadaan `$_SESSION['user_id']`                 |
| `requireLogin()`                  | `void`         | Redirect ke login.php jika belum login                |
| `getCurrentUser()`                | `array\|null`  | Ambil data user dari `$_SESSION['user']`              |
| `sanitize($data)`                 | `string`       | `trim` + `strip_tags` + `htmlspecialchars`            |
| `jsonResponse($data, $code=200)`  | `never`        | Kirim respons JSON + `exit`                           |
| `getTipeGuru(PDO $pdo)`           | `array`        | Ambil semua tipe guru dari DB, cache di `$GLOBALS`    |
| `getTipeLabel(PDO, string $kode)` | `string`       | Label tampil dari kode tipe                           |
| `isValidTipe(PDO, string $kode)`  | `bool`         | Validasi kode tipe ada di tabel `tipe_guru`           |

### `guru.php`

| Fungsi                                                          | Deskripsi                                             |
| --------------------------------------------------------------- | ----------------------------------------------------- |
| `catatHistory($pdo, $aksi, $guru_id, $data, $oleh, $ket='')`    | INSERT ke `guru_history`. Dipanggil di try-catch terpisah agar kegagalan log tidak membatalkan operasi utama |

### `penilaian.php`

| Fungsi                                               | Deskripsi                                              |
| ---------------------------------------------------- | ------------------------------------------------------ |
| `renderKomponenHtml($isiByInd, $editDetail=[])`      | Render HTML form penilaian server-side (mode edit)     |

### `rekap.php`

| Fungsi              | Return               | Deskripsi                              |
| ------------------- | -------------------- | -------------------------------------- |
| `nilaiLabel($n)`    | `[label, warna_hex]` | Predikat & warna dari persentase       |

### `ranking.php`

| Fungsi              | Return                   | Deskripsi                          |
| ------------------- | ------------------------ | ---------------------------------- |
| `predikat($n)`      | `[label, warna_hex]`     | Predikat + warna dari nilai        |
| `medalEmoji($rank)` | `string\|null`           | Emoji 🥇🥈🥉 untuk rank 1/2/3      |

### `cetak.php`

| Fungsi                       | Return                     | Deskripsi                                   |
| ---------------------------- | -------------------------- | ------------------------------------------- |
| `getPredikat($pct)`          | `[label, simbol_bintang]`  | Predikat dengan bintang dari persentase     |
| `tanggalIndonesia($dateStr)` | `string`                   | Format tanggal: `23 April 2026`             |

---

## 🩹 Troubleshooting

### "Parse error: syntax error..." di browser

Biasanya karena file PHP tidak lengkap ter-upload atau ter-edit. Periksa baris yang disebutkan di pesan error. Kalau pesan menyebut `unexpected 'endforeach'`, cek apakah file `cetak.php` sudah versi ≥ v1.4 (jumlah baris ≈ 676).

### "ERR_TOO_MANY_REDIRECTS"

Session write tertinggal saat redirect. Pastikan setiap `header('Location: ...')` didahului `session_write_close()` — sudah dibetulkan di v1.1.

### Toast notifikasi tidak muncul

Buka DevTools browser → tab Console. Kalau ada error JavaScript, biasanya library jQuery/DataTables gagal di-load dari CDN (koneksi internet putus). Coba download library secara lokal dan ganti link CDN.

### Query lambat saat data banyak

Periksa indeks database:

```sql
SHOW INDEX FROM penilaian;
SHOW INDEX FROM hasil;
```

Pastikan ada indeks di `hasil.id_penilaian`, `penilaian.id_guru`, `penilaian.periode`.

### Warning "Unexpected endforeach" di VSCode

**Ini bukan error PHP**, hanya false positive dari extension PHP Intelephense di VSCode karena file `cetak.php` punya banyak HTML inline yang bikin linter bingung parse. Sejak v1.4 outer loop `cetak.php` sudah pakai brace syntax `{ ... }` untuk menghindari masalah ini. Abaikan warning kalau masih muncul — aplikasi tetap jalan normal di browser.

### Cetak raport tidak rapi

Pastikan printer pakai kertas A4 dan margin browser default. Di dialog print, set:
- Layout: Portrait
- Margin: Default
- Scale: 100%
- Background graphics: ✅ (centang agar warna kop tercetak)

---

## 📝 Changelog

### v1.4 — April 2026 (Hotfix & Dokumentasi Menyeluruh)

#### 🐛 Bug Diperbaiki

| #   | File                  | Bug                                                                                                                                 | Dampak                                             | Status   |
| --- | --------------------- | ----------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------- | -------- |
| 1   | `cetak.php`           | Literal `\n` (backslash + n, bukan newline asli) di komentar baris 457 membuat `if (!$pen) { continue; }` jadi bagian dari komentar | Mode cetak semua crash saat ada ID yang dihapus: "Parse error: unexpected endforeach" | ✅ Fixed |
| 2   | `cetak.php`           | Outer `foreach ... endforeach` dengan 230+ baris HTML di tengahnya bikin PHP Intelephense (VSCode) salah parse                      | Warning palsu di editor (aplikasi runtime OK)      | ✅ Fixed |
| 3   | `includes/footer.php` | XSS reflected: parameter `?msg=` masuk langsung ke `innerHTML` tanpa escape di `showToast()`                                        | Attacker bisa suntik JavaScript via URL craft      | ✅ Fixed |

#### 📖 Dokumentasi

- README ditulis ulang secara komprehensif (9 section baru)
- Tambah PHPDoc + inline comment di semua file PHP (tidak mengubah logika)
- Koreksi skema DB di dokumentasi: `isi` dan `hasil` pakai composite PK, bukan auto-increment

### v1.3 — April 2026 (Testing Menyeluruh & Bugfix)

| #   | File            | Bug                                                                             | Status   |
| --- | --------------- | ------------------------------------------------------------------------------- | -------- |
| 1   | `guru.php`      | `catatHistory()` di dalam `beginTransaction()` membatalkan INSERT guru          | ✅ Fixed |
| 2   | `guru.php`      | `SELECT id FROM tipe_guru` → kolom yg benar `id_tipe_guru`                      | ✅ Fixed |
| 3   | `guru.php`      | JS `data.id` harusnya `data.id_guru` di `openEdit()`                            | ✅ Fixed |
| 4   | `guru.php`      | JS `data.id` harusnya `data.id_tipe_guru` di `openEditTipe()`                   | ✅ Fixed |
| 5   | `guru.php`      | Syntax `match` (PHP 8.0+) dan arrow `fn()` (PHP 7.4+) tidak kompatibel          | ✅ Fixed |
| 6   | `guru.php`      | `$msg` diset tanpa redirect — toast hanya baca dari URL `?msg=`                 | ✅ Fixed |
| 7   | `item.php`      | `$msg = null` menimpa error validasi POST                                       | ✅ Fixed |
| 8   | `penilaian.php` | `$msg` dari POST tidak ditampilkan di HTML                                      | ✅ Fixed |
| 9   | `penilaian.php` | DELETE tanpa try-catch                                                          | ✅ Fixed |

### v1.2 — April 2026 (Bugfix & Dokumentasi)

| #   | File            | Bug                                                     | Status        |
| --- | --------------- | ------------------------------------------------------- | ------------- |
| 1   | `guru.php`      | CSS class tidak terdefinisi                             | ✅ Fixed      |
| 2   | `guru.php`      | Double POST handler tanpa guard                         | ✅ Fixed      |
| 3   | `cetak.php`     | `$subTotalsCustom` tidak dihitung                       | ✅ Fixed      |
| 4   | `dashboard.php` | `$penilaianFinal` = `$totalPenilaian` (by-design)       | ✅ Documented |

### v1.1 — April 2026

- Fix `ERR_TOO_MANY_REDIRECTS`: `session_write_close()` sebelum setiap redirect

### v1.0 — April 2026

- Rilis awal: konversi dari Excel ke aplikasi web

---

## 📞 Informasi Sekolah

**SD IT QURANI ADH-DHUHAA**  
Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang  
Kota Pangkalpinang, Provinsi Kepulauan Bangka Belitung  

- **NPSN**: `70002294`
- **Telp**: `(0717) 9116753`
- **Email**: `sditquraniadduha@gmail.com`
- **Yayasan**: YAYASAN ADH-DHUHAA PANGKALPINANG

---

## 📄 Lisensi

Proyek internal SD IT Qurani Adh-Dhuhaa. Kode dapat disalin dan dimodifikasi untuk kebutuhan sekolah sejenis dengan mencantumkan atribusi.

---

_Dokumentasi terakhir diperbarui: April 2026 (v1.4)_
