<?php
include "koneksi.php";

$nama          = $_POST['nama'];
$email         = $_POST['email'];
$telepon       = $_POST['telepon'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$gender        = $_POST['gender'];
$alamat        = $_POST['alamat'];
$password      = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (nama, email, telepon, tanggal_lahir, gender, alamat, password) 
        VALUES ('$nama', '$email', '$telepon', '$tanggal_lahir', '$gender', '$alamat', '$password')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='signin.php';</script>";
} else {
    echo "<script>alert('Email sudah terdaftar!'); window.location='register.php';</script>";
}
?>
