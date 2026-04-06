<?php
/**
 * index.php — Entry Point Aplikasi
 *
 * Mengalihkan semua akses ke halaman login.
 * Semua halaman lain memerlukan autentikasi terlebih dahulu.
 */

// ─── Redirect ke halaman login ───────────────────────────────────────────────
header('Location: login.php');
exit;
