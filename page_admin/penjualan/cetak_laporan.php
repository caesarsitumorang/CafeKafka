<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 50, 
    'margin_bottom' => 30,
    'margin_left' => 15,
    'margin_right' => 15,
    'format' => 'A4'
]);

// Admin login
$id_admin = $_SESSION['id_user'] ?? null;
$nama_admin = '-';
if ($id_admin) {
    $admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_admin'"));
    if ($admin) $nama_admin = $admin['username'];
}

// Data dashboard
$makanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_makanan IS NOT NULL AND p.status='selesai'
"))['total'] ?? 0;

$minuman = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_minuman IS NOT NULL AND p.status='selesai'
"))['total'] ?? 0;

$status = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='diproses' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status='diterima' THEN 1 ELSE 0 END) as diterima,
        SUM(CASE WHEN status='ditolak' THEN 1 ELSE 0 END) as ditolak,
        SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesai
    FROM pesanan
"));

$pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT id_pelanggan) as total 
    FROM pesanan
"))['total'] ?? 0;

$keuangan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(total_harga) as total 
    FROM pesanan WHERE status='selesai'
"))['total'] ?? 0;

// Header dengan logo + identitas
$header = '
<div style="display:flex; align-items:center; border-bottom:3px solid #c66300; padding-bottom:10px; margin-bottom:15px;">
  <div style="flex:0 0 80px; text-align:center;">
    <img src="http://localhost/cafe-kafka-website/img/kafka.png" width="70" height="70" style="object-fit:contain;" />
  </div>
  <div style="flex:1; text-align:center; font-family:Arial, sans-serif;">
    <h2 style="margin:0; color:#c66300;">CAFE KAFKA</h2>
    <p style="margin:2px 0; font-size:13px; color:#444;">Jl. Contoh No.123, Medan | Telp: 0812-3456-7890</p>
    <h3 style="margin:5px 0; color:#333;">LAPORAN DASHBOARD</h3>
    <p style="margin:3px 0; font-size:11px; color:#777;">
      Dicetak pada '.date('d F Y, H:i').' WIB oleh '.$nama_admin.'
    </p>
  </div>
</div>';
$mpdf->SetHTMLHeader($header);

// Footer
$footer = '
<div style="border-top:1px solid #aaa; padding-top:5px; font-size:10px; text-align:center; color:#555; font-family:Arial, sans-serif;">
  Halaman {PAGENO} dari {nbpg} | Cafe Kafka
</div>';
$mpdf->SetHTMLFooter($footer);

// Konten
$html = '
<style>
body { font-family: Arial, sans-serif; font-size:12px; }
h3 { color:#c66300; margin-top:25px; }
.table {
  border-collapse: collapse;
  width: 100%;
  margin-top: 10px;
  font-size: 12px;
}
.table th, .table td {
  border:1px solid #999;
  padding:8px;
  text-align:center;
}
.table th {
  background:#c66300;
  color:white;
}
  hr {
  display: none;
}

.ringkasan-data {
  border: none;
}

.table tr:nth-child(even) { background:#f9f9f9; }
</style>
<hr>
<h3 class="ringkasan-title">Ringkasan Data</h3>

<table class="table">
  <tr>
    <th>Makanan Terjual</th>
    <th>Minuman Terjual</th>
    <th>Jumlah Pelanggan</th>
    <th>Total Keuangan</th>
  </tr>
  <tr>
    <td>'.$makanan.'</td>
    <td>'.$minuman.'</td>
    <td>'.$pelanggan.'</td>
    <td>Rp '.number_format($keuangan,0,",",".").'</td>
  </tr>
</table>

<h3>Status Pesanan</h3>
<table class="table">
  <tr>
    <th>Total</th>
    <th>Pending</th>
    <th>Diproses</th>
    <th>Diterima</th>
    <th>Ditolak</th>
    <th>Selesai</th>
  </tr>
  <tr>
    <td>'.$status['total'].'</td>
    <td>'.$status['pending'].'</td>
    <td>'.$status['diproses'].'</td>
    <td>'.$status['diterima'].'</td>
    <td>'.$status['ditolak'].'</td>
    <td>'.$status['selesai'].'</td>
  </tr>
</table>

<div style="margin-top:50px; text-align:right; font-size:12px;">
  <p>Medan, '.date('d F Y').'</p>
  <p style="margin-top:60px; font-weight:bold;">'.$nama_admin.'</p>
</div>
';

$mpdf->WriteHTML($html);
$mpdf->Output("Laporan_Dashboard_CafeKafka_" . date('Y-m-d') . ".pdf", "I");
?>
