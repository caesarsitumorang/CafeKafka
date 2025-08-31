
<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";
$qPembayaran = mysqli_query($koneksi, "SELECT bank, no_rek, pemilik FROM data_cafe LIMIT 1");
$dataPembayaran = mysqli_fetch_assoc($qPembayaran);
// 🔒 Cek login
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// 🎯 Ambil id_pelanggan berdasarkan username
$user_query = mysqli_query($koneksi, "
    SELECT p.id_pelanggan 
    FROM users u
    JOIN pelanggan p ON p.username = u.username
    WHERE u.id_user = '$id_user'
");

$user_data = mysqli_fetch_assoc($user_query);
$id_pelanggan = $user_data['id_pelanggan'] ?? null;

if (!$id_pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}