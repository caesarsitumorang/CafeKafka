<?php
require_once __DIR__ . '../../../vendor/autoload.php'; 
include "../../config/koneksi.php";

if (session_status() == PHP_SESSION_NONE) session_start();

$mpdf = new \Mpdf\Mpdf([
    'margin_top' => 55, 
    'margin_bottom' => 25,
    'margin_left' => 15,
    'margin_right' => 15,
    'format' => 'A4'
]);

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Admin login
$id_admin = $_SESSION['id_user'] ?? null;
$nama_admin = '-';
if ($id_admin) {
    $admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_admin'"));
    if ($admin) $nama_admin = $admin['username'];
}

// Query pakai filter tanggal
$filter = "DATE(p.created_at) = '$tanggal'";

// Data dashboard
$makanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_makanan IS NOT NULL AND p.status='selesai' AND $filter
"))['total'] ?? 0;

$minuman = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_minuman IS NOT NULL AND p.status='selesai' AND $filter
"))['total'] ?? 0;

$status = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status='diproses' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status='diterima' THEN 1 ELSE 0 END) as diterima,
        SUM(CASE WHEN status='ditolak' THEN 1 ELSE 0 END) as ditolak,
        SUM(CASE WHEN status='selesai' THEN 1 ELSE 0 END) as selesai
    FROM pesanan p
    WHERE $filter
"));

$pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT id_pelanggan) as total 
    FROM pesanan p
    WHERE $filter
"))['total'] ?? 0;

$keuangan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(total_harga) as total 
    FROM pesanan p 
    WHERE status='selesai' AND $filter
"))['total'] ?? 0;

// Header dengan logo
$header = '
<div style="display:flex; align-items:center; border-bottom:3px solid #c66300; padding-bottom:8px; margin-bottom:10px;">
  <div style="flex:0 0 80px; text-align:center;">
    <img src="http://localhost/cafe-kafka-website/img/kafka.png" width="70" height="70" style="object-fit:contain;" />
  </div>
  <div style="flex:1; text-align:center; font-family:Arial, sans-serif;">
    <h2 style="margin:0; color:#c66300; font-size:20px;">CAFE KAFKA</h2>
    <p style="margin:2px 0; font-size:12px; color:#444;">Jl. DR. Mansyur III No.1A, Padang Bulan Selayang I, Medan | Telp: 0813-4757-7205</p>
    <h3 style="margin:5px 0; color:#333; font-size:15px;">LAPORAN DASHBOARD</h3>
    <p style="margin:3px 0; font-size:11px; color:#777;">
      Dicetak pada '.date('d F Y, H:i').' WIB oleh <b>'.$nama_admin.'</b>
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
h3 { color:#c66300; margin-top:25px; font-size:14px;  padding-left:6px; }
.table { border-collapse: collapse; width: 100%; margin-top: 10px; font-size: 12px; }
.table th, .table td { border:1px solid #999; padding:8px; text-align:center; vertical-align:middle; }
.table th { background:#c66300; color:white; font-size:12px; }
.table tr:nth-child(even) { background:#f9f9f9; }
.summary { background:#f2f2f2; font-weight:bold; }
</style>

<h3>Ringkasan Data (Tanggal: '.date('d-m-Y', strtotime($tanggal)).')</h3>
<table class="table">
  <tr>
    <th>Makanan Terjual</th>
    <th>Minuman Terjual</th>
    <th>Jumlah Pelanggan</th>
    <th>Total Keuangan</th>
  </tr>
  <tr class="summary">
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
  <tr class="summary">
    <td>'.$status['total'].'</td>
    <td>'.$status['pending'].'</td>
    <td>'.$status['diproses'].'</td>
    <td>'.$status['diterima'].'</td>
    <td>'.$status['ditolak'].'</td>
    <td>'.$status['selesai'].'</td>
  </tr>
</table>

<div style="margin-top:60px; text-align:right; font-size:12px;">
  <p>Medan, '.date('d F Y').'</p>
  <p style="margin-top:70px; font-weight:bold; text-decoration:underline;">'.$nama_admin.'</p>
  <p style="margin-top:3px;">Admin Cafe Kafka</p>
</div>
';

$mpdf->WriteHTML($html);
$mpdf->Output("Laporan_Dashboard_CafeKafka_" . $tanggal . ".pdf", "I");
?>
