<?php
// antrian-pasien.php (versi fix ambiguity dan aksi status)
include 'header.php'; // sudah memuat db.php dan $dokterId

// 1) Proses aksi update status (Panggil / Selesai / Ulang)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['aksi'])) {
  // validasi angka
  if (!ctype_digit($_POST['id'])) {
    header("Location: antrian-pasien.php"); exit;
  }
  $id  = (int) $_POST['id'];
  $aksi = $_POST['aksi'];

  if ($aksi === 'panggil') {
    $pdo->prepare("UPDATE antrian SET status='Dipanggil' WHERE id=? AND dokter_id=?")
        ->execute([$id, $dokterId]);
  } elseif ($aksi === 'selesai') {
    $pdo->prepare("UPDATE antrian SET status='Selesai' WHERE id=? AND dokter_id=?")
        ->execute([$id, $dokterId]);
  } elseif ($aksi === 'ulang') {
    $pdo->prepare("UPDATE antrian SET status='Menunggu' WHERE id=? AND dokter_id=?")
        ->execute([$id, $dokterId]);
  }
  header("Location: antrian-pasien.php"); exit;
}

// 2) Ambil data antrian HANYA untuk dokter ini
//    Gunakan alias antrian_id agar tidak ambiguous dengan pasien.id
$sql = "
  SELECT 
    a.id AS antrian_id,
    a.nomor,
    COALESCE(p.nama, a.nama_pasien) AS nama,
    a.status
  FROM antrian a
  LEFT JOIN pasien p ON p.id = a.pasien_id
  WHERE a.dokter_id = ?
  ORDER BY a.nomor ASC
";
$st = $pdo->prepare($sql);
$st->execute([$dokterId]);
$rows = $st->fetchAll();
?>

<section class="content-card">
  <div class="card-header">
    <span class="material-icons-sharp">groups</span>
    <h2>Daftar Antrian Pasien di Poli</h2>
  </div>

  <div class="queue-body">
    <?php if (!$rows): ?>
      <div class="queue-item">
        <div class="queue-info">
          <span class="queue-number">-</span>
          <span class="patient-name">Belum ada antrian</span>
        </div>
        <div class="queue-status text-end">
          <span class="status-text">-</span>
        </div>
      </div>
    <?php endif; ?>

    <?php foreach ($rows as $r): ?>
      <div class="queue-item">
        <div class="queue-info">
          <span class="queue-number">No. <?= (int)$r['nomor'] ?></span>
          <span class="patient-name"><?= htmlspecialchars($r['nama']) ?></span>
        </div>

        <div class="d-flex align-items-center" style="gap:.5rem">
          <?php if ($r['status'] === 'Menunggu'): ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['antrian_id'] ?>">
              <button class="queue-btn btn-panggil" name="aksi" value="panggil">Panggil</button>
            </form>
          <?php elseif ($r['status'] === 'Dipanggil'): ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['antrian_id'] ?>">
              <button class="queue-btn btn-panggil" name="aksi" value="selesai">Selesai</button>
            </form>
          <?php else: ?>
            <form method="post">
              <input type="hidden" name="id" value="<?= (int)$r['antrian_id'] ?>">
              <button class="status-link" name="aksi" value="ulang">Ulangi Pindai</button>
            </form>
          <?php endif; ?>
        </div>

        <div class="queue-status text-end">
          <span class="status-text"><?= htmlspecialchars($r['status']) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include 'footer.php'; ?>
