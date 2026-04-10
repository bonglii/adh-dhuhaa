<?php
/**
 * includes/header.php — Layout Header Utama Aplikasi
 *
 * Dimuat di awal setiap halaman terproteksi.
 * Berisi: inisialisasi session/config, sidebar navigasi, navbar atas,
 * Bootstrap 5 CSS, DataTables CSS, dan custom CSS global.
 *
 * Variabel yang tersedia setelah di-include:
 *  - $user (array)     : data user yang sedang login
 *  - $pdo  (PDO)       : koneksi database (dari config.php)
 *  - $pageTitle (string): judul halaman (harus di-set sebelum include ini)
 */
require_once __DIR__ . '/config.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistem PKG' ?> – SD IT Qurani Adh-Dhuhaa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --hijau: #1a4731;
            --hijau-muda: #2d6a4f;
            --hijau-pale: #e8f5ee;
            --emas: #c9a84c;
            --emas-muda: #f0d98a;
            --krem: #faf7f2;
            --sidebar-w: 270px;
            --teks: #1a1a2e;
            --abu: #6b7280;
            --border: #e5e7eb;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f4f6f9;
            color: var(--teks);
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: linear-gradient(180deg, #1a4731 0%, #163d2b 60%, #112e21 100%);
            display: flex;
            flex-direction: column;
            z-index: 100;
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(0,0,0,0.18);
        }

        /* Dekoratif background pattern */
        .sidebar::before {
            content: '';
            position: absolute;
            bottom: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.10) 0%, transparent 70%);
            pointer-events: none;
        }
        .sidebar::after {
            content: '';
            position: absolute;
            top: -40px;
            left: -40px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .sidebar-brand {
            padding: 26px 22px 20px;
            border-bottom: 1px solid rgba(201,168,76,0.15);
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(201,168,76,0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .brand-icon:hover {
            transform: scale(1.08) rotate(-3deg);
            box-shadow: 0 6px 20px rgba(201,168,76,0.5);
        }

        .brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 14.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.35;
            letter-spacing: 0.2px;
        }

        .brand-sub {
            font-size: 10px;
            color: rgba(255,255,255,0.42);
            font-weight: 300;
            letter-spacing: 0.4px;
            margin-top: 4px;
        }

        /* Nav scrollbar disembunyikan sepenuhnya */
        .sidebar-nav {
            flex: 1;
            padding: 18px 14px;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            z-index: 1;
            scrollbar-width: none;        /* Firefox */
            -ms-overflow-style: none;     /* IE/Edge */
        }
        .sidebar-nav::-webkit-scrollbar {
            display: none;                /* Chrome/Safari */
        }

        .nav-label {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            color: rgba(255,255,255,0.28);
            padding: 0 10px;
            margin-bottom: 6px;
            margin-top: 22px;
        }

        .nav-label:first-child { margin-top: 0; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 11px;
            color: rgba(255,255,255,0.62);
            text-decoration: none;
            font-size: 13.2px;
            font-weight: 400;
            transition: all 0.22s cubic-bezier(.4,0,.2,1);
            margin-bottom: 2px;
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        /* Ripple efek hover */
        .nav-item::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            border-radius: inherit;
            transition: background 0.22s ease;
        }

        .nav-item svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
            opacity: 0.6;
            transition: opacity 0.22s ease, transform 0.22s ease;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.09);
            color: rgba(255,255,255,0.95);
            transform: translateX(3px);
            border-color: rgba(255,255,255,0.06);
        }

        .nav-item:hover svg {
            opacity: 1;
            transform: scale(1.1);
        }

        .nav-item:active {
            transform: translateX(2px) scale(0.98);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(201,168,76,0.22), rgba(201,168,76,0.10));
            color: var(--emas-muda);
            font-weight: 600;
            border-color: rgba(201,168,76,0.22);
            box-shadow: 0 2px 12px rgba(201,168,76,0.12), inset 0 1px 0 rgba(201,168,76,0.15);
        }

        .nav-item.active svg {
            opacity: 1;
            filter: drop-shadow(0 0 3px rgba(201,168,76,0.5));
        }

        /* Garis indikator aktif kiri */
        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            height: 60%;
            width: 3px;
            background: linear-gradient(180deg, var(--emas), rgba(201,168,76,0.4));
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px rgba(201,168,76,0.6);
        }

        /* Dot indikator aktif kanan */
        .nav-item.active .nav-dot {
            display: block;
        }
        .nav-dot {
            display: none;
            width: 6px;
            height: 6px;
            background: var(--emas);
            border-radius: 50%;
            margin-left: auto;
            flex-shrink: 0;
            box-shadow: 0 0 6px rgba(201,168,76,0.7);
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(0.75); }
        }

        /* Separator halus antar section */
        .nav-separator {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
            margin: 10px 8px;
        }

        .sidebar-footer {
            padding: 16px 14px;
            border-top: 1px solid rgba(255,255,255,0.07);
            position: relative;
            z-index: 1;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 13px;
            border-radius: 12px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.07);
            transition: all 0.22s ease;
            cursor: default;
        }
        .user-info:hover {
            background: rgba(255,255,255,0.10);
            border-color: rgba(201,168,76,0.2);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            background: linear-gradient(135deg, var(--emas), #e8c060);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--hijau);
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(201,168,76,0.3);
        }

        .user-name {
            font-size: 12.5px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            font-size: 10px;
            color: rgba(255,255,255,0.38);
            margin-top: 1px;
        }

        /* Online dot pada user */
        .user-online {
            width: 7px;
            height: 7px;
            background: #4ade80;
            border-radius: 50%;
            margin-left: auto;
            flex-shrink: 0;
            box-shadow: 0 0 0 2px rgba(74,222,128,0.25);
            animation: pulse-online 2.5s infinite;
        }
        @keyframes pulse-online {
            0%, 100% { box-shadow: 0 0 0 2px rgba(74,222,128,0.25); }
            50% { box-shadow: 0 0 0 5px rgba(74,222,128,0.08); }
        }

        /* MAIN CONTENT */
        .main-wrap {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-title {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 600;
            color: var(--teks);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--hijau-pale);
            color: var(--hijau);
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: var(--hijau);
            color: #fff;
        }

        .content-area {
            padding: 32px;
        }

        /* CARDS */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .stat-number {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--teks);
            line-height: 1;
        }

        .stat-label {
            font-size: 13px;
            color: var(--abu);
            margin-top: 4px;
        }

        /* TABLE */
        .data-table-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid var(--border);
        }

        .card-header-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .card-title-custom {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--teks);
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,71,49,0.3);
            color: #fff;
        }

        .btn-sm-custom {
            padding: 6px 14px;
            font-size: 12px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .btn-view {
            background: linear-gradient(135deg, var(--hijau-muda), var(--hijau));
        }

        .badge-tipe {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }

        .badge-quran { background: #dcfce7; color: #16a34a; }
        .badge-kelas { background: #dbeafe; color: #2563eb; }
        .badge-mapel { background: #fef3c7; color: #d97706; }
        .badge-gtk { background: #f3e8ff; color: #9333ea; }

        /* MODAL */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 30px 80px rgba(0,0,0,0.2);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            color: #fff;
            border-radius: 16px 16px 0 0;
            padding: 20px 28px;
            border: none;
        }

        .modal-title { font-family: 'Playfair Display', serif; font-size: 18px; }
        .btn-close { filter: invert(1); }
        .modal-body { padding: 28px; }
        .modal-footer { padding: 16px 28px 20px; border: none; }

        .form-label-custom {
            font-size: 13px;
            font-weight: 500;
            color: var(--teks);
            margin-bottom: 6px;
        }

        .form-control-custom {
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            transition: border-color 0.2s;
            outline: none;
            width: 100%;
        }

        .form-control-custom:focus {
            border-color: var(--hijau);
            box-shadow: 0 0 0 3px rgba(26,71,49,0.1);
        }

        /* Alert */
        .alert-custom {
            border-radius: 12px;
            padding: 14px 18px;
            border: none;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success-custom { background: #dcfce7; color: #15803d; }
        .alert-error-custom { background: #fef2f2; color: #dc2626; }

        /* Penilaian form */
        .nilai-group {
            background: var(--krem);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .nilai-kategori {
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--hijau);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nilai-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            gap: 20px;
            overflow: hidden;
        }

        .nilai-item:last-child { border-bottom: none; padding-bottom: 0; }

        .nilai-item-label {
            font-size: 13px;
            color: var(--teks);
            flex: 1;
            min-width: 0;
            word-break: break-all;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .nilai-item-num {
            font-size: 11px;
            color: var(--abu);
            margin-right: 4px;
        }

        .nilai-radio-group {
            display: flex;
            gap: 6px;
        }

        .nilai-radio-group input { display: none; }

        .nilai-radio-group label {
            width: 34px;
            height: 34px;
            border: 2px solid var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--abu);
        }

        .nilai-radio-group input:checked + label {
            background: var(--hijau);
            border-color: var(--hijau);
            color: #fff;
        }

        .nilai-radio-group label:hover {
            border-color: var(--hijau-muda);
            color: var(--hijau);
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-wrap { margin-left: 0; }
        }

        /* ═══════════════════════════════════════════════════════
           BUTTON SYSTEM — Unified & Beautiful
        ═══════════════════════════════════════════════════════ */

        /* Base button override — hapus Bootstrap btn-light yg jelek */
        .btn-light {
            background: #f3f4f6 !important;
            color: #374151 !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 9px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding: 8px 18px !important;
            transition: all .18s !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-light:hover {
            background: #e9eaec !important;
            border-color: #d1d5db !important;
            color: #1f2937 !important;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,0,0,.07) !important;
        }
        .btn-light:active { transform: translateY(0) !important; }

        .btn-secondary {
            background: linear-gradient(135deg,#6b7280,#4b5563) !important;
            color:#fff !important;
            border:none !important;
            border-radius:9px !important;
            font-size:13px !important;
            font-weight:500 !important;
            padding:8px 18px !important;
            transition:all .18s !important;
            display:inline-flex;
            align-items:center;
            gap:6px;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg,#4b5563,#374151) !important;
            transform:translateY(-1px);
            box-shadow:0 4px 12px rgba(75,85,99,.3) !important;
        }

        /* Tombol Kembali / Back — abu-abu elegan */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #f3f4f6;
            color: #374151;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }
        .btn-back:hover {
            background: #fff;
            border-color: #d1d5db;
            color: #111827;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0,0,0,.07);
        }

        /* Tombol Batal di modal */
        .btn-cancel {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: #fff;
            color: #6b7280;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }
        .btn-cancel:hover {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #dc2626;
        }

        /* Tombol Hapus Semua / Danger */
        .btn-danger-custom {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            background: linear-gradient(135deg,#ef4444,#dc2626);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }
        .btn-danger-custom:hover {
            background: linear-gradient(135deg,#dc2626,#b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220,38,38,.35);
            color: #fff;
        }
        .btn-danger-custom:active { transform: translateY(0); }

        /* Tombol Warning / Outline */
        .btn-outline-custom {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            background: transparent;
            color: var(--hijau);
            border: 1.5px solid var(--hijau);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
        }
        .btn-outline-custom:hover {
            background: var(--hijau-pale);
            color: var(--hijau);
        }

        /* btn-primary-custom — buat lebih mewah */
        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--hijau) 0%, var(--hijau-muda) 100%);
            color: #fff !important;
            border: none;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 2px 6px rgba(26,71,49,.2);
            position: relative;
            overflow: hidden;
        }
        .btn-primary-custom::after {
            content:'';
            position:absolute;
            inset:0;
            background:linear-gradient(180deg,rgba(255,255,255,.12) 0%,transparent 100%);
            pointer-events:none;
        }
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(26,71,49,.32);
            color: #fff !important;
        }
        .btn-primary-custom:active { transform: translateY(0); box-shadow: 0 2px 6px rgba(26,71,49,.2); }

        .btn-sm-custom {
            padding: 6px 13px !important;
            font-size: 12px !important;
            border-radius: 8px !important;
            gap: 5px !important;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
            box-shadow: 0 2px 6px rgba(59,130,246,.25) !important;
        }
        .btn-edit:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            box-shadow: 0 5px 14px rgba(59,130,246,.4) !important;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626) !important;
            box-shadow: 0 2px 6px rgba(239,68,68,.25) !important;
        }
        .btn-delete:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
            box-shadow: 0 5px 14px rgba(239,68,68,.4) !important;
        }

        .btn-view {
            background: linear-gradient(135deg, #0ea5e9, #0284c7) !important;
            box-shadow: 0 2px 6px rgba(14,165,233,.25) !important;
        }
        .btn-view:hover {
            background: linear-gradient(135deg, #0284c7, #0369a1) !important;
            box-shadow: 0 5px 14px rgba(14,165,233,.4) !important;
        }

        /* Tombol logout di topbar */
        .btn-logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--hijau-pale);
            color: var(--hijau);
            border: 1.5px solid rgba(26,71,49,.15);
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .btn-logout:hover {
            background: var(--hijau);
            color: #fff;
            border-color: var(--hijau);
            box-shadow: 0 4px 12px rgba(26,71,49,.3);
            transform: translateY(-1px);
        }

        /* Action button group spacing */
        .btn-group-action {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><img src="data:image/jpeg;base64,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" alt="Logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;display:block;"></div>
        <div class="brand-name">SD IT QURANI<br>ADH-DHUHAA</div>
        <div class="brand-sub">Sistem PKG – Penilaian Kinerja Guru</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Menu Utama</div>
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
            <span class="nav-dot"></span>
        </a>

        <div class="nav-separator"></div>

        <div class="nav-label">Data Master</div>
        <a href="guru.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='guru.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Data Guru & GTK
            <span class="nav-dot"></span>
        </a>
        <a href="item.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='item.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>
            Tambah Point Penilaian
            <span class="nav-dot"></span>
        </a>

        <div class="nav-separator"></div>

        <div class="nav-label">Penilaian</div>
        <a href="custom_penilaian.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='custom_penilaian.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"/></svg>
            Buat Pertanyaan Penilaian
            <span class="nav-dot"></span>
        </a>
        <a href="penilaian.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='penilaian.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Penilaian Kinerja
            <span class="nav-dot"></span>
        </a>
        <a href="rekap.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='rekap.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            Rekap Penilaian
            <span class="nav-dot"></span>
        </a>
        <a href="ranking.php" class="nav-item <?= basename($_SERVER['PHP_SELF'])=='ranking.php'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 18V10M12 18V4M16 18v-6"/><rect x="2" y="20" width="20" height="2" rx="1"/></svg>
            Ranking Guru
            <span class="nav-dot"></span>
        </a>

        <div class="nav-separator"></div>

        <div class="nav-label">Akun</div>
        <a href="logout.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?></div>
            <div style="flex:1;min-width:0;">
                <div class="user-name"><?= htmlspecialchars($user['nama_lengkap']) ?></div>
                <div class="user-role"><?= ucfirst(str_replace('_', ' ', $user['role'])) ?></div>
            </div>
            <div class="user-online" title="Online"></div>
        </div>
    </div>
</div>

<div class="main-wrap">
    <div class="topbar">
        <div class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div class="topbar-right">
            <a href="logout.php" class="btn-logout">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Keluar
            </a>
        </div>
    </div>
    <div class="content-area">
