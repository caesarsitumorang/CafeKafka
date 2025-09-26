<?php
include "config/koneksi.php";

// Ambil data awal
function ambilDataDashboard($koneksi) {
    // Ambil semua pesanan
    $pesananQuery = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY created_at DESC");
    $pesanan = [];
    while($row = mysqli_fetch_assoc($pesananQuery)) {
        $pesanan[] = $row;
    }

    // Ambil semua detail pesanan
    $detailQuery = mysqli_query($koneksi, "SELECT * FROM pesanan_detail");
    $detailPesanan = [];
    while($row = mysqli_fetch_assoc($detailQuery)) {
        $detailPesanan[$row['id_pesanan']][] = $row;
    }

    // Ambil semua pelanggan
    $pelangganQuery = mysqli_query($koneksi, "SELECT * FROM pelanggan");
    $pelanggan = [];
    while($row = mysqli_fetch_assoc($pelangganQuery)) {
        $pelanggan[$row['id_pelanggan']] = $row;
    }

    return [
        'pesanan' => $pesanan,
        'detailPesanan' => $detailPesanan,
        'pelanggan' => $pelanggan
    ];
}

$rekap = ambilDataDashboard($koneksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Penjualan Cafe</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
   background: #414177ff;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    padding: 30px;
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.header h1 {
    color: #333;
    font-size: 2.5rem;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header p {
    color: #666;
    font-size: 1.1rem;
}

.top-action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 15px 20px;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 10px;
    border-left: 5px solid #667eea;
}

.periode-info {
    font-size: 1.1rem;
    color: #495057;
    font-weight: 500;
}

.periode-info strong {
    color: #333;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.table-container {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.table thead {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.table thead th {
    color: white;
    font-weight: 600;
    padding: 15px 12px;
    text-align: center;
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    border: none;
}

.table tbody td {
    padding: 15px 12px;
    text-align: center;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.95rem;
    color: #495057;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

/* Status badges */
.status-pending { color: #ffc107; font-weight: bold; }
.status-diproses { color: #17a2b8; font-weight: bold; }
.status-ditolak { color: #dc3545; font-weight: bold; }
.status-selesai { color: #28a745; font-weight: bold; }

/* Number formatting */
.number {
    font-weight: 600;
    color: #495057;
}

.currency {
    color: #28a745;
    font-weight: bold;
    font-size: 1.05rem;
}

/* Overlay modal */
.modal {
  display: none; /* default sembunyi */
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5); /* background gelap transparan */
}

/* Box modal */
.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 20px 30px;
  border-radius: 10px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
  font-family: Arial, sans-serif;
  animation: fadeIn 0.3s ease;
}

/* Animasi muncul */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-20px);}
  to {opacity: 1; transform: translateY(0);}
}

.modal-content h4 {
  margin-top: 0;
  margin-bottom: 15px;
  font-size: 18px;
  text-align: center;
  color: #333;
}

/* Input tanggal */
.modal-content input[type="date"] {
  width: 100%;
  padding: 8px;
  margin: 6px 0 12px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

/* Tombol */
.btn {
  padding: 8px 16px;
  margin: 5px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s;
}

.btn-success {
  background-color: #28a745;
  color: #fff;
}

.btn-success:hover {
  background-color: #218838;
}

.btn-cancel {
  background-color: #dc3545;
  color: #fff;
}

.btn-cancel:hover {
  background-color: #c82333;
}

/* Posisikan tombol ke tengah */
.modal-buttons {
  text-align: center;
  margin-top: 10px;
}


/* Loading Animation */
.loading {
    display: none;
    text-align: center;
    padding: 20px;
    color: #667eea;
}

.loading i {
    animation: spin 1s linear infinite;
    font-size: 1.5rem;
    margin-right: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 20px;
        margin: 10px;
    }
    
    .top-action-bar {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .table thead th,
    .table tbody td {
        padding: 8px 6px;
        font-size: 0.8rem;
    }
    
    .header h1 {
        font-size: 1.8rem;
    }
    
    .modal-content {
        margin: 15% auto;
        width: 95%;
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .table-container {
        overflow-x: auto;
    }
    
    .table {
        min-width: 600px;
    }
}
/* Posisikan tombol berdampingan di tengah */
.modal-buttons {
  display: flex;
  justify-content: center; /* tengah horizontal */
  gap: 15px; /* jarak antar tombol */
  margin-top: 15px;
}

</style>
</head>
<body>

<div class="top-action-bar">
    <div class="periode-info">
        <strong>Periode:</strong> <span id="periode">Semua</span>
    </div>
    <div class="action-buttons">
        <button class="btn btn-primary" onclick="openFilterModal()"><i class="fas fa-filter"></i> Filter Tanggal</button>
        <button class="btn btn-success" onclick="generatePDF()"><i class="fas fa-file-pdf"></i> Cetak PDF</button>
    </div>
</div>

<div class="table-container">
<table id="rekapTable" class="table">
<thead>
<tr>
  <th>No</th>
  <th>Total Makanan Terjual</th>
  <th>Total Minuman Terjual</th>
  <th>Pesanan Pending</th>
  <th>Pesanan Diproses</th>
  <th>Pesanan Ditolak</th>
  <th>Pesanan Selesai</th>
  <th>Total Pendapatan</th>
</tr>
</thead>
<tbody>
<!-- data akan diisi JS -->
</tbody>
</table>
</div>

<!-- Modal Filter -->
<div class="modal" id="filterModal">
  <div class="modal-content">
    <h4>Filter Tanggal</h4>

    <label for="filterModalAwal">Tanggal Awal</label>
    <input type="date" id="filterModalAwal">

    <label for="filterModalAkhir">Tanggal Akhir</label>
    <input type="date" id="filterModalAkhir">

    <div class="modal-buttons">
      <button class="btn btn-success" onclick="applyFilter()">Terapkan</button>
      <button class="btn btn-cancel" onclick="closeFilterModal()">Batal</button>
    </div>
  </div>
</div>


<script>
let filterAwal = '';
let filterAkhir = '';
let rekapData = <?= json_encode($rekap) ?>;

// Buka/tutup modal
function openFilterModal() { 
  document.getElementById("filterModal").style.display = "block"; 
}
function closeFilterModal() { 
  document.getElementById("filterModal").style.display = "none"; 
}

// Terapkan filter
function applyFilter() {
  filterAwal = document.getElementById('filterModalAwal').value;
  filterAkhir = document.getElementById('filterModalAkhir').value;
  closeFilterModal();
  renderFilteredTable();
}

// Cek apakah tanggal masuk range
function shouldDisplay(tgl) {
  const tanggal = tgl.substr(0,10);
  return (!filterAwal || tanggal >= filterAwal) && (!filterAkhir || tanggal <= filterAkhir);
}

// Render tabel berdasarkan filter
function renderFilteredTable() {
  const tbody = document.querySelector('#rekapTable tbody');
  tbody.innerHTML = '';
  let no = 1;

  // Update periode
  const periodeElem = document.getElementById('periode');
  periodeElem.textContent = filterAwal && filterAkhir ? `${filterAwal} s/d ${filterAkhir}` : 'Semua';

  // Hitung total pelanggan
  const totalPelanggan = Object.keys(rekapData.pelanggan).length;

  // Hitung total makanan, minuman, pesanan status, pendapatan
  let totalMakanan = 0;
  let totalMinuman = 0;
  let totalPendapatan = 0;
  let pending = 0, diproses = 0, ditolak = 0, selesai = 0;

  for (const pesanan of rekapData.pesanan) {
    if (!shouldDisplay(pesanan.created_at)) continue;

    // Tambah status
    if (pesanan.status === 'pending') pending++;
    if (pesanan.status === 'diproses') diproses++;
    if (pesanan.status === 'ditolak') ditolak++;
    if (pesanan.status === 'selesai') {
      selesai++;
      // Pendapatan hanya jika pesanan selesai
      totalPendapatan += parseFloat(pesanan.total_harga || 0);
    }

    // Hitung makanan/minuman
    const detail = rekapData.detailPesanan[pesanan.id] || [];
    for (const d of detail) {
      if (d.id_makanan) totalMakanan += parseInt(d.jumlah);
      if (d.id_minuman) totalMinuman += parseInt(d.jumlah);
    }
  }

  // Buat satu baris rekap
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${no++}</td>
    <td>${totalMakanan}</td>
    <td>${totalMinuman}</td>
    <td>${pending}</td>
    <td>${diproses}</td>
    <td>${ditolak}</td>
    <td>${selesai}</td>
    <td>Rp ${totalPendapatan.toLocaleString('id-ID')}</td>
  `;
  tbody.appendChild(tr);
}

// Render awal
renderFilteredTable();

// PDF
function generatePDF() {
    const awal = filterAwal || '';
    const akhir = filterAkhir || '';
    const url = `page_admin/penjualan/cetak_laporan.php` +
                `?tanggal_awal=${encodeURIComponent(awal)}` +
                `&tanggal_akhir=${encodeURIComponent(akhir)}`;
    window.open(url, '_blank');
}

</script>


</body>
</html>