<?php
// Pastikan sesi dimulai jika Anda menggunakan mekanisme login/session ID pasien yang sebenarnya
// session_start(); 

// ==============================================================================
// 1. DATABASE CONNECTION (Sesuaikan jika setting database Anda berbeda)
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

// ==============================================================================
// 2. AMBIL DATA DARI FORM (Modal Pendaftaran)
// ==============================================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ID Pasien (Asumsi ID Pasien 1, sesuaikan jika Anda menggunakan session)
    $id_pasien = 1; 
    
    // Ambil data dari form (modal di indexpasien.php)
    $jenis_pemeriksaan = $_POST['jenis_pemeriksaan'] ?? ''; // Dari Radio Button Poli
    $tanggal_pemeriksaan = $_POST['tanggal_pemeriksaan'] ?? '';
    $status = $_POST['status_pemeriksaan'] ?? 'Menunggu'; // Dari Select Status

    // ==============================================================================
    // 3. VALIDASI DAN INSERT KE DATABASE
    // ==============================================================================
    if (empty($jenis_pemeriksaan) || empty($tanggal_pemeriksaan)) {
        // Jika data tidak lengkap
        header("Location: indexpasien.php?status=error&msg=Data tidak lengkap.");
        exit();
    }

    // Query INSERT (Asumsi tabel 'pemeriksaan' memiliki kolom id_pasien, jenis_pemeriksaan, tanggal_pemeriksaan, status)
    $sql = "INSERT INTO pemeriksaan (id_pasien, jenis_pemeriksaan, tanggal_pemeriksaan, status) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters: i (integer), s (string), s (string), s (string)
    $stmt->bind_param("isss", $id_pasien, $jenis_pemeriksaan, $tanggal_pemeriksaan, $status);
    
    if ($stmt->execute()) {
        // INSERT BERHASIL
        
        // Simpan detail data yang baru dimasukkan untuk ditampilkan di halaman sukses
        $data_sukses = [
            'Jenis Pemeriksaan' => $jenis_pemeriksaan,
            'Tanggal' => $tanggal_pemeriksaan,
            'Status' => $status
        ];
        
        // Alihkan ke halaman konfirmasi sukses
        // Kami menggunakan pengalihan 307 (Temporary) untuk POST, tapi header Location cukup umum
        header("Location: proses_sukses.php?" . http_build_query($data_sukses));
        exit();

    } else {
        // INSERT GAGAL
        $error_msg = $conn->error;
        header("Location: indexpasien.php?status=error&msg=Gagal menyimpan data: " . urlencode($error_msg));
        exit();
    }
    
    $stmt->close();
} else {
    // Jika diakses tanpa metode POST (langsung di browser)
    header("Location: indexpasien.php");
    exit();
}

$conn->close();
?>