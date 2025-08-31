<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Akses ditolak!'); window.location='login.php';</script>";
    exit;
}

$username = mysqli_real_escape_string($koneksi, $_GET['username']);

try {
    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username' AND role = 'admin'");
    if (mysqli_num_rows($cek) === 0) {
        throw new Exception("Data administrator tidak ditemukan.");
    }

    // Hapus data admin
    mysqli_query($koneksi, "DELETE FROM admin WHERE username = '$username'");
    // Hapus juga dari users
    mysqli_query($koneksi, "DELETE FROM users WHERE username = '$username' AND role = 'admin'");

    $_SESSION['message'] = "Administrator berhasil dihapus.";
    $_SESSION['message_type'] = "success";

} catch (Exception $e) {
    $_SESSION['message'] = $e->getMessage();
    $_SESSION['message_type'] = "danger";
}

echo "<script>window.location.href='index_admin.php?page_admin=administrator/data_administrator';</script>";
exit;
?>
