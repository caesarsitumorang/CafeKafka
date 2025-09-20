<?php
include "config/koneksi.php";

// Total makanan terjual
$makanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_makanan IS NOT NULL AND p.status='selesai'
"))['total'] ?? 0;

// Total minuman terjual
$minuman = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(pd.jumlah) as total 
    FROM pesanan_detail pd
    INNER JOIN pesanan p ON p.id = pd.id_pesanan
    WHERE pd.id_minuman IS NOT NULL AND p.status='selesai'
"))['total'] ?? 0;

// Status pesanan
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

// Total pelanggan
$pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT id_pelanggan) as total 
    FROM pesanan
"))['total'] ?? 0;

// Total keuangan
$keuangan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT SUM(total_harga) as total 
    FROM pesanan 
    WHERE status='selesai'
"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Cafe</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #414177ff;
      margin: 0;
      padding: 0;
    }
    .navbar {
      background: #343a40;
      color: white;
      padding: 1rem;
    }
    .navbar h1 {
      margin: 0;
      font-size: 1.3rem;
    }
    .container {
      max-width: 1200px;
      margin: auto;
      padding: 20px;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      color: white;
    }
    .card {
      flex: 1;
      min-width: 220px;
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .card h5 {
      margin: 0;
      font-size: 1rem;
      color: #fefefeff;
    }
    .card p {
      margin: 10px 0 0;
      font-size: 1.8rem;
      font-weight: bold;
      color:white;
    }
    .bg-primary { background: #0d6efd; color: white; }
    .bg-success { background: #198754; color: white; }
    .bg-warning { background: #ffc107; color: #222; }
    .bg-danger { background: #dc3545; color: white; }
    .chart-card {
      flex: 1;
      min-width: 300px;
    }
    canvas {
      max-width: 100%;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
      max-width: 90%;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    .modal-header h3 {
      margin: 0;
      color: #333;
    }
    .close {
      color: #aaa;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover {
      color: #000;
    }
    .form-group {
      margin-bottom: 15px;
    }
    .form-group label {
      display: block;
      margin-bottom: 5px;
      color: #333;
      font-weight: 500;
    }
    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
    }
    .btn-group {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-primary {
      background: #dc3545;
      color: white;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn:hover {
      opacity: 0.9;
    }

    .cetak-btn {
      background: #dc3545;
      color: white;
      padding: 10px 18px;
      border-radius: 6px;
      border: none;
      font-weight: 600;
      cursor: pointer;
    }
    .cetak-btn:hover {
      background: #c82333;
    }
  </style>
</head>
<body>

<div style="text-align:right; padding:20px;">
  <button type="button" class="cetak-btn" onclick="openDateModal()">
    <i class="fas fa-file-pdf"></i> Cetak PDF
  </button>
</div>

<!-- Modal Popup -->
<div id="dateModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Pilih Tanggal Laporan</h3>
      <span class="close" onclick="closeDateModal()">&times;</span>
    </div>
    <form id="laporanForm">
      <div class="form-group">
        <label for="tanggal">Tanggal:</label>
        <input type="date" id="tanggal" name="tanggal" required value="<?= date('Y-m-d') ?>">
      </div>
      <div class="btn-group">
        <button type="button" class="btn btn-secondary" onclick="closeDateModal()">Batal</button>
        <button type="submit" class="btn btn-primary">Cetak Laporan</button>
      </div>
    </form>
  </div>
</div>

<div class="container">
  <!-- Cards -->
  <div class="row">
    <div class="card bg-primary">
      <h5>Makanan Terjual</h5>
      <p><?= $makanan ?></p>
    </div>
    <div class="card bg-success">
      <h5>Minuman Terjual</h5>
      <p><?= $minuman ?></p>
    </div>
    <div class="card bg-warning">
      <h5>Data Pelanggan</h5>
      <p><?= $pelanggan ?></p>
    </div>
    <div class="card bg-danger">
      <h5>Total Keuangan</h5>
      <p>Rp <?= number_format($keuangan,0,",",".") ?></p>
    </div>
  </div>

  <!-- Grafik -->
  <div class="row" style="margin-top:20px;">
    <div class="card chart-card">
      <h5>Penjualan Makanan & Minuman</h5>
      <canvas id="chartSales"></canvas>
    </div>
    <div class="card chart-card">
      <h5>Status Pesanan</h5>
      <canvas id="chartStatus"></canvas>
    </div>
  </div>
</div>

<script>
  // Modal Functions
  function openDateModal() {
    document.getElementById('dateModal').style.display = 'block';
  }

  function closeDateModal() {
    document.getElementById('dateModal').style.display = 'none';
  }

  // Close modal when clicking outside
  window.onclick = function(event) {
    const modal = document.getElementById('dateModal');
    if (event.target == modal) {
      closeDateModal();
    }
  }

  // Handle form submission
  document.getElementById('laporanForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const tanggal = document.getElementById('tanggal').value;
    
    if (!tanggal) {
      alert('Silakan pilih tanggal terlebih dahulu!');
      return;
    }

    // Open PDF in new window
    const url = 'page_admin/penjualan/cetak_laporan.php?tanggal=' + encodeURIComponent(tanggal);
    window.open(url, '_blank');
    
    // Close modal
    closeDateModal();
  });

  // Grafik Penjualan
  new Chart(document.getElementById('chartSales').getContext('2d'), {
    type: 'bar',
    data: {
      labels: ['Makanan', 'Minuman'],
      datasets: [{
        label: 'Jumlah Terjual',
        data: [<?= $makanan ?>, <?= $minuman ?>],
        backgroundColor: ['#0d6efd', '#198754']
      }]
    }
  });

  // Grafik Status Pesanan
  new Chart(document.getElementById('chartStatus').getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: ['Pending','Diproses','Diterima','Ditolak','Selesai'],
      datasets: [{
        data: [<?= $status['pending'] ?>, <?= $status['diproses'] ?>, <?= $status['diterima'] ?>, <?= $status['ditolak'] ?>, <?= $status['selesai'] ?>],
        backgroundColor: ['#ffc107','#0dcaf0','#20c997','#6c757d','#198754']
      }]
    }
  });
</script>

</body>
</html>