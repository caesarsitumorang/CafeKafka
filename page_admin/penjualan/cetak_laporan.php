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

// Ambil parameter tanggal dari GET
$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';

if ($tanggal_awal === '' || $tanggal_akhir === '') {
    // default periode bulan ini
    $tanggal_awal = date('Y-m-01');
    $tanggal_akhir = date('Y-m-d');
}

// Validasi tanggal
if ($tanggal_awal > $tanggal_akhir) {
    die('Error: Tanggal awal tidak boleh lebih besar dari tanggal akhir!');
}

// Admin login
$id_admin = $_SESSION['id_user'] ?? null;
$nama_admin = '-';
if ($id_admin) {
    $admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_admin'"));
    if ($admin) $nama_admin = $admin['username'];
}

// Filter tanggal
$filter = "DATE(p.created_at) BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";

// Data ringkasan
$makanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COALESCE(SUM(pd.jumlah),0) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_makanan IS NOT NULL AND p.status='selesai' AND $filter
"))['total'];

$minuman = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COALESCE(SUM(pd.jumlah),0) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_minuman IS NOT NULL AND p.status='selesai' AND $filter
"))['total'];

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
"))['total'];

$keuangan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga),0) as total 
    FROM pesanan p 
    WHERE status='selesai' AND $filter
"))['total'];

// Format periode
$format_awal = date('d F Y', strtotime($tanggal_awal));
$format_akhir = date('d F Y', strtotime($tanggal_akhir));
$periode = ($tanggal_awal == $tanggal_akhir) ? $format_awal : "$format_awal - $format_akhir";

// Header PDF
$header = '
<div style="display:flex; align-items:center; padding-bottom:8px; margin-bottom:10px;">
  <div style="flex:0 0 80px; text-align:center;">
    <img src="http://localhost/cafe-kafka-website/img/kafka.png" width="70" height="70" style="object-fit:contain;" />
  </div>
  <div style="flex:1; text-align:center; font-family:Arial, sans-serif;">
    <h2 style="margin:0; color:#c66300; font-size:20px;">CAFE KAFKA</h2>
    <p style="margin:2px 0; font-size:12px; color:#444;">Jl. DR. Mansyur III No.1A, Medan | Telp: 0813-4757-7205</p>
    <h3 style="margin:5px 0; color:#333; font-size:15px;">LAPORAN DASHBOARD</h3>
    <p style="margin:3px 0; font-size:11px; color:#777;">
      Periode: '.$periode.'<br>
      Dicetak pada '.date('d F Y, H:i').' WIB oleh <b>'.$nama_admin.'</b>
    </p>
  </div>
</div>';
$mpdf->SetHTMLHeader($header);

// Footer PDF
$footer = '
<div style="border-top:1px solid #aaa; padding-top:5px; font-size:10px; text-align:center; color:#555; font-family:Arial, sans-serif;">
  Halaman {PAGENO} dari {nbpg} | Cafe Kafka | Periode: '.$periode.'
</div>';
$mpdf->SetHTMLFooter($footer);

