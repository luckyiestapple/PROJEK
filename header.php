<?php
// ========== header.php (versi lengkap) ==========

// 1) Koneksi database + sesi
$DB_HOST = 'localhost';
$DB_NAME = 'rumahsakit';
$DB_USER = 'root';
$DB_PASS = '';

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (PDOException $e) {
  die("DB Error: " . $e->getMessage());
}

session_start();
// sementara: id dokter untuk uji; ganti dari proses login tim-mu
if (!isset($_SESSION['dokter_id'])) $_SESSION['dokter_id'] = 1;
$dokterId = (int) $_SESSION['dokter_id'];

// Validasi: jika id dokter tidak ada, ambil dokter pertama agar halaman tidak error
$cekDok = $pdo->prepare("SELECT id,nama FROM dokter WHERE id=?");
$cekDok->execute([$dokterId]);
$dokter = $cekDok->fetch();
if (!$dokter) {
  $first = $pdo->query("SELECT id,nama FROM dokter ORDER BY id ASC LIMIT 1")->fetch();
  if ($first) {
    $_SESSION['dokter_id'] = (int)$first['id'];
    $dokterId = (int)$first['id'];
    $dokter = $first;
  } else {
    // Jika belum ada data dokter sama sekali
    $dokter = ['nama' => 'Dokter'];
  }
}

// 2) Cek status check-in hari ini untuk ubah teks banner
$today = date('Y-m-d');
$cekCI = $pdo->prepare("SELECT id FROM doctor_attendance WHERE doctor_id=? AND check_in_date=?");
$cekCI->execute([$dokterId, $today]);
$sudahCheckin = (bool)$cekCI->fetch();

// 3) Deteksi halaman aktif untuk state tab
$active = basename($_SERVER['PHP_SELF']); // ex: data-dokter.php

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda Dokter</title>
  <link rel="stylesheet" href="styledokter.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet">
  <meta http-equiv="Cache-Control" content="no-store" />
</head>
<body>

<header class="top-header">
  <div class="logo">
    <span class="material-icons-sharp">dashboard</span>
    <h1>Beranda Dokter</h1>
  </div>
  <div class="user-menu">
    <span class="user-name"><?= htmlspecialchars(($dokter['nama'] ?? 'Dokter')) ?></span>
    <a href="signin.php" class="logout-link">Logout</a>
  </div>
</header>

<main class="container">

  <!-- 4) Banner Check-in: teks & tombol berubah otomatis -->
  <section class="check-in-banner" <?= $sudahCheckin ? 'style="background:#ecfff1;border:1px solid #c9f3d6;"' : '' ?>>
    <div class="banner-text">
      <strong><?= $sudahCheckin ? 'Sudah Check-in' : 'Belum Check-in' ?></strong>
      <p><?= $sudahCheckin ? 'Anda sudah memulai praktik hari ini.' : 'Silakan check-in untuk memulai praktik hari ini' ?></p>
    </div>

    <?php if (!$sudahCheckin): ?>
      <form method="post" action="checkin.php">
        <button class="check-in-btn">Check-in</button>
      </form>
    <?php else: ?>
      <button class="check-in-btn" disabled>Checked-in</button>
    <?php endif; ?>
  </section>

  <!-- 5) Tabs sesuai file kamu -->
  <nav class="tabs">
    <ul>
      <li><a href="data-dokter.php"     class="<?= $active==='data-dokter.php'     ? 'active' : '' ?>">Data Dokter</a></li>
      <li><a href="jadwal-dokter.php"   class="<?= $active==='jadwal-dokter.php'   ? 'active' : '' ?>">Jadwal Dokter</a></li>
      <li><a href="antrian-pasien.php"  class="<?= $active==='antrian-pasien.php'  ? 'active' : '' ?>">Antrian Pasien</a></li>
      <li><a href="review-rekap.php"    class="<?= $active==='review-rekap.php'    ? 'active' : '' ?>">Review/Rekap</a></li>
    </ul>
  </nav>
