<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID minuman tidak ditemukan.'); window.location='index_admin.php?page_admin=minuman/data_minuman';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data minuman terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM minuman WHERE id = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index_admin.php?page_admin=minuman/data_minuman';</script>";
  exit;
}

// Hapus gambar jika ada
if (!empty($data['gambar']) && file_exists("upload/" . $data['gambar'])) {
  unlink("upload/" . $data['gambar']);
}

// Hapus dari tabel minuman
$delete = mysqli_query($koneksi, "DELETE FROM minuman WHERE id = '$id'");

if ($delete) {
  echo "<script>alert('Data minuman berhasil dihapus.'); window.location='index_admin.php?page_admin=minuman/data_minuman';</script>";
} else {
  echo "<script>alert('Gagal menghapus data.'); window.location='index_admin.php?page_admin=minuman/data_minuman';</script>";
}
?>