// Ambil detail pesanan selesai
$detail_pesanan = mysqli_query($koneksi, "
    SELECT p.id, p.id_pelanggan, pel.nama_lengkap as nama_pelanggan, 
           p.total_harga, p.created_at, p.status
    FROM pesanan p
    LEFT JOIN pelanggan pel ON pel.id_pelanggan = p.id_pelanggan
    WHERE p.status='selesai' AND $filter
    ORDER BY p.created_at DESC
    LIMIT 20
");



// Konten
$html = '
<style>
body { font-family: Arial, sans-serif; font-size:12px; }
h3 { color:#c66300; margin-top:25px; font-size:14px; padding-left:6px; }
.table { border-collapse: collapse; width: 100%; margin-top: 10px; font-size: 11px; }
.table th, .table td { border:1px solid #999; padding:6px; text-align:center; vertical-align:middle; }
.table th { background:#c66300; color:white; font-size:11px; font-weight:bold; }
.table tr:nth-child(even) { background:#f9f9f9; }
.summary { background:#f2f2f2; font-weight:bold; }
.text-left { text-align:left !important; }
.periode-info { 
  background:#e3f2fd; 
  padding:10px; 
  border-radius:5px; 
  margin:15px 0; 
  border-left:4px solid #2196f3;
  font-size:12px;
  color:#1565c0;
}
</style>

<div class="periode-info">
  <strong>PERIODE LAPORAN: '.$periode.'</strong>
  Total hari dalam periode: '.((strtotime($tanggal_akhir) - strtotime($tanggal_awal)) / (60*60*24) + 1).' hari
</div>

<h3>Ringkasan Data Periode</h3>
<table class="table">
  <tr>
    <th>Makanan Terjual</th>
    <th>Minuman Terjual</th>
    <th>Jumlah Pelanggan</th>
    <th>Total Keuangan</th>
  </tr>
  <tr class="summary">
    <td>'.$makanan.' item</td>
    <td>'.$minuman.' item</td>
    <td>'.$pelanggan.' orang</td>
    <td>Rp '.number_format($keuangan,0,",",".").'</td>
  </tr>
</table>

<h3>Status Pesanan Periode</h3>
<table class="table">
  <tr>
    <th>Total Pesanan</th>
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

<h3>Detail Pesanan Selesai (Maksimal 20 Data Terakhir)</h3>
<table class="table">
  <tr>
    <th>No</th>
    <th>ID Pesanan</th>
    <th>Nama Pelanggan</th>
    <th>Total Harga</th>
    <th>Tanggal</th>
    <th>Status</th>
  </tr>';

$no = 1;
if (mysqli_num_rows($detail_pesanan) > 0) {
    while ($row = mysqli_fetch_assoc($detail_pesanan)) {
        $html .= '
        <tr>
            <td>'.$no.'</td>
            <td>PES-'.$row['id'].'</td>
            <td class="text-left">'.$row['nama_pelanggan'].'</td>
            <td>Rp '.number_format($row['total_harga'],0,",",".").'</td>
            <td>'.date('d/m/Y H:i', strtotime($row['created_at'])).'</td>
            <td>'.strtoupper($row['status']).'</td>
        </tr>';
        $no++;
    }
} else {
    $html .= '<tr><td colspan="6">Tidak ada data pesanan selesai dalam periode ini</td></tr>';
}

$html .= '</table>';

// Analisis periode
$hari_count = (strtotime($tanggal_akhir) - strtotime($tanggal_awal)) / (60*60*24) + 1;
$rata_keuangan = $hari_count > 0 ? $keuangan / $hari_count : 0;
$rata_makanan = $hari_count > 0 ? $makanan / $hari_count : 0;
$rata_minuman = $hari_count > 0 ? $minuman / $hari_count : 0;

$html .= '
<h3>Analisis Periode</h3>
<table class="table">
  <tr>
    <th>Metrik</th>
    <th>Total</th>
    <th>Rata-rata per Hari</th>
  </tr>
  <tr>
    <td class="text-left">Pendapatan</td>
    <td>Rp '.number_format($keuangan,0,",",".").'</td>
    <td>Rp '.number_format($rata_keuangan,0,",",".").'</td>
  </tr>
  <tr>
    <td class="text-left">Makanan Terjual</td>
    <td>'.$makanan.' item</td>
    <td>'.number_format($rata_makanan,1).' item</td>
  </tr>
  <tr>
    <td class="text-left">Minuman Terjual</td>
    <td>'.$minuman.' item</td>
    <td>'.number_format($rata_minuman,1).' item</td>
  </tr>
  <tr>
    <td class="text-left">Pesanan Selesai</td>
    <td>'.$status['selesai'].' pesanan</td>
    <td>'.number_format($status['selesai']/$hari_count,1).' pesanan</td>
  </tr>
</table>

<div style="margin-top:40px; text-align:right; font-size:12px;">
  <p>Medan, '.date('d F Y').'</p>
  <p style="margin-top:50px; font-weight:bold; text-decoration:underline;">'.$nama_admin.'</p>
  <p style="margin-top:3px;">Admin Cafe Kafka</p>
</div>
';

$mpdf->WriteHTML($html);
$filename = "Laporan_Dashboard_CafeKafka_" . $tanggal_awal . "_to_" . $tanggal_akhir . ".pdf";
$mpdf->Output($filename, "I");
?>