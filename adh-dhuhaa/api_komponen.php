<?php
/**
 * api_komponen.php — Endpoint API: Ambil Komponen Penilaian per Tipe Guru
 *
 * Dipanggil secara AJAX oleh penilaian.php saat user memilih guru.
 * Mengembalikan JSON berisi semua item penilaian (beserta kategori indikator)
 * yang sesuai dengan tipe guru yang dipilih.
 *
 * Parameter GET:
 *  - tipe (string): guru_quran | guru_kelas | mapel | gtk
 *
 * Response: JSON array of items, atau [] jika tipe tidak valid / tidak ditemukan
 */

// ─── Inisialisasi: load konfigurasi & wajib login ────────────────────────────
require_once 'includes/config.php';
requireLogin();

// ─── Pastikan request berasal dari AJAX (XMLHttpRequest) ─────────────────────
// Ini bukan pengganti CSRF token penuh, tapi mengurangi risiko akses langsung
// dari tab browser atau link luar. Header X-Requested-With mudah ditambah di JS
// dan sulit dipalsukan cross-origin (karena bukan simple header).
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    jsonResponse(['error' => 'Invalid request'], 400);
}

// ─── Validasi parameter tipe ────────────────────────────────────────────────
// isValidTipe() otomatis fallback ke data default jika tabel tipe_guru belum ada
$tipe = sanitize($_GET['tipe'] ?? '');

if (!$tipe || !isValidTipe($pdo, $tipe)) {
    jsonResponse([]);
}

// ─── Query: ambil semua item penilaian untuk tipe guru yang diminta ──────────
// JOIN dengan komponen_penilaian untuk mendapatkan nama kategori (indikator)
// Diurutkan berdasarkan urutan indikator, lalu urutan item di dalamnya
$stmt = $pdo->prepare("
    SELECT
        i.id,
        i.komponen_id,
        i.nomor_item,
        i.nama_item,
        i.urutan,
        kp.nama_kategori  AS kategori,
        kp.urutan         AS urutan_kategori
    FROM item i
    JOIN komponen_penilaian kp ON i.komponen_id = kp.id
    WHERE kp.tipe_guru = ?
    ORDER BY kp.urutan, i.urutan
");
$stmt->execute([$tipe]);

// ─── Kirim response JSON ─────────────────────────────────────────────────────
jsonResponse($stmt->fetchAll());
