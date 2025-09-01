<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Cek login user
$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// Ambil username dari tabel users
$user_query = mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'");
$user_data  = mysqli_fetch_assoc($user_query);

if (!$user_data) {
    echo "<script>alert('Data user tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

$username = $user_data['username'];

// Cari data pelanggan berdasarkan username
$pelanggan_query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE username = '$username'");
$pelanggan = mysqli_fetch_assoc($pelanggan_query);

if (!$pelanggan) {
    echo "<script>alert('Data pelanggan tidak ditemukan.'); window.location='login.php';</script>";
    exit;
}

$id_pelanggan = $pelanggan['id_pelanggan'];

// Ambil data pesanan berdasarkan id_pelanggan
$query = "
    SELECT p.*, m.nama AS nama_makanan, n.nama AS nama_minuman, 
           p.created_at, p.updated_at
    FROM pesanan p
    LEFT JOIN makanan m ON p.id_makanan = m.id
    LEFT JOIN minuman n ON p.id_minuman = n.id
    WHERE p.id_pelanggan = $id_pelanggan
    ORDER BY p.id DESC
";
$result = mysqli_query($koneksi, $query);

// Ambil statistik pesanan
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status = 'diterima' THEN 1 ELSE 0 END) as diterima,
        SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
        SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
    FROM pesanan 
    WHERE id_pelanggan = $id_pelanggan
";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>


<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan Saya - Cafe Kafka</title>
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
:root {
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --info-color: #17a2b8;
    --dark-color: #34495e;
    --light-color: #ecf0f1;
    --white: #ffffff;
    --gray-100: #f8f9fa;
    --gray-200: #e9ecef;
    --gray-300: #dee2e6;
    --gray-400: #ced4da;
    --gray-500: #adb5bd;
    --gray-600: #6c757d;
    --gray-700: #495057;
    --gray-800: #343a40;
    --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    --border-radius: 8px;
    --border-radius-lg: 12px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--gray-100);
    min-height: 100vh;
    line-height: 1.6;
}

.page-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white);
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    text-align: center;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.page-subtitle {
    text-align: center;
    font-size: 1rem;
    margin-top: 0.5rem;
    opacity: 0.9;
}

.container-custom {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--white);
    padding: 1.25rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    text-align: center;
    border: 1px solid var(--gray-200);
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: var(--gray-600);
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

.stat-diproses .stat-number { color: var(--warning-color); }
.stat-diterima .stat-number { color: var(--success-color); }
.stat-ditolak .stat-number { color: var(--accent-color); }
.stat-selesai .stat-number { color: var(--info-color); }

.orders-container {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--gray-200);
    overflow: hidden;
}

.orders-header {
    background: var(--gray-100);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    font-weight: 600;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.orders-list {
    max-height: 600px;
    overflow-y: auto;
}

.order-item {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.order-item:last-child {
    border-bottom: none;
}

.order-item:hover {
    background: var(--gray-100);
}

.order-left {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.order-id {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 1rem;
}

.order-items {
    font-size: 0.9rem;
    color: var(--gray-600);
}

.order-date {
    font-size: 0.8rem;
    color: var(--gray-500);
}

.order-center {
    text-align: center;
}

.order-price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--success-color);
}

.order-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}
.status-pending {
    background: var(--warning-color);
    color: var(--white);
}

.status-diproses {
    background: var(--warning-color);
    color: var(--white);
}

.status-diterima {
    background: var(--success-color);
    color: var(--white);
}

.status-ditolak {
    background: var(--accent-color);
    color: var(--white);
}

.status-selesai {
    background: var(--info-color);
    color: var(--white);
}

.view-detail-btn {
    background: var(--secondary-color);
    color: var(--white);
    border: none;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.2s ease;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.view-detail-btn:hover {
    background: var(--primary-color);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--gray-600);
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
    color: var(--gray-400);
}

.empty-state h3 {
    margin-bottom: 0.75rem;
    color: var(--primary-color);
    font-weight: 600;
}

