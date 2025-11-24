<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Sukses</title>
    <style>
        body { font-family: 'Inter', sans-serif; background: #e8f6ff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 40px; border-radius: 14px; box-shadow: 0 5px 18px rgba(0, 0, 0, 0.1); max-width: 400px; text-align: left; }
        .success-header { color: #4CAF50; font-size: 24px; margin-bottom: 20px; display: flex; align-items: center; }
        .success-header span { margin-left: 10px; font-weight: 700; }
        .detail-item { margin-bottom: 15px; }
        .detail-item strong { display: block; margin-bottom: 5px; color: #333; }
        .btn-black { 
            padding: 12px 20px; border: none; background: #0f0f18; color: white; 
            font-weight: 600; border-radius: 10px; cursor: pointer; width: 100%;
            margin-top: 20px; text-decoration: none; display: block; text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="success-header">
            ✅<span>Data Pemeriksaan Berhasil Ditambahkan</span>
        </div>
        
        <p>Data berikut telah diterima oleh sistem:</p>

        <?php
        // Ambil data dari URL query string
        $jenis = $_GET['Jenis_Pemeriksaan'] ?? 'N/A';
        $tanggal = $_GET['Tanggal'] ?? 'N/A';
        $status = $_GET['Status'] ?? 'N/A';
        ?>

        <div class="detail-item">
            <strong>Jenis Pemeriksaan:</strong> 
            <?php echo htmlspecialchars($jenis); ?>
        </div>
        <div class="detail-item">
            <strong>Tanggal:</strong> 
            <?php echo htmlspecialchars($tanggal); ?>
        </div>
        <div class="detail-item">
            <strong>Status:</strong> 
            <?php echo htmlspecialchars($status); ?>
        </div>
        
        <a href="indexpasien.php" class="btn-black">Kembali ke Halaman Pasien</a>
    </div>
</body>
</html>