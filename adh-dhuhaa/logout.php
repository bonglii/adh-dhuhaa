<?php
/**
 * logout.php — Proses Logout User
 *
 * Menghancurkan seluruh data session yang aktif,
 * lalu mengarahkan kembali ke halaman login.
 */

// ─── Load konfigurasi (termasuk session_start) ───────────────────────────────
require_once 'includes/config.php';

// ─── Hancurkan session & redirect ke login ───────────────────────────────────
session_destroy();
header('Location: login.php');
exit;
