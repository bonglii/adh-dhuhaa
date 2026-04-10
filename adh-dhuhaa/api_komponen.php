<?php
/**
 * api_komponen.php — Endpoint API: Ambil item penilaian per tipe guru
 * (tidak lagi pakai tabel indikator — join langsung via isi)
 */
require_once 'includes/config.php';
requireLogin();

$tipe = sanitize($_GET['tipe'] ?? '');

if (!$tipe || !isValidTipe($pdo, $tipe)) {
    jsonResponse([]);
}

$stmt = $pdo->prepare("
    SELECT
        it.id_item,
        s.nama_indikator AS kategori,
        s.urutan_isi     AS urutan_kategori,
        it.nama_item
    FROM isi s
    JOIN item it ON s.id_item = it.id_item
    JOIN komponen k ON s.id_komponen = k.id_komponen
    WHERE k.type_guru = ?
    GROUP BY it.id_item, s.nama_indikator
    ORDER BY s.urutan_isi, s.nomor_item
");
$stmt->execute([$tipe]);

jsonResponse($stmt->fetchAll());
