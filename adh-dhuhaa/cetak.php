<?php require_once 'includes/config.php';
requireLogin(); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport PKG – SD IT Qurani Adh-Dhuhaa</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --hijau: #1a4731;
            --emas: #c9a84c;
            --krem: #faf7f2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f0f0f0;
            color: #1a1a1a;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 999;
            display: flex;
            gap: 10px;
        }

        .btn-cetak {
            padding: 12px 24px;
            background: var(--hijau);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back {
            padding: 12px 20px;
            background: #6b7280;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .page-container {
            max-width: 800px;
            margin: 60px auto 40px;
            padding: 20px;
        }

        .raport-page {
            background: #fff;
            border-radius: 12px;
            padding: 50px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Header Kop Surat */
        .kop-surat {
            border-bottom: 3px solid var(--hijau);
            padding-bottom: 16px;
            margin-bottom: 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-align: center;
        }

        @media (min-width: 500px) {
            .kop-surat {
                flex-direction: row;
                text-align: left;
            }

            .kop-text {
                text-align: center;
            }
        }

        .kop-logo {
            width: 130px;
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kop-logo img {
            width: 130px;
            height: 130px;
            object-fit: contain;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-yayasan {
            font-size: 13px;
            font-weight: 500;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-sekolah {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--hijau);
            letter-spacing: 0.5px;
        }

        .kop-alamat {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
            margin-top: 4px;
        }

        .raport-title {
            text-align: center;
            margin: 28px 0 24px;
        }

        .raport-title h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--hijau);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--emas);
            display: inline-block;
            padding-bottom: 6px;
        }

        .raport-title h3 {
            font-size: 14px;
            font-weight: 500;
            color: #444;
            margin-top: 6px;
        }

        .identitas-table {
            width: 100%;
            margin-bottom: 28px;
        }

        .identitas-table tr td {
            padding: 5px 0;
            font-size: 13.5px;
            vertical-align: top;
        }

        .identitas-table tr td:first-child {
            font-weight: 500;
            width: 160px;
            color: #444;
        }

        .identitas-table tr td:nth-child(2) {
            width: 10px;
            padding: 5px 8px;
        }

        .identitas-table tr td:last-child {
            font-weight: 600;
            color: #1a1a1a;
        }

        /* Penilaian Table */
        .pkg-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 24px;
        }

        .pkg-table th,
        .pkg-table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .pkg-table thead tr th {
            background: var(--hijau);
            color: #fff;
            font-weight: 500;
            text-align: center;
        }

        .pkg-table .row-kategori td {
            background: #e8f5ee;
            font-weight: 600;
            color: var(--hijau);
            font-size: 13px;
        }

        .pkg-table .row-item td:nth-child(3) {
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            color: var(--hijau);
        }

        .pkg-table .row-subtotal td {
            background: #f0f9f4;
            font-weight: 600;
            font-size: 12px;
        }

        .pkg-table .row-total td {
            background: var(--hijau);
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }

        .nilai-stars {
            display: inline-flex;
            gap: 2px;
        }

        .star {
            color: #ddd;
            font-size: 14px;
        }

        .star.filled {
            color: var(--emas);
        }

        .predikat-box {
            border: 2px solid var(--emas);
            border-radius: 10px;
            padding: 16px 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 20px;
            background: var(--krem);
        }

        .predikat-score {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            font-weight: 700;
            color: var(--hijau);
            line-height: 1;
        }

        .predikat-score span {
            font-size: 18px;
            color: #888;
        }

        .predikat-label {
            font-size: 18px;
            font-weight: 700;
            color: var(--hijau);
        }

        .predikat-desc {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .catatan-box {
            border-left: 4px solid var(--emas);
            padding: 16px 20px;
            background: #fffdf5;
            border-radius: 0 10px 10px 0;
            margin: 16px 0 24px;
            font-size: 13px;
            line-height: 1.8;
            color: #333;
        }

        .ttd-area {
            display: flex;
            justify-content: flex-end;
            margin-top: 32px;
        }

        .ttd-box {
            text-align: center;
            min-width: 200px;
        }

        .ttd-kota {
            font-size: 12.5px;
            color: #444;
            margin-bottom: 60px;
        }

        .ttd-nama {
            font-weight: 700;
            font-size: 13.5px;
            border-top: 1px solid #333;
            padding-top: 6px;
        }

        .ttd-jabatan {
            font-size: 12px;
            color: #666;
        }

        @media print {
            body {
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .raport-page {
                border-radius: 0;
                box-shadow: none;
                margin: 0;
                padding: 30px;
                page-break-after: always;
            }

            .raport-page:last-child {
                page-break-after: auto;
            }

            .page-container {
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    <?php
    $id       = (int)($_GET['id']  ?? 0);
    $printAll = isset($_GET['all']) && $_GET['all'] == '1';
    // Gunakan trim() saja (bukan sanitize) agar nilai periode tidak berubah oleh htmlspecialchars
    // PDO prepared statement sudah melindungi dari SQL injection
    $filterPeriodeCetak = trim($_GET['periode'] ?? '');

    // ── Mode cetak semua (dari tombol Cetak Semua di rekap.php) ─────────────
    if ($printAll) {
        // Ambil semua penilaian final, opsional filter periode & tipe
        $filterTipeCetak = trim($_GET['tipe'] ?? '');
        // Validasi tipe terhadap tabel tipe_guru (dinamis)
        if ($filterTipeCetak && !isValidTipe($pdo, $filterTipeCetak)) $filterTipeCetak = '';

        $sqlAll = "
            SELECT p.id_penilaian
            FROM penilaian p
            JOIN guru g ON p.id_guru = g.id_guru
            WHERE p.id_penilaian IS NOT NULL
        ";
        $paramsAll = [];
        if ($filterPeriodeCetak) {
            $sqlAll      .= " AND p.periode = ?";
            $paramsAll[] = $filterPeriodeCetak;
        }
        if ($filterTipeCetak) {
            $sqlAll      .= " AND g.tipe = ?";
            $paramsAll[] = $filterTipeCetak;
        }
        $sqlAll .= " ORDER BY g.tipe, g.nama, p.id_penilaian ASC";
        $stmtAll = $pdo->prepare($sqlAll);
        $stmtAll->execute($paramsAll);
        $allIds = $stmtAll->fetchAll(PDO::FETCH_COLUMN);

        if (empty($allIds)) {
            echo '<p style="padding:40px;text-align:center;font-family:sans-serif;">
                    Tidak ada data penilaian untuk dicetak.
                    <br><br><a href="javascript:window.close()" style="color:#1a4731;">← Kembali ke Rekap</a>
                  </p>';
            exit;
        }
    } else {
        // Mode cetak satu penilaian
        if (!$id) {
            echo '<p style="padding:40px;text-align:center;">ID penilaian tidak valid.</p>';
            exit;
        }
        $allIds = [$id];
    }

    /**
     * Mengembalikan [label predikat, simbol bintang] dari nilai persentase.
     *
     * @param float $pct  Nilai persentase 0–100
     * @return array      [string $label, string $stars]
     */
    function getPredikat(float $pct): array
    {
        if ($pct >= 90) return ['Sangat Baik Sekali', '⭐⭐⭐⭐⭐'];
        if ($pct >= 75) return ['Sangat Baik',        '⭐⭐⭐⭐'];
        if ($pct >= 60) return ['Baik',               '⭐⭐⭐'];
        if ($pct >= 40) return ['Cukup',              '⭐⭐'];
        return ['Kurang', '⭐'];
    }

    /**
     * tanggalIndonesia — Format tanggal ke "dd Nama_Bulan yyyy" bahasa Indonesia.
     *
     * @param  string $dateStr  Tanggal dalam format yang bisa di-parse strtotime
     * @return string           Contoh: "23 April 2026"
     */
    function tanggalIndonesia($dateStr)
    {
        $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni',
                      'Juli','Agustus','September','Oktober','November','Desember'];
        $ts = strtotime($dateStr);
        return date('d', $ts) . ' ' . $namaBulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
    }

    // ── Loop cetak setiap penilaian ─────────────────────────────────────────
    foreach ($allIds as $currentId) {
        $id = (int)$currentId;

    // Load penilaian
    $stmt = $pdo->prepare("
    SELECT p.*, g.nama, g.nrg, g.jabatan, g.tmt_guru, g.tipe
    FROM penilaian p JOIN guru g ON p.id_guru = g.id_guru
    WHERE p.id_penilaian = ?
");
    $stmt->execute([$id]);
    $pen = $stmt->fetch();

    // Skip penilaian yang tidak ditemukan (mode cetak semua)
    if (!$pen) {
        continue;
    }

    $grouped = [];

    if (!empty($pen['id_komponen'])) {
        // ── Penilaian Custom: item dari item via tabel isi ──────────────
        $detailStmt = $pdo->prepare("
            SELECT dp.nilai,
                   s.nama_indikator AS kategori,
                   s.nomor_item,
                   m.nama_item,
                   s.urutan_isi,
                   s.id_item
            FROM hasil dp
            JOIN isi s   ON dp.id_item = s.id_item
                        AND s.id_komponen = ?
            JOIN item m ON s.id_item = m.id_item
            WHERE dp.id_penilaian = ?
            ORDER BY s.urutan_isi, s.nomor_item
        ");
        $detailStmt->execute([$pen['id_komponen'], $id]);
        $details = $detailStmt->fetchAll();

        foreach ($details as $d) {
            $grouped[$d['kategori']][] = $d;
        }
    } else {
        // ── Penilaian Standar: item via tabel isi → item ──────────────
        $detailStmt = $pdo->prepare("
            SELECT dp.nilai, s.nama_indikator AS kategori, s.nomor_item, i.nama_item,
                   s.urutan_isi
            FROM hasil dp
            JOIN isi s   ON dp.id_item = s.id_item AND s.id_komponen = ?
            JOIN item i  ON dp.id_item = i.id_item
            WHERE dp.id_penilaian = ?
            ORDER BY s.urutan_isi, s.nomor_item
        ");
        $detailStmt->execute([$pen['id_komponen'], $id]);
        $details = $detailStmt->fetchAll();

        foreach ($details as $d) {
            $grouped[$d['kategori']][] = $d;
        }
    }

    // Hitung nilai per kategori dan total
    $totalNilai = 0;
    $totalMax = 0;
    $subTotals = [];

    foreach ($grouped as $kat => $items) {
        $sum = array_sum(array_column($items, 'nilai'));
        $max = count($items) * 5;
        $pct = $max > 0 ? round($sum / $max * 100, 2) : 0;
        $subTotals[$kat] = ['sum' => $sum, 'max' => $max, 'pct' => $pct];
        $totalNilai += $sum;
        $totalMax += $max;
    }

    // Nilai akhir = rata-rata persen per indikator (dinamis, bukan flat sum)
    // Sehingga tiap indikator bobotnya sama apapun jumlah itemnya
    $allPcts = array_column($subTotals, 'pct');
    $totalPct = count($allPcts) > 0 ? round(array_sum($allPcts) / count($allPcts), 2) : 0;

    [$predikat, $stars] = getPredikat($totalPct);


    ?>

    <div class="no-print">
        <button class="btn-cetak" onclick="window.print()">🖨 Cetak / PDF</button>
        <button class="btn-back" onclick="
            var from = '<?= htmlspecialchars($_GET['from'] ?? '') ?>';
            if (from === 'dashboard') {
                history.back();
            } else {
                window.close();
            }
        ">← Kembali</button>
    </div>

    <div class="page-container">
        <div class="raport-page">
            <!-- Kop Surat -->
            <div class="kop-surat">
                <div class="kop-logo"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAQAAAAEACAYAAABccqhmAAABCGlDQ1BJQ0MgUHJvZmlsZQAAeJxjYGA8wQAELAYMDLl5JUVB7k4KEZFRCuwPGBiBEAwSk4sLGHADoKpv1yBqL+viUYcLcKakFicD6Q9ArFIEtBxopAiQLZIOYWuA2EkQtg2IXV5SUAJkB4DYRSFBzkB2CpCtkY7ETkJiJxcUgdT3ANk2uTmlyQh3M/Ck5oUGA2kOIJZhKGYIYnBncAL5H6IkfxEDg8VXBgbmCQixpJkMDNtbGRgkbiHEVBYwMPC3MDBsO48QQ4RJQWJRIliIBYiZ0tIYGD4tZ2DgjWRgEL7AwMAVDQsIHG5TALvNnSEfCNMZchhSgSKeDHkMyQx6QJYRgwGDIYMZAKbWPz9HbOBQAAEAAElEQVR42uy9d5gcV5U2/p57qzp3T845aBRGOdmyZFlyxAHjKIMNJq13YVk2fvDt91t2LW2OgIk2YQkG20hOYMuWoyQr5zRJk/NMT+ocq+qe3x/dI8sssOziCHOeR49sTXdXT9U9+T3vAebkd1qYmebuwpzMye+OwgsAGAtOrR0PTf3ZnBGYkzn5HZLdzBoAnBno+MoMp3g6Gvi9rBGQc3fnd0/E3C343ZI9W7cqZtaDieDqx4/utIamxrYys4OI1FwkMCdz8lss27Nenpk3vdx1mLd884/V4yeeTzFzLQDcz/fPOYS5CGBOflulCCAAGAv414+E/Nw9NZQaTwZsU/GZ+wBgK7bORQBzBmBOfsvDf20wNPa+433nKGkYWsvAeQxOj3+CmTUissCYMwJzBmBOfttk9+7dGgCkzNT7I5zccLav1bLrUusa7VVdk/2FJrARABhzdYA5AzAnv32yCdi2bZvq9PdfcazvHEeTcZZCYjoR4s6JQW1wYvhfsq/kuZv1uyPa3C347RdmFkRkMvOyvX3HP/Ra62FWgJQAiEkc6TmJpSWNTcycByDIzEREc4ZgLgKYk98SIWa2dfv7v3hqsK14IjjFQgpiZuhC0uDUqDoz0e3rmhp6MKv4c2nAnAGYk9+W3J+IrFgqdpU/Fbxy1/HdphCaJM5oOQPQpC4Ot5+wemeGtiTZuJGI1Pbt2+eAQXMpwJy8x0N/yvzFvnNj3f/w9KlX1WQyKATTG3y8FMB4ZIJebNnH5Z6CB5l5HgBjLhWYiwDm5D0sJ3BCIyJreGr8D9umelfsbz+iBJEAAUQMIRgKACmCFEIc6jxhHRtpqxyeGftXIrJOnDgx5yDmDMCcvEe9v7aaVhuhaPT67vDQF36093EzbaalAEFBwOdiNJRa8DosZJJ+gbRpyKePvGC1TPZ8KpaM3bp69Wpjtn04J3MGYE7eQ8pPRGY0lVrZEx7e/pMjz7rHAhNSEshiBZtUmFeUxroGE03lJqRugAFIIhqeGRVPHNultU72PcrMqzZv3mzOGYG5GsCcvHeUXxKRybFYxZmpvh07TjznOdZ9ziJJklkAwkJ5roWmckKJz4RiRiCso28SUARomk4nB1rVY4d32twbHS8lObnBQY62WaMyd4fnIoA5eZfK8ePHdSKymHn++cTUq0+ffbn+5XMHFSQkgaCYke9QWFZtoijHAqBQ7FVork6j0GuCmQBmSBJif9dx9ejxnXndk8M/Y+a6LI5gzmHMGYA5ebfJ/fffL7Zv3y5Xr15tpNPpVWfGug48dvS5pudP7rYUm0IwwErB47DQXMWoLbZgEwxAQgqgrpjRXK3gsVtQYJAiKDbli2deU48debahZbrrgMHG9URkbt++Xc6SiszJe1/mAB/v7XCfAAgisgAgasY/cnKw/etPntjlPdBx0lLEUgJQDDhsJhZXM1bUJZBrl5hF/HLmAxBOCpzqBc4O2REzAAmaxQSr9fNWiw9ecoO5sLThr3Mdvn8GgO28Xd6JO9Vcm3DOAMzJO6z4zFwxnpj68tmRzjsefu1JdIz1Zdp9zFDM0HWFJZVprKhl5HkU3qiyDIYEwUIgLnGqT6J1REfCIBAYBIIC1LzCWvrENXfS4qL6/yzzlmwjosHZmgOAOUMwZwDm5K1W+h3ZlG3L64qfG0fq6paBzof2953Mf+nUHjUZDRAJQaCMajt1xsIKE6tqLeS6FOgC/u8XXQMIJgRODUi0DEkkDAHJGpgMKCU435PLVy5eJ65p3hBbUFLz/zzS9X0iimQiApbYsQN33jkXFcwZgDl5s7w8AaA9e/bQ5s2bzYt+VhxMx24anh7Zdma0s/Llc/vRMtJpWaykFBLEgALB5zSwqJKxpCqFPKcCIPGrhv0YABEhFGecG9TRNqIhlMgcEyKCoSzYhE01ldaIa5ZejpVViweqSir+NQeOnxLRyEXfT170kTxnEOYMwJz8mh7+TgA7duzAli1brJ/7uRvAyvHw9LypeOCfW8a7i/a0HUbrSKeKJRMkBUiAYIEhwMj1MFbVmphXZsBjkwBb2af9qx+5ggJYIJYS6PZLnB4UmAprIBYAKSgiKKXYZbNzc1WTuGLBWiwtbQqU+Ao/X+TObwXQMhsVzMr27dvlnXfeeeEScwZhzgDMCUDbmcWdAH7ew1+k8DUzsVBVJBWripupz4+E/fNOD7bj9GA7+sdHrbgRE0IoEtBhZR2uUwLVhUksqQYqCyzYpfr1HzMpxA2JsRkBl42R7wFGAhLnBiUGpiSSJoNJQCDTVSDF7LA5VHVhpVxW34wlFY2ozi3v80jX/bkenz/H4R4BMEpEgYsvs3v3bm3Tpk08ZxDmDMDvmpcXvyikz/7MC2DZdCpSGk2Ebo2l05smwtNlU/EA9U4OoWuiH/3jwyoUD3PaNISUkjQIWIIBBjRhIc/DaK5QaCgx4XMAKQXoULBrAOi/f9RMwMCUhoOdEmW5CpfNM2CTwEyM0OXX0DVGmAzrsDh7dIjByoJpKdY1XeU6vaK2pJrqS6rRUFSFUlc+F+YWzDg1+89y3AXP59uc49kI4ZcZBCYiNXdS5gzAb1Vov2fPHrlnzx61bdu2C4dbgGCxWh9IhQsmA9Pzkyr9BzPJcEP/zCiGA2PoHx/CRHAaoUTUihpRWGyRgBCCJAQIDAUwQWgMj4NRV2BhXhmjMCcJ09AwFpRgpVCZb8HnEhCYpfv75Y/csgSO9xMOdtpRnKNw1aI0SvMAsIWkKTAWInSOCQxOSUQSGgwFCDCICCqLNVCklIRgl81JPqdHFOYWorG0FlW5ZajJL0OBK2fYbff8p9fmaM/z5E9owD4iMma/w/333y82bdokNm3apOaMwZwBeM96+j2A2LNnD7a9sXjXGExFFk/FZyoT8fSWYDp6effUAPqnh3F+pA/jwUlOGSllmikohuCspdAgMh5cZSr7LBi6BHKcJirzGPPKLBR7GaZi9E5o6PNLeB0Ky+tMFLgJEAzJCvwrogAiYCYK7D1vQ+eIDpedsX5+EourGRpllNxkRiotMRki9Ewo9E/riCYEDFODYs5MGEJCQcGCBcXMGjOIpNI0HU7NJopyC2h+VSPq86vQUFiNPLuvxW53fLfQk9OXZ/d2ElH7BWOQiQywKZMmzBmDOQPw7vb0yCj9z1fsy6NW9Ep/KPSBYDx080jYb2sf70PXRB8Gx4c5koyrhJliKCUEkRACAEkQZ3J3zoI1iRi6ILjsJvLcFqrygYp8C3luhiYUxkIaWoY0jMwQSnNNrK5nVOQZIAgwASITMPwKA8A4Pyawu92JYAwgQWiuNLF+Xhp5TkCxAhGBiGEpQsJgzMQ0jM4Qhqd1TMUYCYORNjQwZ2KU1+0Ng6HACrBgKYJUDs1GHoeTqosqRFN5PeYV16Amt5zdwvGjysKSnR6b5zgR9fyCNGGuZjBnAN51eb24eFCGmZuiRvzG0ZnJeVPJwF3jsan8s/0daBk8D3942oqnU2yxSZJIClC2uy/AirK9OEAKhiYVNJnp5ec4GMV5CiW5jAKPCa8dsAkgYRA6xghnh3RMhSWKcxQ2zDdQVciQsCBYgkn9t0yf6TRwpFfDsT47WAlYUChyK1zZnEJ9MWfCfeLs9+MMTpAlDIsRThFmogLjYYI/QAgnBFKGQMoUMFlBWRLMBBLqAvZcMYPBsMBKI1IOzUnF3gLZXN2E5fULUeEsCOZ6c3dU5JX1eqXzCSLqumAMmLW5qGDOALyjsp23y6I9RRe8PTMXWbCu7JoYaA7FI380mQ7lHe45g7O9bZgMTVqGMqGIZv07iABmAnPWzwuGphHsmgWP3UKuWyDXbSHPrZDvJvicCjYJSCJIoUBQmElKnBvU0DYoEEzpcNksXLHQQHOZlWH8nM36LzjM//q4GQQpLIzMaHi1w4ahKXnhVYKBDQsNrG5MQ8vU/C686+ITxCAoBRiKkDYJkUSmcDgTFwjEGOGYQDQpkDYBy9RgZnKZjNGj7OcphYx5YaULHUW+Qrm8diHWzFuKEnt+IMed863GotqjGrB7tog4GxXMIiLnZM4AvC0FvcnJSZ7t0zPz4kAqcsvg1MjHAmak4WD3KRzvasFo0G+mrBRIWZKEIAENBAYzwQKBSMGuKbhsgMfByHVZKPIyCryEXKcFh66gaYAmMvAdZKv9GSwfIZIQODEo0DokEU1q0IVCU7mJyxcYyHVmwm2QgJkN102LMznArBZn8wEGICWja0ziQJeGlCGzaMHMDEFjiYU1DQY8ToUL/pYykQrPbhEhwGUj2HULAoBigsWApQDTBBKmRCgBTEWA6YhEIE6IJgjxlETazECRRTZlYLIARbCYGQTLodlQnlOiLW9ajLUNy1Ck+Tor8ssfL3blPkxEHbOFw61btwoA1lx6MGcA3irllxd7GsNIvG84On394JT/40Pxce+eMwfRPt5rxZNJFiBJgrNHUYBhQbGAkApOmwWPzvC6CEW5jDKfiVwn4HYy7FJBiAzgJkPaSW8I3TP/bcEwJU736zg+IBBLZiZ0fQ4TG5stzC9OQxJl9ZsQTgLnBjQMBiQEFAQEmAicDeOZBQQxggkgECUQBDI5SeZqNl2h0KVg0wF10YkhZKIXxQyHBJZWp1FTxJBCZX+aNSOc4RkAZ4xf0iJEk4RAXMNEGJgKAsGkRCxFSKUBU2W+nwQDUJn3WopBwvJoLppXWSevWHopqp0lMzWF5U+V+PL3uDTXj15PD3Zrm2nzHG/BnAF4ExV/61bGtm2KmZ1xJD/SNT6wcTQ0eU+rvwd7zh7EeGDCJLBkAkEICM56SGYIqWCzAT4Ho9CjUJJDKM1NI8/DsGsCggABhVmezosX9NLPZe6caR2ic1zHoU4Nk1EJmR3XqSu2sKk5jSK3AquMgjMIiRTQNixxbkTDdJxgWYAg+XoUTyqTn2dz+p+bFAKDsy1/+i+mCCDYdUZ1vonVdQbK8hny9TLG6++/cNQy3zWT/QuABVIGIxAjjIUExkOMqYhENCmRTAsYiiCyHQgwz0YdCgxVllOsbVyyFitql6DIkft0Y3HNUbe0P0REMwBo9+7d8uexFnMyZwD+px5fzQbLkVT0o73TI/9nNDa1eG/HURw8f4LDiYhFBAkiklmlt7L+025TyHEpFPgUynKA8jyFHKcFmwSEEJmQF7PFOcqqO1+k+L/40UyECXvP29A/IS+ok0YClzSlsLI2DaeeaRfOvp0BJE1gZEZH26jA4JSGWIov+Gi+CB3Av/KYvP5TxRkm4SKviYZSxsKyzKCRFL9e9M2YnTRExlAxwWJGypSIxAljIcZoUGIqIhGJCiTMDJmJIAmQCbCAYsUMWF6HR1zauFxcvXQ9iux5HfWFld/02T1fz0Zrc4ZgzgD8z6v6W7duxbZt25QggUAi9Bd902M39wYGN+5uP4STPa1mPB0jEkIKiKzXznhuXVPwOQn53jTK8hjluUCB24JTZwgBZLJ564Ji/vo3nwEIGBZwrE/geK8NSSMDyVVgOHRg8+I0FpWZmYiCL275ZZTNYiCUBLrHNXSMaJiMAoahQdKvvw0wq7JwOxRqCg0sKrNQls9waJy9zm92EGcNoAIjlhYIxgRGZwijQYnpqEQknikkksjgDcAMS1hgBcuh23hV7RLtmuUbUekpOlGZW/qTcm/Rv1mscD+z2ApgrmswZwB+ZYFvB3aILZQp7oXTsU+NBsa2dM4MbX6x9SCOdZ6xUlaKNBJitnzG2bjdaQPyXQqleWlUFQCluQpuG0NQNn6gzLGePeav+1P6tdQuQ9ohMTLDePW8DaPT2oUsncHwuRSubk6jodgCQD9nADg7HShABJgWMB4UaB8T6J3MKBVflPP/KtF0hRKfwvzSNOaVEnwOdZGRUW/KYmHKthnVRQYlmiSMhQhDM4SxgI5AnJFMCyiVQUWCMvfIYkvpQlNrGpdpVy6+DPPyq/fV5JU9nePwfvGirsFcoXDOALxRdu/erV3UzlvVGxz5x/OTfdfubj2M/e3HraSVgCQp6UIInEHW+OxAic9ERaFCVYFCvktB0zIFLGL+lci7/9EjYkBJhRPdOg506UhbBMF0wQDkuxlXLU6jrtD6b40JQUAxIZ4mdPoJx/sEZqL6LNzolxoCAmF+hYlVdQaKvJk2o2R+e7aJZ+9lmoGZiMTIDGFoSmI8LBBNZp6HRMbQMjMsZSm77lSXLlipXb1gHebl1b7QVFL9d0R04Oef9++6/E6TPM6G+5s3bzaZOW84OvGfu9r3XXtitN318qnXzEAyQlKS1DLte1icKXLlu02U5inUFCqU5ZnIdQroQl3w15lZmTdHMRiZSCKeEBgPa0ilCULSRXHEbMGRfi1jwlAAATlOQlmOBpsm/tsKwOw3KfQyij0mdEFgVhdQim+9myIQAw4Cyn0mir1AXZGF0RmBoRnCaDYqUAqQJCGFEGlliL0tB61TnS24atn669ZUN189FPb/pNJb/GdENHHn9u1y+513/s4PH2m/w8p/oa0XToc/dWTgzKfPjHUt3XnsJQxOj1nSpmk6aYDiTHBLFgrcjPJ8hZoiC9V5DJ+DwcTIFKZ/vXLa/zYuno5qmIlli2cqg+6blbRlwrAYijMAI/HfhOMEgrIEpqKEQES8gSVoNhfnbOX9AiYACmMBhWCJQKE32zH4ZcxCmG0kvp6o/MbP66LugiSgwK1Q4GbUFhOGZ4C+KcLojEAwLsAWQUIABBkzYnji8E7r4Plj8uZLrrt7RdnCddPxyNcKXN4vZvemid9lI6D9Diq+2IqtyNJnr+gNjX751Z7jG3ee3I2TPecsAgtd1yRni3skCDlORlWBhYbiNCrzBJx2BTGr8Bc876yqvPnppQVCKMGIJQGCzBidi9Q5aUpEUhYsZuhE/803yOD5Q0nGyIxA2hR4/fwzLFaQIlPcVG8o0An4gxqmohbyPepX+34G0goQZEKKNydKoF+YtRK8Dsb8MoXqAoXhgECvHxieJoTiAiqLarDpmpyMTOE7Lz5qLq9bVHfDik3/0Tczcn1tXvn/JaKT9/P9s2fid84Q0O+Y8l/w+sFk9P+dG+38wms9x1wvnd5vhhJhIbOnNbtTE247o6bAQF2pQlUew2O3QIIygzos3pa7xwAsRTjWq+Fwtw7TzACFLoT02e+wtDKNy+cb8Dgy4fmv/nIK/dM27GmTmAprmDV2mgBKckxUFALJFKNvUkMkSRcRCTFW1ZlY15iG2wZY/F+vQgTEUwR/WKDAp+C1qbfwmL1eVAUELFiIJjM1gj6/RP80EEkRBOsQlEFRWspUPoeHr12+QV4x/9LEooKaf8r35P8dfkejAe13RPFpK7ZS1usv75oa+MqLHQcuf/rwLrT6eyxNCk2T4kI7z64rlOebaCxRqC0ykeuaReLSBQz722U6CYBShESaYVqzUF56o4UAMB4SCMYBl0NdFInQLygBMtIWYTIIBGMyU9AkRo7bREOxQlOZhdJcRsoglOVmJgSHAwTT1ECETJgdIzht/F8LjFnasOmoRM+YgNfBgO2tNo+ZSIwzfQ7kOBjecoXSXKBiUqJzAhgLKCSMzO8upRTRdBxPHHjOOtvf4bxm0fq/7QsM3VzpqfwcEe3Zvn27bG1t5Yu5G+YMwG+H1+dgOn7/wYEz//eVjoPOF06/ZsXTSWHTNMmcUTJBJgq9jPllGYBLkdeCpEyVmfHOdY4UGIaVQfFpWsZbv9FCMEJxwlBAQ6HPgEP+4jSAmAHJiMR0DAc0pE2CrjMqck0sqDRQX8Tw6pn832ZnLK5Ko9An0DUm0D1OCCQIgThhPKKh0GdBF+qCmWEQBCkkTKBvUoM/AljK+i9w5rcqiKWLTAKBkO8ykVttoixfostvoWtMw1REAipDfEo2XXaN9/FQcAJDwfHV1y3duGs6Ffr3AnvOF34+WpwzAO9x5Wfm/P6Zkcf29h295rF9P0XXWJ8FIqkJkUGhkQm3DWgsNjGvjFGZlwHv8AW/+c4nanyBG0D9fH0wUwg0NXSPA3WFFsp8r6MNfr5RpxRjKgpMhhXyPQrzyxkNJRaKfBZ0QYCafYeCIEJ5rkKuk1CaZ6BtlDAyJTA6w2goAuxOhsXiAqLRBDAe1NA9LjIh01uu/L8sKsj+/kQo9jFynBbKfQpt4xIDExoiqUyNgzRJyXQCz57cbXWM9No/uP79fzUUHFtUmVP6aSLyb+ftchYXMmcA3mMh/44dOwQRWVHDuOnsWPdDL3ccKH/2xMtWMBEWUkqZAccoSKlQ5iMsqDBQX2wg1yUyZ5dfV7x3XP+ZISjz5xe/IPMdJ8ISnWMEj0PBZVMZ/D69XuHPrP4TcOrAwnILhXlATQHDZbMydoUBRfRGKDIDLrvCvDILeR4N/TmZ0WVBbwzBiYBIgtA6RJiKChR7zSz68X+GeXxz7WZmfsChAbXFCnleRlmuQseIhvGggKUogyiUSp4f7+GvPP8DdeOqTbduXnjZuqRh/J6D9J1Zohf8toKHtN9C5Z8t5Fgz8fDfnBg+u23H0edwtOuUZbGSmpCZKTYQ3LpCY6nC/AoLlbkmdI2h1OvK8q6p1BJglwwp+I3h/895PtNitI/akeu10FSWhl1YAGsXADLIhullOSaKvIBdy5B88OzEA2WVZnb45iLjIhRQ4jGR78yM+dpktu2Yzb7TBqNzXKJ3SoepAJIEXXvjR71jZwKAYEK+A3BVWij2AG0jOronTMQTBEUETZMUSUbloweesbonBks/uPamZ2bi4b8G8E9EpH5bUwLtt0z5Z0N+70ho8rv7+0/c+cO9T6m+yUFAQIrMwDkEMQp9JhZVWphXaiLHmWkYscIbaKzeLSIFYLcDmqaQMsWFlV0X2vcXlFchlCC09ANuKVHmUbDMTBXeZidozoxf1CSgScriFwhgCRYKylJIhBjplILUCboNsDkATQdAEgoMXTJsF9Z+SLCwoJRC/6SO1kFCMpUxMrrMGK1MpPXOt5sy05EZWrXyfAMeF6PQJ9AyZMIfsmUqBxkYhzzSdUpNTU/iw5tu//vVVYsXMfOniCiyfft2+fP7Gt7rQr9Fyq8RkckJrm8P9z6zp+/Yoh37nzODiZCUAkQsYQGw64yGQgPNVQYq8xm6lgHOv1tvxKwZ6hjRsa9TIhgXme62qTKaJQksGcJk0IwBFUhBS5kociv4bBbYyoTBuo3gyZUoa9RR3iThclO2/59ZA5qIMjpPpdF7zoKRTEPTNEgNcDgJZY0CNYttAAuEpiykkwqaTnB5BOxugYmkDUcHBEZnZqm/LCwst3DtUhM2od6A6383HPkMBQmQMgmDQUL7UGa6Mm4AIgvtsCzmXLdX3bLqGnld88bOqpyiD7tt7mO/bTDi34oIYPfu3RoRmSnmlSeH23ftPLe76Lkze82kaWi6kBeGbnMdJhZUMJorzdfBLOoiWqp3SsUvzjcuou+68I0UwyEsOA0NoYk0KGYCsTTIUFAuHVTqAMdMUHcMIpyCMgnDZlqxYpWZGSAIAdIdmsxtSWHBajuaN7jgy5eZ5aEaYazHxNFdCYT8CqyITRW3pBBSCKLBToHRboaRZsz4LZhphpCA0y1gz5GI2kxM2J1gj4CSAg4pke9RGa4BEtlc/J2rBfz8/SYQLACaxqgrtJDrAHLdAq0jGkJxAWKCJkDhRFg+euBpazoSbLpl1TX7g9HgB3I9ubt+m9KB97QBmC32bd682UwaxvuP9p54dPux591Hus5aCqZmI8DK0lWX5lpYWmOhsYjhslvZAhmyBJfvgjiMZg2BeJ0ZgIBEGBhoM9DdFkN0gEAhEzAYbGbgx0JKiLAJNiwgmIYUGrx5XhQXF4n8/HzhdDozBcKJCXR1dfPMaIrO7ktCOggrrrBD2gmGoTAxbCAwnobb48W8xvlUWVGmDQwMYmBwAMHJMGJBA5alYBpKMUMha1ZtOknSJalcA1TthnTpoFQKcWVgUhPIKwbsDoK4uLL6zicEeB2/KVDgUVhZZ8HnUjgzoMEf0jKThkLBZJbPn9ltjQUm9I9tvG1nKBX9GBE9nCUmfc9PFmrvaeXHDrFlyxZrIjHzrwcHT37uu3t3oH2kWxEJKZBxOjbdRF0BY2mthcoCAzaRWWLxzmv9BWQ7wArplEAqbsEyAM3GcLoFCBKdx6M4/kIS8SDBMBRDwdJsNhQUFAnTMEQoHIYaT1zg6auqquKPfvSjlFeQfyoRTxwRUhhEpDRNWzrpn9j8gx/+QI0MjYjeMyaq50uU1upIpgmJuIJpgstKK+h973tfYN78eU9NTU5ueeyxn3iOHzvOQhB5PF6+7LJ1YuGiZpFMpTA6OoLWllYM9PezmkyTiCtYOmCkFDo7gLETDG++hpJaifolOgrKNUj57sPXMAhuG7CwzESOU+L0gIm+CYJpIosghDzW38KRZBz3XbXlh+PhiepSon/YvXu3xszvaSOgvVeVHwC20BZrIhb857P+zs994/nvW/2BcSGJBHFmiaXdDiwoZSyrMVHsMy+MwgL8Bn7Mt7kefQFUZBkKkYCFyQELo30mQtMGLINgswFl9TrySmwYaGPM+AG3x475zfNp1epVWn1dHfLz83D06DE888zPEJiagZASihV7PB40NDYEmubPv5qIZpg5H4ChaVpkYmLiB5Fo+N4vf+lLVmgCcmLQgbI6wDIVrJSAslgVFhXK0vLSzmXLln2ytbX1Mq/Hu0Apixkm33zLzeL66953xu32/iQcjUrLsgo3XXHFPfv27y/ctfN5jkcTJIghpYZEElYsYNJEvyWGOoDpER1rb3CjsHKWK5ChFGXIPSiblavsPaK3/7koBnQpUFWQgkPX4dQzAKioQZAAdEHUNd6Lr73wsPq9a+76e3942ijxFfzr/Xy/YOb3bJtQe48qPwGgyWjgS8dHWj77lee/Z/gjM5rIPgUmgtthYkmVwuLqJHIdMjsWclFm/Y49LgUCIxKU6DqdQM+JFKb9FlJRhmUymxYpKSGGO03y5KeQimhgWLx8+Qq6/Y47RiqrKx/OKyhsz/P5xt1uz4/b2toKD/sPKZuUAgzEE3EKhEJuAKmenp4bT5069Ww6nTa6urqW5Ofn/9/KyqqrvZ7csmg4zAODBpUEHFBxE4lEhoTTZtdhs9k0ZtYOHTokE4k4lFKqrLRcNjcvbpk3f/4NACbKgbUAzgJ4JC8377n21ra8trY2kNSprqEeV199tezr68XJkycx4Z/CQJuF8nlJFFW6YaQUZsYVAhMGnF4bCksFnG6AtFn+wXeuEk6QKPKZWN1owe2UODdgRziR6RwJKWhgZgRff+6H1n3X3PMvo9EpRxkK/m7Hjh2Cmd+TC0vEe035d+zYIYQQaiTq375/6ORnH3juu6Y/PK1LgAgKgEK+28S6BhOrak3kOcU7oO10URs9U4PIFMQIIIHYDOH0K3Ec25nAULuF6LSwXK5ctWzZSrr22qtlTXUtJaMS/kEgEkxDKaXcLjdyfDldDfUN/y8Zix07ceLE/pKSkr9etnSpcjldnOnzg6KRKOu6ZgNQGIvFKnft2oVnnnlGT6VSq4hoPBaNvlxcWkxm0rT6hwjPn9HxymkdwxMEIgGHwwmn08lCCDORSHAqnQYz88KFC8nlcvVomjZ6/vz5jldeeeXAyZMnzxHRUQJ2VFRUCNM0LZtN5w0bLudNmzd/7e57PrznzjvuhK5rSlkMIyFgpiS6TqTxyo+j2L89iVcfjuClH0dxZm8KEwMWkjHOYBbeIV0izihFnouxosbEJfOSKPRamfFrVhCQNB6elN/c9UPj8NCZbcPB0W9s2bLF2oM9kpnfc1017b2k/ACwZcsWa3jG/50jwy23PfT8D4yZWFiXgsDZYl+xl7G8zsKCEgsOG19YvPH2xZWZAJ+ydicVB+JRC0oJOL0Kmk2gv8NEx5E0YiGgqLTEuubaa+WaNWtgs9n7HQ77Kf+4/9YnHn8cx48fZwVBRIRQOITp6Wm5e/duLZlMnsvJyfmnwsLCf1y6bNk3C4uex/DwMISUSCTiLEkSgEIhRDKZSLJ/bBzJZDL/fr5f8LPk97jdAAukkwR/RAMFLWgxhhAEh90Bu90ulFJi5zM7KZVMQgjBlZWVyM/PHzBNs+KFXbsavv3Qt6w/+dM/rWXmP+5ob796ZmYGRCTdLhdWrlpJtbW19w8PDN9us9s3JRIpVVAiRX6phqnRBM7tT2G014KEzqZlWFNjKTnSlaL8UonSOh2NS+worReQDs4wLL9eKcVbHr5lh62ICS4bsLAcEELhbB/BH8lAxyURZqJB/ZvPPWya193zqYGpkUgNVXz+vVgT0N4ryr8DO8SduFOOR0NfPjJ44pMP7vq+GYhHdEGZJRREQKlPYWWDhcYSA3ZxMUvO26X8mUMqWCGdBGYmLPSfszDSlYRpStQsFKhd5MRgRxqhQBoF+SX8p3/653LhogV/V15evh+AklK+HAwGfz8UCn79/PnzIhKJEBEhFoshEo3QB275gHXo0KGAz+f7AIB/KSste2L+/Pm3Dw4OWkIIaRgmkokEAFg2p03L8fnQ090Nu93+e9to29d2PbfLdLlcWRYhlVk6ohTYVBBCsNfnhdS0fgAOi63yeDwOm26joqIieL3eAAAViURgGCadOnVKFRYXPTDQP4DJyUkQEVfX1AivxzMIIDQwPLTszJkzUJaFnAIdnlxCf4uJ0R4DNpsTK1Yuo+rKKu3gwcOY8Put8R4lJwbSGO40sO5GL+qXaSCZHW+ebdW+be3EzLWcWgY27dQ0HOshjIUZUBIkgOlEWPvWiz82+Zq7PjcS8icrckr+hpnleykdeE8YgBM4oW2hLcZIaOqBczPdn/7Grh8YM/GwTiRggSChUJZrYU1DGvVFDE28Exj+THExlVIIjTL6Wi10n0lhZsxAOsVQbCDg1xAYZ0wOK6RTpqpvqBPzGmu/VVFR8Tft7e33uFyuH7acO3fI5/NtOHL48L3Lli1bv3v3bkvTNAoEAlDKqgLgATBts9mW9Pf2/3Ftfe2fLFy08LYjR46IaDTKSilEo1EgnVZ2zR4tKimm3Lw8gMilaRo8XveSUCgECBBlyUPYZJCVoSt3u1yw6XoEQCqZSErDMCA1CYfDAU3T5Pj4eLi5eXHsknWXul966SXzpZdfglKKQsEQ6bqulixdSprU/xlAhaaJT7S1trHdqcu8Uh2RGYWe0ybMlOKlaxfRli13RaoqKr55+eUbPtvX3+988aVX0NvVg4n+OPrbEiitcwEkEJ42oemEnAINdjcuogd6i+cMOcO3YBNAQ5EJqTGOdEuMzmjZzQaMQGxG+97LPzHt19v+eig45iaiv3gvRQLvegNwnI/rq2m1MRkMfvrMdOdnvvLst9MT8ZBNkgQpApGF8nwLK+sVat4h5WdkOOtiQQsdx0x0nUphaiSFWITZ5Xar2toyEQqFORgKia4z6UwpkMGCBNKmmrn//vvF5OTk+1966SVRXla+vre39z+HBwarhRAgIkFEHIvFEI1GJQCzsLAQ3d3d7HK51tXW1z64YNEiKi8vR0dHB1uWxclkEpOh0PyioqKDq1atUsuXLxdej+dTQ0NDf3ns6NGbenq6LWnXJds1KGIICyALgBRwOp1wOBwRAHVSk3oymWTLsmhwcBDLVixfVFtbGwsEAkvu+uBdzzbNb1r0yI9+bI2OjgrLsuDxeKhpfhOVV5Yf9vv9NwQCAefk5LTp8rLm9EoMnTcwMZyG2+tRmzdtlkuXLv2X3Nzcf2Dm7y5dvuLa/ILCf//ed79r6+rsRjoBGu+10NeWxFhfEna7hppmOxqWSeSXSWh6Brr9lj7Xi3YrSAlU5wGqQeEE0hietmf+nYDJSEh+64VHjT+54WN/7g9NjpXkFP37cT6uAzDmDMBvFvprRGRMRaMfO+tv+/I3XvyhOR6e0cXshBsplOcZWNNgoa5QQYq3EXd+IRLNVIhTBtB2KI0TryaQiAjY7R4sXlxPmzdfIVeuXImWlhb6wQ9+wMFgkIQgaFLKsfFxxOPxv9y6des/HDt2bMTldPFLL76YCszMfLytrQ29vb3QNI0AcCwWQyqVtgEwfT7fT/v6+j5XW1t7JYCakuLi7y1dtuxj3d3dSinF0VgUhmGUuVyuIWauzn7jsY6OjmdOnz4Ny7LE7FIQVpzZ9MEMQQSP1wuXy9USj8eXaVKTyWTSJCLthRde4JKy0ltGR0e/RjbbDxsaGtYWFRV9Kjcn59+/+B9f5NHRURQWFori4uJJt9vd39PTc31raysnk0nkl2lggzHQbiKVUuqydWtEc3PzaE5Ozj/OzMz868jIyMnKysqv/fTpp/8lnTaIBLFgDe1H0uhrTSGdzGw28A9HabxXx6J1LlQ2AU4vsluD3vqHziDoQqGukCGhQSkDo0GRWa0mQOPhKe3BFx81P3P9x/9tKh6aLKScH8zC0+e6AP8L2c0ZeG8oFLr0/FT3dx/e84TePzks5eyyWwaKcxVWNjDqsp7/bcn2sxA9Eplyn2UwzBSQiFkY6mKEpghen48/cMst6nP/9/+M3nDDjZ9YunzZ0nWXrXvyhhtvIClgsWJIKTEzPa062js4GgjUNzQ0HLvhxhuoefFix9DwEGx2GwoKCljTNCYiEYvFTI/HU2RZ1u0lJSX/GI1GIYRwBYPB2+rq6v6meXEz+XJ8wjRNxKIxxONxi5llX1/fZX19fX80PDx8a0Vdxaqrrrr6zPyFC2EZpkLEBMUV2GKwYbHT4dSikWjK5XI9bhjG/FgsBtM0YbPZEAwG+Tvf+jY9/MOHP9Pbcf7I4ODg+pycnP+orav7t/r6elKWlV64cCEJIf6WiALxWOzKlrPnSGQhl1PjFgJ+g4uLi2nD5etpydIlW/r7+7fl5eV9zrKsBmZeMjU9bZuYmFB2h4bpsTRGetIwUhL1jfWYN6+eOK2rnrMm9uyI4ugLcUyPZUIAkiLDdsxvDcrwAtUrEaQgVBcy1jZZKMs1M5dkghSC+qaH5Pde/YnVMdb9vUAsfAcRmcws5yKA/6FsZ5abicxkMjm/PdD38GNHn8HZ0U5FkiQzYIFR7rOwut5CXZGCJOtto6gmjWGkCKEJC0G/QmRGZToQUiAWNgFYXFhQgPUbNowsXrz4ciIa6O3tramrr//jK67YdPWZ02c8LS0tLKWkWCzG7W1tYs3aNQ/V19evGx0dHbvv9+97IJlM5ivDzJ2emfE+8cQTOHjwIAAgkYjT9PT02uLi4qc2bNgQKigokJZlvQYgWF9fH6+uqnadnT7N8Xgc6XQ6ASAnkUhs37VrF6666ipUVlZ68gvyH7/kkkuWtZxpsciwBCUtkMlgpWCz2QBwUtO06fHx8fp4LIZ0Ok1r167F4sWLxejoKPa8+mrS7/c77txy573333//y5FI5Khu02Gz23nhwoWcl5c3zswrD+w/cFV3d7ey2YVMJwlj/SkYpsWrVq6kRc3NJ4nowOHDh7+TTqetpqam/UNDQ/8YDoW0RCJh2R2amBo3YZmEsrISvueee6iwoFDt379f7H51NwLT0zi3L45UQmDpRhtySxhOlwSRyoCL3iIvQNlNpzoxavIVjHqC2WPAH8wsatGFpI7xLnr00E587Irbf8zMLUTU8W7mGnzXGQBmJgIUM+d2Tvb95/OtexsPdZ6wSEBSduFmvtfC8lqFphITGs2Od7yFxT3OrKOylELUDwy0p9B1Kg3/gIl4xGKGIodDg7IEpCZpbGzcioTDVbFYrLmvr89ZVFjUOjg4uLSsvOyj995771P/8I//aIZDIU0pJU+cOKE2bLz80ng8/jmXy/VvAJbPpj8AVkcikZ+Mj49XnT17lgOBIOLxeAURpZh5HoBcAGEiio6MjPz7qlWr/rq9rd0KRyJ6KpUiAJiZmVF7du+2ysrK5MKFC2s9Ho+Zn58PITMThSJlZqjPLQVd1+Fyu2GappwYHy+Ix+NgZuva666V77vuui9P+Ccu/fa3v712YGiQk6mktm3bNnXHbXfcGQgE4fP55IIFC6iysrKrp6fn0r6+PqRSaSWEEImwhVSKuay0nNZdto7mz59//fDg4BcGh4cXdHd39zY1NZ0YHR1t6O7uATOTUgRWgJDC3LJli7Z8+fIHqqqqHqqorPi7urq6Wx758cM0Ojouek6mEJkyUVKnoW6pDUWVDLtDe0u7hCwyPAg2odBYxFAWcKSLMRUXENAgQOJw/2mV5/XaPHbXLmZevnXr1jAz07uxKCjebcq/B3skA9pgcPzHBwdOX/b0oRdNgCSxBJjgcRFW1BDmlyvM7rR4a9Sf39AbTsaBgRaFA08nsP+pGPrOpjgRhsrPLaL83GIYKcFGWmWYcSIRbm/vQF9f36VlZWUiEAwIwzB+WlJS8mpVTfU/3HHHHZoQwhRCYHJyUvz44R+pIwcP/+u+11579YUXXvj2kSNHvtPT0/OXRHR4YfOikxs2Xk5CShUKhRCNRsuICAO9A+87efzEjs6O8539/f1XlZeX/3DlqlXky/HJcCiEVDxlAdCcDqew2+zSMkwRmJoq1zSNXU4ndCmgFAFGRtnYAuwOOxx2OwEQyXTaTCQScLlclJubC6fb/WMiUnaHQ8yb10Qup6uHmcuUsrZMTkyoeU3zdJvDfhJAazQSubnl3DkwK2IG0mlAkFAbr9iIhYsWvgRA9PT2fXawfwA1NTUBAMbI0HD5yPAwhBAEJiilrA0bNmgLmxftr66u/lMiaq+urv74ylUrhxoamgQzcyLG6G9L84kXI9jzWASt+xihiUxdg+mtYXHMAIUyn61LoLFEYUUdIdcuYCEzWSrA4uWzr1m7u4/U9E2N/Oe2bdvUCZzQ5iKA/67dd+KEtnn1ZmMkOPX/2oN9Nzy8+0nDEErXWICZ4bQpLK4ysLBMwSHNtzjsz67eZCAwbaLruIHuYylMDlswDVa1dTVi3foNVF1TA01InDx5kvbu3cvxeJyISBw9egTLVyy/3m63fz0UCnWEQqEFzPxAY2Pj/Yl44lNnTp/OP378OCulqKOjQ/z9P/y9Ki8v32yz2TZ7fT7ccccdCAQC/tzc3C+UlZVdX5Cfr0UiEYSj4ZQUAqFQ4MGnnnrKres6rn7fdR+vra39cOf5zoN1dXWXJRJJCLuoJKKJUydPht1utzedTsPmdCZ8Pl/M6/NB03WYygLSJsi0oCyLvV4v2ez2MSIyent7q6PRKBx2u8wk2BiSmv7UZevWrXV53Kq4rOwcEtCHhoY4lUpZzc3NQgjxAIBypfiG9rZ2FkJIIoJpmlxbWysuW3cZNTQ03DUyPLKtt7u7OG0YVn19/ZcAVAaCAad/3K80TSOlLJQUl/CNN9yo6uqqtg4MDNxss9nOplIpx9DQUO358x1KKSXsDhsXF5XS5OQUj/VEEZtO0OSgxIJ1DpQ3ahkik7cUBUpwaApNpQYSKcbJASCWziwlMaDkjkPPmmV5RbeOBvz/UE4lf7Wbd2ub6d3FJaC9i7y/JCJjJjxzedv0wBe+/cKjZigV0zQiWMSwSca8MgOLK0x47LNd2LeQc5YZJAnhKYUzL6XQeiSNZFTB6/Ni7SWXiGuvvTbd3Lz4ey6X6288Hk/pwkULf+xwOhfvfPZZlU6nRWdnp5VKplbHYrHlDofjM2fPnH1R07WPBWPBr5aWld655a67Xu3r7zNnpmc0pRSISLjdbhQVFuLMmTPJJ554QvN5PX+Rl5f37X379vU1NjYuCAWDiIaj0jBNcfLEyZmxsTFnZ1eXumLTpsuZuR6G8Zdr16597eDBg4qY/nBmZmZpR3u7g4ior68Pne3nvyF1Kc93dEAKKQErkwYYDEGC0uk04rF4ZX9f/0/GxkYXTE1NIRaPq0gkoqUSqT+orK78W2b+HoA4ESVGRsb+9nxnJylL8aKFi1BUVDQ8PT29OhwKIRCYMUzTtEkpYbPZ1KZNm2RdXf2Ptm7dGvrQBz/kqGuoRywWn3C5XD8eHhp6OhgM2qKxqOl2uzUiMm+9/VaturrqwXRa6aWlpT8NBAL3+P3+plMnT2JiYoKlpvHlG6+gyzdcjr6+Ptr32h70Dwyg9bBCYNLAJTc6ULfY+RbjBDKFQZcdWFRlIpYGWodsSLMJQRKBeFT++LWnzfyrvH8+FQucKqS8x99trELau0T5RTbvL2sd7/7e40eec/VODikhiBQLSLZQXaiwvNZEvvN1lX9rOWcZEISpYYWuEwmkohqqa6rUTe//AK1fv/7ovHnzPgQgAOC+ycnJHzXNn7/+pvffdHZsdLTy0KFDrCzFR48c4dy83BtXrVr1jZmZGbnz2WcNXdMfWrFixea6+rr/+MhHPvIXX/3KV02llFy2bBl94pOfHCopKQk9/dRTi3c9/7waGR2rZGb7yMjIucbGxgWHDx8GFNcA0HLz86yi4iJx6tQpPnf2bLXb5doXDIXiExMTCARmaPerr5Z1d3Xd2dHRgcnJCUxNTSIWjy0x0gb6BwaQTqWInIBmF4DFkBqR3+/Hrl273H29vVvGx8cxODgIwzDkzp07mZn/6uTJE+v7+/uHvF5v78jw8IrW1nN3HDiwnwsK8vWi4qJEYWHh2bGxsdqc3Jz43ffc4zp27Bi3t7ejsbFRrL1kbbqiquJPt23bpv7+7//+903TfDAYDDIzi0P7Dy7o6eqBruvCNE21/vIN2pKlS8/WNzb+dUdHx0gymYwVFBTYOs+f/8vDhw7DNE26fONG2rJly/SKFStu9PvH1q9YseJfdmzfLo6fOCZGe9PoPmOieqEFKd+6KPF15L9CjouwrMZCImWg069nWJiEoG7/sHj8xAsOz2XOrzLzvq1bt07ef//94t2yd0B7Fyj/LB0G9QZGvv3awKmGAz2nTBLQBDMUmSjxGVhWzSj28NtK1knIsOCk0xpMy7SWLl0qN2++8lxtbc2lzKwfO3bshFJqidPp/FBRUdGlDQ0Nd9x8883Hent72e/3yxMnTuCqa66+D8C3fD7fM4uam9/v8/lWB4PBeTU1Nf8UDUdvuOSSSxbu3r3bcDqdus/raysoLLh+cfPiw7U1NWt9ubmniSg1MzMz4/N5EQ6FzUQiUTs4OLh3YKC/JBQKQ9d1ueuFXdzW1loejUYxNDiMVCpFTz/1NFumaSmGplQmohkZGlNEgCalIAlwnhMoskFFTLBbQyQYwrHDR3DowEFTSCkJRCBJZ06fxcjIsG3J0mXX1tRUIzcnB8FAEHv27uWx0TEsW7YMbrfHBOArLy8/zsyV9Q0N32laMP+2L/3HF42rrrxKnz9v3mcBpLu6ur4HoM7v93+rtLT0EWaun56ZrhgdGwURUU5OjnXttdeaixYt+qe+vr77hRC2ycnJ7xUWFkbb29vt/f395qJFi7R77rk70dDQcDMRHQFwJB6NutKp1N+dPHnCZIM0I0lZyrQ3kpLSWzBuKEBgxSjymlhazQglgLGQBsGAJkns7zxuNlbUl+Y6876/bdu263fzbm3OAGRlz549cvPmzeZ0ePq+3tDgjU/se9ZgZeqCBBQzvE6FxTUKVQUA0SwvPr3pqj4LLeWLO78M5BQQCisJ8Q6I1rYO1dfXVx2PxzcAaInFYk0PPvhg+srNm1f4PL7v1DXU3Ts4MPjE9Tdcf/sjjzyihoeH1fDgkKOyrHJZWVnZxwsLC19MJpN78/LyTgHAzOTkX952++2Pd3V1oaWlhbt7uq+rrK6svnT9ukuZeQkRnWXmuraW1pvb2zs4kUzIV3e/Suc7Oy7t7OpCf+8glALGx/w0MjyipCbh8kjhzBEgAmm60DRdwuZUEJqGQEKIiInMpJJDA5c5oXJ0sEMHGhk8nQCZDLuC5tEYOR4F0hjhKYXJST8/t3OXYlYMWCCSZNN1qWkSfr+ff/azp7wVldWv7d6929/Z2dlTUFBQEIvFeNGiRVrzkuaR3IKCb/X09DzU3tr2sbPnzuGqq69axczbQ6HQH8YTcc/kxKQppcQtt9yi1dbWPubxeLrOnDnzqFIqsXjx4nPtLW1fPLj/IJeXl/NHPnpvfP6CBX9CRLeMj49HS0pKOs93dFxx6NAhJBIp4ckFSqolSM+cFylEdjiLoFiBWWRhvurNihUzG4yJUJYPLKsBEt0WgjEtk6gSaT89/LxZl1/xPn/A//kSKvnXdwut2DtqALL9fivJvODscNuXv/fKE2YgEdUy030W7DphYblCU2mmBjCrmm924J+d34OVUrAYkDYBKQhKAfkVEvNXOxCaNKmnu4d3796dU1hY8MMlS5bUT05OfuP666//s6eeeDLV0ND44amZmZcL8vLu3rBx4/TZs2ddJ44d50OHDqG+seEDFdUVPwawampq6pqRkZGdhmH484uKPtHf3//Vj33sY3/+la98xdi7d69eUVWxyzTNf41EIvrAwMA/vPLKK+sO7j9QcPbsWTYMgw4eOAjTUJYmpdTsQE4RwZtrg9PlEO68zP87vRqkzGz9sTkA6ZSYSjpwqh+IJCSU1DNs4Rn2dMBOUHUuoMIBSjNgKXhyLKxoVCjwKowPpTDeq1No2pLxMGCkMtBYAAhNGRgeHKJHf/QocgpyKurrGysWLVy0MicnBy0tLVbzomZZW1fXxsw379+//yMPPvhg2uP1yjVr1wQAYHJycnlfbx+isSjWrl0rL7nkkuCiRYu2nThx4vmRkRFVVFT0E5/Pp9o72qvCkbBx33336atXr/663+//UGNj45WWZf1kYHjgxqGhoat37XrelLrSKhc40LBUBxGQThGmRy0EJ0zYXYSSSg3eXIaVdST05h4k2DVCY2kKobiOkwOEdFqDEEAgHpI7Dj5rlVxX8DdpTh8EcPDdgA94xwzAhYULIB6fHvmPVzoOujrGey1JmdWXQjAq8y00V5hw66+vmHozcf6MDKzQSgFD5w0MnTdhpgnFtYSGpTY4PBK6nVC/xAZ/vw1th1Ni75491qJFC+r8k/6/LCoq+vOzZ8/edvrkqepnn3kGhUWFf16Ql/ej6qqqD955553P9vb08tmzZ9WdqdSdzLwhEolMnT9//tnnn3/edumll8Lv9+8rLi7+XDwWu379+vUL9+/bp3RdX7BixbL/jCeS6O3txckTJzDQP8CWaZHTLeH0MBxOTXoLJXxFhKJKifxiGxwuwOEm6A6G1LM04ZRZ/e0PA33nCTOkwbJnduQRZ/f/SAGo7F21C5BDwGQg4LQwYzNRVWJhUTmjYYlALKwQj2QmHYXGYAsY7ZEY61UIjTNCMyF19NAxHD9ygh1OOzmdDhkORaDZtGvKysquPnz4MI2NjVk3rlwp6+vr/URk7t29N39gYAAej0fddNNNWn193bapqakb29vba70eDxYvXpw8d+7cX55rOaduufVWfc3aNUdCodDVlmUt7uzs/GlTU5O/t7d3+49/9IgZjydEUaXAwrUacoolVFKg72wKZ/fFMTOqYHMJNCy1Y9lGO3JKKTtL8CaPGLOCSxdYUKEQjFroGMuSjEqNWsa6eHfXUbdPc/31wrKG63bv3q3h51c9/a4YgNnQfyI89ckT/vYbXjy9x2SwlkFxSRR4LSypYhR6LCBb8X8zfT9nc8F0nNB9KoWTuxPwDyiwycgpkYgGFRZfZocnh5BTKDB/rRNTw4SxvjA9++xzXF5R9gVm/qFhGLdfd911x7/10EPGmdNnlvl8vr+rr6//q/7+/ueuvPqqG3Y+82yqva3dnpObWzd//vxpy7JsL73wYrrl3Dn56c985uvFxcWWJjW9qKgIkUiYfvr00+r553cqI51GIpEmTRPCm+Og8kYNtc12ePIIDifBnSfh9hLsTsqMzKoMkQZzloxEKSgwZuLAqR4bev0SppoFfnA2w8n0UmaJSoDMZxCAWELg3ABDJ6C5QoPDxXB6JYhm2ZUISimUN2oITzICfoWAXxfToxYCEwqRGUY0EsPJUyfR09uFwoJCmgkEWdMkFRYWIi8nN8XM1U898WRNT08Pf+CWD9jmzW96oaCo6MFXXno51t/bpzZs2HC+qKjo8J49ez6yqLlZbNq86XAimbSdO3ducW1t7dSSJUv+tKur6wuHDx6sb2s9Z7k9QsxbaUfVfDss00LPWQNHdsUxOWjBsiyFGUnxYIJSSYUVVzpRUJatPkG+yXElo8ijsKiKMBkHpsIMwQKCSHv2+MvmvPzqTaOB8T8vzyv94judCrwjOOXt27fLm266yWLm+vapvme/s3u7GJrxS40EKTBcusLKGhMLyg1o8q2Z9CAwjCTj/JE0Trwcw9Qww6ZJy+lwcDRsiJlxA5qUyC3XoNsJHh/BTDMmhi0aHppQ+bk59sKi4hUlJSX//E//9E9rDMNYsOv5543mxYvXfeMb3zhSWFj4VafT+ZdtrW00MTEhli5b5igvL3/gM5/5TEkgELz0zOnTHA1HbOlU6ta9e/fm79u3D/FEnIQECc0UdocQBRU2UbfYQQsusWPx5TbUL9FRXKkhp0jC6SbIzOgjWKlMpYsyMNgsPB2BuIazAxIdIzpSlswOUbzxXmbWdxHeSGaT2fQbNyXCScBhI+S6LUhklqgqld2gxIC0EXx5EkXVAmWNGspqbSipFSiqlHDlZK4ZC6cxNTHD6XSadN1G6XQa0Wi0PBqJ3nf23NnceDSGm97//tE1a9de3dnZueOnTz/dUF9fL9atu+zJsfGxVbm5uUtra2t7fD5f9JVXXllbXFyM4uLi3wNg7+vr+8rXvvY1wzDTWs0CG1Zd64IrhzDUrnD4uRjG+kwU5OVh/YbLyel00djYJIcnFCWjjLxCCbdPgumt2FxAcDksMDMmI4S0KSAgkEynKBgPy0U1TRsf+o+vP7d169bxPXv20LZt2/h3KgJgZhoKjH/1YM9p5/mxHosEiKFAglBZmEJ9KUHXAWb5prb7ZmHDRorReczAyd1JTI0wPN4c3HnXHTLHm4tHHnkE/olRnNmfgrADzZfZ4fQw6pbYMNZnovt0Sr744svmwkXNV05NTf1RQUHBB9atv2zizNmzOS/uekEvLCz8pwULFlxaVlZ2/+YrN2/bvn2HkUomr2fmKycnJ4+vXbMGB/Yd4BMnT+J8ZwcHAyFYhkWefEL9EjvKajVoDoI7l5BbRPDkikw7iwmmeVEiNKvtF5pRDJFdXR6OE072EjrHdCRMBl0Ylvk1i6KUgVkFohpO9Zsg1tFUrmDXFFgJZGpqCsQMpQBYmbjAVwj4ijRUNhEalgtMj2rw9zGmhg2aGLEQGLXQ0XYeQ0ODWmlZqS8UDFuLly6WLre7H8DHOzs7rxsdG1Wbr7pyori0+LFob/TrZWVlz9lttr6XX375M+FQCMWrVh2rra0923Ku5blHH3nEikcSWm6JQNNqG/JLMzMZ3adT8A9YyPHm4sorr+Jr3/e+UxOT/uYnHn/Cfvz4MdVzigVbCquvd6CwygbmDLMEYL0pDocB2DWBeSUW/GGJ8yMZdipdE9Q23GUe6jnl9ErXX27btu2uTVs3vWOpwNtuAGY3rsbT8XXnJ/tueP7kbmWwkhoIFgGFHoWlVYQ8V3a55VtQ8ouHFdoPK5zdl8KM34Tb7VF33HG7uOaaa3/mcXniUooPPvzwj6zJ8XF59tUUpAAWXuJAQTmw8FINwQkDYwMB8czPnuH8goL/b926dT+on1d/3+133P7E177y1cTylStW5eTk/F5FRcVXFi9Z8jfP7XxOe+7Zncrn9f1sYGDAfO211wChtHTKRCCVIG+uhoomJ+qW2lDRSPAVSkiZaS8pBagLBIP8c8uwL1JYAIIFmEyMBWw4MwicH9WRMiXEb1JnYmAipOFIj4FgUsPSShN5ThMWZPZ6F7H1YPYYCwgBeHMZnnw7yhsUYkE7AhMp9LdaGOm0MO0PoaM9zFIKeerkWeg22/rent71z+581lizZo1eVVX1IhHtYeZNgUDgfQcPHvrmgf0HjDvuvCNeX1//sc7u7huOHT9afeLECdPmBDWtcKJ2sQ5NE0gngEjAhGWBPT43FjU3W8tXLL8uGAluyPHlPiyFdB0/fkJ1nbGEwSmsvYZQUi2ANzHaJABCEXJdFporLAQjhPGgDoChBGk/O/6y2VBSc9t0JPjBAsp97J1KBbS32+tvxVZmZmf7WM/3fnbqFfZHJ6GRBgXApVlYWGqiIt+CJH6TG36ZldeJMOPc/hTO7E0hNgMIoVtCCpWfny+8Xm9LSUnJX42OjAy53e7Pfe2rD6QDkxHbqVdTEBph4SVOVC2wYbJPITSdFCdPnrSWLl9aVlxc/J3Gxsa7ent7n779jjtuKSwogKmUW0oZPH3qtKXpujx6/BjiyYR7ZHgUvb09EATkl0nULrKjpFpD+TyJvBItw0OvGJZJ2Up1dub9l/5Os8rHMNjC0LQNp/okBqYEDKVBCOs32nSa2R1ImI7qON0HpE3C0iqBfLeZNSw/R7pKF9fDMoU2aWfklhLySm0oqQUmlhqYGNTg7zfJP2AiODmBV154Cfv3vKYspejuu+9GQ0NdNwCk0+mq1pbWHz6+Y4e65ZZbRFV19d+m02n4x8b+7fEdO0yw0krqCPPXSDi8AooVNA1wODVIYdHU5JTV3tZG8xcueKmpqWlFLBb7yH333feUpms4duwY959KkVCMdTd6UFjNsDgbRb0JjkdRJkqrzleYKFMIxBWSBiChMBmbphdb98tie95fMPOOrXv20O9CBCC20Tbrj8J/9pHWqb75pwbaLAJJZMt8ZbkK80sYdpHZOfuLvd3/5hQTmBjpNNBxNIUzryURnlYoKizErbfeJoeGh+X27dvNsvLy/y84ExzJzc/9/ODgYNnvf+pTH/7KA181AhMx/eTuGKQusGCtDY2rHRgbUuhrMeSzP33GbGpacFskErnb4/Hclkwmf+h2u8NVFRXfjgSDf/DKq7v1wPS0FY5E5IF9+9m0TLi9giqabFi41o6qBTocboKUBFYqc2hmF1X+N787sQCTAUAhadpxfozQMqjDH5KwmCGh8KYQ1RJDgpAwCW2DCrEEsKpeoDzHzKwN5l/SUMtuGyamDAUSCG4voW6hDRX1QCykMN5n4fzxNIY6UojHo0LX7GLXzufY6/Z8LjA9Pe/k8RNXPvboo1xaVirqG+r3V1RU/KDrfOehp554kicnpoUvj7BorQf5FRpIZLgZ/AOMwJiRAXMKwaVlZZrT6TwAAG63++lgMHjNxz7+sZ8wW7nHjp5Ef6tBDm8UK65yI7/0wsLzN8EIcLYDANQXM0aDFrr9maE2jTR5uO2kedm8lasrAkWf3rZ589feiVkB7W30/iK7ubfk6MC5B545/qKKp+JCSgHFJnKcQHOlQp7HzFak39ywn4iRDAOdJw0EJxVKS4twz4c/ig0b1u+YGB9f4HA4lnztq19N/f4f/P7X/X5/vLi4+F6NpJb+lPHBhx78hjkzktZOvBSHIAsNy+2Yv9aOwISCf8wvnntupygsLNi6fPnyZxYtWvQRZnZMTkx86djRY596+OGHVTwWl3YHw+0TVFTpRu1yibJ6DXmFGoSuAEVZKmz6H+gkQZEJBUIwZkPLkET7qEQo+XqLj39hzHBh8dj/vGXKQNzU0OUXiKQUllcTaooV3Dr/krkMvtgSzJ4DEBNsdsBeKuEtIBRVaxho09BxJI2JQRPHjh+hiakJ99o1l97T2dWJcChkfeieu8P1DQ2fGBkZ+cypU6fmHTl6yNTtrNUts6NuiQ7dpmCkgf4WA0dfSME/BDhcTvPue+7R1l56yc6qqqo/mpiY2Ojz+e6Nm+ZXyysqW+bPX7jx+LFTViolZMexFFJxxpprvCiu4Wxh8E3qDiig0GNgYblCIKxhKgZIQUgpQz595CVVfU3Z36U49ZoNtpa3GxvwthmAHTt2EDPTaGjyL44Otzo6xrotQVIwZ0AldcUWqgqsrO6/ecE/Z5sdbFqwkkA6YYEVoOt2tWTJYlFVVfUvVVVV4x6vb3csFpv3rW9/O3nffff955o1a86UVZbfe9n69SXJVGrz97//XWNqOK6ffImh6QLV8zVMDWhIRHRx6NB+a/Hi5nmF+YUPhsPh7W1tLT/e99o+9zM/e5b9E5PC7WEsvNSB6gU25JYScos06Hp2ky4yM+bMFylmdqc2QTBlOa8y462UrYtYULBMw9K1iaDA2RGB7nEN6TRBiGz0wPxfYnKFDAAm08QTv9bdYyYmMDKrbywIIsAChmYkoinCTDST4/rcFgThwvoVQEExs4CkC5uYZp8HZaIGtgCSGaSlt8DG+aVEbYc1DLQk0d3VzX09faxYqZtuer/m9XqP2e32hr6+vm2PPvaIMg1Tq6h3YMFaJ1y5gGkKDHYaOP5iCv4+EzabzfjQh+7Wr7zqygNN85pumpmZube9re17bW1toqam9pPTM9PGvtf2AbCETddMM6lrPSdN6HoUa9/nRm6ZyFLKvxnZpwUBicoioDqQRmhQh7IyC1s7R3usM/7zufl2731NpXWf3c1vLzZAe5u9f1H3eN+fv9Z6iA3LElJKWAzkOwjzyxhu25vdjlFgS8I/nMJol4l4kMEWQdMkJicm6ZEf/Yg/8YlPvlBVWr2usKjwli0fvGvns88+WzszMwPTNJuJ6BQz33jllZtfTadSl37ve/9pTo5AO/ZiCutukGha5cTEoIXBzrT82dM/4/zc/Lt1Xbt753PP49yZFittxKUnl9B8iRNLN9vhKxTQpA1erQweZy3segEkdFhWHJH0IMKJPphmFCYYHkcJPHopWWQHZWbMAc5QXylOwu11a4PjFhKGDXkOwvKqTLGPLvQI3ui5LNNEJBXDRHQawUQIprIynPv8iysLDMClu7C0ZiG57HYc7z2HUDwKAmAJwGd3o9xTCMFuxOMplHgJOc4EIulJJKwAbHAh31dDChpIcXYpWubDmbM7uLNbkhQLwJ0mp30YOcURlFZLtB2J0/QIk2Vp4vjx43B5nNcGAsFrn3zycR4f8wuHR6LpEh3FtQJKASNdFo6/FMdYrwWSmnnrbbfqV1991ZHGxnmb0un0mlMnTn7r4R88TKdOnTLKK8p1pZQ+PDzMK1eupKuuulJ74cUX+NzZduo+bcKZk8DKK+3w5MxO/v9mEels1OXWCPNKGWMzCqNBQJIAQcnnjr5qLSys+zinUv9JZD/9dkYB2tvl/QFgKDj+xQMDZ2W3f8DShJAWAKdmYUGZQkmOkdkgq+hNG/ixlMTw+TSOvZCAvz+zTdcyM+w+6bRJe/bsZbvNXvChe+451jS/qTmdTt945513/nEgEEgWFhY+n0gkGoLBoFVVXX3l+66//lXTNC790Y9/bPoHDO3YC2GsvNqHxtU2BKcNjI2N0be/8x02jTQHAjOk2UhWLdSweJ0bNYskXLkSOfZaVOZeAWFbgZGIA+NRgsrWO/LsadT6RhGK7UXamEZtweaUoBV6ayAtRqIGTBbQGZzr1miej6NFzuCjWmH+hyJCc1FOinJ0UF2uHSYz+sMWImkrk38TQbBSNklU4pLklXH0+TtwoOMg+meGQWQC/MaiF2eITnlz8zq6dcV10y6bY6bYVzjv6eMvccyIkVPYcM8lH8DqutVIU8bwuG2AQ7NgGR3on36BC7xNXOrd2GUob6UJuAEBmzCSEiKRskSuBSYlAGLBdsGsUWxkRH+umMReu+vyJMobfTh/PI2O4wn4xyfw7E93Yt/e/RwMhAjSRP0SHQ3LBHQdGD1v4ujOJIa7FITQzNtuv127/sYbjzTOm3d5MBhcev78+Vd+8P3v206cPMFEpA8NDYGIzPfdcL12ww03TNY31D9eVlb+6e9+97tWW1uHaN1vkN1BWLLRCaf7QgL0mxVSwRAwUZkr0VBMCMQIaZPBgmgwMMZHh866C/Wc3wfw6Sw+57fDAGzfvl3eeeediplX7+s99uGDXccVgyWThFBAcR6joUTBITNUS785n1t2AScxpoYtnNkTw1AHwEwMwLIsSxCREAKwLIteePFFy+aw59z1wQ/uqa+vX0xEnwKAyfHxWyYnp38SS8TS8xcsuL6quurK666//kQylVr4+OOPW6PdphRaFIvWuVC72I6Oo0mMj4+T1EB5xQINyxxoWu1CSTVD2CWKnItQmfcB9EYW4MkzJiKGiffV56Ixx4FzEwk83jbD5d5GuqYqT62qTgiHlXuGtZpHKIkv/+hor9UbSsuFBQ7+9NpKKnVrPeUe/JtDw7oZGxY/e7hPba7OpeaSXCTAODI5jSc7ZmAowrwCO+oLvCIcTWJ62IRT07GqaC1uWFmAF0/vRH9wACar7BGnLCjIQqG3iNbVL4WmV/oUCfv6htU41nWGWv3dyLHpWFTRhLStEq8NBXB2ykQ0ZaLIbsMdC9agvsBGDs3LaSwqfaY36j49FUWRW8OmiiJ7odPheH4wglZ/EGlIFGjA+po8sam8JpxjP+seFQ673ZHi8gZJnnygoAxoO2xgvD+OkdEI6bqG4hodzZe54c2VGO+xcPSFGEa6AMFk3XTTjdqNN97YvWDBgitCodCKwYGBVx595FHP8ePHlRBCMDMcDodx8/tv1m+6+f3DDY0N77fb7aeDwWDrJ+/75Nce+NKXeGhwjM8dTJA7V2D+GluGWIQvjgT4fxUHAIAmgPpihaGAwuD07B2H9vzxvdba6hWfTMfTPySiQ29XW1B7i0P/LF6FuHOi7xuvdR7jseAka0IDKUDXTNSVMvI9VrbV9Wa4/sxDigUUWg4kMNBGYLZ4/YbLacniJdru3a+ivb3DYlYyC2eVO5/daTldrsZbbrnl2XA4fLfP55ueCYbu2bN3j62trU3/+Cc+/mJVQ/UGn8939c3vf//hZCJZ9cwzP1Mj3SxsjgTmrXTC6ZXw95nw5BBqlmqomq/D483suHNrJagpuBUdMwvxTwf86Agm+DOry2lTuerKsUe6inV782DMWfPg/hHunPCKP7MVGhsr9YWSRheYcV9nWnHT2HiALysuFE3OBAJh/9KZFHXmCc+RBnvhnrUlnivmeyzWTb9l09znnFAr+qbCDAI+uaKWrqqwH4yYWvmunmjtN4+M88C0jb6wYRGuWRzCI0cnEEzFLqReRIDFwMKyBtQW1eORjhldE5r+0UU1ZlNJreiaHBRJy8KpvtPY2JyP5YU+fPuEH13TBhw2xnTCg/+3dikqHDGh8aStKc8+1RdBYa1bcnO+mnBowYESO1f8aDJR3ukP47ZFedSU47Qcwt88kRpG2ohmqgQWw52joekShYIKDb1nNfgHFFwehfoVTpTUC0yNWDj2UgKDHQzFSl111TXy1ttuH6qpqdkUSCRKBnr7Ht6xfbvn0MGDlhBCMjPsdrt577336tdee21ncWnJ9fF4XDHzLQAeamyc5779zjv/5fvf+6EKjE9R+yEDZbU2FJRnJlN/c0q/TNpT4APqiyxMhQTiRqbpGIiHse/8Eb3EnbuVmW/ZgR3pt4NH8C2FAm/dulUjImsyFvzDg72n7ttxdBenjKQkIggQKgsUVlan4XOpN43Yk8EwEoQz+1JoOZhCMqLUpesuE3duuXN41aqVX1q4cNG86urqnP7+fiuRSIAAUkqJzvPnTafD0VhRWXnll7/85R9byurXNHnHsaPHtLa2dr2upu6j1dXVj7jc7i9XVVTdHgyGc7u7+jgaMKhmgQNLN9tQ22xD7RINxTUa7O5MaK/Bi7qCqzmlLqH/2DeJV7uDaM630adX2UC8K28o9FJDVY6P87XKmXMTUd/uzkk2lUaX1GkOSk8VDkw7A8fHkuWDYyFeW+WlZYUpPHniSfrO3h2quriicllJ2XN9U8kFLj3uePXcTlGcU1wwHLTL13omyc1E75/nwXxfzFPpMvNqcnK0lrEwnRsJY2G+D6srHDjcdxLRVAJgAoGgwMhxeXHzsish7Y341jE/TvsjWF+dx8UuFufH+ygYj6BveghmOoKV1fPxYncCacNAOmVhYDoBu2ZHrVdgOnRebywpdTnYjjJXGnn2Q+5EspXLvctCXTOp4vbhGdwxv4RurE+JnslHeDiwL1NhhMgUCqGgaRq8ORpKamTm/i52oKSakI4xjuxMoedMEkLZ1BVXbqK777lnoqGhYX08Hufh/v7nH398x8KXXnrJAiBBhPyCfONTf/Ap/Zprr326oqryjlQqVdTT3b2vv6//41NTUyubmppejERjHzp37izNTE6zESdKpEz48ik7B/FfC6v/844qQQrAYQOmooxATIOEAAsl+ieGrYaahnk+6Uqtc12yZ+vWrfpbzRvwltGlHOfjOhGZ6XT60jZ/z9d/euJFFUpESQiCAsFuYzSUW8jzXCgNvzk1f0UYOp9C57EU4kGgqqYKt95+a3rV6tV/WFFVtXXNJWubr7/xhq/8zdb75VVXXSXsDodpKcWpVEp79LHHjJdffGlVf3//kyUlJQeaFy/+zMc/+QltamrK3P7YT5xnTp1+za7ZLyUidrkdgsiEaRJMS8HpksgtJHhzJTRJIIsACDhteSjOWU0to4TjvVPgVBp1Hju8thkMBfeLsfBxORw86WrIo+TGSh+QMnCsJ4CWUR05rrIXTcsylGGAEwmYaRMJI43JcABDU34k0klKK643UmmVNlMYC0/BYMvGSkElU7CMJOIJAxPRcN54eMbhIAsOtpCKpxGMW7BpOmxCA13UMTCVicaSOjSVzsexwRjOj0XRPR7Cwb6QrClqoMbSaggihBNRDIcmEEun4QFwx/wCLM3TEQ6n8fhpPx8ckSjxLZiEEgdznE6QMlU4PU2xVCTHqZHHaVmAYUKDBUslETemyFDRi4BFlOlUcGYBjMNNyCnMoAtJCgSnFMb6DaSTxLX1dbjrrg+aixcvvszhcKSnp6YP/Ozpnza/9OJLFhFJBnN1TbX5uc9/Xt+w8fLvlVeU3xqJRPLOnTl75OEf/LDgC3/1V+aBAwdu2rdv30+ffPwJjA6PAASKRxXOHzVw6tUkwlPZuuVvSPM/W9/KdVpoKGF47Bm2acECCTMpfvTK4+ZAeOJvE4nIJiIymFl/z6UAzKwTkZGKRlecDww8/eSJXeq8vw9CCmIloJGFqkILtQUWNKnevHYLCIZhYqQ30+vXdc284447tJqami86HI5n/H7/M5FIZGdZWdmDhYWFjxUWFPzrZRvWb9j+2E/Q09NjJRIJ/ZEfP2I6XM5rBgYGHsjPz/+TiYmJgj/+7Ge//MADD6gnn3wyx2azPbV37x7s3r0HREIUVwkUlAqwylBEgxivZzMCbnsxYBZMdvjjRaFIEroJuIUJiwNIGzMwrRii6SmH25ZqqPUI5EtFo1MxazBoE0Lk9ik1Xq+bFpBMQRgWJDRoQoNN0yGIQBBpYgYxoEsdpBhkWhBJA7AUbKRQ7PZCwYYzY3F0D4fggYESt4ZYOo5oInoh9lLE0KUdKysXwmEvwURknO3KoOlQAi+0+nFlXRNW1yzCmcEOhBMR2DQdxIDNTGFjZRGWFusYnAhhYDyCx074sSB3nrWs3B0kNmApA4bSwWxasEwFwwCSKcDMdH4EaSD65crF1mxzONtSFIAQmbEn0zIpFAzyzMzMNQMDA7fteWV39XPPPWcC0JhZLVq4SHzko/dqy5Yt+5PCwsKvAEA8Hv+7ltYWbf+BAwYR6U89+aTa+cyzHA6HpZFOs8+Xo6QmKBIOiZ7TaeSVpLD0Ch12x5vjkKUgNBQBo9MmOsYzW4cFSxoIjIkfHXpKeTbd9Z1UKnoHEZ2eXY/3rjcA25ll0Z49RERGOp2+tGOi76ePnXiu+MD540oQhFCZXnRxjoWl1UCe0wQuDPvwb6T4s6Os4OzIKikATNPTUywkFjCza3R09AUp5TdPnjyZrq6u/mzjvHm3l5aVba4oL/+3w4cPVz377LPW1OSU+P53/9PUhPzjgf7+wuLi4ntmZmaC93z4ww8+/IMf6v/385/nVCol44k4FVVKrLrKgfIGAWYFEhdV0zkzj++05UJBG58JhoqspAENAroywFYsC4qRMFUKjDR8Th1uEgjHE+QPxRBL+ZZKBR2GCRgZ9l5GBqTCfAF8I0hlwD+Z7TgAWSrzWqUwFTYxHM7F0Z4Adp4dg5m0cOfiMlxSaUPLcCeCqchshAylFOaVVmNZ1Xz0TSexrNxHNzQm8d2DERzrmsCxgWKsr1uCusKjODXUnjHcTFBpEzmCefPCQqNrOGj7/oEhOtLhx3cdVPp/rm++IR6LQSkS/X6ATYGCWgCmCaQMsGlc1HjkX/mIZ1ucJBQKCjXUztcQnTEx0D+E733ve/KWW2558Ny5Vryw63llmqYmNc1af9ll8rbbbos1L27+QF5e3iupVOpDoVBouKio6PeamuZfs2jRIm9rayvHozERVRGQEGr+ooXiQx/8oLTZbHjooQfVYP+QaD2YQH6JhtolgJT0OsLnf+m6GIDHYWFJNSGUFBgOZgFcBHGs55TKs3saPrj+/S/HOX6Li1z7jx8/rq9atUq92YVB7U3w9mIPILBnDzZn96DFU/EPHh9teejp06/49rQctTJBm4SlAJ+bsaQaqMwzshaf35TQn4ghBEO4gMpGDQOtOvwDlnzqqZ9ZBYWFt9h0xyPl5eVb+vv7lyUSid87ffr0QxUVFXfW19d/dsXKlSvKKyq+uHLVqnt/+vTTOHTwEH/7299O2+32u4f7Byfy8vK+WVpWGi4oyC/q6emBZgeV1tmx+io76pbZodk5+2u8cZdABiKrgwFhZ4JmAkRWpsrG5gVKKkkWBFvQlIBmmbApCw4SYIIXbEFaFmBZkKwuukamxUegNFmKM5fLoAmFUtBUZrNO33gAoUACj58YhT+awtWLSnDf+kpMRM/h5Y4jWVKUzFnWpIYVVQvgsZVgLKywpMQd0lSB/UDHpOP8WBSvtoxibVUdVlYtRNf4AFhlcAeaZSFlGqRTQvvw6kr0jcfw3Dk/Xm4d51yb4MsbC0WumzAdEUimGKsACIuhGSaEssD/g2NIIEARHB5g6UYn0paFjsMGOtrP4yv9XzUNMyXS6bSw2+187XXXyltuvTVQXV19XU5OzonBwcHnjh49en0ykTTWXbrur3NyfLGCggIfADYzy02tzZs3y/fdeIPV2NDwGdM0yz/0oXv+5qtf+Yo1Mx6TLQcSyCtzoaCUwErgN2lZUbYAV5ZnYUklEEnaEUgQJBiCSbzctt+Kq3TBlpXXvxCNRz/tcXl+CAC7d+/WNm3alAnY3gSsgPa/UXgAYs+ePZjcNMlZi6QAIGEkrptIBT/62uDpD/3kwM9wZrhdCZJScMY7u2yMJVUG5pWY0CW/iRt9GOkEEI8RSDDKanUsu8KBEy9HMT0akt9+8CFT0/UPrL9s/SO1tbX3EJHrxIkTd58+ffrqK6644lxlZeVlZWVlH8/35n8tLy/vgbWXXLLuySeflD96+GErkUj8adPk/M/u2rVLtrS2sc1uo5J6xuqrHahbokHYZkk0fhEW3kTaDMMmuLDSo3MBm5Q0FHTDgmQ7iATSSiCWdsC0dCSiCVAsjVIpqEhXpscmvqErfN5rKThSBmyWgmDK3DcSpCwTJqxqmJZOFl8ogupMcKZN6EJgQ00JFlT5uKVvAsHpKI0MB3CmawJpOY6JwAQy5VjAUoyKnAKsrlmKqZDgYChBotChilw2bsqxYWDI4DOdATq/tAxLa5Zi7/ljsJQFYgWbAhKpKPZ3tou64np88rIqTExGcG4oQi8dGyFOKdy+Og+AQAZgS9AsC07ThG5RFnv/P2/z5lQQVl3lARBHz6kk4pGoxlCw2WzqmquvoQ/edVd7TV3dlmAw2N/R0bH3yJEjG77/ve+l5zU02pRp/vOhw4dx7NgxCCFQVlbGd999t1y9atX++nmNHyGifgDo6e655MqrNl+382c7rZEuQw62mMjJ1SEcswb+N4GpETRhobGUEUkCpwY0xJIalGAAkPs6jqpAOOD60Iabf9A93X9Fpa/oaYfufub1iHu7LNpTRL+JQdDe6Mn3iD173viC5slJ3tK6hYH7gW3bOHsRddH7FsaRurZ7pH/h7s7jv3fG3ylfOPaqFU3HBUkhZlFgDgdjUQVjSaWCxw4wvzkTV0wK8QCj83SG0kuTjKomDWWNTqxiH069HMPUWFz7xle/ZlimefuGDZdbNTU192ia1i+l/P8mJia03Nzcy4joGDOPzJ8//7JYLHZ7TU3NAzt37ix9+umnlRAkR4dHOK2SVF6vY+373KhdnOF6m4Xz/sL6KitEUhNgBIqby/JQYRMYj6WhIilYlhfhhButwynMK8zHilIHJienoMJxzKvMo1qfQFqFLy326sWFAsixGHrKAFsZQJNN0wlKIZrgNToACROGMgFlwW5ZyDEVbJLhI6WWVXie/cOrF90cmzjKw6Nhev7AIG7cWI2l1YtwZOAYBHSAFBZXNqKusA6vnArToD+CUqctL2Up1Oc4UO/WaWomikPnRrGiuhELyhsRSgQBZrgsBVKMM6NdODnUgS2rb8XvbazDl59uwfB0FH2DM6CVedlmeibUtSsLTlNBZwVLEUxLZjP8LEKQ6YKi/5LJBBAU8ssYl9zoRW6xRNeRNEYHFZwuJy9dvlQUF5c+ZrfbW6anp7/T092z4Ztf/0aKme3nWlq4rb3dsixLSiHUunXr5B1b7lRLmpfcn5uf+3eRROLKsbGxz8fj8QP19fX/94brb7iy5WyrHBjoQ++5FKqbJXLL5G9cQWdiEAScmsDSGhNJS6B1yEQ8nflsARLnRs5z31PfVNevvOITa6oXf+LMUPt368vqWjzSvpuIzvy8y7n//vuprbmNFhX94X85lps2AZuw6Q2G4oIB+HnF/q+yDQIEi9UCA9amkZnxhVPRQP6+7hO3BTnuOtl7DkfbT8MfmbSISBIBpDLz7B6nicWVjKU1Sfics+wzvzm7HxHDSGk4fyyBYy/FEZkBiEwMtuuoX6xQs9SBxRvtOLfPxORoXP/Wg98yNE3bsn79Bq6urv7g2NhYh2VZW8rKyh7t6en5lzNnzvxJX1/ft91u92eZeYRIvtze1u7oPt/JmsZU3WTHqmvdqFmgZgtQv5KshAhIpKcQTHVTY/nlWFVbgJcnBjA2HMPUdDlSiZWIxodRU7IcsSSh/fwYZNzEisoccnqj2vmp8N9UFJWhPk8iX0JMj4WRjBdhQXk9XLoDdYV16Oya5opcB0lHGKZpsqZrZCMFp2lBWMLKy/VJt1P/9+aanIGPXLPysw/8aL/Z1TujdRW5sWbpZZiIjqFnYsgq9ZaIS+uX0cwMQSVNXLW8Al6PBdNI84c31ZGZSGHX0WG0dkyif1kV1tQtQ+dYO4gZDkshg5VRONR1AgW+HFzTeC1uW1mFR1/phMOwQAxopJMkDZKhXKzgNS24lImkYcPQlOCQKag0R0GSwMWg5l+Or8uw/vryFJZudMBuI0SesRAOxcRPn36Gi4uLvxAKhV7SNO1Bn9f7kbq6Or2/v5+VUpROp2VhURHfeustcuMVV4zMmzfvDpvNdnhqYuJfW0+f/tzBgwdx4403fRrAGm9Ozv/ZuGnTAz/8Qb85M6G00BSQW8z/hZDlfwcNyhgCty3DgqURoXVIIpyyMkaAJEWNqNxx+Dlz97lD8pJFKz65Kr4UBdKTPjHQuiPH4RmvKC7rd0B/TZI4+zqz0I5foMG/IAKYxR2PBSY3xcz47QP+UU1KkkJqpNgECB6d9Ip4Kp5OW4b3uZa9K0wb6UMzoxieGcf5gS6MBPxmWpmQgBSCJBNBKYIghQKfiebKNBZVMHLs9CZy+2UGaEZ7Umg/ZiAyTcgryIcUUNNT02g5lBRTIyZqlzpQvVDAtAjhybj+za9/0yASdw0ODoqSkpItAB4OBoMfmpiY+PyXvvQldcP11//Rgf0HVp89e1Yd2L/XPdDfr6QDVNXkwKprbKicLyDEbHvqvw9VTRXBSOAAmkuasOXK+rRuGbaOvgCOHotgybJN2FQTQ4msx7FTIxgZjuCaFRXYuCoXZ4ePYyw4Zd277nZ1+dIy7uicsnUPTOHgQQ+vWLYCyxdcQlbIg1AkScsWFGI8BmyYfyk52IvpCT9EzIAk0NhogDt8+h8uqPf9U3SBcef65rLSfUf7+PCxIcpzl+GGeddjoHpQVnpKsKxiGQ6fmEG+14bFde7Q4a5jHpvTJZcW56bWNhTaz5wewdRIFHuP9OOum+tRU1AIOzwQySQ8mh3rGlZgMjKN19qPo9hXiJsuW4OhwSkEIyk4NBstLK+HaaVEJGq4Q5NxiKSJeCQNjUuR51pHnQNBxBJ9qMxXcNiBWaoy+qV+ibOvYTicjLplGqb9DrQeTFJrSwv/5CfbNV9OzouLFy9e3tQ0/5EP3Hzzx77y1a+aUghqXrpU3nnXFlq5atUOr9f7p/F4PHm2peWJAwcO3vbwDx827Xab2njFFXo8Hofb7m6trqqEFBrSKQNmSmUYmojB6jdH7VKWnTHXqbCyRsBuS6J1WMNMVEBZgMzwCmgzsSCeOfayuevkXpTlFNkW1cy/pzKvBJXBCkiDzZ0te04x84RDs5HFPCZJZzE7lkVslReUxD025/aqnLIj9zOLbURKmzVExLjXke/7+NDwWbT3diGRToAEkLJMxBJxpIwUwvEoApEw0mbSnG3dC5AURJomBAQTzAxJHTwORmW+iYUVCrWFGVrvN5fYUyAcsNB5NInxfgNOtxM33nQDFi1cJF595RUcOnRIjfZGaGrCpIoGO/KKHEjHU4hFI/o3vvp1Q5nWnYODg49XVVVtGRsbk6lkColYXH3tq19Ta9asubSktBT79+2FaZmifJ7EmvfZUD3fDv61KaOyrTVYmI634/zk86ipuiX65/euydt3bJBaOyZw4NUZ2HSJ09wJ3S7x/o11WNach77EWTxz+hUwCbmotMG4afnV1h/cutT2/GtdMBImBcYFbMqBkJHAgtoCRNW0cricYnPpJT8dG0ld7nOI/I0ryyCFFJFAwNK0qg/Gp4wvzq/I/f/+4LY1D/ikclomSWnYsaR4lbqucsPL6RSvHh1KFXR2TnJZaQ7FwxhfXruoLm0md6fCIp3jcl9f4tbZjKVFV+coBnpKsKihFKP+ODiVxtRYAmuKFuHe9fn4t2e/gaePPo8/3FyGLZub1LFWv3BIEVxdt2zR8HjsmyfbAx8Y6Z+wimyQ/X2T6O/Lx/oFVwUnIwnHM+emHcPBKBaVm8j3KGhC/BqRYMZQ+PKB5ZudSMYtdJ1I05HDR5TP6/WYd9x2JJ0yQq1tbfD5fLRx40Z58803J5oWzL/Pbrf/GABmZmY+2tfTc9s3vvmN5PKlyxwfuvtulJWW3Ot2u48PDgw80dHRAaWUsCzC+IAFm4uQXyrgzbFA4jcnrZvVDLfTxMpqDfkuC+0jwNAMEEtnbIwghg6psbIwMuPn4elxC8xQRLBLTcvz5a7xur1w6Xa4HS5oQsvW3uxYWNGExfOXIBUMSwBHNgFiG6A0IrLuv/9+UZpf9IlQMvrEJWXND1DabHjh9Gvc5u9JMysphCDOgkUECSEEaSJbPp4N0lS2xed1mCj2AXVFJuqLFXKcBClEdr/kmwU0yFx3vMfEUKeCUqSu2HiF2LBhw7GVK1f+aUVlxcNLly+rf+anT6Ozq1P1t8aFx2cHgUBCIBqL6g899JAhde12m8324/Ly8g8NDQ0t+KPPfOavHvrWt6zjx49bQhApxaKgXGD5RifKGmR2Zv/XrF0wASIzr+8PmzjQdRTFbpG/rnEDNm1owtJ5RcZjT7TqL+4+j7w8Jz5210pcf20Dzk914lzPeSgGJiJTeK5tj8PlsmNl/VL8eeNaA0qEMqO1nGMgqfcF+vDksZeoqrAc71+8aV5tVYEoK/PhfWZN5mQQJDiW6kvN/FsiFqspLysLfvaTy7yGYWWwf5pSoVQgRgmPstKM5oZi6JKQSqr5ul3AglZuGFaV10a05X0LYWb2CMJnt2FmMglOm7jx8vlw2gjhUAyco0CS0Ds9iidP7cSW1bfSHeXzU16PVGmV/LRpmJfZYOGe65sFs4CUioXSyEyYgLA4nNQw0adhKqxhQVkadcWAx27+WgVjVgp5BRLLN7uRSjL6Thliz949HInGCmy6VjA8PMyf/OQn5crVq/Y1NDTcQUQTpmn+aTQa3afr+ssOhyP5iU98wrF65arBqsqaD9nctoPj4+OPHjp48LZXX32VGUpYScK51xI4f4LQuMSBZZt05JZqbwpvJWXPjaZZqC9SKPaY6JmU6J2UmAwC4VR2kJsIgpiYoCkiSCYYymR/YEKNB/wgACYzCyFUc2mDvqR5Ha2uaj5vS+PfinPLfpSJ+oX5C+F3zOwNpSJ/dn5y4M/3dh7LefHMHjUdi5AURIIztJMqSzsrOGP9nDaGx6mQ52FU5SpU5CvkuBXswspmyW8at8/rTTAmtO5PYv9TSSTjrK65+lrce++9w41N8+4iosOBQODvW1tbP/vqy6/49r22j2cC09A0jWZDRgDw5eQYn/nMZ/Q1a9fsqKys3DI6OvrASy+9+Mdf/9o3TWKl+YoJK65yYvE6DTan+PV+i9nUgIBYijA0o6F9RGJgUkIpyZUFFXRV0xq+ccmV8ekR8OM/bfWcaxlFXVUerr6yAeyKIcEx+K0xvHj+NfiDkyjPLea19cupvqgiJYUYZwY0kqX+2Iz9eO9ZnB/vR4HLi2uXXY4cRw4MIw1FDGYFVgomK0ylI5iYnkJ1QSlslBnDZlZIGgnYXHbk2nJQ6MoHZXkH01aaW4Y6ye50or6oGk6yXVSQy/orvjgHJqSsNM4Ot+Fg12mEUzG4dTs2zb8EG+atisXTabuwC81MWxAqU7+5OMOPpuPY03YY50bOZ//dgtdporEUWFhmoNjH0PUsIWk2sP1FU6MMgmILIx0KB59JYqwnDdNgVVZepu77/fuw/rIN/1JQVPCFZDJ5U1dn19aJCf+qnNzc9lWrVq0H4ALglVJ2WJa1rquz618PHjy44dFHH7GCgaAEGKZhMkAQUpLLq7DyGhdWXeOCrvMFrP9vetJfnx5UsJTATAwYDmgYCQjMRAUiSYFUGjCVAMh6Izoxg+fgPKeHNzevF9csWj+zoKTxH3x25zeJKPHL6hCzyn9hAomZy0ejUw8cH2y948f7n+LuyQGABAlWIBawCPDYLNQUWajIt1DoYRR6CS6bBQUGqQy2nN/kWQbOHrxYgHBqdxRnXzORSjDsdjtv3LiRrrr66tTCRQsfKikp+T8AXH19fU+dO3t28zM/ewYtLa2WZZlCZCp4AICcnBzj45/4uL7h8stfCQaCeU89/cTKZ595jh0uppVXO7H6Gid0+685AZbdHZ9UAtMxQveYQMeohpkEQKxlH6hCjjMHH7zkRnxg+ZXDVsjhe2VPp+/g4T4AhPnzirFqZQXcpQo/OLkd+zoPQwqZWWJBBM0uQSAYSQuKrUwngiQEM5RSKkPdQcR88R48hf+fvf8Os+y6zjvh39r7nBvr3so5d85AoxFIBAIgCIo5CiRFUemhJEvWPOPR2GN75ABx7G9kPaPRZ488/ixbkkVJFEWAIMFMJDZybACNbnQO1alyDjeec/b6/ji3qhsgAKLRASCly6fYqOquG87Ze+213vWu91WnkYgYXWmmA2LwrOJCF6kzxhknK0FOY2FPp7o8qCFvlMVJTaxETZwhmFrl7omhPpunGoXMLc6FBrFqzpqwnV2ERkWMYDTWKSCeUDSidDco67sc/W0hzSlXk/GXmjvvaw/cuNAwtK/K098tMTpUpbOz0/3mb/22u+WWW/7VzMxM49DQ0L/89je/xcjYSPUf/87v6I033nj1gQMH/nGxUGwKg6o3NTPz8V3PPufd98P7wshFnnNORcStXr3adnd3cfToMTc2NmY6V1lu/VyGjgHvAiYFf+IAAYpHNRDGFyOmlwxjM5bjEwkKFc7OKAjgVAdbevWXb/2U2dK6+usDTb2/LSJTr97fr8kDqIl2CGBEZAS4Y7a89M8bMnV/8NUnvmOeOf6Sw2BiAkSs3tPWAFt6HL6Jb+nywlOJVWzkIrt5ighz48ruh4scfrFKpegIoshFUST333+/Hjp0KPmJT33yf77mmms+ODg4+I9XrVr13qWlpS/29vT+8aOPPpq///77GRsbc57nybr16+TKK670n3riSYpLhdvmFxbY+eDDGOOkd73Pmu0p/GQtwsobZyQS7yfmKsLRMcvRMcPInKUS2LNmHMSWY4uVJb754kOEzvW8e9WVS+//8Brefe0qhkdnq/X1KdPX12AOzhwxc8UFDDbu1BvFKYSVSGuvJV5tlktj+SDEE7OSp+i5p4mFWJrnx963FcF4YiNnMCuzYfEUoziLGDXxfXzt2bFX+uo4zrEeic9xdUwtziCA73ker4/txy+xogRtMDVy05k5ZaYgjM5ZNveEdDXVMCV1r3tHxHP0brQU51MUlyJGR8fNvd/8hvF9/w8nJyf5+te/rrm6nP76r/96orOz898AB+fn53975492Mj87y8FDhxgZGYlU1aq6qL9/wN50001206ZNc4ODg9Ejjz7a/Jd/8Rc6MxLK0T1VWrptPDZ8Saj1ACEJa+hrVNrrIxA4PhWC2BUfCBc5t71vk/zKTZ+WLV3r/u/mTP0/i81i1NZ4AtEbZgDnPu7UO81mfl8+IxIthpXPHZ44+d/+x6N/l3v6yAuuBoOjDjqbA96zLqSvKYpX5SWx7K4tJ1GWFhy7H6jy4s4S1bKloSHnOjs6zcLCAsPDw1E1qEomWxe956ab/Bvfc1N49dVXf729vf03gdTp06fvevbpZ979+GOPJcfHx/mFL/wiO3bs2PPC88/3fePr9+SOHjsm1WrFdPQbrv9Elt4NtuaB9/p1v9ZYiOosI/PCgRHDkRGfpYp9VUL3ytMyEkedl9YNnWtkdWsfHY2tJK1fERGzUCrYfWcOmedP76dULb3qLtXaY7XAqhd0vRXrxQvMRbXPKK/44/XRKnntpRRrB+rr/No54UJeFT1/4scwiI3ozFdZ36ms7XDk0zG1yJ3LEnxFDwaW5oXn7iuy+5EyVn3qGxtcIpF0mzZv8j75yU8urlq16t80NTX9p5mpmT/92tf+7ot//dd/7YrFotjYV1z7+/rsDTfeyMbNm+av2HbFC13dXb8A5Hbt2vWDP/lP/2nVy/tepm+9Z277hQba+oVLbV/tVDk9B08dTHBqxkNYVhAzbsfgFvm1Wz5d3d6+5leSXuZrd6qa3wd9o5Hi12UCfkm+5OBL7FT1ciJ/V61WT/3aDZ/+dujCpl1HX3JirFGjjM957D0NuayjKaVcqHrKG3L9Q+XMAeXQrjJBGV2/YR0f/dhHTXNj8/DC4nzd5ORU/ROPP86hQ4f0+9//frhv3z5v9GMf+9z117/7+p7Bvi/19fXdqqrXDq5e9TeLi4utPT09/3tXV9f35+dmdznFlMslmlo9tt2Spne9xdo3koMSlAjBUKh4nJhW9p1KcnoGwlopwOskhRrTPSkEJXnmxB6eGnoBo4ZUIp00IlSCMk4Vay2iGtfr5wSTeJyaZSrwK44KfavB9VVvVt8ApDq7j+OiQJfnEGKQ+DX3tL76WRVe9wK95mtHuMgwPJtiuuCYXAzY2BPS2WBJ2B+XPpcaTpRtdGx6d4K5yZATLwcMDw9z83tutp///OdPb9q06QpgcWho6L5HH33k/ffdd59Wq1Xj+77r6ekxN9x4A1u2bi1t2bLlke7u7l8SkSlVbRaRoydOnPrTj3z0Y//XocNHwrlxZ04dCmjuTtTWzKUJAoKyWLLsP20Znk3ELE5xaKR6Rf9GvnjzHZXtXes/4Yt/305V71aR8EsXSgW+VSTcpeonRJ6cmZ//1c9f/4lvzCzOeccmTqmISOTg9LRwcsKnoSfEGL3oIUAVjCcsTCnH9wTMTSgtLS36qU9/0tx++/t/v66u7kuq2jgxOfn/bNy08bbDhw51Pvzwwxzctz/873/639yePXv6bv+59//5qZMnPwX80o4dO9apapuITAwNDf3N448/1nrwwMEokRK76irLmit9rKe1y6OvuYWXHyMLhoPDhuNjHnNFqclpLW+UNyAJ1ebvE4Y4jVNBo4hQiNs3ApFzJG2ShkyeZDLOL0MXUa1WWawUKFcrZzMAXRYElVdIWr/V1qu86qhXVSIUdWezSDGGrJ8ik0yT9BNELmKpXGCpUiJC8WrTfT8xGLxJYExq9KByFfaf8ZlesmzqrbKuDeqSr4VLOpyztHQbtt7oszjlGD1pZXZuVkaGR7KD/YP/x56X93xg165da771jXvDqalJ6erutjffcovdtGnj0o6rr366o6PjfxGRfap6/aGDh36wc+fONQcPHnygv7/3D8fHRyf6entbTp88qZOnAikt+dTVX6oJeyF0cHracmLCEqrgSUQ1jHR1W5/71Zs/ZTc0Dn7BF/++5WncizYLcHVtLllEvjsyP/YvP3f9x/79H/3wL/xKueAZsRTKwrGxgO4Gpb3erKDsFzPyuVAYOxUxfLRM5CJ3w403ma1bt+ysq6v7UrVa3QGU2tvafklV/TVr1vz3VatX33LowMH+J598kscefyw4dOiQ/eAHPvDh99xy88GFhYV/KyJ/Ojs7+/N7dr/0i9/4xr0hJvK6VvtsvDpLOhMvtdckoKhgxTBXcQyNexwYSzAyo1RDi5haray84eZfZn8tb44IrTkAOQSHqfW1+5t7eN+W6+mub6NcKMU0YAHjGdQXqi5kbmmR0dlxRhcnmFycYWZhnmoYIBZsbBFcY8y9uezg7JxbhEJtVh2yqQytuWZac8101LfSXt9CPl0XAwuR4gIlchGRhfnKEi8Mvcy+0aNEGtRSVzAYjI1Rjbe+EpaxLmFkVlkqJZiei9jU7ehoDM957vhfGo1Bw+51PuuvEZbmkUMHDvOD7/+waW529n964okneOKJJ8KW1hbvl375l1m1evXSVddctau3u/f3ROSpQqHwriNHjuz56lf+dsPzzz/vR87xu//r794BfME595UtW7f87pEjR8OlWetVSkq2/hKc/xrPuMyUlAOjsFiNVYQip+TSeffxq99vN7cM/Kt8NntPTYfjTY8Ov+lhIBEJdu3a5XfVd/zxwZFjWz+y49Zfvefx74dq1HMqjM74nJpW2uqDi+65LkYoLcHEyZClmZD29g7ZumVLdfXqtb80dHLoXz7++OP/7tSpU+Hhw4dHy+Xqv+/s7PxVEeH06dP/dcOmjR87cvhw5/333c/ffOUrlX0HDrTd+t5b/+v+/ft/Z+jY8favfe3vdGpq2ja0GDZd79PcK688RV71iFDGFw37z/gcGLEslryYEfoWux0OaEzXs71vEwPNPSwWC0wsTlANQ64bvJJeaeSlx3YzNTWBixzG+qQyaRpzObL5LN31daxq3YL0WsqEzFYWOT0zwtDkMOPzE8yXC4QaYd+k10KkcQTL+Gmas430tnQw0NJDa6aRnJ8h4TyickBxbonK0Axzi0ssLi1QqZRQVeryedasX8vGHR/m2dN7GVucpDXfQkM6z9TiDHtHjjAyO35RFoYgzJUNL502TBWU9d2woS0imzynxVhLP5IZw7prLFNjHoeeKfHMs0/p0888GWUzWfnoRz/qXXX1Drd169b7+/r6fldEDgIMDw//3OOPP/7NBx94MH382DHdtGkTN950YxSG4f8rItU9e/bkPRsbfYQRqKu5OF3kAxCjhA7OzHmMzPjxHI0AzkS3bn6Xvbp785NNdU3/586dO70d7DgvY5HzmgbcsWOHA2RVZ+9fvLt4xWcfbXg2OTk/pcZEUggMwzOGdZ1KfWpZ414u+CYrBmOUoOJYnA2pBmh3d490dHWURWR4eHh4R7VS9e65++vywvMvDN50441/fvzYsf9tsG/V74kvv6Wq/3z92rX/edXq1R89cOBAwzfuuSc6MXTcHnj39VujMGLfvpdJJKB/S4L+jWk8f7mvqmezj1ihn4pTTk8Je04lOD0jlEOwoq9oT55Pa8cpdOZa+cj2W9netpHizCI25xP1ge/7lGcX+Nbd3+To/sfZNODIZ0LKVcPMuOF4wTK35INtoLGhldbWdtq7O2jr6eLd7du4Zc27mCrNcmz6JPvPHOf45GmqYbWGIbByf5YzFtUIaywd9W1s7FrNho5VdNY1YwJYmlxg7sg0B8YOMj0xzvziJFE4Q1rmySWrpBMRSc8RRpb9B4UXn1/Fhz7xaW7ZtIPx2VncUpVE2bC1dxXb+jbyg5d2sm/0GKoXasQp2FpD8cy0Ya7gUywKG7sjGuq0hsOYlVM032LYfL1hYTJi+FgggpgPfehDfO7zv3Cwo6PjN0XkMVXtVtVtwN7Tp083WWvTTc1N3HTTTbJ165YHBwYHvyQij1cqlS8+/dTTv/j88y+4RMLzMhkP6y3LiC+LiV88n8HFsnBqylB1ERYhDFU7G1rMdQPb3MbO1b915513mslbbtHz1RA8rwBQaxNaEXls38iRe9+75V2f++rj346simeMY2pRGJuz5Dp0WV/2gkd+pLaxDIoVC+KoVCsaOWdVtXlxcfG3165b637zN3/z0y+++CJf+cpXwieeeGLD9ddf/42jR48eA36js7v7l1V17ebNm//v7s6uj/7VX/2V/vAH33e+7xunTjoGfLZcnyCTd+dIZC83tuIFNF92HBxNcXBYmFg0/LgrnpzXDQ2d0pVr57Pv+hBrsz3s/PYD7N7zAplMlmyujqTvMTU6RWnuGJ+82fH+q0KyCSVwEeUgYqFUZW6xytT8EmemTjIy5XPwhTS7nq4nkWqjq7OH/jWr2dzby9VXb2KqssSuk3vYc+ogc6WFmD4rglMlaXzWtfVz3drtrG7sQReqTJ6Z5KUTT3Pq9CmK8yNk/Hma8ksMNlTo6lfaGg2NeUcuBemE4IsSiWP/GY+/u+8k9/zNV+jo7SWMQmYrS5TCCv0NHdz+/tv5/LUf4Z4XHuSF0/tep5V3funxMqayWLa8cEoYX/TY0hMw2OZImGhFysvg6FkLG65NMTdZZn666pLJpFcqlZ4GTh8+fPjFe+65Z1Mul0usWrXqX61Zs+Y/BkHw1+vWrUu2tbV9OZVKfV9V+4aGhh763ve+995v3/stRkZGEQNTI1We+k7EwBafNVsyJLIXKnJzLvLvmCkkGJ+xaC2siOCuXb/drmvrf9wYs9c5Z96KWMh56wE8DKKqUgnDe66uzv3Cd3c9JIWgiFGYL1smFiwD7QG+XIxaKBaHLC3B6aMB02MhvvXlxNCQPvrIo5nW1tbHVq9efUc+n/+sqt627Ypt/2rHjh23Pv300/zt3/5t8Oxzz63eseOqh06cOHF0cnLycx0dHR+bmZj6DxMTE//ir//6y1SrgbT0Gq642aOtz4vT+JhKc04fWxmbN+w9leL4ROyGE1fVb7EBJ/FzduZb+fj221id6uSHX/8OP3z6EabrHVFRyBwv4k1VaM4ZPvt+xydugKZseE471IBRMLH/XbVsmFlSJuaWGJlY4vjYaY6M7OKx4y2kMr109QyybstmPrT6Jrb1buTRA89wbOIUKrCqtZsdA1tYne+hPFXgwIsvcvTIPqanT5E1E6zprLD+GktvW0hns6W1HpKpmgSEY8XgAxefee9aHxGFEV/+3hmefew05ZYEk1c1QdJy4OheJu+a5uc/+Wk+eeVtJDzDM0N74kt+gS1NV1tv5Yrh6DgslhwLVcOGdsglXS2kWzxf6dvoOH0Qluatd/8D95PN1/3yCy+88KsvvfQSzz33HMlE0v3Tf/ZP/4/BwcGjq1ev/uUaCLpxaGjogR/+8Ic379q1y3/iiSd0anxCjLWoqk6PK7MTkUycdljxWLsjgbEXzgqMWZmGiQXDUtnGJDyNaMjWyZWd6xho6b6zhrm9pRc67wBwSyzbStLzvpcKvD2betZue/rYbmfFmGoIMwWhVAU/5Woz/xf44QPl2J4qzz1QYmY4npIulUpy/333sbi0uPH973//nsnJyf8B3Nk/MPDecrn8ybXr1/3q0aNHP/bow4/wt1/522j37pfWfuGXvvCoql5z5syZP+/o6vwXnpeQSKusuyrF6isSeJ4BjWouLvH/BxGcmPHYcwJOThvCKD5FuJDNj6OjrpWPbr+NdZlu7v3q13n05ecYW5tidmOe9GKFbMXSIsKH3hXy8esjmrIhzsWp+2LZcGwCphY8kr6hLefobHA05QLaGmHboKEUeMwsCEOj8xwanmX/iYN898Bz9A9u4+obr+eXrv0opxfGEAw99a0sjs6z6/uPsf/l3bjqOBv6Ctx2A2zocfS0WrIZYpJQYKgEjolFy/iCYaEUn0Y9TRFdNUdnn4B3bTSUQ4+lquVwmEaaU8wO1FHuyLJ71wTBXX/Hz3/i03x0y22IeDw9tAfnAi5kVmyZfyK1GfupeY8XjkTML1g2dUNHQ6zG5CKhvsWjf2OC0eMVxkYn+MbXv26qlYCJiQknsWmEfO1vv2qiKPrayMjIP11YWGh/8P4H+nft2sVTTz21TBCKm2BRRLYu66XTaYrFItMjZYb2lulaK+SaalOjb2lv1soIsfE1n4szASNCJQxdX0uv6avvWPTwdp/DQb70AUBEdOfOnd6tt95aPjZ24omtAxu2PXnkRadWjCgsFg1LRUtDSrkQsW9VsJ6jNGk4/FzA1CmNaa9ETsSYhcVFHrjvfrdv78vm+uuv/+INN934ayNjY/8lmUz++3Xr1n1zYWHh1/t6+j526tTJjx47foxqtZoAZiKNPnrq5EmKhbJmGqBrIEEquzzSKTiJEIFiJcH+EWXvaY/pJUvkpLb5L6SdqfTWd/Kp7bfTEtXxza/czSNDuxnfUsfM+gwmhMbTFZoXitx2ZcAdN4S017sauBTz32cWlcf2+Ow6KFQrQsY3ZDNCU73HYDsMdiurO0O6GpXuZrh6rTC5UGb/6TGe3j/Bfd84yOoN1/Oum64nlc3wzMNP8fQTjyHVE1y3vsJ1m2F1h9KUi/CtMDZr2DcknJkUjo87JmYTlIqOQlkohoZs2vHxGy0t9Y60H9t8paxy08Yqi4U0f/tAgepuQyXjsdCWgGuacC/OUfn61/j0Bz/CJ697H+lkkscPP0e1WkWtXGDmXOt4iLBQSrDnDEwvlblyUFndKrH8mg8DW3yGjwYc2BUwcmYERdVPeEZEcBG88OKLjE9M0N/ff+3S0hKnTp3SyYmJIIwiSadSpn9gwGzYtNGsXrWaxqZGPGOLO3fuzDz44IM6fjKSyVOO+uYETqML5MYoxYphtlQbjRZFVHWgrYeeju5/LSKzd91111s2EXlLmoC3xGADq1p6dnZPt/22SGwyZwSWKrBQNjU09EIov+AiYXosYupMiG99tm7fRn9vv3nowQej+fl5g7Xm1MmTOjw87J559hl77bXX/k/vufnm3xkfH//zXC73v23ZtuXPiouLd1xzzbXvn56d/ipQmZqY+n+ffOopVQ1MS6dPfYvWgL4amOcsE4W4t39wxDBfMIhR7MqpL+f9QcTFBqBrWvv5yLZbSS8I3733Gzx9ej+jVzYws64OWw1p3bNI+9F53rO2yGdvc/S2aI1pSDz0gUdbfcT7r4zY0m0YnvY4PiacmYJDp4W9Q0rCc7TkEvR3Ga5ZV2Zdj6G7OaS3NeTKAeGRvWf4/rPfZv/LLyN+gpnx42zrn+OD7xa29Cv16YipgvDkfp+XjngcHQmZmTOUI4MxllRCyWYsfa3Q0xjR3RKxZQDSXsyIFFFULdmkcvtVJcoly1cfKRA+L+g1jSy2JgmvaSF8fpLg+9/mU0HAB264gaSfZOf+pygEZexbdDB+JTTgVlplp2cSVMKIoBqyph2SiYhck2X7+9KkGzxGDgdMj6gEQc0mTSGoRu7woUPRwQMHXDqdls7ursT7P/BzibVr17Jq1WoUPZbJZL63du3a+YaGhh8Ba8XIf3vyySfc/FRgR46F9G6M8BPylvb/8tEZOsdi2aNUjScBnXPUpbO2ya+jNdn4bYA77rjjLV8s761tTonhW2u/EVSCAy35po2zizPOiGeqARQrMcJ9ITmdCFRLMHE6YGkhoKGhmeuue1fxphtuOrh58+arvvzlL3Pq1CnnJxJGVe3Q8SE9c/qMPvfcLnPDjTf8+k033vSFsbGx/5Cuq/urTC53N8DExMQfPvvs04kTQyeiVNbY/k1JMg027slrbE02Mm/YdSTBmTlDtRL39lXcW6d4OgUrbGlfx4e23YqdrnDv393DS1PHGNvRwvSaDFil6WSFtkOLvKuvxM+/Vxhsidl1okoltBSrErtyWuhqcfS1x7oLhQAqZWFmwXBywnFk2PLycXj0BWXX/hRd7Y7tqyzv3hzS1x7xsXeH+F6Vv/j2PmYXDbe9S/m1Dwh9LRXmix4PvOix80WfI8OOQsnR3GDYtlYY7AzobxVaGhwJ32CNYq3gm7hcmls0qAieB5lUgBWhIQ0fvA4WSxFffXye0PeIrjEUGhNMXtuM2TXP13/wHT5eDXjvLdeRMD4PHHichUoxnjrQt3ZixqBgjYsZexsztuDx9DFlKVA2dVvyyZC2AUu2UfANzI2XqVQMnudjrLrWtjazbv16s27tWtauW4efTAQJ3/+z7u7u6Z6enmeB/UCyUqlEv//7v3/s93//9x/NZLO/dsWV29/95OOPu7FTYmbHE7T3eSsKWG+lsIkUSlUhjBRRQ6gRdcksPY3tANkLRdneuirwnYiIRN/du3OqOdfEzMKMigjOWcphSBjGPmhvsfxBDBQLjsmTjqCq2tzSxObNm1m9dvWH09n0h+vyuT/5zre/k37yiSdCVfU8zxPnnJw4fpzh06fdk48/kbr+xht+/7rrrvv9I0eOPCMi9Y899tiG73/3exqGFdsx6DO41eKn43IjUsPJGZ9njxjOzJg47TZuBWF+SwCGA98YruzfzCe2vI+pI2f4+j33sq84wvh1TcwNplHPUX8moOXADDs6inz+fcqGviDm04uhEAk/2m25/3lLoQRGhFTK0dkA/W2O9mbo74CBrpA1PYYbrwiYWzScGIOn91leGlIOnxQe3e1x69U+H393les2VXhmf5Jjp4SbtgUMtAUcH01y96OWp192OBexYZVw4+aQDf2WllxA0hecU0YXlEPDyvC4cGLCMD4L5ZJAKKjv2DSofOG2iI76+Lo250M+doMyX/T43guzuKTgrm6knLOMX91AsHeOux/4DoXyEu/5udvIJlN8f89OZgoLscz6ReqpiyozBZ9dx6BQhqsGoCHj8KxHuVQhqEJdto4rrrzC3Xb7+0xdNjuaSqW+1tDQMDwwMFBMpVKPA0NBEKw7fPjwljAMH1taWurcunUrv/Ebv7FLRK45c2bkuWuuu/bdTzzxmJseVjNx0tHep2859UcMqKEcxHhUXEY6sokszZn6CKi8fQGg9sglsl5dMhPPYQuEKNXQEACeeSsnp6ISj3gsTkVMDFcBI2OjY+7F51/ItDY33zuwatVnGxoabm5ra7t3cNVg1z333BMUF5dWXqharXLk8OHo9KlT7uGHfmQHBgevU1UOHTqks7Oz0tCUYNN1SRrbfAxKpWo5NiXsOgHjswmWZcbfeu0WM/syyRQ3rr6K29fdwIkXD/Pd73ybA8Ekk9c1M9efxllDbrxE84szXGkrfO62iO2DEYka/wEiUB8vEZHNGNRJzDaJlOFJy9AIVENIekJjTujvErYNOvraHdvXG7YOBIxMWZ4/anhmPxw85bhuk9LWAO0tjtFJQ3MDhE544VjIsweF/i7Dz10bsX0wpL4OlsohJ0aFl096HDzpmJwVSkE8WpjwDNZTkknFpJRE2tDZEpL0z0nj1NHTFPHpm6FYsNx/bI4oY5nckqOUt8xsa0DMPN95/CGCSsR7PvI+Elf5fOelnUwuzNTuw4X3k5ZBwmLF8vLpeI1e2efI21qJpeBbiW5///vMrbe+98/T6fQ/SSQShWq12iYiE+Pj46tnZmbO7N27Nz81NcXg4CB9fX3Mzs5+KZ/P3xUEwa1DQyfeMz09qZ7nm3IBSgshcLZ1d/5UbEekcfmlNddsh2gum5VkIjkNjP0YN/1yB4CEl3CpRHIF7VRVXKRcQGcCY5Rq0TB6PGRxJiKZTOF5vvzgB98P5ubnrnvPzTcfXrVq1cc3b958XT6f/8s1a9bc9sLzz2OsxVobK6YYuyLbPjMzw8t7X3YzM7PGesraazwGr0jiJ4SliuXAsLDvjGFiMcEruf9y3oc+KCGGfDLN7Ztv4Ob+a9i/aw/f/ua9HJMZpq9tY2YgAyYiMxPS+uICW0pLfPrWkOvW1frWxGAPomT9kPdusly3Jlq5roqyVA4Zn7NMLggnxyMOn/R5ZDc8utunPmtY0w3XborY2BPwqRsjbr3SsFCElpziiZCwrsbRMBgcm3otv/WJiDWdAQ0Zw/QCPLLH5/mjMD4dUQksjfWGrWsielsdLfVCS32J+oxgbfx+PTGkkxEp/1waTAxermkP+cRthoWS8Oi+GYK0x8z6LKWcx+QVTTg7y33PPEqlXOH2T3wI78rb+c7uBxldmCHWCLgwTsny7xoRqoFwcMSjFAhXD0a09Hqk8hUWZguMjo7IqTOnPBy/8sILL/yLkydP9p08efJ2a21eVfMTExNcddVVtLa2/ml/f/+f+b6/cPr06d/e+9Ke/+Vb3/42u194HiMidY1KrtXUFKQuBDh2hKFZATctBt96eF7iomRGbzkA3HnLTvOlL93qwjA4nE4mbziXB6cqtdPq/IPT8vBKYU4ZPhoQVg1r1w3wK7/6q5JKJv19+/fx8MMPJ4wxPwiC4A96e3s/UV9f/8nBwcG2KIrUWmudiyeWRUSM54k4/fyf/9mfbTtzZsTV1xszsDVBtkkpVeDlM4YXTxoWiz4qbyRA+WZqzxipbcnU8+FtN/Ou7m08+/ATfPd73+NkusDYdS0UOlOoUTLzjrbdc6yeWuCTNyjv3e5I+0HttFAiZ6mGFofDGMhlAnxTkxoSaBNlVWecEFSqylxROD6uPHPQsvuQsvP5OBgMdlpu2+F475URAy0hCBQDRV0slBpEDiOGDd2OvlY4Mmb45uOWFw4pi0vQ1mjYvl64Zl3A6nalPhuRTNTabSulrVtZrJGDSmQxongoarTmPeC4sq9C6b1pZr4XoruniVIwsypPNa1MbavH+QUqLz1BpVzgg5/6OJ+77iN86/kHOT47DK8YcboAUk2MYRGFHkPjBiOWwWYl31ZlcSqw3/vu9ygUSr9SKVd+Zdeu52hsbHT/6Ld+6z/29fXdurS09G9vv/32cHBw8FHf98fHR0c/fvL06T/60UM/YudDP3ILC/PiIhUvAe09Pg3NlqiqGI8LymKck3NwBMWKWZm6fNsCwP7JyVgiwpqmKIrtvcUpmHM42G/plgmRg9nJgOnRADEmumrHVfaqHdvv8zz/j5tamteHYfhF4Iq2trb/fWpqar61tfUPVTUFbAvDMBuGoXieJ57naRiG9btf3N0zNj6OSihtvWnqmw2VAPae8tlzSlgs+izr1V9I2yJSpaO+mU9ufz/bmtfy4Pcf4EcPPcDpXMDktc0sdqVxRkgvOZr3ztN7eo4PXx3wc9c56tNVNLKIB1MLsOuo5fgwLJZ8Ep7SkPFpqoN0OsJPCvmU0pyD+owj6UNHY0BHo2HboGP4GsNzhyyP74kYGhb+fAKOjwufvdnQ3RQRRh5VF8+Wh4ED45gvWL73rMd3noClRUtnm3L7LSHv3hjS1eKoS8a4fLFqmCsoc0vK1JJHsSQEVSUIDcUqlKvxSZ1MOJqyloGuCqvaYh0ZS8TV6xzzBUflB1DePU+U8lnoTFFNGWY25nCe8qO9uyn/XcCHf/7j/MK7P8o9L97HwdETr0jlL4RbQs1fUFUYmhDKdR7anCBRV2VkZJS7/+4uF0ahS6VSum3rNj+dSq0GooGBgX+nqt78/PwXDx8+/ODzu3al7r/vfh0ZGXFRFFlrLeLFVuJnjlaoVGDLDQlWXeHH3YDzlAt7pbqBIxZkCXFy8eZt33IAuHvfZ5Z1iDpKlQrLR5PUiGpx68qd9/4X4tK3uACFBcHzEmLE6OTkVLazs7N58+bNPzDG/Ilz7srx8fGWIAgOqmpyeHj4Kw0NDZ/au3cvpVKJMAzjcsAYnnj8cU6dPE0iKdKxymJThiOjCfacgvnixYikcQehJdXAJ7a/nysb1/LAt37A/Q8/xGhjwNQ1Lcx3JXFGSBYjmvYv0Hl4jhvXBnzsRqEtH6DLAs3iGJtM8OjzyplpQ6kCURSbjBjAWIvnCekk5DNQl1HyecPqdseqjoimPHTUOz7x7oibtnm8fNzwxD5ldByGxjy6G+KWZ9Upqia2uDLC8Ixjz1HBF+GDN1R5/9XQ2xLhVJhbshwfUQ6NwLERn6VFpVB2LJSFcmAIoxqDsnZSaU2yvSGnfOzGBIOtVTAOVUvKlrlhi8f0gsfSzgLRbkuYaqLQ7BMkDDMb8ogIT760j8pXKvz85z7Dp7f/HPfqg+wbG0I1jKclLw4ySBh6DC9a8o0RpiFAh6tEJjLbtm3j85//vFm1atVOL+H/GjA3Mzf3O3v37Pnd40ePrb7761/n+PHjURiGVkSsGCEMQ5xGasQqzjcn9lVxGlDfmqdrlSWKasNCb/LdL7edjdXaXrJxKHCOKIoUcG9bAFh+VFwQVl08Aagmnte2Bqy8RRqkKFEYUlyII3QUhuZ73/suJ0+dvPGmG2+6sbmlhRd27/6j6enpl9vb2/eJyBmAkZGR35qYmFilqlcePHiQF198keEzw2G5XJJqtWqq1ao0tvm09ycZWTLsHhJmi/4FYSjLgdghtNU18Yntt7Mh18d3vv5NHnriUSY6DJNXt7DQmkQN2KrSeGSJ1r2zbG8L+flbHb1N1Zqnn4ufMBLW9jh+51OOqUVldgkKJaEUxKdrFChhIBTKMFsQZgtwasjx/D5DqEJDHtZ2wsYBZctgwA1bQ27aYlgqCZ4f1DwL/bPgulVwhs4G+Nz7IvJpaG+AoBrx8gmPXYcNLx83jM8ouNjhqSGvdLcZNuchk1T8BPi+4NVmqCJVqpGQTTm29LsaN6AmE4ehPql84NoKMwtJ5p5cJHpRcFe3UGoQQk+YXp9DrSXafZjyX/01n/7MHdyx44OY3Q+wb+RQbX5AfpLkwpshC9Q0A2DRT2JSJZAqCc+PfvGXvmBuuOGGf+153v+nWCx+dv++/f/59OlTH/nGPffw8sv7wmq1atU5a4xREXEJP0E6n5bGxkbT09MjU9NTHDt6jKkTjqkzEZ2rvbeQEStWhKSxZ2UcFSmXS4QaNgCtwMkL6bdfcACohoFUgmBlGxkBz8QY9luy/VaYnxSOvVwkrCrOBUxPz+hjjzymzzz9jGtqbvK2btv2z67avp2169bx8ssv39vZ2flUU1PTvcaY7eVyeXtra+vWm2666Q9ODJ3o+t53v6N7X94vQkTXep9KxvDSyQRjixcmXyY1WXSn0NvYzsd33M6aRBff+9q9PPLc40x2pRi7ppFCq4+i2FBpPLFE8+551qWrfOo2x7bBCGuEs2NF8ftJ2ojuDHS3RBC5Fb2AZWBVXZwVVCKoBEKpEjE04bHvhOXQaeWZA8IjL1raG5R3bROu3xqxoTvAX06xxGFqwiViYqPS5jpDc1aZqwi7j/rc97zw8nEPieLe/5VrlS2rlfXdEfVZQyYVkPGkpolfM2etYShiYk1bE4vZn9Obr4GCorQ1KB+/KWRqzuPBPQWwlpFrGqnWeYS+MrsmQ5gQ3ItnqH7lq3z4ox/mju23kU+l2XX8ZSru4o6dhwim3oe0oVqpytEjR2TVqlWbD+zf/2e7nn3ui9/61rd4+umnw0KhgLVWMpmM5uvrpbWlRVatWmV7+/oYGBigvj5faG9rH3r6mWc2/48//wuZnh5nYSqiWqQmLnv+vEbfW84cYh2JSlDF4ZJvLw/gbACgElRfkcN7VrFG3hJs4yJl6oxjdlTxPZ+Ork7N5/MyNTUl01PTZnxsXMdG7osefuhHNDY3m21XbPvEtddc84nWtrZ/u3v37r+qVqv71qxZ841UKvVX5XL5X8zPzf3BwUOHHCrGb0hzYMLn9FzsMHshZMVlTKa/sYufv/YD9PutfOuue3j82aeY6PeZuKqeQovHsgVqfqxM20vz1M1WyA8YymV4ep/BGotnFOvFbjOeBc9TEp4h5TlSvpJKGFIJF5/WK5hbQMatVF70t1e5YWOsy7D/lPDsfsee4x53P6Q8vc/wufcZbtxcJZvQWpCOR2l9Y8AZIgcnJzy++6zlsd1KEPoMdERct0nYviaksyUimwRP3DKwH+9oszz1pbhIqFZ9gtARhI5y6BGESjX0qUQOFzqiyBJESuQMzkFDTsiaiIYDC0Rpw9gVeYK0R+ALcwNp8KH64hjFu77Opz76ET505U3kkhkePfoCxUqhRpG+0AKuhj01pfDqyrhy1fzooYdIp9K/cGJoiIcfebgahJG0t7f7nZ2ddHV1sWr1Krq6u2hv7yj5vn9vIpH4f3p6eorAMaDYfrJjd19f77aR0TNufiptyouORDoGbd/0sVhzHzM2VkeOsx4hUkelWuXtLQFqYmOBq1INy3G00him8EztZFB3XrMAIhBUDdNjVSqlkKbGDn7xF39RNm3eXHzhhRcyd3/tLkZGRkQ8z0v4PosLC27nQz+KfvTgQ9ra2pq96qqrfvva664lk83++0cfffTLhw4eet/Q0JCUikXNNXlMO5/xRYuLLmjuBCdgVOhtaucX3/UROm0j3/jK3Tz2/FNMrEkxdWUji42JlTvolxz1+xaoO1UChOHpiHsfh1zKR4xiLFhPsdbDGocR8HH4Nu7/NzRAcx46c0pj3tGYDckmDb4XqwWjcZss6UFfW0hvG9y41bDrsPDoS4YDQ/C9JyO6W+IxWWOUpBXESqzoY2C+IPxwl+NHu2Cg03DLVY7rNoS05h3e8jrTWGFIXax8VAkM82VhbkGYXDRMLsJswVBashRKjkrVEAKhKqF6RJEQORfXwpHgVJicc1QjIVFwNB5YZLE9yeygDwrOCHM9GSLfIrum+erdd/PR8kf40K03k0pluW//oxTLRQwXPnQGimR8XFMCO1dhaOgEf/M3f+3WrF5jPvyRjyZ6envo6uqipaXlZc/z/kVjY+NEfX39DDAMpIBbZ2dnu+fn528YHBz8/x08eHC6t6+PJ598UudnqiwseNS311iB8mbfl6AS4YlXY6RG8YRqFFIJym8vCLiSOoUh1TBY6QEKYK3Drii4nUcYNhCUHTMjSqUaamtri6xbu256/fr11548efLvrLVXVyoV19raaq+95lodXDVoXnzxRfbt28fExIT+4Ac/iHbu3ElLS0vD1q1b/0m2LsuuXbtwkTM0ppk2sUz3W6tNXskqa0jV8aFtN9PrN3PP397NY88/zeS6LOM76inmbYzZLF8URyzhlTGkyyGNecPmdcKmPkd7k5LwhChSSuUqS2VYLBjm5x0LBVgswdisUFxyhBga6z3a6z06m6CjOaK13tGUh/qs4hupiWA48qmIWzY7rlotHB42DE0IXk2q3TOCb1ztdNGVEnJVG/zyB+DqdQFdjWBtFJcI1CidokSRwaljYtbw1H7LniHD6JRSCYWUhVwWMlnIJoT6rJLLOvJ1hrqskslAKiFYC8WqMjLtsWuvMD7tKGSEap2H0fj6ai09U4H5jhTuXS3I41N85zvfwxePD77vBqYXZnjq6IuEhFxIQbeCqVtFmpLo8BLhUuCuvvoa8wuf/4XdLa2t/7a+vn7a9/2jIjLheR5BEHx0bGzss6dOncpUyuVPGDFdQyeGaO/oQFX/dm5u7qm+vr5bEauFuYjSnHKupNn5POJDIV5TBiFQRzUM3iEBwEUauOUx2pjEY0UQovMm0ilQLkXMjjlciOvu6bbJdOpfisjxBx98MN3c0ixj4+Pa29vL+95/+8J7brrxP3d0dPzzkydPesViUVTVW7dunW7dtpWJ8Ql98okndWJiwtikUG1IUjE+GpkLy5wEXKisautha/d6dj34LI899jhFP6TU1IBai1HB+QI1XC9ICxPbG6k2J0mNVCgWyswcihibLLO9zzHYLXS1hqzrUNKJGDxxTqiEUKxGFEqGxSXDxGLE8IwyOuHYM2TYfVzwrEdDvdBWF9FWD21NSkej0FSnWONoyCrXrBV2rI23iGj8Z8LEpQBGwDkaM8LPXS04E2GJWGZRFUqW6XlhYl4YmzdUKsr6fksUOk6OKa6qbF8L/Z2O9gZozCqZtJBORqS9ONPAc4QRTC/C1JwwMgFHx312D1v2z/qM9XqU2pIs9mQpNtqV18aCiuBXNT4ccj7jxyZ46KEH2XLNlWzpWcvzJ/YRBMHZ33nLGUAcbDTnYZIWNxtEIGZ+bu7PN27a9J1jx461W2N+7/HHHu+uVivX3vfDH/YdP3ack6dOMjExSblUjBKJpP3Ahz5IGVoaGhr+dbFY+M1MJtNSKVa0WtK3FKFEDL5d7q7H7NDIvUMCgIgQOeeHNaVYh2Akrv9jY5DzKAAkJjwsTkNhoUo6maZ/cADf948D9Pf3P/jrv/Ebaw4cPJBqbmqmu6/36WQ6/a//x1/8j19wzvWHYahhGOrPffAD/ic/+ck/nJgYv/7Lf/nlm775jW+6ZJ1vqikvFjJQd4EtVK2JekAprNLf38e173oXx48dwTuwRGq4wFJHkmprijDnE2R8woSh1OxTbajHbIDUfJmRyQoHx0vc90KV3j0hG1sjrugMWd0R0tUOrXklk1TSSaU549DmiI21CxU6mCnC8ITlzKRycizixSNCxQlJ37ChJ+SD10W0ZOI1l0iA1Zj8E08VGhQTJ0J6FlwyUo3vnRPmCxax8NwR5Ue7fMbnYt27viZHb2vEtlXKQFcM/GU8xYiLJ/CW/RONIwxhsmQYn7YcOyPsGxEOTyQ4VjBMJNIstKcorklSbkoRpgxqHGoEG4ENFK8U4s9USY0XqJ92dBaS9G+5giuu2IZYy9TiPJFG8WeQC6UJ1RCrhIGsD17VHjl8WO3HPvKrqvo3hw8f/seHjx77Jz/84Q+Znp6mVCrR0tJMb28fa9espbW9TdeuXbuYy+X+Oi1yTFVvTySS6SAoOz/liZrlg8ecZ24Sb34jZ9NkVUfgwrcvANTEEJxzrvmeXT9cWy6XEcQsC3gaE72VqVlcRZkecQQBpNMp2tvb6enp2a6qjxtjftc593tXX311Z7VarTty5MjB3bt3Jxbm5uZ/+Zd+2R44eIBqpUJrS8tMIpH4o3K58lBxqYA6R5T0iBL2wttGtXtgxXJ0/BQP7nuS9218N1/4zV9mdOg0hw8c4sjR45w6eobRvdMs1AuFlgRBc5qgOUGl3idIGgptKQqtKebW5kguVRkbr7BvssoDeyu07A3Y0lhlc1vEQIcy0OboaHXkkjGNFSISQEdWaV8VcuUqQxQYFirK6Rk4cjIkjITFUjwQdGJMWNsDm/sC0n4sZhI5R7VG0VAX1aTMY17A8Ixl33Hh0LCweZVQrhrqs4513SHrB4Q1nUJTXYhFSCmohKA1tp8YHJaFgnJ6ynJ8xOfgGdg7nuBowTKbTVBoSrO0IU25ySdMCeqZFUDVD4TEfIA3XSYzWSY3FdJSTTDQ0Mng4ACr166hf90Aifo69o8f4+mjz1NxlYvHC4DYi7suAcmyGRsfixR2BARbnHOjxlpWrVrFtddeS1tb29LAqoGZ+vqGv6uvr/9aIpE4CcyLSBiG4W/s27fvD/bt25eNosDlGhOSrQcn5rw7diLxoo2Zl/FNC9QRROHbmgEsf4pGJzSEUUzOcChWYq65qnA+O06JxSSq1RBUiKLIPvbI4wTl6h/V1dX9o6eeemrixIkTQ81tbY/ls9n/dk4wum7blYUv3PCeG387iqL2MAzvAubHxkZ6R0ZHUWOEbBJ87yw58QIhIxFlMSjw0KEnOTI6xOaeNazpGGDjrTvYeO2VzE1OM3lmjDOnz3B6bJThkSnG3AyFRo+wJU2lLUWlPkG1zmOpNU2hJY1XdkwuVjk9H3FooswDp8p0DpVZn3Rsagnobw/o6zB0NRua82C8CIkUq4L4Ac0JaKkzXNGnVCND4JQTY5an9/s8vtexfbXh6nWOdX2xg1Oh6uFqm1+sZXgGXjiqvHjYZ2oG8lnlqrUhV20OuWGjkk8K1sa9yLin7xAxiPFAlPmCYWQShoYNh8Y99swYDpdSTFqfhfYE1dY0pbxHkLU4z8QnvQOvFJCaDbDTFRITRRoXoMvWM9C4mv4tPXT2dFDf0YKpS1IyVZ6e3s/hl09xYuYMi9WleNxX4GLp74kIkvUxnlAqFfXlPXu1qalp08aNG//r5OTk1Vduv1Lb6uv/L5LJoWX5bVVNFQqFfzE6Pr7ukYcfXvf9737/6kcffZhnnnlWPS9prIlHuzWKjTzeTG2s5+QlXq0E0BpvQV1E1UVvfwkANBnPoOocYk0cQA3neG+eR1Zh8HxHe49PJhuwMFvmkUd28sTjj2lvX+/anp6etYODgzd0dHZ+4Z577vlnzY3N01293SOFQmE0m81+OZfLXWuMIYoiVLV1dHQ8NzExifEEzXloogaYXxTeX/xcYRhwdPo0R6ZOkfGTtDe0MtDczWBLN93Xr2d1uJlovsLc5BzDQyc5deokJ48PM3Z8gblkRLklRaU9RbXBp1LnUWpJUmwVFgbTTBUdw7MB+yaK3H+mStuJgLXpMusbIla3Bwx0J+htDmmqc6SSsXYiLu5OpKySsvDuDUpLXcCuwx57Tyj7Tls29Pi05OHQaaVQMewbsnpmtMJzR5CxaY/V7crHb3Ks7VU66iMSKwLJy+IkEvf51TBfskxOK8fHfPZP+OydhpeLGcb8JOUmn2JrkkpjgihhcLU14VUjUgsRibkq/lSJ9ExIc1HoNvWsal7Lqo0DdA70kG9tIMp6jC9Ns2v0IMeOnWFsdpJSVEGoeTBw8R8qgst7mIwlWqrYhx9+WLdv3/6vVHW/iPy69Twq5fIXZqemfm/v3r35iYmJtV//+tdbjUj70NAJjh07yv59+12hUBBj4os3PRFy8GlLXT6ktcecczDKmw9Ky18a26CH4dufAQB0JBIJcZFz1rMrzIW3hsfEtWMiIXi+i1V6nNNyUNZDBw5Ghw8eFmuNWs+Ttra2tb29vWv7+/sZXDVIKpX6nfvvv/9AV0fHyfbOzpOVSiU3Ojrqzc7MqfWtaNaCF7evLrxWfOUlMLVeeDmqcGLqFCemTvPYIUsmmWKgtYdNnWtYv3GATddtRssRY6dGODF0gpNDJxg6dZJTh8eZy8elQrUtSbktTTlvCbI2zhC608xVlanZMkNjJR6frNJ8vMygH7KuxTDY4ejrdAy0O9obIjI+SA3nyPjC9kFhfX/AsfEkT7zkeGIPjE5aCuW4Xfu9p3zJ5vvIZSJuv26MW7YWac66FanwKKq1yGrErlLoMz5nOTamHJhM8vJ8kr3zHmPJFIXGBMVNKSp1Ps4zNQagwwsguxDgzVRITpdJTVVoWjT0ZppZ2z/Iqv4BBtasoqWrjZIJOTU3yhOje3n5paOMz01SjYL4PYhgrbn4uvuvIt64OoNrSmJnQzly+Aj33ntv37Gh4/d997vfHQ6DMP/Qgw+1j46OcurECY4dO8aZM2eYmZ4OnMbtJetZK+ASqbQnAtVywLEXq7T3WRrbU3h+bdT7zb4n4RWejE4d4TugBKASVAbUUKOTLbe9HUZqftLnVevA/EzIy88VmZ93GM+nv6dX2jvaJQxDMzU1xfj4OOVymeHhYXf06NHQRU4TCZ/Gpiavp6dn4+Dg4MZVq1bR2NjIC88/TxBUxOSTkLIrTKqLb98sNVxQELEr5cxitcie04c4eOYo9bl6+pq7WN3SR29jJ2s7trDhmq2U5gtMD09w5sQpjp4Z4ujuYSaYpdiRodSZptCWoJr3qCSg0u4z3+xBMWBkOsnxsTIvTIT0T3isPZ1mfW+CTQMe125MUleXISAFkohtyY2wti1N18YsW65v4PAJR6FSwfMN6YS/0NU9WFzVI/VNqUPplBunqDGjz2kAGuJpCS8qcHpqgd1Hyzx1ssjTS8oR6zGX9wl6k7g6nzhdACKHvxCQnK3gz1ZJTlTILkS0myx99R2sHexlzeBqOgZ6STZmqEjI6NIUTxx+lKMTJxmfm2CpVIzXu1nGPuTS3L7XKEUxBumqwy2GMFmRH/7wB+65557LZLPZtYVCgampqUqxWCSVSvlNTU2mqbmZgYEBP19fT2NjI/lcjubmZkqV8tLU5JT39DNPp4aHT7M4nSSsOHxffuJosyyvK4m5NWdF0gwOJVL3tgaA5f62ca+OxrVU5byfKhLGj4ec2heiYVJ3XHMlP/dzH5hta2ubiKLIP3F8qPPb3/525sSJE7pmzRq58cYbE/n6PMVSidmZGeZmZjl8+DAP3P9A1VhjrbVWfNB8Ak2ZS+NZ+oYniSDGEKFML84wMT/Fc8dfIpvM0plvo6uxjf7Wbno2t3HF5m62L17LzNg0Z44e5/DJ44weXaC4lCBY34TpyJNKJGnwMzRm8jSk6kipJTsf0TxvyDoLYplPKPtshAks85WIQjWkFIRUXJVIPXwy5G2a5l7Bm5vHiSORS2shX3V7y75S6EWjDow1+NaQtD5Jz1DnKdlUyHQ+otJZpbtpiVsbDe+qtxS0ykRhjrniIvPVIgUXoMWA+olFmibTtHk5ejrr6dzWTmNnG/nWBtL1WYo25Pj8GEcPn+LE1DAjs+MUq8VYx8HEPofm0u/316R4iiia96E9g85WoaJmcnLSGTFmw8YNdPf0JJubmwnDMEwkEmP5fD5qaGjQdDo9m8vlJhsbG4P6+vqn0+n0f37+uee/NT09eePQ0ImoWHC2WoW0LLsIvfnDMS4BasCtxlnA2x4AAqLYB3CFS6I1+o+uONXKTw65iFHCQBk/4SgshuSy9frpn/95c+stt94hIj9S1eZ0Ivmdhx9++F19fX3RF3/9i97VV199V76+/omlpaXr5ufnm6cnp2VmdmbT0WNHe7733e8xMT6OSXu4piQkLm3a+EanSUz8Mvi1SZlqGHB86hTHpk7yxJFdZFMZups62NC2ig2rBrl223vZNvsuipMzqDV4zTmixlhwxRNDEg9PoVQps5AqUJIy46UCi9UCS4UCi3tKFCslCtUihWqRIKxQjUKiUohMlEiNVbBlS7VcoRpU8RvT9Zm+fL1rzyF1HtZaPATP+CQ8n5SfJJlIkPTTZJMZ8qkUmXyGAZshF9QhnqFa14nLC+J54FkSkZDqqZAODJJOEGWEUCOmivO8NHuCU6fOMDQ9wmxhjshFiBiMCAnfj0FJdcuIA7wNdw0UcYqUQ3AOL+HpTTfeZG688aZSf3/f3rb29jNNTU1P+L5/j4icrAGBFvA836uEQShAPXBdfT63XmJtClNaUoKyQ0xM6nkz7eb4z1h7YVlhXNXh3DsgACxbQ5877BEnN+ep6SqCC4W5yYAgQNs72k1He8cccHzfvn3f2fmjnVcfPHCgdXR0NPrYxz/uXXPttX/Q3Nz8ewDGGBKJBOVyGVX1N57Z+MDS4tJ77v3GNzVCjfpyVqPgbXgsN0HOGqYqnrErkbFYLXFo5ChHRoZ4NFtPd2M7HfWtJP1EfNOnhqmOVigHAcVqiUKlRKFSphiUCKKQIAqIooBqFMWbySxLMcRtORM4dLqKN1pGpioUC+FZIqRAtbio5alFopak2M4s2pIgTMcpt4li5qBDUY3Hezxj8ayHZ30SnkfaT1GfriOfyZFPZskkkiS9BGI9gihgcabEVGGGiflpFoqLFIISURTWTnqDZ72Vw0JXwMa3736xfMoGDjNfgUB1YN2AfvZzn1vYvn37Z0XkPt/3CYIAVc2q6mdnp2d/6cjhI+1LS0sdD9x3v7n3m9/0yuVyaxiEMjQ0xJHDR/B9TyolCCrySvbhT+YnXtI86MJKgMj9mBNw3Lc8C+q9qTevjqAilBcFUXG9vb3WGPMDYG0+n//In/zJnzB85kywdetWf8dVV801Nzf/3vDw8K+Pjo5+6cUXXpCGxkbZunXr/OTk5Od7eno+29vXO5qty5rZwpwSOolX/Nu4qF6rztRz2jySAFHmivNMFWbZfXIfupxB1a6nWbb9rpVYdoW6EiPX1ho8E8twqQABMFOB8RJMlWEhRJ3EwzPmbNBWQUxVkdEyLESYmRTSlcI0poi8eHLP17MW5AqEUUQYhZSqyhzzDM+NxdOKuJrLzwpahZi4FDJisCKxQ7Dn1xoLevlT/Dd5hyR0SKhoRLRxwybPGPPHInLfkSNH/mz49OkPHz1+XP+/f/zHCWttc6FQZHZmhoX5eYIgwDkX3yPfx/cszoFnfAoLEeWi1iYw30yLXONW66vWrurFS2gvsA1oXlE7xZJYUc0R6E16o9WMzqJIa4izaCqdQp07DTxdKpXuveOOOz4RhqHf1tIy2dvf/wVVNU8++eTvfvtb3+ra+9IeMMZ98de/2HHttdfeCfxCGARiJG63CPIOXWTnXrp4vt2KwRoD1nvD2K+vyBL1FRmGhgJzFZgoYSbiTU1UO/KNrpRs+splFv9sKYTSAjpXImrPoq0pJJfA+YKaeMbgLLczNkgzxMxP7Gu3ic59r7qcGeo7+Y7E01EirpYNxH+GYVhQ1Q0T4+Nf/K/f+S7T09OkUil836e+oZ7Ork42bd5EQ0MDdbkclUplyfO8gw2NDYnHH318273fvIfKUkC14GoTgfqT34fW+AP6yhJW5OKdZxcUAIw998S/gJILwfMFLxWBiB06fhzn3G8Bf7lhw4ZPRlF0E5ABnhaR+enp6f90/PjxDfv27QtuvvVWf8vWLaatrQ0R+ZtCpXKLsZZSqeTEE6PeG9t9v5NOnXMlFPV8fkvARAKLAYwUkfEKshQgATg556TRnww2EYGdCdDFRRgvo00JaEsjjQk0Yc9pX53j5vcaG1p/IsXlHX43rIl17Y2Y0bFRksnkPwL+am5+/s9ue9/7bi4UC4u+9Y/kG2IAsKWlJUilUuPZbHYsk8kcB/YAM8C20eGRhzOZrFkqzEmx4AiD2OXqTa1KOc+fX94MADGmZsAgZ6edXrOM+QlBwE9BW2+SkSMlOX78JF+7+678zeO3vHD40OGxifHxZ0T1gUwuNTg1NfGPXnpp92994xv3RB2dnf5t77ttaseOHf8OeEREXhoeHn5p+MwwpVJJTYNPlDC8PXDyZQGsEWdgIYDJEoxV4tO/Wit5zpMhJwqu1sokVGS6gpkPcJMVtDmFdKTQJh+XEMzP4PWMr0EM3OIJkvTAGDN0fMgtLi6uDcPw3evXr/8NkVj9WuNU19ZS4V5Ap6enr1hcWPylQrGwarGwMLi0WEgfOXrUOKeiKlTLEEUOa+xPvjM1oGbZa5tX5V5vewDw1A9Mrc5fRrx1uQJ8s6duDd30E4bV2zzGjlqGjxd48IcP6MGXD6Z6+3oGuru7B3L1+c8GYUi1XOG5Z5/l5NAp097WjnMuMzY2NiAi+bGxsf/zuWef2/rM00+poJZsrPAiPwXn/1tKnQKNT/szSzBThYqeA2Sdf32ttQ2w8r2J63jmq5ilEJ0to/0ZTHcWkvIzG1RVQDzQxgQyaliYnjN333WX4vTu06dOPY/IhHMud/jw4atmZ2f96elpU6lU0gsL8wTVKosLC0xNTzE9Ncvi4iIzM7OUlpbwM45UDjxPVtSv36D6XxYBw8nZPeVwGPGwYt/WAKAAqZR/Aqcr5D+t1aLx1/k9oTVK26DH1R/IYn5UZOR4RY4PHdHjQ0dUxFNrRa21NgxDp6rWWuTI4SP8+X/7s8y69et+N5VKMjMzywsvvsjE6AguLdCagLStybCYn7kAoJUQRgrIWDnWH5CLj3fEZJzY35D5EDdewrSkIWn4WX2YmhiJtPhIU4JwvMRzzzwrw2eGE/39/e9Op1OUyxWmp6cpFouUS2WWlgpaqVSoBhUXRSGqiDoVJMJaI8mUsGpLiq7VfjxwFL05IoCq4pzDqTs7HyAmZkS+3QEAmAiDAMGY2GAPcZEQOodBic5vOZPwDQNbINecZfxEiomTocxNhlJagqAS8589X6y1PsWFkIXFWZ7b9Ry7du0KbTyaLGLF2qRFunPQno4pqfqzd1Qpgk140FuHGgtTJVzFYdzFO5hluaGriktY6MxgOmNmJcrP9EMAzXjYVfWoCpWpMkePHtHDhw45RVANsdYYY+LZF883kkhbcvXYVMYjlbakcpa6PGRzQq7Fo21AqG8xiJM3R5BZHv91tYxBNP5dG7di3wkYwKmgEhR9z8tEqjGYXBOzeGsrRLG+0NZvae7yWXWFUi7EgoqVciwl5XkG60VMDfucOeKxOBtRXjKeixyer0jeZzGTptCcwWRsDf+Sn8EFKriE4DpTmLyPN51Ex0pE0xVM5cL7RELsjSgJH2cU6jxcfwaavZ/5zb8SAIwStfiIn4fpBP5cVTIhNpcKSKQgVWfJ5qAub0nlhEQGkmlIpQ3JtOCnBM+TWDE5aRAbxYY555vpORObg9QCssXgm7exBJB4ptEA40FYPZBKpXYsFgtqxIhzsbCH1DTuz98XOa47rXVkGwx1DaamkGMAj+WX7hiA1VcmCasaqweHiucbRoqWZ0/7LJY8MFqz5r4IOgDvSMCqpuqd9QhTFlp8zJkSMlRAi9EFLn+Ha0igq/Jo6JDFAHxWpLrkZz8GYLRG2Gr00Los2p2mtSXimlUB9SmHeILvgfUFW1NIFqPxgJgoIjYendZ4KIro/BeialwtxHy7+Nr7xpD0/Lc/AxAR/eoz3ylbLwEsqagQOkvgahqBTt6aLvjy70RuRbE1JkOcFaf0k5BIa00VVmOhSitMnzFEw/GFftN8i5/yo8poLJlF1kN66mAmgHIJVamNQJ//BRBjcc1JaE/F7cAgWhFT+nuQALyy0lUQz4JvMDloaItoytRI77VM/VxC3ErKvgyJL2+D81iIck4JFrpYpcnUZgCMsST8xNsfAACSXkKSvn92VNEpYRTbZF1w/11e/+hThVgV6SxLTRTKFUcQURPHFP4+PVQFlwDJxrp6MZX3rbkbqyj4JgasqhHi3M8ejnq+V8U5gipUqkKQdGfPKnlVwLiYvBNVwihuxtQSAHyxJC9SAHjrt/TOWgTxfVJ+amVjOoQwkh+jCF+Sw09+/Mudrxrxz9iBpUYwjUk0Z1FPYn0G5E22QnWZmImmPMh4qDEY34N0Ms62lJ9ZDsCbeTg9u8NFLuUZs1zzx/vJLUu4oXjWkk2mL2z/XsQMgEwtAGhNLLMaGSIXy09f9rpN9O/l6b+8ZJwYXGsa8QWdqsB8FWZDqP5kKaqa4hSa99CBHNqSijkFlRBZrKBBGhU/lgP7+7j7hZrWhV62slKdoeJiw1xEcaqaSmUkId48MPnK1ONyZgC1R8ZLkk2kVt6BqhIEQhS9PRz8hBE8o39Ps4CakEdSca1pdF09sjqP1tkaePqTRCji0KktCehJo8m4haphbFFGbdJQ/j5mADXRF2vP9VK4lC8Xi4CqQiWMzWfjQS8hm0ji+4kSUHjbSoA7b9lpAIyxQw3ZPKAagxRCEAlhzXrr8tyb5UFpSCaUhBfPc/P3NARIjQmsvuDqLJo2KD+5bSS1mVxjDOqZZdEztD6B666Ln0f172VutczNS/rgWWq0l0v/upFCpSbhrMS846SfoM5PL9OQ354AcEvtzzo/Xc5n6s4RK4BySBwALn+QJpkQkr7yjkOslNcdkKx1jV77er3JocrXXrAKnsSCKG9ywUoMpNTEXcARxZ2AviySiF2VVC7sc8trfL3Za/W2hgGJ+/qevRwFZty+ChWKFRv3v1QRMVqXqiOdyJSA6tuOAWSSaZtL1q0sOqdQCZSLJFp6nggYJDzFs++klVMrrK2cLbT1nCk6XTaMrJ3ayyjcuX9SY4AtHwPnk4Eag0l5Zw093+SePfuNAVub9Xij/r+es7uXPwzEiKEst8u0pmqrK+Ifwjmf1azYD8aA23JwdO+cW+kZh1drLeulrARq1zpyQqli0FoJ4BDXkKs3mVTqoIhUVNXUeDlvTwBIpdJRPl1XU2uNO5flQKgEcjnvS9waRPCtI+H7yNteqCpOBBMB5QgqERKBU8VGEpNrIgehe+XeMRKPovqCevF/izWQiGfuxZr475c39BvtSgWs4HJ+bOJbayW9PggYRyNnYxEPXZajeoOOzvJzqqlxNZxDIwehYkJwUYQEIJFCpJgw/swu0tqJVnstK/EEnm9qz6WIJ5ikjybl7SVz1TJba0Pq09ErYvmlawIoojH4V6wqikFwWCPGwyOXSP/hq0Lu5Q0At9wSFwFpL3mqua4Bz/g4FyIIpSD+ktow2eUMBUlPaUpH+J4hjMzbHwScIrMBjBaQ2QoSxNREQ61V+oqaWlbqeKyeDQgpD3I+krJo2kLOg5SHpgwuURMgfZ3NoRbI+fFQVPAm0jIjqG/jSUD9ycChmtjpVwJFK1Wk6GItglKIlAJkKUQrUS2DEYwqTnWlQFuhd4nE6r8SA16kDdKcjLGHhB+Tnd4m9EE0Nq3xPWiuE8S4c6b1LtVKjg+0ciCUQoMTh1FIeUmTMQnqUnXDr5WwXc4MQAGas/X70japCetLSUMEpRgohWq8MCO9HBFAVi5a0oO6FPieEITyNmYCtU1pQZoTGE9waQ+ZqaJLEVKOXpkqn5PbiyoandUQlHIF5mMzSPHjml6yHjQkkLyPy3tI1ouzg1ed1saBJg3alkIKixC9QSqg8QlskrXZLvdKGuVZdhuomDigFANkLkLmQ3S+DKUIqbi4H3xuml9bpytiOOe8vF1WCzKKJi1S7+NaUmhLAs3ZWFnubYUeY5vupC/k01EteF3qgCRELmKpEtuqxy7wjoTn055vgtiS/IIfbzkAiEhUuwLfrJSK+1sbmjafmDjtPBFTrVpK1RCHu+xafEaUpjpHXTKiUJLYUultxI4R0JTgUiloSaGLVZgpE01WkfkAU4pAXY1jX1PZkdfOQUVBqrEjL4UInSzjJQyuPoFrS6NNCSTr4ZK1+fFlkmTCoB0ZmK0i00FMKX2N9yrqiPIxiUjcaxCIpYZZVCJYqGAnK8hsBZ2vQgCWWjmjcs7RJD8OFJxjJY8qkSeYjA/1PtqaQJtTkIlxi3cClKvEyH9LNiLlXy46tBA5w2JJahmTEDhHLp2lIZ0rArNvawAAuPPOO0VE3EMHnxxprW/afHL0lOILkVOWyh7lICRhL2/q5oD2vKOxzjE+/85grovaeNFYRRsSmHwS1xXBRAkdL6ELVWQpwjiJJ+9et0ZfPndiUM44QcsOKZVhqozkfUxbBtqSkPfAj7ek4KAxgVuVw7hF3HwlPt313EWuaMqgnRnI++c2E85u2Iois9WV9y2FMParW1atEXlTUncsdz1szbatJUXYnESaUkgqHiR3ohh9ZzQcFUhbobMxIuWdh97lBWIAQWRYKEhcEgk4Vc1lctKcaywCcxejBLigAHvLLbcYgPp0bryjoY1opeIXFitKsUqcgl/OkVyN24DNOUj4rrYB3vYIsNI3Xh4zNSmL6a7DbW6EDU1obx2aNSubIy6gzGveouVTdqVbJmAjkNkQji3AgVn0ZAFdqtYcZixqQTuT6MZ66K2DxgRkLZK0SNpCkw+DdWhnOnbsrf3P1dIFma0gR+Yx+2cwJ5YwS662+ZfTfPeGE8jLdGRxihgH9THb0G1uRNfXI50pNL0sKG/eMZt/BexOKC31Dmsj9LIwTZUgUpbKEg9hqWBEtKWugfpUbgYox4m4vG0YwMqjta5loTPfshKKjApLZWGpbGjISG3xX56NGLeRDN0NytFMyPi8dxH9AC/uDY4skLWQ8ZAmH9eSQCcrmKkypujOq9++HAgkBDNVRedDdK6CdmWhJYUkY5V2bUugeR8tBGgpwkY104mMH7vhJAGNEDVgLFqqImNlGCnBTDlWHX6dJFje8P3Vzoach7SkidpSaHMCkgbFISrvWIahEWjLO1qycSp+ed6mUAoM8yWpsQAVa63rbes26URyl4gEO3fu9G699dbwbQsAy52Almzj0Z7mThJ+IjaQEKFQNiyUuOwS0MuLqD0f0pZLMLHIZRlMOv/tr6/Q7IwyFumrQ1rTMF7EjZbR+QqmvCy94F6jpn7VQtVz/k2gmDMVmA1xvQHak0GyPuBiubR0otZtiNN25+K2k7h4jh0HZr6CnCiiY4X4fYicqwX8prFsQ6yuQ2uCqC0FbSkkYTE1+Th5R09vCKmE0t/iSPsOhxeXVJd6fbjYvXmxLCtDQGk/aZrTec2lM39T238XvLAvNANwAJlk8qm0JirNdY3+5OI0VhzFCsyX4qEgu5ICXw7+VHxNskmhuzliaNqwVIpNKfQdtrBeuXnjYCkpD+3P4VoS2LEKeqaEzFXelL/Bq3MsRZFCBMcXoRBAfxbXnEI03uxxr7nW0lLBESJi0AhkoghDRWQmiE0yWOEtnccnjF3jXbMflx3NKUjFwhlnTUHkJ2YPb3P1RkPG0d7gMNbEzsuXYWWEzjCzFHeyAEKNyCay0pZuknwydVFagBeMAdQYSCIiT/sqLwy095ooipwKVCJhZsmjEMY4wGW/wSr0NoV05qO3tRNwvndeJcKJg5xPNJhDN+eJBjNEGfuWtomKYqoOM1zGHFzCjJRixxv58TgkGExFMScLyMEFzEQVCfU8UO+zPAYjSpTzcKuzuE0NaE8Gl41JPj8tD0WxnmOwNaQxE10+ixmJB4DG5y2hgsHgolDbco3S29i5CInRd0QAANi5c6dVVelp7ppY2zlIqNEyaMnskjJfrFnzqbl8u4jY0SWX9uhrhUzSgZNXuNq8k78MgnE1MY62NLK+CVmfhxY/lp0SffOa/1rLDJyiUyXk8Cw6XIx1A5fx+2UAoeCQ4wXk6AIyF6Gib15eXBSRuEYWD6K2NKxvgLX1mIZUXD7osp69vOO/pJYVNWYCBttCUp67fKeIOgoVmCmAc7XBA7HRQFc/LbnG/yIik3fddZeVi0ByuWAQ8JZbblER0VD1m52nmz/qizVSGyxfLMFcwRA1vD05nmccA60RQ+OW4+UQnKn12/W148brVSn6Gv+ON/GzN0LsXu/3V1pp51h4JYCebFzDn1zAjFWQoOa9qG9ed9GoRRccHFmE0CH9dYR4sYZdOYITS8jpIpSjFe2/88m4HA6X8TGdGVx3hqjBw2JiavDK0+nFv2av97PXu5dv9Lvn8LJ8z9HXZmnMBMRdmUudcdT4/2qYKXgsVWKMxKHkkhnTnW/F9xL3qKrcfffdF2ePXPB5GxOC8ES+/I0X7vuXfe29G85MnHHGGlOqWmaXHOUwJOW9DeIgqjRllOvWVlnfaVFqWoX6Gnf7TSe68iZu44XhAa9eEqJSO41BChnmD1uOPL3I3GTMo3/lVMrrv76rHRimEOCOL9FR79h0fYbFJcexJ4vMniqiFX0LOoqKsULHgMe6m9J47T6aUKCMitRaeq/l+nsxr9X5POcbRfuz190aaG+ISHiXz7FYUMLIMLFoKIUGEYhcRGO6RQabejTvpasionqRkG3vokUvVXnu5P6F1W19enrsNGIMAY7pRaFYNqRzlx+NV4nj9kCzw7S4nwGRoHhhFvpT5OocLz9WYXqsNljzpuKXrqx7WQyJji9huiO8RUc4FEClVsGf5+b309C/0eOK96RZtdXDWAcu4GfhEUXn8h0uw/YXqIbC2Jyg1Zrljqjrae0ynfUdI4lE4sjFqv8vWgBQVSsi0emZ0aPre9Zc+8jLT0WAEYSJJY+5UkRr3hG6t8el29WklX/6H/HoTCqrbLo+QbZeeOnhEmeOOzSI21NvpmJXFTCG6TF46gdlUKW0qG/yhK39G3FARCJl2HRdhq3v8Wnt9okiiKKfEe1giRGZy3VwSG3kerZkmC0IEbGytRXfrenoMw25/H8SkeLyfnsnZQAK0NPY8QeNfvaj+WyubrG4pAYjC0WYXjIMtIbxtNdlvX9xXXpWVvxnIQOwOKekM5bBKyyZnM+uBxc5uT/CVd9sIbM8cO8oLZw/tq21KadkUuhZ63HFzUlaei3qdIUr8NOabmmtVDk7zXD5GAqCEjoYn1fKVQExhBqRT2btQGOX5lN1DwNcrPr/ogWAZUECEXn5oX2PDw2292174dhel7BWnHNMLhgWK0J96vLOBztR5orC6UlLsSrIz5SWlQMseBa7rpHE5BLlkfJ5pKu1hb5M7jmPKGAU1INEfwpvY4bDJcOxYz8bZqFxcDOIQnNG6W8NSfiXj89WDoSJOUvkYiK4U0dbrknWtPRT72fmAPbt26fvqAAAcNddd9k77rjDDU2dHl/fPqjPH9mjzosv5sySMl+01Keiy3ozhfh1d5/2mFmKQR19PazoFUj8G+BKl7wL8CpM8jWG6Vhpq9ZwdadEXVncbATF8Lzc0N8KLqOA1luKHXWccAmOn6hNK4qeC1W88r/1dT7nxbhmr/ez17uPb/jaAmoQiRhsc3Q0QdKXyzRToixVPCYXLYEzWBSLRBv71tr6ZOabwJELVQC6ZAGg9Y47RER0qVp8bH334O3ZVIYgqgKG2YJjpiD0NDhEzOVLDtXgHFQDQ7liXxkAfibKAVkRjjAtGVjtMEcKUK6+ucm8twRSCeQMrK4nyCcJKnLOrMVPf4oly0pOGMLQ1dSl9ZLfSYMQqjKxYFgsO1CLUyXtp9jYtYa6TP4vRER36k4LvPMCwC3EZsBZP/0f85L5nf6WrrbDI8fV+CKlAMbnLKU2SzbJZcunzvFwiNmIP6tytgIuKUh3hmgxwJ4JIdRLckU1IZjuLK41gfqx4MjZDaI/EwEgXjN6Dpahl/r2AUol9BibgXJoMQKhczRmGsxgfadrzTYejffZLRcVzr5o9Lxab9KIyGJvY9eBzX3rJAyD2vLwmJyH+aJB3nGc/J/2PKAm3q0OkgI9GTTvs0J3uKirRZGWFK47EweC6B/u5EUCHlCUxZIwtWiInK25bGm4oX+tNKbzfyQiB2vsv3dmAAC4m7tFVWVte/+XevId1WQqLTjUIMwXDRMLQhhGf1+Nuy7Z6RETheJUXBuT0JmBRM0f4KKFW0XTHqYzG+sIaIxS/8PjYtzEWAdxbN6wUARwqHNkE2nZ2DmouVTdzrjMbr3oW+ei3sHP8BknIuoZ+3BrqmF4sLPfhBqpCBSrhrF5SzEwP7up+DsBF7AO15rE5exFbscJNKWImrzLN9bx92X/C1SdcHrWUgx8lov8lmy92dS1TloybZMAk9yi7+gAEAvdqgldZNZ3DIxv7l6Lajz3qSijs4b5osGI+4cy4BLlA4IgWYs2p2NZ8QvOTuOuuPqCtqfRrFer+//hcdHCtsbt6skFIXAr+Va0sX+d5Gzmq77Pnrvuust+5iKRfy5dAFiBA8T1dXT/8ermXs35GVmmVswVHOMLhkpkkH8oBC5O+v+qL6MS+wi0pmKFX72wubh4dk8xOQ+p81aGEN/Q2ecfHuf1CJxwZtrURGxj2fSMl+LKgc001NXfLSLBpUj/L1UAcAAJEt/sSDePDbb3mcCFTjAEzjI8I8yXDXIeujL/sMF/fKNp7UJHApEIoUAoQiBC1ROinE+YEEKUECFECOBVX0IVoVr7vvqqvw+JnWkiUap5n2o6fp3ALr9e/BXJK1vsr/jSfwgQb3yHlXIVzswYyoGpkb1V2/ItZlV9t+vKNR+Ci4/+Lz+8i/6Rat0AIFrbMXh46+CGzr1nDsX0cyeMz8P0otCcXvbv+4cw8BqVfNyPRnACbhk0UbCqeA6sOqzGevFWlUTksBqz9DwFW47iQGBiwS3jpGZtHWcJtibV5Qw1haC4oxCJ4ohNXiOzPAVnsE6wSyFhAJEooRFCsxyADE7i70MxhNads8AdRs9OBC5bEvxDUFi+pcL0omVqQQicYI1DjIl2rN1mG1K5e0Vkfw39j34qAsC5ZYCq/pu1jb0PtdU1eVOFWbXiyULBZ3RO6W20ZBMOd/lEVi7rotPX+uY1+DIqseXzMolOXLzBDUIqcqScI+EgGUIqcqQDR11VyIQRdUH8fSp0ZEMlEzqSoZINBS9wzJSVkhqsKEkx+BiSCkkUqzE3IlBipyIRnINAlCpKIDVnWoGKMTRMBGQWlgg8oegJJU8o+lDxLAVPWPLjnxcShiVfKHuGijUEFipWqFiDE4hMnLHEvgVnR4/l9a7Za+CYconXCVwOzV/F4ChHlhPTQqHkx7J1zpHxM3LD2quks6H9LyEm2V2q93GpAoBDVYyYx148ue/0+t7Vq0b3PR15vrGhKmemDOvaHRlfuJST1iststiW7qzAxiW9s7VJOTGxFFktD5aVpryLfd5UMJEjEUV4ISQiyASOxmpESwVygWIrAamyI1t11FWUusBhnQOnGKdkndIdKTk1NeuseHosQDiFw4ngicGpUAGCmuZ4HQZfHJMOZmpAn9VYrvxc3b8EilUh4SLKkSMsBbFQuREy4qhTQ6J28leNwZnY4ETFUkgKSwmhmLAEKY9i0jCasMwnoZA0lG1cSjjfEHmGqjEYFBF3zgSersgdqzv7xpxc2qNCNQ5Sy2q8l+YRuzgtFISROUMlUoxRqs5F63pWmya/bmc+mblP9U5jRMKfqgBQKwMs6lw5Kv+HLd3r/vTpAy+iCgZldskyMhfE2v32Ug4IKdYI6XREXaiXlAq8MkOvFjERQRWCxQCqsSGouLhoF1WSYtEogkJArqikqwGpiiMdCq3ViPVBRD6CQ045HEHg4pLAihCoo+zAIdQZeJeB20Spd6amJ6jsRfiGwMlIcQTxpqwJgSac0GMDctYyZBzTNU1eOSdIqXHgDMlYn4ZALaIRpvYeXAiRCj4hORECFaouwGJIG8GTgGDOgggJGzHgQSMeUybEeAn8hIGE4nsGk01QnzeYjGEygoWwNlFoJfYpNKBJSOd9bIIVe/JLQnRaCQHxIE7Gp6ZX7Lj4DbP4IDg9Y5kpxGmQqpCwKb1x4zXSlGm8R0SqO1U95UvupyoALB91tUDwNwMN3X+4prOv8cDYceeLNaXQMTSeZLClQnOdonLxL3AtltNQF7Bj0KMSuB8z4Xx11X0eg7RnT/tlKy8Tq/aYKCAqw/E9ZU7tK1GZd0gY227hHB6Q8QxRCCUNWbJKQZSadxDHIuGZUEg5Q9mLSDWF1GWVamhZmDdksiENDXGpcHLSY3rBsB6lyTjC2kn/vMIRG9I2ENHVEWBs/MlCpywtGE6PJJieMWSyEWvXVuhsixifTHD0uEd3R0R/f8D8vOHgkQRRpGxYUyaXizh5xmN8IkFPZ5WGesfUTJJTZyy5uoCOjioutIyPW8LQ0NZSQcQxNe9xZDaBcRHpZEgmW8U5RQNLWDQEk4tc6RL0+paHNWTJuZX5BpXYCdnLCz3bcwys8/HTMahp0HNsw1/tYfjmwLcfh1XPeh+KOnJpxffkkhiBCrBQMZycEopli8Ej0kB769vt+ob+cm9jx6OgcguXVoP8kgUAEdE79U4DVNd3Dry8dXDTTYfGhmrja4axOcfEvKUhW6kJRV4agCWfVPLtr57l0jexIM59lrO/EpfMyydlDSILlOQiJBaU5GzE0rhyZl8JOxOwvt2Rz1dJJCOMsYyN+Zw6YzFiWbe2zOYNFVJJh3gCBiolw6EjPi/vT5JMKbfcUuKG6wJOnTLc/1CSDetCPnB7kcUly1e+Xse+FzKUxJzVEQQW1WFSyvvfW+JDty+RTNaCrBpmFoSHH85x9zez5Oocn/5ogfdcX+HJZ1J8+W8zvO/WKj//iSUOHPL5L3+WJwiEX/3CAhtWh3zze1l2PuL47KdKXLOjxGPPpPnLv8qxfVuVz392iUoV/vrvsiwVhM/fUcL3lQd/VMe9P0igyYArrirznhtKhFUlCA3DIz6PPJlmaDRiqqxMJQI2rgnp6YoII0e1KszMJDhxxsM7NMc1zVkauoVyzqPUJAQZqWEILrZJQzBuuUR4I12z1wr4r3UA1JbrRU41XE0g8cyMMrUo4CxqIoDo2o3bbVO6/s9FZO/OnTs9kVvDn8oAALCZzSIikar+z5taVz3fWtdsJpam1Rcr5RCOTyodTYaG9Bvb3F+MVssb13JvPMwiGqPlzsQ1tF82+IsOf8GRmXUkp5XMqCM9HpGZUU4tOcKi0t5X5fOfW2Ld6jIJT3BE/OixOr761QYiArZfUeJXfmGJTNbh+fGiDKsRL7yY4b9/Oc+ZUY8NawJuuGmR5r0pnt2VoLXVsWlDhbkZoSGXRMURqnlFPlzbCqTSIY1NISIRY+MeiYRj1UCEf9sSLx2A8dEE2YySaqiSyVqsB+mUI1VfJVtn8G1csuTyEdnGCql0CutBfUNIY3NEti7EWiVfH9HdXaFaMWRTirXCpg1VksmIvfs9jCbJpYSrr6jw0Q8UcJHD+JZjxzyOn/Z4eSTFjAlIZAzvv63AbTcXicK49Nm3P81f/m2ewglD90yR3nphodmj0G0othsqjYZyk1DNC0HG4PzYrrw2m8Yb6/690feXDvUXCVmsGk5OWhZLghglEqU+kZPr1+2Q9qbm++DiGH+8rQHgM/KZSFWNEbN7/8ixXVev2XrND1582KlRG6nl1JSyug1yyRD7ju0IxsSM5KKQnIXETEhmwpEdVdLjjsxYRGrBQSTLrnaUjRBISH29snFNhdVry5QKHsmEsGlDhUQmorBkUZX4dI4iDhzzCELLxrWOjZsqbN9WZWTUJwoAdYgXokYJI4dTBc9gvLi3EeJwYhC33GIzsRaSE6w45hYN3/xunqZG+NynZ2hsqNLfo4yPEMuF14A2WwuU6qhNbDqsldjYRZUoUlQNxsSARhTEocazgpGIUIWwZjVmbIBzEAYG54RcXUBnd5VyVVlastTXh6QSllw6okSIitCXD+jvrdDWGVCYh2QSygWfbF3EmFNYVLLzin8yoO0FJUwJ5WbLUpel3GlZ6hCK7UKlyVLNCrEexTun5bisiizOMjprOTPrEUQenjgEjd61/irT4tU/3pjMP1gbrIt+qgPA2QNU6Wvt+sN3r9txz5OHnpf50hJWhELF4/hkRFejkk/DO+l2iSrOWFIzAU37QupGIjIjjsxYSHJRkUCQaJnJYFbSTcVRAgIRkglHMhURBYZTw4ZV/Y7utohsRllcUMqVeAMXS5YHf1TH3Lyl44sLNDZENLZE+L4S1paAZ+Kme1CNC3pjojhoGoeLLJ7G4FkCgx/besadD4EoNBwbsszMOCJn8D0hnT5L51uuucMQ4gl4WcmbjMSzG4KgLv60YuLBo8gZIML3alWyM1Srgk0onlHUxVhH6IRcXunqdJRLyvgE+J4hlVQyGcDGoiatzY6WpoioKoxOGlqaItLZas3kVaisqBjF781WIDMSkBkNUSuEGaXY7lHoMiz2GubWxZkCK+z6t3dtLWsjF6qGE+OGxaJirRKpkjQJbt38Lulq6P4TESnt3LnTg0vvQXbJA4CIRHfeeafJeMnv9Oc6X9zav2H7o3ufcjaZNKHCmSmP4VZHOunwJKpNtr0jQgBYyIwpg98JSM2EmKDWLxepKQ6f29U9i0wXiYk0mXREKqmEkeX0aZ/uziq5vNLZXmV8PE2lGrvwGusIIo+5eUtQMfheRCYNGCWI4l5iXcaxdm2JrnbFGIdnLJ6NJcMPGEg4ITRKCIxoDJRptAJpYQ01p+CYEGRtrS8qBhx0tYXcfvsSa1cHcbdiORzbmpsvgoviCtaYWqirLU8vEf99wnNsWBfgJx2eJ5QrEIaCZ4TmhpDmxohSyWN8wqMhH5HPR2SyIZ4RKgF0dgQ0NSnzc4bJCY98nZLNxM68oCwhRK8q7aUm36UhJOeF5Jyj8ZBSbA0QJxQ7DJiYa8E7ZFmdmrGcnrFxd0UcuDDasXaHafHz9zWl09+4S++yt17i2v9yZgBs3rxZRCRQ1d9+95rtT+8ZOiBLlSWsGObKhkOjQmcDNGbAqbkcge9NADXxBvQqDn/JxeCSp7HIqBqWycz6GuBSQZXQKLk68P1YJXd4TJibFxobHIN9IXtehnIZIqd4xpD0lSBUQo3pfCk/dqKJwjjMNDaW+diHIJkQjA/GCp4X991/FChPRXGpElhHQQVfa+CV1pSlRWoMP8UzDlMDMEUchEJ/f5n+VTYmTESuNuobt05NTfEnVlaWWgmgRDWnYN+LQ2AmG/GRDxZAwE9AoQRBKCRTEe2tEZm0MjvtMTGeoKe7SFurUJeJW5C+L3S0R9TXRwyd8Jia9BjoC6nPKcmEIRIoEpOT7OtU707ijAgHXgn8ArjL6Of7Ri1iV8taCmXl2LhhthCLfjiEjJflQ1ffKl0NHXeJSLhTd3qX671dlhe64447nMZQ6oGtHevObB3Y1P3EgWdVPBFVGJnyOTWjZJMO7x0yarZMxV12hNGVXr8558x/7TqvKBAYqMsqngdh6JgcTzI3H9HWWqG3O8L3HNWKQR0YX/F9Rxh6sRmkgYQf94WjKCbAWGtpbIhtu43Gp6pnFWehtT+gva0KxuGJZWzcZ3xCiM6xQpBzST7CWYXmWiozM2kZOpWgoz2gu+fsrjJGVxSVI61lE7Ud6Fz8vecpiMOpEIYGPxGuoOfVIAYW21odiVTEXMFjZNyyuSL4qZBsJn5vuUxEW3OI7wvTM5bJaUMYeiT8gFTK4ayjqIYIwXsduGi5bBHOsgxNLTC8nee/iiBEqFpOTFpGZqTGiQHVyF21bpt0JJpf7Mg337NMo79c7+2yTHbXPMxERBZWt/T88q0br5OmbL065zAoS6Fh/4jHTMH7KeSIy8o+Wn7vRY058vmMw7OOagClslIqGXxj6Ggvk80oQdWAGqxV/AQx8h3LKJLw42cLahlAqWT5zvcz3L8zRbks+BasCJaI999a4n/9rSX+6T+e45/89jw/d1uRdMoRuXiOQERX0vbYI9GsbGoFsMKJMz53fSPLnv0psCsFQBwATIwuahT/hjFxNHS1762neNawsOhzz7eyPPxYDhfGASyoCpm0o70jQETIZwPWDJSpzwKekM0o1ihNDSGtbVUQpVIxMTfMKGLizMIYpYDgjJxjM/bT8VAUEcNUAY6MecyXvBVlrIyfdR+86hbT3dj6JyIy/zAPm4vh+feOygDik8S4O/VOkyL15Ob21S9ft3b75vv2PuIAoyqMzRqOjkN9Skj6biV1k3foltdX8QQ0UueiSMue2EINWKvLxfVyOq2879Yynd0B4jlaWw11OUe1qqgTrBWSSUfoDJWqBam1BY0jiCIQIYxg794kfspxy82GVDZCbJypRzgCp7iqR1iFYqUWkaK4nFLx4r6AA62VWCvNUY1bm+Wyx8SET2Ex4CzDRjFGVnKesNYxiLMHQ1BzGfa9+GflMry0z6NSieJSTqEaCLk66G4DXER/r0d7W5VUIgCnZLJKOhnR3Kw0N8fDQ2tXl2hr9WmsD1AVclmH+EIhcASR07SiYq2oWR4qOF8rs8u/XsoBHB3zGJ4VVA1iQsJIow9ecb3Xk25+ti3b/NWLafjxjgsAqsrmuzeLfEYqBa3+6vu23bTrhRMv6/DCJEnjEVQtR0aSdNRX6W9xeO/YKF/jhztVjZxTE0/V+C0tXmp1L+W6OsLDQ7jpU+TqHNZAJhFxzdU1qywT0dgY0NQQUiobQheBDUn6BhdGBFWNZalthCqEQU3zz0IqBRFCFApiwHrxSMkP7s/y7C5PozA+bGbnElIom9gnKAJroKWlSiIpGC8gjGytA6HLn6aGB/y4mHAUxYCmiiGTUhobqvh+nEmUKnGLz/Nq9ZGCEUMyGSLicM4SVA25XEB7RwUXepwZ9pmYhMEBpS3ryGYiMllHW2tIc2MAamhrEdraAjwv7g7ksg6ThEJzF966XknMzUnh0CnMXCGM37IR9YzhHTplKAJnZg1HRn3K1ZherE5ozTboLWuvLa9p779TRMqqai93M9y7nC/2mc98JrpL77JZSTx/cmb0z95/xU2//pUnvhW5OPIxVXC8fMqjIas0ZSP0kg5jvFaUqpERaieg1Oq3Fe55FDmiyFWNIdHd5uXWrLZ2cACzdg0lp8NRS/0pSee2R3/91ZSbOUkuqxhfOTOS5IGHMtTnIz7yAajLRnR3RRw+JgRVD9GQ3t4y114T0dIUgTrCqkdQMXGrTRXPCGKVsFJD463Dtw7BMTPXCrZJctmchFHEwtI4zk2DxsBdrq7Kxz9s8KwjlXLMTCUYG1/xnYvLgXOOUKk54gjK4oKlWPQQCbjh+hKbtxg6O6qEVWF6qta1SLj/f3vvHmTXdZ35/dbe59x3v19AdwNovEiCIClShGxJ1ANU2ZblWB7LCmDPeDy2xnE8U/FUTZKy8yi7SCaVSaacmckkGqdq7IlHlmfsgOPIlmSPbMcCJMvWC3yDIAgQz0a/H7f7vu89Z++VP8653Q2IlKzwBUC9qhoCG63uvnefvfba3/rW94EhxQAEY5NWnfceT0z/AAwMKI2m8v9+Kc83vhnyt3+yxqMTLfJ5R6EIoyMJxbmybvl/Pp+num75+I/XGB93FJOrlOrdh8R9/IeuShyt51bW7+1rd4LOuXO0XnmF1vmrTmotRayIsaZrA9MlRXWBZX2LpGh047DwlGuGl2cClqvpjVs8eOKPvfeHg13F0T/Oh/kvJKw/id/q5BS85enwycRIFPifPnjXu3/uucsvyXMzL6tFxDm4sqyMzAgPTUE+1BuNb9/0VO0RVSJV1moB9ZpS6ijeiI/7Slrat9cW7tlvuPtumtlCw/WWnvWjo9PZqalzeZP5VxJmli5dfOWiCzP7jPG+UPQG45lfsPzJF/IMDyofeF+LoQHPnt2ei5eg00kG5A/f02bv7piBfk+7ETB9PaBeC3Ae1CmZULABRHVB1SaAXwBGjD7yyPfLB49+oJbLFS6pc2Nf/OKXx/7qL/9EO20jtXpAvuAY3+mInWFmNs83v5nn+TMZAmto1i3NsqFWs3gnNJqGxlqWajXEuZBrM4Znns8wNtJhz6Rjyng6bXjhpSwvnQ/pRGCMRb3FuYDIQxCCWIP3hmwGduxwhFlYWQ24dDnk0tUca6tNRJRSwTM23GF0OCYoRixczvLlLxdZWRce/WCD8V0xxZIixrlMT3/Q6Nnxu6X77v/Vpos/RFz5SX/h8E67vvLAaLO9x1+7RnzmZarnLhDX17zz3sysByjKUB/kzFtLBRAc7chybs5yeTFx+xHjcM7rfbvvlSO77ps5uGPqV0+cOGGPHj36tqDfb3kC6FYBx+X4lXJt/Rf+00c+8tvX/mgmrjTrgTGWVgRnZgx9JbhrR4Q18ubf7xKIEp+Kl15ZspyfteR62twzOkL/33+/yR+8l6YNzkU7dz5rd02+nDe5T4vIxRu+Tfly/8sLrVK9XiOXjyUMDeqy1FshHRdQqXsWlwN6e5TBQUcQesqVLKV1T7sN7U7A3MvCi2ez/NkXs9TbwvVrGf78i33EXpmdDSmvGT73hTx9pQJnzmZpd5y77/D9wTvfeeRfDg0N/WrUjD60sLD8F1/+0p/6089mbbM1iLEO5yCOYH3d8srVLLPzAaUCfPYLRZ56NvnvhaUMf/kVmJkNWCsHzMyFlNcNn/lcLxcvBYwOecQorZbl7PmACxcLqHq+/OUcM9eF9YpheSnDi2eV//CHPVSqhrn5EDHCyS/nWVkOuT5n8WqYW8hy7ZJndc0wMCjki0LUDLk+l6fZtmQCodkO8XFMoQBBoFQrVVyntQfvyIt8Efhier0sAj8RV+bf6x66cld+Ze0DvauzweXOX+nc2pJcWFKmRmD/DhjIu/R6KW/y5lecFy4vBZydtdQ7iRamB0qZUvyT7/9oOF4c/d9E5MxJ1eCtvvu/fRUAcFyO+xN6wg6Z/n97dv6VH/voez78sd879Rnn8VbEstZQnr8CxYxlcshjUtRN3pydjwhEDmbKlpdn4cqyZa0Vcpdt+MF33S0M/a1vuv7R38iTeVJEGlv+38KJE4ZjxwyJgpb6mbNh1Gqq6xi+/vUsFy8JZ17OUq8J9brld363h54ex9x8hmtXc/zv/yogmysQxQmo1m4FrK1lqNUD8lnDmbPKtZlk01TXLXEsPPmZABFDswXFUq/sHB8jnw87643597T84tHJqX52TI6Zi5faXJ7e1AlwmszyD4yWOHA4YHGuyTefdzz9QjbBqr3l7PkcZ1/JogqZbMCe/T1UqjF/+kVLEHgkRf/V5hgZG6Oyts5XvqZ87akC6h2dtuXZ5y3nzifJodG2XJtxvHg2SxxbyuuWKDb82V8UePr5kCiCSsVy/nzAl76UZ3nVMr+QkJw+9ydZXjgTsLCUpV4X2p2mb8dJdX3mxInM4ZER/+Rv/IaKSB34dPqBqr7HNRb/l8Gyeb984z/4hbXALK1nuL7a4dBOYe+oT6pL2YAu3tBnK2m1GmbKhmeuCqu1ZNxNRXGxdz/+yIfDu/t2f2mif/T/VNXgrWz73RIJANBjHNNj/pgAv9T28cNnL5+fePrKc94KxqkwUw54+pKSyUTsLPk3fPcni57cr8st4cI8nJsxLFZDYgeBgUzg/cDQUGB7+v9ZINkT+hhGT5ywjIwIR496EfEcP+4Al15rmqWe0rWpqamB5194uvMHnzPG2Kw0o0BEs2I1y9efyouYAGtzhKFlesEgEhIEIZnAkssF3HPfGA88cC8ujnnhxeepN6oYG7PnoMGGivMdxMS4DIR9fXYx8w3Ol889gWk9IYGwXpjmPR9TmZou4L0iCT6GBJDLBfQPBoS5gPUVaFSzKMkdXkS2tPuEbB76BrO06gELc556NeH/O2MZH88xuiPL6nIPa0t5iLIYAgKbpdNRXCSICzGSwXWETgviyLP/QBGssDC/TKUS45wH6bC44phZaAExpd487VaNv/xK4L8adIi9aLsTxA8+9I7cwMDALMDSyIiXRxO2nKoKp07ZU8DRkREjIl+tt5dO9Y+NfSAMcRmLqTbh8kLIatUwV/UcHo8ZKXmseWNH0UUTa7GFKjx7NWCmbFI7dnCKP3LgQfP+/Q8v7R+a/DkRqaecf/1eSwCkkmFWRGYrlcrP/NwHPv6lufJ8PFtZMJbkTnll2ZO9lCHc32aoGCMSvGEgqQDeK/PrGZ6/LlycF2pRgGjSfrMGsqGaZruFL7mynjwZcBReazzz8ccflyeeeKLTarV+6iM/+tHnpvZNZebnl0ANuXyOoaFBcrkszWYbY4RsNiSTyRBmQCzYIEH1TRiTLToyJaXZWWTsHQO0IsFrE0cbTwevERjPYjXkqekmf3juGxyuNnVyoE0Yem/E2NEDlpG9OZxXmi7DelNZXoc1FXqHPAN9EUMTQicKqbUhUksmVAqhEtoYVBAxIDF4YfyuHHGszK0FnJ+39OzxjIyUGdob4iNI/kgmD70XxAeExiTDRBQJKBKYXnryo+TsMHHT4GOLc4KLDXHkidoRcaxkMxnXaLZstbZmmo0Wqsru3bvtvn37XhyZGPqU6o100XQDdZNBcOLECatIUZ0jaxOlHbUeUaFcD3jhqlKuwuHdyt7hmLx1qULi6wX8FG+Elarh2csBlxYtLt1ksVcme0f98SMfDg4MTvyiiFx5O9p+t0wCSBfOpa2Pv5zyE//8P/vw3/2v/sVn/3VUazVCY5TYGy7Me4SAh/cJoyX3BhhRJqystrNcXRKeuqLMlgNiZzCyOf8dGE/GqM9mQqPid8qjj8b6KhTN9GEUgI9+9KNhLpc7V6vVvm/P5MRHqpVKPshm781mcmGYDyJrrfG+dZ9K46711oJ24rK040Wabp1mvEI7LuO1RVU7tNebOG0jRYdRRdQhGqPqE3QfYXYx5upKSCdWQhEZ6rHEZGwgijWORidgZk24ugjza4ZWJAz0OEZ6PD6G9WbIS3PCxWWh0zaUMp5dI4aD48JAwSc24Km7kAkNay04M2+5shQggWcg7+ktxNhssoFSjVuCtB+XdBcCvITEEoAErJscNQmwpQKh7aUQDpMLhsgHw+TskPYVx8WawmV14VdRaXeacaPZrNb6B8fP5/P2D0SkdlJPBpzaAJP1Zrus48ePu2p72RuryaSikY0OjwhEMUwvWioNqO7xHN4JhezmNv7uDxlJuw3KUsXw1GXDK3OWyAkmVU0uhfno5z/8U+GhkQP/ZqA48BlN7v0xb3MEvP3hARnvG/6VBzkw9Ykf+Mmf+M3/+O+ilm+FVoVOZDk3J3Riy7v2txjrNRiTIKybZ/l3B87UOgHnFyzPXTYsVW06YaY3JyeM2XAylps3/KlTp+RU9xqwpW2uqnsA44pyikzr+7Nh60Gn13tWG3O9tfacqbcXgo6r0nY1iVwd5xt4jYh9B6edxGYbuzGBt1WmsvuYCbDasFxbNjTjgGLgGO51rDdDzk47MllDby5gZlmYrxgarYRavHcEDk9Y+guei4uGl2ZDrq0Y6m3w3iAW5ioR9bbhgT1CIePxLilfc4FnbtVwdSmk2hEuL3l2j0A+m0wqiqYNW5EN71cPOOLkcFZFcQkxqduOE4s1GULJYmyGMChKZrmXYnbgQC4c2Z21E7UwmGpnS/1/Pd+59KWd+Z1HVHX6ZvD1xIkTdmRkRNL5+a1G3xiTzNtvntSaLlTAchVOX4Rms8N9u9PW83fZJkxGwJPBqNlKwNNXhItzGdo+OauMF4wx8c8c/YnwyI7Dn5noG/2l06dPh92K5Xs+AaSyYTz++OM8/vjjP/u+Pb5YfX/1w7/75c9EsXRCq4bYwSuLlthneGhvzMQg5Ex6txL9jhlbN/rBQj1SXpwRnr9qKddNigTcKE0qurnhku6g71XV4Kmn/rUcOfKov6kCeEcMu2aWrw5E4u57cf7sLy01qoXra4usthYYKlxmqLBIO1rH+Sax6+DUbdBvJD05RQyB5NLudVfBSG94zNIBXQRlZlVYWg9BY0b6hPFBx+yq5cJ8hliF0AqtKGHojfU67t8dsWvY47xw5nrA+TlhtWZxHqwoNkg2ZjMKeGnGsFJ35DMW5wy5rOMdk8pAL/SVIurlDJVWyIU5GOuNGCj4jRmDrelSuAEuRQg3zljSUSrVmMh38E5pdpYRhJW6kAkKmWqzNPjs9CA9xb0f3zM8+vHdg2PsLA21L65c+PehMX+1q39iAbLXROT5rUn4pJ4MtOONpnp+8irlvTcOUaXWCnh+GiIf884ppb+YYAJ/U/6JATpOmC7Ds5cDri5bYq8YbLKKhvjYIz8SfGDvkecn+kZ/OiX8yNt577/VKgBERE+cOGFEpKZa/Xsfvu99T8b4D/z+Vz4bd3wcWBLd0EvLAY0OvGPKc/cORyaIE9bcdyjbJN3VjY7w4nTIs1csa027cZu4uSUkKnhN7JlFAkwYrIpIbBLq7AMNGvuWy8t9Hd/5gefmnvs7s5UVc7U8x5WVWc5PX2G9XtNm1PK5sKmP3htZGetgBTEkQpmBBK8yRajcOGL0rXJV3URWjZTpFaHSTGYCpkY9mQzMrFg6sUXxdGKhlHPcszPmnkmlJ+uZKxtevB5yfVVox4nQRyZI5vU3hn8Emh3DtUWDpCq/1kAx9DwwpRwYVVZrnnpkub4K11YMxZwntPptVDrlhtf5LYrfYm/aop5O3KIeNbk4t6bTK9d8LmPJZTIyUChl79696xP7Ryc/sW9oFzuLo+7a6sX/K5cpfnOkODQDwdMiMl+PVhqIJfagPm0ly41r3CXqNCPD2esW8cJD+9oMFLpXTfmOvJFWbLi0YHnqkjBfSUReVBIWZtbY+Ni7/5PgRw4fPbOrOPajItJMQb9bxlwtuFV+kePHj7vEAKFnUbXy8Y8e+uBnS0H2PZ/+0h9G9bgZJlx2w9yaoX3B0Yng0ATkMw7xwXckg7di4aX5gGevhpSbKe6rr5WQEtCmGYupt2p0WtHfub52fV+1VT36zNwLH1psLHFh4QqvLF7n8uwMlXrNtTptWi5Wj7dGjBgRW8wZMgFYE9ykK/g6kr8qM6uW+bUgMZDsUSYHY6p1y0K9O0QjlEJ4eK/j7nGl1TE8ddFyaVFYbgYY7+nJO/aMQDEDL8/Bet1uWrdLgoNoalMd+YDz88LukYiDY8r0iuPyiqHaDjk3CzuHHGNFfR1D3Poqa6Bks4YwsOLF2I73tBpNyvW6XlpZcNngWUqZvAwUS/bQ7r2/cM/47l+YGt5Lj+1dPrd47pPtTuu9jbhBFFsTfwctEIPSjANenPOIFR7eG9GfdzdVYd8ajbbl5TnLM1csK7UwabeiREDRhNHffv/Hwh859P5vjEnheKFQmL7VNv8tlQC6SSB9k5ZV9cNhEHyqp9Tzsd/689+LyvX10JLQclfrwjOXLXjh0C6lGPotVmNy0+0skbi6thTw/FXDWiNIpZ6/XQaAjjO021kztzRDKM/8yJnZ8o+cuX6Ji9cvs1StuLpraxRFYiUwCBaEwNruX0Ehax1hwEa5/7qFTkRoRsr15YBK05C1yp5RTyEPl+YtjXb6oxWGehz7RxzLFXjmqjCzEhLFkM9FTPYrB8eFkaIj8mCN4cy0Z70VpCexZ/PITLZBuS5cWTQ8NBWzfwcs1jy1pmVhzXJlydCX9WStf11o+mY+SHCH0HjCQLEpoBhYQzKqqEEcK+W4wWq9rhcXZ/2fZb6m/WFR9oxODB+auufxe0Z7yZqXaLQxrag73C2vkX4SObRWBGdnA4r5mHdMKrlQEU38DmRL8k7cri0vzQrPXLGUa8GWkWllIFeM/v4P/GT4yJ53fX2qf+wjIlI+cQsg/rd8AtjSHjQiUlXVnw5M8OulH+35L37zz/6dmynPGVEVxLLaFL55xaNGeGCyQyYwGxz2rtZVYkOlLNSF564altdDNifhvvOhtNa0vLI8zZnZT7ovnw10oe4FvBFvrBVDxoY3KUtvkQ5GsdYT2KSsdm8AkUnUs1ixXC9bohh29HqmhiCKPTPrpPJhieJPoeBoxPDs5ZCrq4bQeMYH4eAOz96RZEbgxRlDuaHcu0M4vDvmmStKq5NJ78x6Q/nuVbi0aNk1HLNvLGJ6Ec63Da1YuDCTZaIfxvv8Bn7xRtA2BCUTatKrT+dkUtLmpp4BIqrWdiJY6DRZunxen7t+wb17v5j7dnkzW/F0ogzf6eDtIjLNtueFyxn687B/R0SQqilv/k6GVhzz8myW05egkl4lvXi8F901sMP9wg/+VPjw+P2/vbM0+F+LSFlv0c1/SyaAm5JAE/ilcnO9PPBj/+BXf+NPP+3Pz19CRMSoUG3A81cgH1ru2unJGLkB6/VGaXYMZ6cDZsrJ3Vgkhfw2kGrZIHCwZUZeRFmpGy4tOg5Nduz9e0KyswHrLUMnhsh5vKZAJPotG0YkEQMJgzcO6+k4YXrVUK4LgXHsGfYM98RcWjKs1gLUa+JGpLBWCVmtJXJauwdgrK/DnhHIhIkZxSvzynw5JPLg1HH/hPDgbs8z0zHttklrpy2vB1itCpcWQ0Z6O9y9K2axJqzUMyxWIy4tWAYLnnzIdyydv5tbQSZIcJjYsbE++BuhBSOQtUrGKvmcyq7hKNg76pheNUyvBnhx2E2Rs67rwKZ4CLohGSYqrLbg+WuGgUKG0d6IDTcnIHbKlYU8T18R1hs2+Wwi+aOHduzj5x89Fjw0ce8/Gsz3fLLbNbpVN/8tmwC2JIHEckPk12rtxiv/8Id++rd/8+Tv69npCyAqIspKA56/aijllD2DMSJ2o2z1qlxfMVxZDOjEgsHgvKbb1RCIw1qwJhWvcDcC2Z1OwCvzSl8xw8Gdnt3DHZZqlpWqUGkpnU4ijOl083QxiQQdVjwTA5C3r89Yogv8IYaVmmF6xdKMYaQEk8OeMBCurwqN1mZyU4SVmuP8rGWk1zPap2QCw0zZM7cmLKScANGktL6+nPyehybh3ijmxZmAdmQxN52aToUrS8L+YcPEAEwOedZbMZ3IcnnJs2/UsLPfvf4JzvTNsggTfdDY4enEiTaA16TSkqQMwIiStVDIwVARRnscpbxnpR5wdjq13N6SztBkPNpYwCddEefNxvtsRPFimSsbLswrPQXIBxYweI2ZKVuevWpYrZuEKJWM9ur9e+6Rn33Pj/PQ+L0/3Z/v+fcnTpywx44d87fanf+2SQAbLULwJ/VkUJLCp2pxw/+jH/rZ3/nkn37Kn5k+hxgjqpbZdXj2mqGYhdGS3zByqjQsZ2dDVusJdyA0MYVQyIZKLowoZIRiFgKjzFYSqeatSJZBWa0FfOOCY72RcMh3D8TsH0nm3lUVp4kjTyK3pVgSQlHGGgLrMKnl0+svh5PTJ2eVYuDZPeQYG4gp10IW14VYZUNaXQDnLddXlNmyYCQRofCaai52LcDEp20QyyuLljCIuHsHxN5xdsbjfAaT9u1Jm2OVuuXCvGeoN+LuCWVuTag0DD05jxV3k8LA6wtrlHsmYvaNe1yczCD49HolJllTm44zK0rkodk2XFm0nJmxLJQtG4T/jYTq6SvCriFHKJ5aR6h1hHZbacWGTqy4GFqR5aU5x+SwZdegYlQpNwKeuxZwfc1uvELnRe8bP+A/8cjH9cEd+z7WV+j5fJfff6u0+m7bBNAFpJFH45Q59elquxH9zCM/8bv/x198iunlWYzxot5wdUEYKkBpb5tcJpmumFszVFsw0hvRX4S+YsxAURguCn25mMCCtUK1Kay/nBhs+ht6Rcl9dq0R8vSlmItzhrF+y2DJU8gkp6+xQWrxC049ziX8gd48jPU5soH59oDj3+gwTBLMeL/nvXc5Li07JvqVQhauLjsanWxqOJrOvIsiavF0dQF1AxQVFBG3oRbcrU68gwvzhkzoOLADYg/n52K8M4goTgSriWjppWVh/w7LeL9yaNzTipocmoDRnjd62j458ddqnpVqksCMmOQEN4makZJIp7c7hnLDsrAuLNcgikzyGuXmFoAQWuXgDseugXRKUoVaG1YbhtWaUKkra7UkGcysCCO9MYJwbibk6pLBedLEDrsHd/pPPPpx++DEoR/ryxU/f/r06VBEIm6TCG6XX1RE4tOnT4c92cLvz64vPvT3PvixX/nkH38qLrerQUBAy8ecnxNGei137UjUdAphzIO7YbCo9OSUfMYRWNlo9Yk6wNCKAlYrinOpGO6WGqB7akSxYaUiLNc8RixhQJJAJKW0aHL++zQZlLKOd+4NuXdnlKrmvm5oHGNgpN/R35Mg44GHkaJhR09MrSXpz070+FU8loSKejOXYCtSv1GuC7TjkHPXPbkADo07YgeXFiyxbrHyBhrtkLWGY/eg4/5dydWjGLLZRnyDQjXBPE5fDlhcMxunrjEpXqNJUou9ELnEuGQjAb6G3ZsIVJtKuS7sHhJyQYwiFHMw1qt4r7SihB252pDEZchZrlcML80ZGrHBpr9bMVd0P/3BH7f3Dh/45b5c8XOnT58Ojxw5ctts/tsqAQC868iR6PTp0+F43+h/88ritUM/8f0//NHf+cvPxKoaGDUs15Rz8wFjPdDf49g9LKndtN8U7ezaTSuosbhYWFgXKm2zIYX9qqewdNt4Bu+hFQlEitEu6m2SQkAM6jzNjjK/puwfg/ANwMQ2MGiFrNm0VR/tdTy41xD7DtOrIYFN/9iShAAAGdVJREFUKLzqlVpL6HiTUmEF9Sm/UfSGpLDlvKUeZThz3ZEJlPsnFeciriwbSDkBHiWXcfQVUkGS0CY37Deh2PWqrNZgvmypt8GaTUCyS+BKvBoTZ2PZeI/kNbkWCRvUslhxNNrQmzNbAOBkCKyYdRRyMNyX/KC1epaXrgtrjSDJO14JxLiPff+H7YM77vnjsb6h/zVF+m+rzX/bJQAFHn744RiQ/SO7PvH+zpHTF5emp06+8FUfBqER8cysWK4NOXoKpBLjXXR3C7okusEObMbC3HoicS3fzkwy/bdU33hLbz8BmFL1bgI8hZxnsEfZNezJBKnw5hvUFtvasvIoIp5dQ4onoLcY05uDnoLivWF62VGuW3rziX1XtaksVA2d1NLrW3dtUhtUmoYXrlm+b1/EA7sNsROulz3OW3KZmLt3Osb6E9Ve4988iS1jhLF+Ze9oh+urlnorqUa6GIakPXmjulEQ6IaU7E2tgi2vUVVZqRiqLWUgb4i3iKTKliU3kkyMTq8oM6uJ/qHRROD7vt13y9ED7yofGN71i4899tgta2x3RyWADWAwybYr9aj1jz9073v/7+evvhys1SoYC/UOXFq27Bp0DPUkoNFrPqAe6i1DuWbx3nSPeraalHVLZu9Tn/i0vAYIxJAJlDBQchlPKQf9eWWk17Gj3zPUEydXDn2jveXZ0u40WFH2DMWM9VpWap7Z1YBC1nNkn9JoO9ZbiR5gX044P+c4fSWDjxOQTYx8K+IgnnLd8vRV5V0HHQ/tdXgVmp2I/WMx9++CUujRNxnfNijjvVA46FlYE+YqjnLDUG8lgqStWIiiZGp0w1xWEk1HkQTxuLn40rS7sF4T1uqGyQG/0RB8teek1slyeVFpNMPEXEWV/kKfP/aej9o9faOfEJGZt0vP73syAaRJwKVJ4I9enrt46gff+f4P/96X/shlsFZUmCkb5iuWwVLMt2OnO4W1hlBt2nRkZCuJRXCa9NVzgZINlWxGyQURmVDJhpAPIJdViiH05BI/+d588rWiJjlP3/RzIeE0BCI0WvD0pQwXFwOmBpPhn1ZNeP6KYE3AI4cc9+12ON+m1QlYrsNKzWyQbDa/ZcKZmK8Iz13JcmRviyP7OiieicGkREZtlyLEmyW0p4AxnqGiMFSEg+OeagPW24Z6w1LrQDNKPBZbsSGKoR0pzcjQ6UDLJdWLiG6O96RdgaYTyg2hE3tC+xpJ1sPCmjK/HiRELnF4g3v3Pe80U70Tnx8oDHz2rbTx2k4AN2NEyWjuf3to9eojO3qHC0u1FbVipdmGhTXYMyyUsomEtb5Kee9UWG8I7Ug38HAVxasjGwpDBWW4L2agoPQWhGImmT3IZyAXQGhIyUVsmFVIF2PgrVEz7cp5x+pZWBfmVy3FrGfPaNIFeeaSYXY9QzHrKNciJiaVD9zr8AqXl+DrFwwrFcON0zJdvSTL9VXPUCHk++5u05cHcalXuuhrlNhv/CvUNNEEKINF6C85GNxsT6o3tH1MsyM020K9E1NrCmt1YbkaslyDeju9ogk4SWzU1htCsyNkCt9aKQrQiuHqitJsC2IS56OBQo988O4jMjkw/D+KiJ7QE9zOcdsmgC5RSESefebqi5ce3nv4gc8/f9JZI1ZVmF0PWGsopWyEk+7Q7ZaTRRTnAtYahi7JzCNYgYkBx94RZeegY7Ck5ALB2sTFCN06SbBlE2hi1MlbzvtIfQTEM9Kv3L+nTV9B2TEofOVcwHwloSsP5mN684ary5bFCuwZ9OwZciyUDWs1j9taBaT0V1HPjoGYiSFPzuoW+/G3bdGT1LTleq+AGE/BKMVAoCiAw3lDO4b1pmOhbLm8bLi+EtJsGyRw4C31ZlJB9Er8KpWap9oOmC9niGNBLaiqv3/ybrOzMPxCISy89Jg+Zo7LcX87JwDD7R3mscceMweGd//bw7vuJpfJpl4CwkpVWO1Ox73GI+u9p9pKfbxQenKO+3ZHPHLI8fC+mN1DSimT6AOigle7YQjaBeJko73mN7CBt3hXoKIYMYz1CEf2R9w36cmFQq3p6TjPUCHi0KRQbwl/fV746ssZXrweEMWWQqbLfttMa16FTJj0yt93ULhrPCYf8MYM+rxhZU/aEtx4iNOOgPrEeUeEXAg7+pV37Il53z1tHt7fYrgvSsVJlEaktCP/LYOkiWaUsFiFasPgxIBXMkHgjxy8X4d7Bj8pItWjHL1twb87JQH4J554wpcKpd8azPau7R6ctM57FYR2B5ZrQjuyvJYztFehHSU3/7688s69Ee/d32ZXv8earfPtm7SZm3V6Nh8ZeXtdaTRp42UDi4iSCWLuGVcOjXkenHKUCsqL08L8usEDHe9px0K9nVBhRVPsXKA3K9w36Xnv3RF7RiLCVDpXbqRJ3aq14WaHgM1mwEjR8M4pz7vvajPel2A7kfdE7uaGYXJ96zjLwlpCBrKStCT7sz32nh37Zbhn+CmAJZb0Nt8/tzUGkBBPEp5tbWpw4sqBsakHz81c8KEJrGJZrjoabUM+G+P1pg2aEnhip/Tk4J37Ig5POPJdAE9uDZvy7/a23EUfQgOHJx37djhCI5ydERaqIV5ChgoRe0aU/qJjcsiyUEk47k4tw6UO79jluGvc0ZONUyzebPTab9MnBVXIWc/BUUMgEV9/xVPvGJwKKu6mTo3S7MBaLSDyiWJUrN4dnNxniyb7uRCefUzVHL+Fh3y+VyoAPXXqlBUR3TU89vtTY+NJW0sUFaVSN9RayquxVJKH2dBXdNy/Wzk04cllkqmvhEl2+13tNnvgyUBvMaMMlTyFjJLPCNlAGS7EPDSllPLw8gxkA+H+PZ6RHsfUSMT77nLcvyumL5dqE3Yly27bzd8tj5JmX2CUqVHPQ/uUsb6ULak3wp+oUGtBpZmoCXk8QZDRQ7sOEIbB50TEHeXU7b53bv8KAODUqVMeoBgWP10i+/hQaTBXbVRVjJFmBOstQ+wNVm4sEj2QtZ7DkzDWk7TyVM1b60X45j/2ifuwKLtHHOoNmaBDb9HzwtWASwt57t3V4b5Jx7v2Q3/Os3MgxspW8tKd8350X01gHAdGlFIolHI3n4OJ4ep609DoJJwPBUphViZ6RnQg7FsGOMrRO+KNue2z2OOPP95diKX+XO/saP8IMbEK0I6EWssknmw3PwgKYejYOwQ9+XhL0+7OeuC7bcL+nOeBXW32jykrNcOFRYMNlKGS0psTDo5F7ByMExtxPHdyiArZACYGPf1FbpjWNCQtwfWG0PGbdsmFbF529AzLUK5//k56L4I76LX44d5BBgo9yemlCT233k5sv7Kv4ikiGEKrNwlYyJ350JNYiocSMVgMuH9SGSjFHNwRE2Y9xgfd3fF2w5lvCUQIEJrNAaktwBIeqDSUyHkMlli9H+obMjkTXiHkhZu4xtsJ4G0GAjXlYvu+XGlxuNS/z3g0GdkSqh02hD5ejbOmfC+FYoww3q8MFzsEgRJage7m/557N17tkwm1uNFJxn4tilf8WN+QETH/RkRqJ0+eDORRie+E9+COADKOHj1qREQLucKlwZ7BZEolXd1mm5QO+je5MG8UzWydBLhzNkYyl2AFCtnkBFSVO2/zv461VJR2rHRiUoo0iDH0FfrImsyKJADA9hXgFksAAPTmi6+UCsV02D9Z63YnkfraeDDk26dCvWGKTDYsObpqu3fMHtmQAbgDT/2UnrOVmPXddDE6UXJodB8ZK0J/sYdc5o1UeNxOAG94Ndef6/2qOMWIMd17byeWmxR5b5CCTHTmAOc93ida+Nol9UsyZhqk5hjd8VNJ5IbTrWPQ24Igc+cstGz+sTHarSo3rqWS2JqpSX0KE4fpwCR0b8EjkgwKJUNbyQXRC3RcMh7eFR4wYihlcoQmvOOy5R2RAB5noxPwcqvRigIbhuqcqqh4D5Iupkoy0OG9EHuoNg2VplBtGuodoR1D7AzeJyeHMUpgIRMIhdDTkyf9cGRt8u9GfPoQpdWCbm/SN+36kqZbJHEZxCeIfdtZqi2StWwZmh0lioQoNri01DECYQCZILn+9OQcfXko5ZVQwASJwKhJ7dG9l43BBxFDNsxhTPfGfHQ7Adyi4QTVrd50ToVIEwWfpXpAuQaLZcty1VJpK504afd4p6iatDQ2qXBQMkZqJBGfzFgwoVLKWIZ6lJEez1DJM1iAfEYxxiXKvMpbNAv4PXLiA4hDVYh9omi81rAsV4WlimW56ml2hCgWImeIU21H3dRxSdZSEywosErGCkEAxRyM9HjGeoXhXqUvp0QKnjSxd69KxtwZgNkdngC0O6qj6ansFaZXAq4sembKIZUmtCLo+CRdJCWk2WIHeaN8hEdwKB0Rmh2DNpSyKLPlxDG3lI0Z6IHxfmW839BX9ORCT5hOrvkbbyl3dIvtdZ3vW5SKu90aIwmjMfbQ6hjW6pbZNcPsGqzWLbVWMvcfO7sx7i3S9flJ5zY2urvJdS/h+UNDAxRhuQIzq55cRunPxYz0QWgNsUsBwI3pxzuTG3GnXAHkCZ4AmMrkcmHsY28SCjft2PDMFcW7kMgFG/P7FlCreAXnYxITAknmhDcgPzVd5VkUEZMYewrgPdQ7QrOdYakGVxZjegqwc0DZO+zY2efJZ5TQduWq5FskqrdjS6qVTZTeiuDV0HFKs22ZX7dcXYG5smetaenEBnVdDKCr8Z8M7HhVlChh9ouoJhxgRFU8Kgm+I2IQbOIzj3OGeh3qTcNcJfGLiPymZazH04miOzIF3CkVgACst+tHgzAU77wzNpWQ9NDsBIgBY5K5b6+KR71Rq7kwRz7Imp5CUXrzJYrZvGQyWUSEVrtFo9OiWq9Sb9W1EXd8O24TxREixohIIgGgQisOaFaUcg0uzRuGehNR0okBz0DRkw+VUBLXWVX5nr8i3OgQrBuGpN4LzVhZrVvmy8KVJViqWjqREDtJ5M+NglG896ii6r0PTYZCJks+zNpirii9hSLFXEHCMCSZDm1TbzWpNGtUG1Vtxh3filuo9yKIEZMYfXintDamO5MrgKpS77SI4jhdslPbCeCWqgBOJQtSazUeqtaryaKa1ILbJKYO3nna3rtcJsdIocfsHttl9o7uYs/wBKOFQYo21yhkMjUrti7G1hDfVmXCex+0Op3epm9nl5tle31tgesrC1ycvcJydcVXWlX13htrAzEmcTCutQz1dsDCWsy5PIz1GCaGHSOlhHteCJUwSDTIu/fU1O/i7RXceLPv8huCqgnKkoiwQBwbmpFQacFK1XK9rCyuWdZbhlbsU0XiVLRVHVEcq0F8qVCS4eKQOTi+204O7WRyYCf9mR5KuUI7a4J1K2ZJjGkCeO/7vPr+ZtQp1l2rsNQo22srM1yan+bq4oyW66u+2WmLMcZYE7BhjCpJV6FSr9DqdCJVlVPbCeAWi1OnvKqalxZemViulVFJ8rcTj3qPFfF9+V7unthvH9hzD4d37mesMHwmDOy/GCj1rwxkexaAaaACtK2Yjk/UY4skt4UBYGi5UZ7odJpBvRMdq3Tqxy6tXAvOLrzCmSvnmVtdco24Kagz1iSYQrsT0Iw9KzXDxYWY3oIy3Avj/TGjPYkWfTb0BFYITGJZpW+SxPbbAtxtKc9M0j/FqSP2EHlLJzJUWspyRZhfMyxWDGstQztW8BbBYSSRI481RhCfD3I62jdhH5g6bO/ZsY+9Q+M6WOj9TDYMTvSEhcZAYeBKuo6rgbVVl3Z0VNUCRaAHmKy0G2NLtZWh6N74l5dq5UMvLVyyT18+w7nZi77eqeN9bIykVYH3LDfWaGpnXET05MmTdw72cvujfipdpeCvXPzmy7/z9c/t//qlZ30o1gjiRwsD8vDdD8iH7nuEHWH/58cGx35rINszDzwrIu3v8N7ot/m5B2qt2t5yq7ZrsbL8Ty+Urw9/4+JzvHD1nJYba955b4xYCSQd003lwowo1iqZQBksesb6lKGip68olHIJgJi1mgiSmO4wbndeIa0SbqifU17D1t643qiBKP8/NvCrPSTfApJqwrjpiimnrt6bX69Jm65r3NFMh7PW67BaD5ivCKs1aEcJ7Za0ly8CKoLXGPWi1ojvy/XIfbvvMu++653cNTzVGioO/VopV3huKFeaFpFzf4Nn/DWYv5oDHlhuVHcuV5d/ebG99sipF/6ab1x4Thcay6oeo+r1wM598t/98H9efnDynntFZD41/bztYYHbvgJ4/PHHuxu1d7G6Ojy9Oo84r6N9w/qee95l3n/wYfYMjf/53v7JXwe+uNWp9eTJk8HRo0f1ySef5MUXX9TuZGHq6aa6yZO94X8f53EVkVeAV9KH6E8PTEy998j4Pb9y8fDMkedmXrbPXXqRayuzut6seiNiQmskQacT9dooEhpNx+yqJwwD8hlPKWsZKCoDJejNOQoZyIVKJoSM9YTWpHZmLnU2Min/wKdGGZujLTdrIH43ccNwbEqGkQ3Pv0Ruq/udJUXXvYM4ho43xE5pxdCMhFZbqDUN5Qas1IVa09KMuk4+ZmPDd3l3XhzOeQXVnmyRycEJ8469h+y79t7Hrv6d5yaGRv9JkfzXReT8ll0sJ0+dskePHt3K9dXuOnYPiu7zcvjwYTl27BinTp0SEWkB30i/5j8egB/c0zv2y4/c/a4PfvXiM/JXL53W2cqCLq6t+LW4PtBoN75PVT93p0A4d0IFYEXEldvV/+HE01/4tU+f/IPOO/cfznzkoQ9xz+i+z0717fifReRrW7++Cze/HvPGVJE4NQ1Kkkr6vR+eq67+3dnK4ieeu3q2dGH1Gq9cv8xCddlXW1VVr8ZaK1bSzSupoGjSuU7uuZIM6RSySiHrKGaVUkYo5pRiTimESiYUQqMEJhl9CEUTtqJJW2GyxeZgq2WnbJ7Or/b3jYTRtRnTrtZeohXoXeKMFKknUsG7hEDV7CQeC7W2UmsLjbal3jY0OkoUp8CnbtVSZCNdefV4p2qM0UI2r4M9A/ausSkOTxzkgV13tUaKg7+xq2/H54DTIlIDOKFqj21udP861nFjYKC7jkYE5/2R6bXFf3Ju+eIPfuHZL/HM5Rfbx9//o+FH9r/3F6dGd//WST0Z3M5y4HdKApB0EYNnrp45+6WLTx0YH9nBQzvv/quDw1P/WEROdw8qTepV/2Y4tm6xMXdbPjceRdGexebq35pdX/6ll+YvFi+XZzg/c4m51QUqrZpz3ol4ETFgjZVE4oxNWfGuey/dTb3JhgsDJR8mHgSZjJC1nqyFIBBsoGQkcdYx4lPAbctAVDrjvuVicUOloIDzyddEqsQ+OeGjGDpO6cSWVkdoRUIr9sSx2RRM1S0DtmoQlRTY1NRYA2KNVRM3H7VitD/XY0d6hzkwPsXB0T0cnjzoh3P9vzM+MPLbIeGMiFy8qWp7U2y3X20dI9Wj06vX//nXrj3z0OX56/zofT9w+oHJgx8Gyluqxe0E8Dbf/yfPzJ6/qCLtnUNj/3I40/vrIlJBVU48+aQ5fvz4W6XdJidOnDAAW3+mqk5U2pV31FrNfzi3vvKuC0tXx5aaq1xdus7V5TnK9XVWqmXfjtqaMYHBCMaatFstN5bkW5htCcU54SmYjYPMp/MKslFNpA9q4pjTbbzpjWX8xp0+3cAqiW24164DkKTdilQWPZVdk1SWHDYTTPfbefXEoqjzquqJXexzNm8G+/ploNDL+MAoB3bsZSjbw4EdU9VdQ+PnSrnCPyvZ/HNGzLluIjlx4oQ9duwYb1YCf60KT9I3R1Wzy1H1v5xdnv3vxwZGe/I++56+YvFr3epzOwG8/YnAlDv1+wcyxRURuZ6UiCfscTnu3s7k9OSTT5pjx47dUKKqaj8wtRbVjpbrlZ+/ujC968rSTFjuVAurrXVml+cpN6us1ddptJtxO+rgXGxEREzaqzbp/V82jY7RGyTPupvUb47FSgqVbHJrN8/7LZ9LsMQEaRSxG6YcW9H9JEGkUrnp1J1q4rnnvaLq1Vrrs0FOCpmCGSj2SH+plx2Do/TZAsOlgfq+kV3x7uHx9YHiwK8P5EpfAeaNmPmt8hwn9IR9kRf1CXnCv43raLdc8XZWm9X9Pfmer8odIAh6xySArXFCT9hjHPO3UmnWLS1vvq+mnx8GssDBhXr5HyzXy49eXbwarDarA5HxLFfLLK4ts1Zbp9qq04iaNDotoiiiHXVw3uFRnCYtT7xueBd0V/i7WeQbWni6gfGnttySJiCDESEMQoIwIGMzFMIchTBLT67IQG8/o/3DDBUGyWHpy/eu7xre2Rws9X19ojT6T4GrQAQ0RKR+84a7GcC7RdZP7gTU/45NAKpqHn/8cZ544gl/i/+e3wI63fTvfWl3ZrLaqX9f7N2j643qXau19fG1xnpYrq+z1qxQazcNKgMelY5rEztHx8XE3uGcw6mDDWpsCrjdLIl2k0HwJhDYnYJLpiSMGKwxZEyAtZbQBuSCDOp901hbL4YF6c/3aH+xh8FSvw4U+6Z786Wz1pi/6M/1PgfMAvUugLc1und6Uij0VhYoeDWMYDsBbMfreaI2ypQnwRz7Noj2FlJSNywwdtPnAOhs/bOz+ffN/4ZM5m/w+2UyZIDkz1eNdRLSzQ2cCRGpvFaSfvLJJ+XYsWN+y9duD0dsx3ZsPWXSD3NSTwZpSXy7JWpRVXvy5MlAVU33NW2v7nZsx+tPDFs/zNv58dhjj5lX+722V2s7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tmM7tuN1x/8H6NuytBseO40AAAAASUVORK5CYII=" alt="Logo SD IT Qurani Adh-Dhuhaa" style="width:130px;height:130px;object-fit:contain;"></div>
                <div class="kop-text">
                    <div class="kop-yayasan">Yayasan Adh-Dhuhaa Pangkalpinang</div>
                    <div class="kop-sekolah">SD IT QURANI ADH-DHUHAA</div>
                    <div class="kop-alamat">
                        Jl. Melati I No. 257 Kel. Taman Bunga Kec. Gerunggang Kota Pangkalpinang<br>
                        Provinsi Kep. Bangka Belitung | Telp: (0717) 9116753 | NPSN: 70002294<br>
                        Email: sditquraniadduha@gmail.com
                    </div>
                </div>
            </div>

            <div class="raport-title">
                <h2>Raport Penilaian Kinerja Guru</h2>
                <h3>SD IT QURANI ADH-DHUHAA</h3>
            </div>

            <!-- Identitas -->
            <table class="identitas-table">
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['nama']) ?></td>
                    <td style="padding-left:40px;">Periode Penilaian</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['periode']) ?></td>
                </tr>
                <tr>
                    <td>NRG / ID</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['nrg'] ?? '-') ?></td>
                    <td style="padding-left:40px;">Tanggal Penilaian</td>
                    <td>:</td>
                    <td><?= tanggalIndonesia($pen['tanggal_penilaian']) ?></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['jabatan'] ?? '-') ?></td>
                    <td style="padding-left:40px;">Nama Penilai</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['penilai'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>TMT Guru</td>
                    <td>:</td>
                    <td><?= $pen['tmt_guru'] ? date('d/m/Y', strtotime($pen['tmt_guru'])) : '-' ?></td>
                    <td style="padding-left:40px;">Jabatan Penilai</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($pen['jabatan_penilai'] ?? '-') ?></td>
                </tr>
            </table>

            <!-- Tabel Penilaian -->
            <table class="pkg-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No.</th>
                        <th>Komponen Penilaian Kinerja Guru</th>
                        <th style="width:70px;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1;
                    foreach ($grouped as $kat => $items): ?>
                        <tr class="row-kategori">
                            <td colspan="3"><?= $no++ ?>. <?= htmlspecialchars($kat) ?></td>
                        </tr>
                        <?php $sub = 1;
                        foreach ($items as $item): ?>
                            <tr class="row-item">
                                <td style="text-align:center;color:#888;"><?= htmlspecialchars((string)$item['nomor_item']) ?></td>
                                <td><?= htmlspecialchars($item['nama_item']) ?></td>
                                <td><?= (int)$item['nilai'] ?></td>
                            </tr>
                        <?php $sub++;
                        endforeach; ?>
                        <tr class="row-subtotal">
                            <td colspan="2" style="text-align:right;padding-right:12px;">Jumlah Nilai <?= htmlspecialchars($kat) ?></td>
                            <td style="text-align:center;"><?= $subTotals[$kat]['pct'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total Nilai Keseluruhan -->
            <table class="pkg-table" style="margin-bottom:24px;">
                <tbody>
                    <tr class="row-total">
                        <td colspan="2" style="text-align:right;padding-right:12px;">TOTAL NILAI KESELURUHAN</td>
                        <td style="text-align:center;font-size:15px;"><?= $totalPct ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Predikat -->
            <div class="predikat-box">
                <div>
                    <div class="predikat-score"><?= $totalPct ?></div>
                </div>
                <div>
                    <div style="font-size:12px;color:#888;margin-bottom:4px;">Predikat Kinerja</div>
                    <div class="predikat-label"><?= $predikat ?> <?= $stars ?></div>
                    <div class="predikat-desc">
                        Sangat Baik Sekali ≥ 90 | Sangat Baik 75 - 89 | Baik 60 - 74 | Cukup 40 - 59 | Kurang &lt; 40
                    </div>
                </div>
            </div>

            <!-- Catatan -->
            <?php if ($pen['catatan']): ?>
                <div style="margin-top:16px;">
                    <div style="font-size:13px;font-weight:600;color:var(--hijau);margin-bottom:8px;">📝 Catatan & Rekomendasi:</div>
                    <div class="catatan-box"><?= nl2br(htmlspecialchars($pen['catatan'])) ?></div>
                </div>
            <?php endif; ?>

            <!-- TTD -->
            <div class="ttd-area">
                <div class="ttd-box">
                    <div class="ttd-kota">
                        Pangkalpinang, <?= tanggalIndonesia($pen['tanggal_penilaian']) ?><br><br>
                        Kepala Sekolah,
                    </div>
                    <div class="ttd-nama"><?= htmlspecialchars($pen['penilai'] ?? 'Hasyim Ashari, S.T') ?></div>
                    <div class="ttd-jabatan"><?= htmlspecialchars($pen['jabatan_penilai'] ?? 'Kepala Sekolah') ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php } // end foreach ($allIds as $currentId) ?>
</body>
</html>