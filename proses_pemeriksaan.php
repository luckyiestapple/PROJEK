<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rumahsakit";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pasien = 1; 
   
    $jenis_pemeriksaan = $_POST['jenis_pemeriksaan'] ?? ''; 
    $tanggal_pemeriksaan = $_POST['tanggal_pemeriksaan'] ?? '';
    $status = $_POST['status_pemeriksaan'] ?? 'Menunggu'; 
     if (empty($jenis_pemeriksaan) || empty($tanggal_pemeriksaan)) {
        // Jika data tidak lengkap
        header("Location: indexpasien.php?status=error&msg=Data tidak lengkap.");
        exit();
    }

     $sql = "INSERT INTO pemeriksaan (id_pasien, jenis_pemeriksaan, tanggal_pemeriksaan, status) 
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
     $stmt->bind_param("isss", $id_pasien, $jenis_pemeriksaan, $tanggal_pemeriksaan, $status);
    
    if ($stmt->execute()) {
       
        $data_sukses = [
            'Jenis Pemeriksaan' => $jenis_pemeriksaan,
            'Tanggal' => $tanggal_pemeriksaan,
            'Status' => $status
        ];
       header("Location: proses_sukses.php?" . http_build_query($data_sukses));
        exit();

    } else {
        $error_msg = $conn->error;
        header("Location: indexpasien.php?status=error&msg=Gagal menyimpan data: " . urlencode($error_msg));
        exit();
    }
    
    $stmt->close();
} else {
    header("Location: indexpasien.php");
    exit();
}

$conn->close();
?>