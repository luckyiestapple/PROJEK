<?php
session_start();
include "koneksi.php";

$email    = $_POST['email'];
$password = $_POST['password'];

$data = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
$user = mysqli_fetch_assoc($data);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama']    = $user['nama'];

    echo "<script>alert('Berhasil masuk!'); window.location='indexpasien.php';</script>";
} else {
    echo "<script>alert('Email atau password salah!'); window.location='signin.php';</script>";
}
?>
