<?php
/**
 * ganti_password.php — Halaman Ganti Password
 *
 * WARN-01 FIX: Akun default (admin/password, kepala/password) wajib mengganti
 * password saat pertama kali login. Halaman ini juga bisa diakses kapan saja
 * oleh user yang sudah login untuk mengganti password secara mandiri.
 *
 * Alur:
 *  1. User login dengan password lama → login.php set must_change_password di session
 *  2. Redirect ke sini sebelum bisa akses dashboard
 *  3. Setelah berhasil ganti password, must_change_password = 0 di DB & session
 *  4. Redirect ke dashboard.php
 */
require_once 'includes/config.php';
requireLogin();

$user    = getCurrentUser();
$msg     = '';
$isError = false;

// ─── Cek apakah user sedang dalam mode force-change (wajib) ──────────────────
// Jika must_change_password = 1, user tidak boleh akses halaman lain dulu
$isForced = !empty($user['must_change_password']);

// ─── Handle POST: Proses ganti password ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPass  = $_POST['old_password']  ?? '';
    $newPass  = $_POST['new_password']  ?? '';
    $confPass = $_POST['confirm_password'] ?? '';

    // Ambil data user terbaru dari DB (bukan dari session yang bisa stale)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $freshUser = $stmt->fetch();

    if (!$oldPass || !$newPass || !$confPass) {
        $msg     = '⚠️ Semua field wajib diisi!';
        $isError = true;
    } elseif (!password_verify($oldPass, $freshUser['password'])) {
        $msg     = '⚠️ Password lama tidak sesuai!';
        $isError = true;
    } elseif (strlen($newPass) < 8) {
        $msg     = '⚠️ Password baru minimal 8 karakter!';
        $isError = true;
    } elseif ($newPass !== $confPass) {
        $msg     = '⚠️ Konfirmasi password tidak cocok!';
        $isError = true;
    } elseif ($newPass === $oldPass) {
        $msg     = '⚠️ Password baru tidak boleh sama dengan password lama!';
        $isError = true;
    } else {
        // Hash password baru dengan bcrypt dan simpan ke DB
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?")
            ->execute([$newHash, $user['id']]);

        // Update juga di session agar tidak redirect lagi
        $_SESSION['user']['password']             = $newHash;
        $_SESSION['user']['must_change_password'] = 0;

        $msg     = 'Password berhasil diperbarui! Silakan lanjutkan.';
        $isError = false;
        $isForced = false;

        // Redirect ke dashboard setelah 2 detik
        session_write_close();
        header('Refresh: 2; url=dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password – Penilaian GTK SD IT Qurani Adh-Dhuhaa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --hijau: #1a4731;
            --hijau-muda: #2d6a4f;
            --emas: #c9a84c;
            --krem: #f8f4ee;
            --putih: #ffffff;
            --teks: #1a1a2e;
            --abu: #6b7280;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(201,168,76,0.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(45,106,79,0.3) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9a84c' fill-opacity='0.05'%3E%3Cpath d='M30 0 L60 30 L30 60 L0 30 Z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .wrap {
            width: 100%;
            max-width: 460px;
            padding: 20px;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.5s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: var(--putih);
            border-radius: 20px;
            padding: 44px 40px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
        }
        .icon-area {
            text-align: center;
            margin-bottom: 28px;
        }
        .icon-box {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 8px 24px rgba(26,71,49,0.3);
            font-size: 28px;
        }
        .card-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--hijau);
        }
        .card-sub { font-size: 13px; color: var(--abu); margin-top: 4px; }
        .forced-banner {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #92400e;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.6;
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--emas), transparent);
            margin: 22px 0;
        }
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: var(--teks);
            margin-bottom: 7px;
        }
        .input-wrap { position: relative; }
        .input-wrap input {
            width: 100%;
            padding: 11px 40px 11px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            color: var(--teks);
            background: var(--krem);
            transition: all 0.2s;
            outline: none;
        }
        .input-wrap input:focus {
            border-color: var(--hijau);
            background: var(--putih);
            box-shadow: 0 0 0 4px rgba(26,71,49,0.1);
        }
        .toggle-vis {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--abu);
            font-size: 16px;
            padding: 2px;
            line-height: 1;
        }
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            margin-top: 6px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: width 0.3s, background 0.3s;
            width: 0%;
        }
        .strength-label { font-size: 11px; color: var(--abu); margin-top: 3px; }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--hijau), var(--hijau-muda));
            color: var(--putih);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 6px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(26,71,49,0.35);
        }
        .btn-submit:active { transform: translateY(0); }
        .back-link {
            text-align: center;
            margin-top: 18px;
            font-size: 13px;
        }
        .back-link a { color: var(--hijau); font-weight: 500; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }
        .req-list {
            font-size: 12px;
            color: var(--abu);
            list-style: none;
            margin-top: 6px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .req-list li { display: flex; align-items: center; gap: 5px; }
        .req-list li::before { content: '○'; font-size: 10px; }
        .req-list li.pass::before { content: '●'; color: #16a34a; }
        .req-list li.pass { color: #15803d; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="icon-area">
                <div class="icon-box">🔐</div>
                <div class="card-title">Ganti Password</div>
                <div class="card-sub"><?= htmlspecialchars($user['nama_lengkap'] ?? '') ?></div>
            </div>

            <?php if ($isForced): ?>
                <div class="forced-banner">
                    <span>⚠️</span>
                    <span>Anda menggunakan <strong>password default</strong>. Demi keamanan sistem, wajib mengganti password sebelum dapat mengakses aplikasi.</span>
                </div>
            <?php endif; ?>

            <?php if ($msg): ?>
                <div class="alert <?= $isError ? 'alert-error' : 'alert-success' ?>">
                    <?= $isError ? '✕' : '✓' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="divider"></div>

            <form method="POST" autocomplete="off" id="formGanti">
                <div class="form-group">
                    <label>Password Lama</label>
                    <div class="input-wrap">
                        <input type="password" name="old_password" id="old_password"
                            autocomplete="current-password" placeholder="Masukkan password saat ini" required>
                        <button type="button" class="toggle-vis" onclick="toggleVis('old_password', this)" tabindex="-1">👁</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" name="new_password" id="new_password"
                            autocomplete="new-password" placeholder="Minimal 8 karakter"
                            oninput="checkStrength(this.value); checkReqs(this.value)" required>
                        <button type="button" class="toggle-vis" onclick="toggleVis('new_password', this)" tabindex="-1">👁</button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-label" id="strengthLabel"></div>
                    <ul class="req-list" id="reqList">
                        <li id="req-len">Minimal 8 karakter</li>
                        <li id="req-num">Mengandung angka</li>
                        <li id="req-up">Mengandung huruf besar</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <div class="input-wrap">
                        <input type="password" name="confirm_password" id="confirm_password"
                            autocomplete="new-password" placeholder="Ulangi password baru"
                            oninput="checkMatch()" required>
                        <button type="button" class="toggle-vis" onclick="toggleVis('confirm_password', this)" tabindex="-1">👁</button>
                    </div>
                    <div class="strength-label" id="matchLabel"></div>
                </div>

                <button type="submit" class="btn-submit">🔐 Simpan Password Baru</button>
            </form>

            <?php if (!$isForced): ?>
                <div class="back-link">
                    <a href="dashboard.php">← Kembali ke Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function toggleVis(id, btn) {
        const inp = document.getElementById(id);
        if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
        else { inp.type = 'password'; btn.textContent = '👁'; }
    }

    function checkStrength(val) {
        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const fill  = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        const pct   = [0, 25, 50, 75, 100][score];
        const colors = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
        const labels = ['', 'Sangat lemah', 'Lemah', 'Cukup kuat', 'Kuat'];
        fill.style.width      = pct + '%';
        fill.style.background = colors[score] || '';
        label.textContent     = val.length ? labels[score] : '';
        label.style.color     = colors[score] || '';
    }

    function checkReqs(val) {
        const set = (id, pass) => {
            const el = document.getElementById(id);
            if (pass) el.classList.add('pass'); else el.classList.remove('pass');
        };
        set('req-len', val.length >= 8);
        set('req-num', /[0-9]/.test(val));
        set('req-up',  /[A-Z]/.test(val));
    }

    function checkMatch() {
        const np = document.getElementById('new_password').value;
        const cp = document.getElementById('confirm_password').value;
        const ml = document.getElementById('matchLabel');
        if (!cp) { ml.textContent = ''; return; }
        if (np === cp) { ml.textContent = '✓ Password cocok'; ml.style.color = '#16a34a'; }
        else           { ml.textContent = '✕ Password tidak cocok'; ml.style.color = '#dc2626'; }
    }
    </script>
</body>
</html>
