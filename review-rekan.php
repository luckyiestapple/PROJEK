<?php include 'header.php';
$st = $pdo->prepare("SELECT DATE_FORMAT(tanggal,'%d %b %Y') tgl, pasien_ditangani, rata_waktu_menit, total_jam_menit 
                     FROM rekap_harian WHERE dokter_id=? ORDER BY tanggal DESC");
$st->execute([$dokterId]);
$rows = $st->fetchAll();
?>
<section class="content-card">
  <div class="card-header">
    <span class="material-icons-sharp">description</span>
    <h2>Rekap/Review Dokter</h2>
  </div>
  <div class="recap-body">
    <?php foreach($rows as $r): ?>
      <div class="recap-item">
        <span class="recap-date"><?= htmlspecialchars($r['tgl']) ?></span>
        <div class="recap-stats">
          <div class="stat-block">
            <span class="stat-label">Pasien Ditangani</span>
            <span class="stat-value"><?= (int)$r['pasien_ditangani'] ?></span>
          </div>
          <div class="stat-block">
            <span class="stat-label">Rata-rata Waktu</span>
            <span class="stat-value"><?= (int)$r['rata_waktu_menit'] ?> menit</span>
          </div>
          <div class="stat-block">
            <span class="stat-label">Total Jam Praktik</span>
            <span class="stat-value"><?= round(($r['total_jam_menit']??0)/60,1) ?> jam</span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'footer.php'; ?>
