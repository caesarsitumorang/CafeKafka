<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Cek login
$id_user = $_SESSION['id_user'] ?? null;
$username = $_SESSION['username'] ?? null;

if (!$id_user || !$username) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

// Ambil data user (role + id_pelanggan yang sesuai)
$qUser = $koneksi->prepare("
    SELECT u.id_user, u.username, u.role, p.id_pelanggan, p.nama_lengkap
    FROM users u
    INNER JOIN pelanggan p ON p.username = u.username
    WHERE u.id_user = ?
");
$qUser->bind_param("i", $id_user);
$qUser->execute();
$resUser = $qUser->get_result()->fetch_assoc();

if (!$resUser || $resUser['role'] !== 'pelanggan') {
    echo "<p>Akses ditolak. Hanya pelanggan yang bisa melihat riwayat pesanan.</p>";
    exit;
}

$id_pelanggan = $resUser['id_pelanggan'];

// Ambil pesanan dengan status selesai berdasarkan id_pelanggan
$qPesanan = $koneksi->prepare("
    SELECT id, created_at, total_harga, status, bukti_pembayaran 
    FROM pesanan 
    WHERE id_pelanggan = ? AND status = 'selesai' 
    ORDER BY created_at DESC
");
$qPesanan->bind_param("i", $id_pelanggan);
$qPesanan->execute();
$resPesanan = $qPesanan->get_result();

// Function untuk ambil detail pesanan
function getDetailPesanan($koneksi, $id_pesanan) {
    $qDetail = $koneksi->prepare("
        SELECT 
            pd.jumlah,
            m.nama AS nama_makanan, m.harga AS harga_makanan, m.gambar AS gambar_makanan,
            n.nama AS nama_minuman, n.harga AS harga_minuman, n.gambar AS gambar_minuman
        FROM pesanan_detail pd
        LEFT JOIN makanan m ON pd.id_makanan = m.id
        LEFT JOIN minuman n ON pd.id_minuman = n.id
        WHERE pd.id_pesanan = ?
    ");
    $qDetail->bind_param("i", $id_pesanan);
    $qDetail->execute();
    return $qDetail->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --light-orange: #fff4f0;
            --dark-orange: #e55a2b;
            --orange-gradient: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            --success-green: #28a745;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
            --text-dark: #2d3436;
            --text-muted: #6c757d;
            --shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-dark);
            line-height: 1.5;
            font-size: 14px;
        }

        .history-container {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .history-header {
            background: var(--light-gray);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .history-header h4 {
            margin: 0;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .order-list {
            max-height: 70vh;
            overflow-y: auto;
        }

        .order-list::-webkit-scrollbar {
            width: 6px;
        }

        .order-list::-webkit-scrollbar-track {
            background: var(--light-gray);
        }

        .order-list::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 3px;
        }

        .order-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .order-item:hover {
            background: var(--light-orange);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .order-id {
            min-width: 80px;
            font-weight: 600;
            color: var(--primary-orange);
            font-size: 0.9rem;
        }

        .order-date {
            min-width: 140px;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .order-total {
            min-width: 120px;
            font-weight: 700;
            color: var(--success-green);
            font-size: 0.95rem;
        }

        .order-status {
            min-width: 80px;
            text-align: center;
        }

        .status-badge {
            background: var(--success-green);
            color: white;
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .order-actions {
            margin-left: auto;
            display: flex;
            gap: 0.5rem;
        }

        .btn-compact {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary-compact {
            background: var(--primary-orange);
            color: white;
        }

        .btn-primary-compact:hover {
            background: var(--dark-orange);
            color: white;
        }

        .btn-secondary-compact {
            background: var(--light-gray);
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary-compact:hover {
            background: var(--border-color);
            color: var(--text-dark);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }

        .empty-state h5 {
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* Modal Compact Styles */
        .modal-content {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow);
        }

        .modal-header {
            background: var(--orange-gradient);
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-title {
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
        }

        .btn-close-white {
            filter: invert(1);
        }

        .modal-body {
            padding: 0;
            max-height: 70vh;
            overflow-y: auto;
        }

        .detail-section {
            padding: 1rem 1.5rem;
        }

        .detail-header {
            background: var(--light-gray);
            padding: 0.8rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--border-color);
            gap: 0.8rem;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .item-details {
            flex: 1;
            min-width: 0;
        }

        .item-name {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.2rem;
            font-size: 0.9rem;
            line-height: 1.3;
        }

        .item-price {
            color: var(--primary-orange);
            font-weight: 500;
            font-size: 0.85rem;
        }

        .item-type {
            background: var(--light-orange);
            color: var(--primary-orange);
            padding: 0.1rem 0.4rem;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 500;
            display: inline-block;
            margin-top: 0.2rem;
        }

        .item-quantity {
            text-align: center;
            min-width: 40px;
        }

        .quantity-badge {
            background: var(--primary-orange);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
            margin: 0 auto;
        }

        .quantity-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .item-subtotal {
            text-align: right;
            min-width: 80px;
        }

        .subtotal-amount {
            font-weight: 600;
            color: var(--success-green);
            font-size: 0.9rem;
        }

        .total-section {
            background: var(--light-gray);
            padding: 1rem 1.5rem;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .total-row:last-child {
            margin-bottom: 0;
            padding-top: 0.4rem;
            border-top: 1px solid var(--border-color);
            font-size: 1rem;
            font-weight: 700;
        }

        .total-final {
            color: var(--primary-orange);
            font-size: 1.1rem;
        }

        .payment-proof {
            padding: 1rem 1.5rem;
            background: var(--light-orange);
            border-top: 1px solid var(--border-color);
        }

        .payment-proof h6 {
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .proof-image {
            max-width: 150px;
            max-height: 150px;
            border-radius: 6px;
            border: 2px solid var(--primary-orange);
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .proof-image:hover {
            transform: scale(1.02);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .order-row {
                flex-direction: column;
                align-items: stretch;
                gap: 0.5rem;
            }

            .order-actions {
                margin-left: 0;
                justify-content: center;
            }

            .detail-item {
                flex-direction: column;
                text-align: center;
                gap: 0.8rem;
            }

            .item-image {
                align-self: center;
            }
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .table th {
            background: var(--light-gray);
            border: none;
            font-weight: 600;
            color: var(--text-dark);
            padding: 0.8rem;
            font-size: 0.85rem;
        }

        .table td {
            padding: 0.8rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }

        .table tbody tr:hover {
            background: var(--light-orange);
        }
    </style>
</head>
<body>

<div class="container-fluid" style="padding: 0; margin: 0;">
    <div class="history-container" style="margin: 0; border-radius: 0;">
        <div class="history-header">
            <h4><i class="fas fa-check-circle me-2"></i>Pesanan Selesai (<?= $resPesanan->num_rows ?>)</h4>
        </div>

        <?php if ($resPesanan->num_rows > 0): ?>
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th width="20%">Tanggal</th>
                                <th width="20%">Total</th>
                                <th width="15%">Status</th>
                                <th width="35%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $resPesanan->fetch_assoc()): ?>
                                <tr>
                                    <td class="order-id">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                    <td class="order-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= date("d/m/Y H:i", strtotime($row['created_at'])) ?>
                                    </td>
                                    <td class="order-total">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="status-badge">
                                            <i class="fas fa-check me-1"></i>Selesai
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" 
                                                    class="btn btn-primary-compact btn-compact"
                                                    onclick="showDetailModal(<?= $row['id'] ?>)"
                                                    title="Lihat Detail">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-secondary-compact btn-compact"
                                                    onclick="downloadReceipt(<?= $row['id'] ?>)"
                                                    title="Unduh Struk">
                                                <i class="fas fa-download me-1"></i>Struk
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile View -->
            <div class="d-block d-md-none">
                <div class="order-list">
                    <?php 
                    mysqli_data_seek($resPesanan, 0);
                    while ($row = $resPesanan->fetch_assoc()): 
                    ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="order-id mb-1">#<?= str_pad($row['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                    <div class="order-date">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <?= date("d/m/Y H:i", strtotime($row['created_at'])) ?>
                                    </div>
                                </div>
                                <span class="status-badge">
                                    <i class="fas fa-check me-1"></i>Selesai
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="order-total">Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></div>
                                <div class="d-flex gap-2">
                                    <button type="button" 
                                            class="btn btn-primary-compact btn-compact"
                                            onclick="showDetailModal(<?= $row['id'] ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-secondary-compact btn-compact"
                                            onclick="downloadReceipt(<?= $row['id'] ?>)">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h5>Belum Ada Pesanan Selesai</h5>
                <p>Anda belum memiliki pesanan yang telah selesai</p>
                <a href="index.php" class="btn btn-primary-compact btn-compact mt-3">
                    <i class="fas fa-utensils me-2"></i>Mulai Memesan
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt me-2"></i>Detail Pesanan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="previewImage" src="" alt="Bukti Pembayaran" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Generate order details for JavaScript
<?php 
mysqli_data_seek($resPesanan, 0);
$orderDetails = [];
while ($row = $resPesanan->fetch_assoc()) {
    $details = getDetailPesanan($koneksi, $row['id']);
    $orderDetails[$row['id']] = [
        'order' => $row,
        'details' => $details
    ];
}
?>

const orderDetails = <?= json_encode($orderDetails) ?>;

function showDetailModal(idPesanan) {
    const data = orderDetails[idPesanan];
    if (!data) {
        alert('Data pesanan tidak ditemukan');
        return;
    }
    
    const order = data.order;
    const details = data.details;
    
    let detailHtml = `
        <div class="detail-header">
            <i class="fas fa-info-circle me-2"></i>Informasi Pesanan
        </div>
        <div class="detail-section">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">ID Pesanan:</small><br>
                    <strong>#${String(order.id).padStart(4, '0')}</strong>
                </div>
                <div class="col-6">
                    <small class="text-muted">Tanggal:</small><br>
                    <strong>${new Date(order.created_at).toLocaleDateString('id-ID', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    })}</strong>
                </div>
            </div>
        </div>
        
        <div class="detail-header">
            <i class="fas fa-shopping-cart me-2"></i>Detail Items
        </div>
    `;
    
    let totalAmount = 0;
    
    details.forEach(item => {
        const name = item.nama_makanan || item.nama_minuman;
        const price = parseInt(item.harga_makanan || item.harga_minuman);
        const image = item.gambar_makanan || item.gambar_minuman;
        const type = item.nama_makanan ? 'Makanan' : 'Minuman';
        const subtotal = price * item.jumlah;
        totalAmount += subtotal;
        
        detailHtml += `
            <div class="detail-item">
                <img src="upload/${image || 'assets/img/no-image.png'}" alt="${name}" class="item-image">
                <div class="item-details">
                    <div class="item-name">${name}</div>
                    <div class="item-price">Rp ${price.toLocaleString('id-ID')}</div>
                    <span class="item-type">${type}</span>
                </div>
                <div class="item-quantity">
                    <div class="quantity-badge">${item.jumlah}</div>
                    <div class="quantity-label">Jumlah</div>
                </div>
                <div class="item-subtotal">
                    <div class="subtotal-amount">Rp ${subtotal.toLocaleString('id-ID')}</div>
                </div>
            </div>
        `;
    });
    
    detailHtml += `
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>Rp ${totalAmount.toLocaleString('id-ID')}</span>
            </div>
            <div class="total-row">
                <span><strong>Total Pembayaran:</strong></span>
                <span class="total-final">Rp ${parseInt(order.total_harga).toLocaleString('id-ID')}</span>
            </div>
        </div>
    `;
    
    if (order.bukti_pembayaran) {
        detailHtml += `
            <div class="payment-proof">
                <h6><i class="fas fa-receipt me-2"></i>Bukti Pembayaran</h6>
                <img src="${order.bukti_pembayaran}" 
                     alt="Bukti Pembayaran" 
                     class="proof-image"
                     onclick="showImagePreview('${order.bukti_pembayaran}')">
                <div class="mt-2">
                    <small class="text-muted">Klik untuk memperbesar</small>
                </div>
            </div>
        `;
    }
    
    document.getElementById('modalBody').innerHTML = detailHtml;
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
}

function downloadReceipt(idPesanan) {
    window.open(`download/receipt.php?id=${idPesanan}`, '_blank');
}

function showImagePreview(imageSrc) {
    document.getElementById('previewImage').src = imageSrc;
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
}
</script>

</body>
</html>