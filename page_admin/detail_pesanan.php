<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
html, body {
    font-family: 'Poppins', sans-serif;
    background: #414177ff;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    color: #2d3748;
}

.detail-container {
    max-width: 900px;
    margin: auto;
    padding: 30px 20px;
    color: white;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    padding: 25px 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.page-header h3 {
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
}

.order-id {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    color: white;
}

/* Info Cards */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.info-card .label {
    font-size: 0.85rem;
    color: #718096;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card .value {
    font-size: 1.1rem;
    color: #2d3748;
    font-weight: 600;
    line-height: 1.4;
}

/* Status Badge */
.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.status-pending {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
}

.status-diterima {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.status-diproses {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
}

.status-selesai {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

/* Payment Proof Image */
.payment-image {
    max-width: 200px;
    max-height: 150px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    object-fit: cover;
    border: 2px solid rgba(139, 92, 246, 0.2);
}

.payment-image:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.3);
    border-color: rgba(139, 92, 246, 0.5);
}

/* Image Overlay */
.image-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    cursor: pointer;
}

.image-overlay.show {
    display: flex;
}

.overlay-image {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.close-overlay {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 2rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
}

.close-overlay:hover {
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

/* Items Section */
.items-section {
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    margin-bottom: 30px;
}

.items-header {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    padding: 25px 30px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
}

.items-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
}

.items-table th {
    background: #f7fafc;
    color: #2d3748;
    padding: 18px 30px;
    text-align: left;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.items-table td {
    padding: 18px 30px;
    border-bottom: 1px solid #e2e8f0;
    color: #4a5568;
    font-size: 1rem;
}

.items-table tbody tr {
    transition: background-color 0.2s ease;
}

.items-table tbody tr:hover {
    background: rgba(99, 102, 241, 0.05);
}

.items-table tbody tr:nth-child(even) {
    background: #f9fafb;
}

.items-table tfoot tr {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
}

.items-table tfoot td {
    padding: 20px 30px;
    border: none;
}

.quantity-badge {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 16px;
    font-weight: 600;
    font-size: 0.9rem;
    text-align: center;
    min-width: 40px;
    display: inline-block;
}

.total-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #10b981;
}

/* Status Update Section */
.status-section {
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    margin-bottom: 30px;
}

.status-section h4 {
    margin: 0 0 20px 0;
    color: #2d3748;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-form {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.status-form label {
    font-weight: 600;
    color: #4a5568;
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-select {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #2d3748;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    transition: all 0.3s ease;
    min-width: 140px;
}

.status-select:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.save-btn {
    padding: 12px 20px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.save-btn:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

/* Back Button */
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.back-btn:hover {
    background: linear-gradient(135deg, #ea580c 0%, #dc2626 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .detail-container {
        padding: 20px 15px;
    }
    
    .page-header {
        padding: 20px;
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .page-header h3 {
        font-size: 1.5rem;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .info-card {
        padding: 20px;
    }
    
    .payment-image {
        max-width: 150px;
        max-height: 100px;
    }
    
    .items-header,
    .items-table th,
    .items-table td,
    .items-table tfoot td {
        padding: 15px 20px;
    }
    
    .status-section {
        padding: 20px;
    }
    
    .status-form {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }
    
    .status-select {
        width: 100%;
    }
    
    .close-overlay {
        top: 10px;
        right: 15px;
        font-size: 1.5rem;
        width: 35px;
        height: 35px;
    }
}

@media (max-width: 480px) {
    .items-table th,
    .items-table td {
        padding: 12px 15px;
        font-size: 0.9rem;
    }
    
    .page-header h3 {
        font-size: 1.3rem;
    }
    
    .payment-image {
        max-width: 120px;
        max-height: 80px;
    }
}
</style>

<?php
include "config/koneksi.php";

$id = $_GET['id'] ?? 0;

$queryPesanan = "
    SELECT p.id, p.id_pelanggan, pl.nama_lengkap, p.total_harga, p.status, 
           p.bukti_pembayaran, p.catatan, p.created_at
    FROM pesanan p
    LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
    WHERE p.id = ?
";
$stmt = $koneksi->prepare($queryPesanan);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if(!$data){
    echo "Pesanan tidak ditemukan.";
    exit;
}

$queryDetail = "
    SELECT 
        COALESCE(m.nama, mn.nama) AS nama_item,
        pd.jumlah
    FROM pesanan_detail pd
    LEFT JOIN makanan m ON pd.id_makanan = m.id
    LEFT JOIN minuman mn ON pd.id_minuman = mn.id
    WHERE pd.id_pesanan = ?
";
$stmtDetail = $koneksi->prepare($queryDetail);
$stmtDetail->bind_param("i", $id);
$stmtDetail->execute();
$resDetail = $stmtDetail->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $update = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $update->bind_param("si", $newStatus, $id);
    if ($update->execute()) {
        echo "<script>alert('Status pesanan berhasil diperbarui'); window.location='index_admin.php?page_admin=home_admin&id=$id';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui status');</script>";
    }
}
?>

<div class="detail-container">
    <!-- Page Header -->
    <div class="page-header">
        <h3>
            <i class="fas fa-clipboard-check"></i>
            Detail Pesanan
        </h3>
        <div class="order-id">
            <i class="fas fa-hashtag"></i>
            Order #<?= htmlspecialchars($data['id']) ?>
        </div>
    </div>

    <!-- Info Cards Grid -->
    <div class="info-grid">
        <div class="info-card">
            <div class="label">
                <i class="fas fa-user"></i>
                Nama Pelanggan
            </div>
            <div class="value"><?= htmlspecialchars($data['nama_lengkap']) ?></div>
        </div>

        <div class="info-card">
            <div class="label">
                <i class="fas fa-info-circle"></i>
                Status Pesanan
            </div>
            <div class="value">
                <span class="status-badge status-<?= $data['status'] ?>">
                    <?= ucfirst($data['status']) ?>
                </span>
            </div>
        </div>

        <div class="info-card">
            <div class="label">
                <i class="fas fa-calendar-alt"></i>
                Tanggal Pesan
            </div>
            <div class="value"><?= date("d-m-Y H:i", strtotime($data['created_at'])) ?></div>
        </div>

        <div class="info-card">
        <div class="label">
            <i class="fas fa-receipt"></i>
            Bukti Pembayaran
        </div>
        <div class="value">
            <?php if (!empty($data['bukti_pembayaran'])): ?>
                <?php 
                    $file = htmlspecialchars($data['bukti_pembayaran']); 
                    $file = str_replace("upload/", "", $file);

                    $path = "../upload/" . $file; 
                    $fullPath = __DIR__ . "/../upload/" . $file;
                ?>
                <?php if (file_exists($fullPath)): ?>
                    <img src="<?= $path ?>" 
                        alt="Bukti Pembayaran" 
                        class="payment-image"
                        onclick="openImageOverlay(this.src)">
                <?php else: ?>
                    <span style="color: red; font-style: italic;">File tidak ditemukan (<?= $path ?>)</span>
                <?php endif; ?>
            <?php else: ?>
                <span style="color: #a0aec0; font-style: italic;">-</span>
            <?php endif; ?>
        </div>
</div>


        <?php if($data['catatan']): ?>
        <div class="info-card" style="grid-column: 1 / -1;">
            <div class="label">
                <i class="fas fa-sticky-note"></i>
                Catatan
            </div>
            <div class="value"><?= htmlspecialchars($data['catatan']) ?></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Items Section -->
    <div class="items-section">
        <div class="items-header">
            <i class="fas fa-utensils"></i>
            <h4>Item Pesanan</h4>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th><i class="fas fa-list"></i> Nama Item</th>
                    <th style="text-align: center;"><i class="fas fa-sort-numeric-up"></i> Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($d = $resDetail->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= htmlspecialchars($d['nama_item']) ?></td>
                    <td style="text-align: center;">
                        <span class="quantity-badge"><?= $d['jumlah'] ?></span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: right;">
                        <strong>Total Harga</strong>
                    </td>
                    <td style="text-align: center;">
                        <span class="total-price">
                            Rp <?= number_format($data['total_harga'],0,',','.') ?>
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Status Update Section -->
    <div class="status-section">
        <h4>
            <i class="fas fa-edit"></i>
            Ubah Status Pesanan
        </h4>
        
        <form method="POST" class="status-form">
            <label for="status">
                <i class="fas fa-tags"></i>
                Ubah Status:
            </label>
            <select name="status" id="status" class="status-select">
                <option value="diterima" <?= $data['status']=='diterima'?'selected':'' ?>>Diterima</option>
                <option value="diproses" <?= $data['status']=='diproses'?'selected':'' ?>>Diproses</option>
                <option value="selesai" <?= $data['status']=='selesai'?'selected':'' ?>>Selesai</option>
            </select>
            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i>
                Simpan
            </button>
        </form>
    </div>

    <!-- Back Button -->
    <a href="index_admin.php?page_admin=home_admin" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
</div>

<!-- Image Overlay -->
<div class="image-overlay" id="imageOverlay" onclick="closeImageOverlay()">
    <span class="close-overlay">&times;</span>
    <img class="overlay-image" id="overlayImage" src="" alt="Bukti Pembayaran">
</div>

<script>
// Add smooth animations when page loads
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.info-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Enhanced hover effects
document.querySelectorAll('.info-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-8px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Image overlay functions
function openImageOverlay(imageSrc) {
    const overlay = document.getElementById('imageOverlay');
    const overlayImage = document.getElementById('overlayImage');
    overlayImage.src = imageSrc;
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeImageOverlay() {
    const overlay = document.getElementById('imageOverlay');
    overlay.classList.remove('show');
    document.body.style.overflow = 'auto';
}

// Close overlay with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageOverlay();
    }
});

// Prevent closing when clicking on the image itself
document.getElementById('overlayImage').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>