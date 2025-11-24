<?php
// ==============================================================================
// 1. DATABASE CONNECTION
// ==============================================================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rumahsakit";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// SIMULASI LOGIN: Pasien dengan ID 1 (Ganti sesuai implementasi login Anda)
$id_pasien_login = 1;

// ==============================================================================
// 2. FETCH DATA
// ==============================================================================

// --- Data Pasien (Tabel: pasien) ---
$data_pasien = [];
$nama_pasien_login = "Pasien";
// Kolom pasien: id, nama, jenis_kelamin, tgl_lahir, alamat, no_hp, keluhan
$sql_pasien = "SELECT id, nama, jenis_kelamin, tgl_lahir, alamat, no_hp, keluhan FROM pasien WHERE id = ?";
$stmt = $conn->prepare($sql_pasien);
$stmt->bind_param("i", $id_pasien_login);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $nama_pasien_login = $row["nama"];
    
    $data_pasien = [
        "Nama Lengkap" => $row['nama'],
        "Tanggal Lahir" => date('d F Y', strtotime($row['tgl_lahir'])), 
        "Jenis Kelamin" => $row['jenis_kelamin'],
        "Alamat" => $row['alamat'],
        "No Telepon" => $row['no_hp'],
        "Keluhan Awal" => $row['keluhan'] 
    ];
}
$stmt->close();

// --- Daftar Pemeriksaan (Tabel: pemeriksaan - diasumsikan ada) ---
$daftar_pemeriksaan = [];
$sql = "SELECT jenis_pemeriksaan, tanggal_pemeriksaan, status 
        FROM pemeriksaan WHERE id_pasien = ? ORDER BY tanggal_pemeriksaan DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pasien_login);
$stmt->execute();
$res = $stmt->get_result();

$no = 1;
while ($row = $res->fetch_assoc()) {
    $daftar_pemeriksaan[] = [
        "No" => $no++,
        "Pemeriksaan" => $row["jenis_pemeriksaan"],
        "Tanggal" => date('d M Y', strtotime($row['tanggal_pemeriksaan'])),
        "Status" => $row["status"]
    ];
}
$stmt->close();

// --- Rekam Medis (Tabel: rekam_medis JOIN dokter) ---
$rekam_medis = [];
// Kolom rekam_medis: id_rekam, id_pasien, id_dokter, tanggal_periksa, diagnosis, catatan
$sql = "SELECT rm.tanggal_periksa, rm.diagnosis, rm.catatan, d.nama AS nama_dokter 
        FROM rekam_medis rm
        JOIN dokter d ON rm.id_dokter = d.id 
        WHERE rm.id_pasien = ?
        ORDER BY rm.tanggal_periksa DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pasien_login);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $rekam_medis[] = [
        "Tanggal" => date('d M Y', strtotime($row['tanggal_periksa'])),
        "Diagnosis" => $row["diagnosis"],
        "Dokter" => $row["nama_dokter"], 
        "Catatan" => $row["catatan"]
    ];
}
$stmt->close();

// --- Riwayat Laporan (Tabel: laporan) ---
$riwayat_laporan = [];
// Kolom laporan: id_laporan, id_pasien, tanggal_laporan, jenis_laporan, file_path
$sql = "SELECT tanggal_laporan, jenis_laporan, file_path 
        FROM laporan WHERE id_pasien = ? ORDER BY tanggal_laporan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pasien_login);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $riwayat_laporan[] = [
        "Tanggal" => date('d M Y', strtotime($row['tanggal_laporan'])),
        "Jenis Laporan" => $row["jenis_laporan"],
        "Dokumen" => $row["file_path"]
    ];
}
$stmt->close();

// --- Daftar Poli (MANUAL: untuk Radio Button Pendaftaran) ---
$daftar_poli = [
    "Poli Anak", 
    "Poli Gigi", 
    "Poli Jantung", 
    "Poli Orthopedi", 
    "Poli Mata", 
    "Poli THT"
];

