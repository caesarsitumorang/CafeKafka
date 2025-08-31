<?php
header('Content-Type: application/json');
include "config/koneksi.php";

$totalBuku = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku"));
$totalDigital = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM buku_digital"));
$totalPengunjung = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM pengunjung"));

echo json_encode([
  "buku" => $totalBuku,
  "digital" => $totalDigital,
  "pengunjung" => $totalPengunjung
]);
?>
