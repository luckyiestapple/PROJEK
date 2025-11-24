<?php
include 'header.php'; // header + db.php + $dokterId

// Ambil data dokter yang sedang login
$st = $pdo->prepare("SELECT nama, spesialisasi, poli, email, sip FROM dokter WHERE id=?");
$st->execute([$dokterId]);
$d = $st->fetch();
?>

<section class="content-card">
  <div class="card-header">
    <span class="material-icons-sharp">person_outline</span>
    <h2>Data Dokter</h2>
  </div>

  <div class="card-body">
    <div class="info-column">
      <div class="info-item">
        <span class="label">Nama Lengkap</span>
        <span class="value"><?= htmlspecialchars($d['nama'] ?? '-') ?></span>
      </div>
      <div class="info-item">
        <span class="label">Spesialisasi</span>
        <span class="value"><?= htmlspecialchars($d['spesialisasi'] ?? '-') ?></span>
      </div>
      <div class="info-item">
        <span class="label">Poli</span>
        <span class="value"><?= htmlspecialchars($d['poli'] ?? '-') ?></span>
      </div>
    </div>

    <div class="info-column">
      <div class="info-item">
        <span class="label">Email</span>
        <span class="value"><?= htmlspecialchars($d['email'] ?? '-') ?></span>
      </div>
      <div class="info-item">
        <span class="label">No. SIP</span>
        <span class="value"><?= htmlspecialchars($d['sip'] ?? '-') ?></span>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
