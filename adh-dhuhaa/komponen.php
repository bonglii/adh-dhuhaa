<?php
/**
 * komponen.php — Halaman ini sudah tidak aktif.
 * Pengelolaan indikator dan item sekarang dilakukan melalui:
 *  - item.php          : Bank master item penilaian
 *  - custom_penilaian.php : Kelola indikator + item per tahun ajaran
 */
require_once 'includes/config.php';
requireLogin();
session_write_close();
header('Location: custom_penilaian.php?msg=' . urlencode('Halaman ini telah dipindahkan ke Custom Penilaian.'));
exit;
