<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID pelanggan tidak ditemukan.'); window.location='index_admin.php?page_admin=pelanggan/data_pelanggan';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data pelanggan terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index_admin.php?page_admin=pelanggan/data_pelanggan';</script>";
  exit;
}

// Hapus foto profil jika ada
if (!empty($data['foto']) && file_exists("upload/" . $data['foto'])) {
  unlink("upload/" . $data['foto']);
}

// Hapus data dari tabel pelanggan
$delete_pelanggan = mysqli_query($koneksi, "DELETE FROM pelanggan WHERE id_pelanggan = '$id'");

// Hapus juga dari tabel users berdasarkan username
if ($delete_pelanggan) {
  mysqli_query($koneksi, "DELETE FROM users WHERE username = '".$data['username']."'");
  
  echo "<script>alert('Data pelanggan & akun user berhasil dihapus.'); window.location='index_admin.php?page_admin=pelanggan/data_pelanggan';</script>";
} else {
  echo "<script>alert('Gagal menghapus data pelanggan.'); window.location='index_admin.php?page_admin=pelanggan/data_pelanggan';</script>";
}
?>
