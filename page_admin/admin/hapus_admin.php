<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID admin tidak ditemukan.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data admin terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE id_admin = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
  exit;
}

// Hapus foto profil jika ada
if (!empty($data['foto']) && file_exists("upload/" . $data['foto'])) {
  unlink("upload/" . $data['foto']);
}

// Hapus data dari tabel admin
$delete_admin = mysqli_query($koneksi, "DELETE FROM admin WHERE id_admin = '$id'");

// Hapus juga dari tabel users berdasarkan username
if ($delete_admin) {
  mysqli_query($koneksi, "DELETE FROM users WHERE username = '".$data['username']."'");
  
  echo "<script>alert('Data admin & akun user berhasil dihapus.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
} else {
  echo "<script>alert('Gagal menghapus data admin.'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
}
?>
