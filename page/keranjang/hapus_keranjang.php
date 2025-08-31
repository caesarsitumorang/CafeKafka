<?php
include "config/koneksi.php";

if (!isset($_GET['id'])) {
  echo "<script>alert('ID keranjang tidak ditemukan.'); window.location='index.php?page=keranjang/keranjang';</script>";
  exit;
}

$id = $_GET['id'];

// Ambil data makanan terlebih dahulu
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM keranjang WHERE id = '$id'"));

if (!$data) {
  echo "<script>alert('Data tidak ditemukan.'); window.location='index.php?page=keranjang/keranjang';</script>";
  exit;
}

// Hapus dari tabel makanan
$delete = mysqli_query($koneksi, "DELETE FROM keranjang WHERE id = '$id'");

if ($delete) {
  echo "<script>alert('Data makanan berhasil dihapus.'); window.location='index.php?page=keranjang/keranjang';</script>";
} else {
  echo "<script>alert('Gagal menghapus data.'); window.location='index.php?page=keranjang/keranjang';</script>";
}
?>
