<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID makanan tidak ditemukan.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data makanan terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM makanan WHERE id = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
  exit;
}

// Hapus gambar jika ada
if (!empty($data['gambar']) && file_exists("upload/" . $data['gambar'])) {
  unlink("upload/" . $data['gambar']);
}

// Hapus dari tabel makanan
$delete = mysqli_query($koneksi, "DELETE FROM makanan WHERE id = '$id'");

if ($delete) {
  echo "<script>alert('Data makanan berhasil dihapus.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
} else {
  echo "<script>alert('Gagal menghapus data.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
}
?>