// --- Jadwal Dokter (Tabel: jadwal_dokter JOIN dokter) ---
$jadwal_dokter = [];
// Kolom dokter: nama, spesialisasi | Kolom jadwal_dokter: hari, jam_mulai, jam_selesai
$sql = "SELECT 
            d.nama AS nama_dokter,
            d.spesialisasi,
            jd.hari,
            jd.jam_mulai,
            jd.jam_selesai
        FROM jadwal_dokter jd
        JOIN dokter d ON jd.dokter_id = d.id 
        ORDER BY FIELD(jd.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')";
        
$res_jadwal = $conn->query($sql);

if ($res_jadwal) {
    while ($row = $res_jadwal->fetch_assoc()) {
        $jadwal_dokter[] = [
            "Dokter"     => $row["nama_dokter"],
            "Spesialis"  => $row["spesialisasi"],
            "Hari"       => $row["hari"],
            "Jam"        => substr($row["jam_mulai"], 0, 5) . " - " . substr($row["jam_selesai"], 0, 5)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Halaman Pasien</title>

    <style>
      /* --- CSS STANDAR (dari indexpasien.html) --- */
      body {
        margin: 0;
        font-family: "Inter", sans-serif;
        background: linear-gradient(180deg, #e8f6ff, #f6fbff);
      }
      .topbar {
        padding: 18px 32px;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #dce7f3;
      }
      .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 600;
      }
      .tabs {
        display: flex;
        gap: 12px;
        margin: 20px 32px;
      }
      .tab {
        padding: 10px 20px;
        border-radius: 999px;
        border: none;
        background: #f0eaff;
        font-weight: 600;
        cursor: pointer;
      }
      .tab.active {
        background: white;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.07);
      }
      .page {
        display: none;
        padding: 0 32px;
      }
      .page.active {
        display: block;
      }
      .card {
        background: white;
        padding: 28px;
        margin-top: 20px;
        border-radius: 14px;
        box-shadow: 0 5px 18px rgba(0, 0, 0, 0.06);
      }
      .card h2 {
        margin: 0 0 20px 0;
        font-size: 20px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
      }
      th,
      td {
        padding: 12px;
        border-bottom: 1px solid #e6edf5;
        text-align: left;
      }
      .btn-black {
        padding: 12px 20px;
        border: none;
        background: #0f0f18;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        margin: 20px 32px;
      }
      .empty-box {
        text-align: center;
        color: #777;
        padding: 40px;
      }
      .logout-btn {
        padding: 10px 18px;
        background: #ff4d4d;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
      }

      .logout-btn:hover {
        background: #e60000;
      }
      
      /* --- CSS untuk Modal --- */
      .modal {
        display: none;
        position: fixed;
        z-index: 10;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
      }
      .modal-content {
        background-color: #fefefe;
        margin: 10% auto;
        padding: 30px;
        border-radius: 14px;
        width: 80%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      }
      .close-btn {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
      }
      .form-group {
        margin-bottom: 15px;
      }
      .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
      }
      .form-group input,
      .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        box-sizing: border-box;
      }
      .btn-submit {
        width: 100%;
        padding: 12px;
        border: none;
        background: #0f0f18;
        color: white;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        margin-top: 10px;
      }

      /* --- CSS UNTUK RADIO BUTTON SEPERTI TOMBOL (SESUAI PERMINTAAN) --- */
      .poli-radio-options {
          display: flex;
          flex-wrap: wrap; 
          gap: 10px;
          margin-top: 5px;
      }
      .poli-radio-options input[type="radio"] {
          /* Sembunyikan radio button bawaan */
          display: none;
      }
      .btn-radio {
          /* Gaya Label agar terlihat seperti Tombol */
          padding: 10px 15px;
          border: 1px solid #ccc;
          background: #f0f0f0;
          color: #333;
          font-weight: 500;
          border-radius: 8px;
          cursor: pointer;
          transition: all 0.2s;
          flex-grow: 1; 
          text-align: center;
          min-width: fit-content;
      }

      /* Gaya Label (Tombol) saat Radio Button AKTIF/Terpilih */
      .poli-radio-options input[type="radio"]:checked + .btn-radio {
          background: #0f0f18; 
          color: white; 
          border-color: #0f0f18;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .btn-radio:hover {
          background: #e0e0e0;
      }
    </style>
</head>

<body>
   <div class="topbar">
    <div class="brand">🏥 <span>Beranda Pasien</span></div>
    <div style="font-weight: 600"><?php echo htmlspecialchars($nama_pasien_login); ?></div>
    
    <a href="signin.php" class="logout-btn">Logout</a>
</div>

    <div class="tabs">
        <button class="tab active" onclick="showPage('pemeriksaan')">
            Daftar Pemeriksaan
        </button>
        <button class="tab" onclick="showPage('pasien')">Data Pasien</button>
        <button class="tab" onclick="showPage('rekam')">Rekam Medis</button>
        <button class="tab" onclick="showPage('laporan')">Riwayat Laporan</button>
        <button class="tab" onclick="showPage('jadwal')">Jadwal Dokter</button>
    </div>

    <div id="tambahPemeriksaanModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeModal('tambahPemeriksaanModal')">&times;</span>
        <h2>Tambah Pemeriksaan Baru</h2>
        <form action="proses_pemeriksaan.php" method="POST">
            
            <div class="form-group">
                <label>Poli / Jenis Pemeriksaan:</label>
                <div class="poli-radio-options">
                    <?php 
                    if (!empty($daftar_poli)) {
                        foreach ($daftar_poli as $index => $poli) {
                            $id_radio = 'poli_' . $index;
                            // FIX: Menambahkan 'checked' pada opsi pertama (index 0)
                            $checked = ($index === 0) ? 'checked' : ''; 
                            
                            echo '<input type="radio" id="' . $id_radio . '" name="jenis_pemeriksaan" value="' . htmlspecialchars($poli) . '" ' . $checked . ' required>';
                            echo '<label for="' . $id_radio . '" class="btn-radio">' . htmlspecialchars($poli) . '</label>';
                        }
                    } else {
                         echo '<p style="color:#777;">(Tidak ada data Poli tersedia)</p>';
                    }
                    ?>
                </div>
            </div>

            <div class="form-group">
                <label for="tanggal_pemeriksaan">Tanggal Pemeriksaan:</label>
                <input type="date" id="tanggal_pemeriksaan" name="tanggal_pemeriksaan" required>
            </div>
            <div class="form-group">
                <label for="status_pemeriksaan">Status:</label>
                <select id="status_pemeriksaan" name="status_pemeriksaan" required>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Simpan Pemeriksaan</button>
        </form>
      </div>
    </div>


    <section id="page-pemeriksaan" class="page active">
        <button class="btn-black" onclick="openModal('tambahPemeriksaanModal')">+ Tambah Pemeriksaan</button>

        <div class="card">
            <h2>Daftar Pemeriksaan</h2>

            <?php if (!empty($daftar_pemeriksaan)): ?>
            <table>
                <thead>
                    <tr><th>No</th><th>Pemeriksaan</th><th>Tanggal</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($daftar_pemeriksaan as $p): ?>
                    <tr>
                        <td><?= $p["No"] ?></td>
                        <td><?= htmlspecialchars($p["Pemeriksaan"]) ?></td>
                        <td><?= $p["Tanggal"] ?></td>
                        <td><?= htmlspecialchars($p["Status"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-box">Belum ada data pemeriksaan.</div>
            <?php endif; ?>
        </div>
    </section>

    <section id="page-pasien" class="page">
        <div class="card">
            <h2>Data Pasien</h2>

            <table>
            <?php if (!empty($data_pasien)): ?>
                <?php foreach ($data_pasien as $key => $val): ?>
                <tr>
                    <th><?= htmlspecialchars($key) ?></th>
                    <td><?= htmlspecialchars($val) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="2" class="empty-box">Data pasien tidak ditemukan.</td></tr>
            <?php endif; ?>
            </table>
        </div>
    </section>
    
    <section id="page-rekam" class="page">
        <div class="card">
            <h2>Rekam Medis</h2>

            <?php if (!empty($rekam_medis)): ?>
            <table>
                <thead>
                <tr><th>Tanggal</th><th>Diagnosis</th><th>Dokter</th><th>Catatan</th></tr>
                </thead>
                <tbody>
                <?php foreach ($rekam_medis as $r): ?>
                <tr>
                    <td><?= $r["Tanggal"] ?></td>
                    <td><?= htmlspecialchars($r["Diagnosis"]) ?></td>
                    <td><?= htmlspecialchars($r["Dokter"]) ?></td>
                    <td><?= htmlspecialchars($r["Catatan"]) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-box">Belum ada rekam medis.</div>
            <?php endif; ?>
        </div>
    </section>

    <section id="page-laporan" class="page">
        <div class="card">
            <h2>Riwayat Laporan</h2>

            <?php if (!empty($riwayat_laporan)): ?>
            <table>
                <thead>
                <tr><th>Tanggal</th><th>Jenis Laporan</th><th>Dokumen</th></tr>
                </thead>
                <tbody>
                <?php foreach ($riwayat_laporan as $l): ?>
                <tr>
                    <td><?= $l["Tanggal"] ?></td>
                    <td><?= htmlspecialchars($l["Jenis Laporan"]) ?></td>
                    <td>
                        <?php if (!empty($l["Dokumen"])): ?>
                            <a href="<?= htmlspecialchars($l["Dokumen"]) ?>" target="_blank">Download</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-box">Belum ada riwayat laporan.</div>
            <?php endif; ?>
        </div>
    </section>

    <section id="page-jadwal" class="page">
        <div class="card">
            <h2>Jadwal Dokter</h2>

            <?php if (!empty($jadwal_dokter)): ?>
            <table>
                <thead>
                    <tr><th>Dokter</th><th>Spesialis</th><th>Hari</th><th>Jam</th></tr>
                </thead>
                <tbody>
                <?php foreach ($jadwal_dokter as $j): ?>
                    <tr>
                        <td><?= htmlspecialchars($j["Dokter"]) ?></td>
                        <td><?= htmlspecialchars($j["Spesialis"]) ?></td>
                        <td><?= htmlspecialchars($j["Hari"]) ?></td>
                        <td><?= htmlspecialchars($j["Jam"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-box">Tidak ada jadwal.</div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        // Fungsi Tab Navigasi
        function showPage(name) {
            document
              .querySelectorAll(".page")
              .forEach((p) => p.classList.remove("active"));
            document.querySelector("#page-" + name).classList.add("active");

            document
              .querySelectorAll(".tab")
              .forEach((t) => t.classList.remove("active"));
            
            // Menambahkan class active pada tab yang diklik
            event.target.classList.add("active");
        }
        
        // Fungsi Modal
        function openModal(id){
            document.getElementById(id).style.display = "block";
        }

        function closeModal(id){
            document.getElementById(id).style.display = "none";
        }
        
        // Menutup modal jika klik di luar
        window.onclick = function(event) {
            const modal = document.getElementById('tambahPemeriksaanModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>