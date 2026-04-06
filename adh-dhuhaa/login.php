<?php
/**
 * login.php — Halaman Login Sistem Penilaian Kinerja GTK
 *
 * Menangani autentikasi pengguna menggunakan session PHP.
 * Jika sudah login, pengguna langsung diarahkan ke dashboard.
 */
require_once 'includes/config.php';

// ─── Redirect jika sudah login ──────────────────────────────────────────────
if (isLoggedIn()) {
    // Simpan session sebelum redirect agar data tidak hilang
    session_write_close();
    header('Location: dashboard.php');
    exit;
}

// ─── Proses form login (POST) ────────────────────────────────────────────────
$error = '';
// Tampilkan pesan session timeout jika ada
if (isset($_GET['msg'])) {
    $error = sanitize($_GET['msg']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil & sanitasi input username; password tidak di-sanitize agar hash tidak rusak
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        // Cari user di database berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi password menggunakan password_verify (bcrypt)
        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil — regenerate session ID mencegah session fixation attack
            session_regenerate_id(true);
            // Bersihkan counter gagal, simpan identitas ke session
            unset($_SESSION['_login_fail_count'], $_SESSION['_login_fail_time']);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = $user;
            // WARN-01 FIX: Cek apakah user wajib ganti password (akun default atau baru direset)
            // Kolom must_change_password = 1 → redirect ke halaman ganti password
            // Fallback ke 0 jika kolom belum ada (migrasi bertahap)
            if (!empty($user['must_change_password'])) {
                session_write_close();
                header('Location: ganti_password.php');
                exit;
            }
            // Simpan session sebelum redirect agar data tidak hilang
            session_write_close();
            header('Location: dashboard.php');
            exit;
        } else {
            // Catat percobaan gagal & tambah delay untuk menghambat brute-force
            $_SESSION['_login_fail_count'] = ($_SESSION['_login_fail_count'] ?? 0) + 1;
            $_SESSION['_login_fail_time']  = time();
            // Delay progresif: gagal 1-2x = 1 detik, 3-4x = 2 detik, 5x+ = 3 detik
            $fail = (int)$_SESSION['_login_fail_count'];
            sleep($fail <= 2 ? 1 : ($fail <= 4 ? 2 : 3));
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username dan password wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Penilaian GTK SD IT Qurani Adh-Dhuhaa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --hijau: #1a4731;
            --hijau-muda: #2d6a4f;
            --emas: #c9a84c;
            --emas-muda: #e8c870;
            --krem: #f8f4ee;
            --putih: #ffffff;
            --teks: #1a1a2e;
            --abu: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--hijau);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Islamic geometric pattern background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(201, 168, 76, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(45, 106, 79, 0.3) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9a84c' fill-opacity='0.05'%3E%3Cpath d='M30 0 L60 30 L30 60 L0 30 Z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: var(--putih);
            border-radius: 20px;
            padding: 50px 42px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
        }

        .logo-area {
            text-align: center;
            margin-bottom: 36px;
        }

        .logo-icon {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(26, 71, 49, 0.3);
        }

        .logo-icon svg {
            width: 38px;
            height: 38px;
            fill: var(--emas);
        }

        .sekolah-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--hijau);
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .sekolah-sub {
            font-size: 12px;
            color: var(--abu);
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--emas), transparent);
            margin: 28px 0;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            color: var(--teks);
            margin-bottom: 6px;
        }

        .page-sub {
            font-size: 13px;
            color: var(--abu);
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--teks);
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            stroke: var(--abu);
            fill: none;
            stroke-width: 1.8;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--teks);
            background: var(--krem);
            transition: all 0.2s;
            outline: none;
        }

        input:focus {
            border-color: var(--hijau);
            background: var(--putih);
            box-shadow: 0 0 0 4px rgba(26, 71, 49, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            color: var(--putih);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.5px;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0);
            transition: background 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(26, 71, 49, 0.4);
        }

        .btn-login:hover::after {
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-text {
            text-align: center;
            margin-top: 28px;
            font-size: 11px;
            color: var(--abu);
        }

        .emas-text {
            color: var(--emas);
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-area">
                <div class="logo-icon">
                    <svg viewBox="0 0 50 50" xmlns="http://www.w3.org/2000/svg">
                        <path d="M25 5 L35 15 L45 15 L45 35 L35 45 L15 45 L5 35 L5 15 L15 5 Z" opacity="0.3" />
                        <path d="M25 10 C20 10 15 15 15 25 C15 35 20 40 25 40 C30 40 35 35 35 25 C35 15 30 10 25 10Z" opacity="0.5" />
                        <text x="25" y="30" text-anchor="middle" font-size="16" font-family="serif">☪</text>
                    </svg>
                </div>
                <div class="sekolah-name">SD IT QURANI<br>ADH-DHUHAA</div>
                <div class="sekolah-sub">Pangkalpinang, Kep. Bangka Belitung</div>
            </div>

            <div class="divider"></div>

            <div class="page-title">Selamat Datang</div>
            <div class="page-sub">Masuk ke Sistem Penilaian Kinerja GTK</div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>


            <!-- Form login: autocomplete="off" mencegah browser menyimpan/mengisi otomatis kredensial -->
            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24">
                            <circle cx="12" cy="8" r="4" />
                            <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                        </svg>
                        <!-- autocomplete="off" pada input username agar browser tidak mengisi otomatis -->
                        <input type="text" name="username" autocomplete="off" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <svg viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <!-- autocomplete="off" pada input password agar browser tidak mengisi otomatis -->
                        <input type="password" name="password" autocomplete="off" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login">Masuk ke Sistem</button>
            </form>

            <div class="footer-text">
                <span class="emas-text">SD IT Qurani Adh-Dhuhaa</span> &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>
</body>

</html>