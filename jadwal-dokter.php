<?php include 'header.php';

// ambil poli sekali dari tabel dokter
$dok = $pdo->prepare("SELECT poli FROM dokter WHERE id=?");
$dok->execute([$dokterId]);
$poliDokter = $dok->fetch()['poli'] ?? '-';

// ambil jadwal (tanpa kolom poli)
$st = $pdo->prepare("
  SELECT hari, TIME_FORMAT(jam_mulai,'%H:%i') jm, TIME_FORMAT(jam_selesai,'%H:%i') js
  FROM jadwal_dokter
  WHERE dokter_id=?
  ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'), jam_mulai
");
$st->execute([$dokterId]);
$rows = $st->fetchAll();
?>
<section class="content-card">
  <div class="card-header">
    <span class="material-icons-sharp">calendar_today</span>
    <h2>Jadwal Praktik</h2>
  </div>
  <div class="schedule-body">
    <?php foreach($rows as $r): ?>
      <div class="schedule-item">
        <div class="schedule-day-time">
          <span class="day"><?= htmlspecialchars($r['hari']) ?></span>
          <span class="time"><?= htmlspecialchars($r['jm'].' - '.$r['js']) ?></span>
        </div>
        <div class="schedule-poli">
          <span class="poli-badge"><?= htmlspecialchars($poliDokter) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include 'footer.php'; ?>