.btn-primary-custom {
    background: linear-gradient(135deg, var(--secondary-color), #74b9ff);
    color: var(--white);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-primary-custom:hover {
    background: linear-gradient(135deg, #2980b9, var(--secondary-color));
    color: var(--white);
    text-decoration: none;
    transform: translateY(-1px);
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 1000;
    backdrop-filter: blur(4px);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: var(--white);
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
}

.modal-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--white);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.2s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 2rem;
}

.detail-section {
    margin-bottom: 1.5rem;
}

.detail-section:last-child {
    margin-bottom: 0;
}

.detail-title {
    font-weight: 600;
    color: var(--primary-color);
    margin-bottom: 1rem;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--gray-100);
    border-radius: var(--border-radius);
    margin-bottom: 0.5rem;
    border: 1px solid var(--gray-300);
}

.detail-label {
    font-weight: 500;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-value {
    font-weight: 600;
    color: var(--primary-color);
}

.status-detail {
    padding: 1rem;
    border-radius: var(--border-radius);
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.bukti-section {
    text-align: center;
}

.bukti-image {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-sm);
    margin-top: 1rem;
    max-height: 300px;
    object-fit: contain;
}

.no-bukti {
    padding: 1.5rem;
    background: var(--gray-100);
    border-radius: var(--border-radius);
    color: var(--gray-600);
    text-align: center;
    font-style: italic;
    border: 1px solid var(--gray-300);
}

.modal-footer {
    padding: 1rem 2rem;
    border-top: 1px solid var(--gray-200);
    text-align: right;
    background: var(--gray-100);
}

.btn-close-modal {
    background: var(--accent-color);
    color: var(--white);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius);
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-close-modal:hover {
    background: #c0392b;
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .container-custom {
        padding: 0 0.75rem;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    
    .page-title {
        font-size: 1.6rem;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 0.5rem;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-header {
        padding: 1rem 1.5rem;
    }
    
    .modal-footer {
        padding: 1rem 1.5rem;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .order-right {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 576px) {
    .stats-row {
        grid-template-columns: 1fr;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .order-item {
        padding: 1rem;
    }
    
    .orders-header {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .orders-list {
        max-height: 500px;
    }
}

/* Scrollbar untuk orders list */
.orders-list::-webkit-scrollbar {
    width: 6px;
}

.orders-list::-webkit-scrollbar-track {
    background: var(--gray-200);
}

.orders-list::-webkit-scrollbar-thumb {
    background: var(--gray-400);
    border-radius: 3px;
}

.orders-list::-webkit-scrollbar-thumb:hover {
    background: var(--gray-500);
}
</style>
</head>
<body>

<!-- Main Content -->
<div class="container-custom">
    <!-- Statistics -->
    <div class="stats-row">
         <div class="stat-card stat-diproses">
            <div class="stat-number"><?= $stats['pending'] ?></div>
            <div class="stat-label">Dipending</div>
        </div>
        <div class="stat-card stat-diproses">
            <div class="stat-number"><?= $stats['diproses'] ?></div>
            <div class="stat-label">Diproses</div>
        </div>
        <div class="stat-card stat-diterima">
            <div class="stat-number"><?= $stats['diterima'] ?></div>
            <div class="stat-label">Diterima</div>
        </div>
        <div class="stat-card stat-ditolak">
            <div class="stat-number"><?= $stats['ditolak'] ?></div>
            <div class="stat-label">Ditolak</div>
        </div>
        <div class="stat-card stat-selesai">
            <div class="stat-number"><?= $stats['selesai'] ?></div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>

    <!-- Orders List -->
    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="orders-container">
            <div class="orders-header">
                <i class="fas fa-list"></i>
                Daftar Pesanan (<?= mysqli_num_rows($result) ?> pesanan)
            </div>
            <div class="orders-list">
               <?php while($row = mysqli_fetch_assoc($result)): ?>
    <?php
    // Ambil detail pesanan untuk pesanan ini
    $detail_query = "
        SELECT d.jumlah, m.nama AS nama_makanan, n.nama AS nama_minuman
        FROM pesanan_detail d
        LEFT JOIN makanan m ON d.id_makanan = m.id
        LEFT JOIN minuman n ON d.id_minuman = n.id
        WHERE d.id_pesanan = {$row['id']}
    ";
    $detail_result = mysqli_query($koneksi, $detail_query);

    $items = [];
    while($d = mysqli_fetch_assoc($detail_result)) {
        if ($d['nama_makanan']) {
            $items[] = $d['nama_makanan'] . " ({$d['jumlah']}x)";
        }
        if ($d['nama_minuman']) {
            $items[] = $d['nama_minuman'] . " ({$d['jumlah']}x)";
        }
    }
    ?>
    <div class="order-item" onclick='showOrderDetail(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($items, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
        <div class="order-left">
            <div class="order-id">Pesanan #<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></div>
            <div class="order-items">
                <?= htmlspecialchars(implode(" + ", $items)) ?>
            </div>
            <div class="order-date"><?= date('d M Y, H:i', strtotime($row['created_at'] ?? 'now')) ?></div>
        </div>
        
        <div class="order-center">
            <div class="order-price">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></div>
        </div>

        <div class="order-right">
            <div class="status-badge status-<?= strtolower($row['status']) ?>">
                <?php
                $status_icons = [
                    'pending' => 'fa-clock',
                    'diproses' => 'fa-clock',
                    'diterima' => 'fa-check-circle',
                    'ditolak' => 'fa-times-circle',
                    'selesai' => 'fa-flag-checkered'
                ];
                $icon = $status_icons[strtolower($row['status'])] ?? 'fa-question-circle';
                ?>
                <i class="fas <?= $icon ?>"></i>
                <?= ucfirst($row['status']) ?>
            </div>
            <button class="view-detail-btn" onclick="event.stopPropagation(); showOrderDetail(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($items, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>
<?php endwhile; ?>

            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Belum Ada Pesanan</h3>
            <p>Anda belum memiliki pesanan saat ini.</p>
            <a href="index.php?page=home" class="btn-primary-custom">
                <i class="fas fa-utensils"></i>
                Lihat Menu
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Detail Pesanan</h3>
            <button class="btn-close-modal" id="btnCloseModal">
    <i class="fas fa-times"></i>
    Tutup
</button>

        </div>
        <div class="modal-body" id="modalBody">
            <!-- Content will be filled by JavaScript -->
        </div>
    </div>
</div>

<script>
    document.getElementById('btnCloseModal').addEventListener('click', closeModal);
document.getElementById('btnCloseFooter').addEventListener('click', closeModal);

function showOrderDetail(order, items) {
    const modal = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = `Detail Pesanan #${String(order.id).padStart(4, '0')}`;

    // Buat list item pesanan
    let itemsHtml = "";
    items.forEach(item => {
        itemsHtml += `
            <div class="detail-item">
                <span class="detail-label">
                    <i class="fas fa-utensils"></i> Item
                </span>
                <span class="detail-value">${item}</span>
            </div>
        `;
    });
    
    modalBody.innerHTML = `
        <div class="detail-section">
            <div class="detail-title">
                <i class="fas fa-info-circle"></i>
                Informasi Pesanan
            </div>
            <div class="detail-item">
                <span class="detail-label">ID Pesanan</span>
                <span class="detail-value">#${String(order.id).padStart(4, '0')}</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Pesan</span>
                <span class="detail-value">${new Date(order.created_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}</span>
            </div>
        </div>
        
        <div class="detail-section">
            <div class="detail-title">
                <i class="fas fa-utensils"></i>
                Item Pesanan
            </div>
            ${itemsHtml}
            <div class="detail-item">
                <span class="detail-label">Total Harga</span>
                <span class="detail-value" style="color: var(--success-color); font-size: 1.1rem;">
                    Rp ${Number(order.total_harga).toLocaleString('id-ID')}
                </span>
            </div>
        </div>
        
        <div class="detail-section">
            <div class="detail-title">
                <i class="fas fa-flag"></i>
                Status Pesanan
            </div>
            <div class="status-detail" style="background: var(--warning-color); color: var(--white);">
                ${order.status.toUpperCase()}
            </div>
        </div>
        <div class="detail-section">
            <div class="detail-title">
                <i class="fas fa-image"></i>
                Bukti Pembayaran
            </div>
           ${order.bukti_pembayaran ? `
    <div class="bukti-section">
        <img src="${order.bukti_pembayaran}" alt="Bukti Pembayaran" class="bukti-image">
    </div>
` : ''}

        </div>
    `;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}


function closeModal() {
    const modal = document.getElementById('modalOverlay');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Event listeners untuk menutup modal
document.addEventListener('DOMContentLoaded', function() {
    // Tombol X di header
    document.getElementById('btnCloseModal').addEventListener('click', closeModal);
    
    // Tombol Tutup di footer
    document.getElementById('btnCloseFooter').addEventListener('click', closeModal);
    
    // Klik di overlay (di luar modal content)
    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});
</script>

</body>
</html>