<?php
/**
 * api_custom_komponen.php — API: Ambil data custom penilaian via AJAX
 *
 * Parameter GET:
 *  - tipe (string)     : daftar komponen (tahun ajaran) untuk tipe guru
 *  - id_komponen (int) : daftar isi (indikator + item) untuk satu komponen
 */

// Pastikan semua output adalah JSON bersih — tidak ada PHP warning yang bocor
ini_set('display_errors', 0);
error_reporting(0);

require_once 'includes/config.php';
requireLogin();

// ─── Mode 1: ?tipe=<kode> — list komponen (tahun ajaran) per tipe guru ───────
if (isset($_GET['tipe'])) {
    $tipe = sanitize($_GET['tipe'] ?? '');
    if (!$tipe || !isValidTipe($pdo, $tipe)) {
        jsonResponse([]);
    }
    $stmt = $pdo->prepare("SELECT id_komponen, ta_komponen FROM komponen WHERE type_guru = ? ORDER BY id_komponen DESC");
    $stmt->execute([$tipe]);
    jsonResponse($stmt->fetchAll());
}

// ─── Mode 2: ?id_komponen=<id> — list isi (indikator + item) satu komponen ──
if (isset($_GET['id_komponen'])) {
    $id_komponen = (int)($_GET['id_komponen'] ?? 0);
    if (!$id_komponen) { jsonResponse([]); }
    $stmt = $pdo->prepare("
        SELECT s.id_komponen, s.nama_indikator, s.urutan_isi,
               s.id_item, s.nomor_item, m.nama_item
        FROM isi s
        JOIN item m ON s.id_item = m.id_item
        WHERE s.id_komponen = ?
        ORDER BY s.urutan_isi, s.nomor_item
    ");
    $stmt->execute([$id_komponen]);
    jsonResponse($stmt->fetchAll());
}

// ─── Fallback: parameter tidak dikenali → array kosong ──────────────────────
jsonResponse([]);
