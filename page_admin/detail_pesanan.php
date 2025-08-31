<?php
include "config/koneksi.php";

$id = $_GET['id'] ?? 0;

// Ambil info utama pesanan
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

// Ambil detail item pesanan (hanya nama + jumlah)
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

$bukti = $data['bukti_pembayaran'] && file_exists("upload/".$data['bukti_pembayaran']) 
    ? "<a href='upload/{$data['bukti_pembayaran']}' target='_blank'>Lihat</a>" 
    : '-';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $update = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
    $update->bind_param("si", $newStatus, $id);
    if ($update->execute()) {
        echo "<script>alert('Status pesanan berhasil diperbarui'); window.location='index_admin.php?page_admin=pesanan/detail_pesanan&id=$id';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal memperbarui status');</script>";
    }
}
?>
<div class="container" style="max-width:800px; margin:auto; padding:20px; font-family:Poppins,sans-serif;">
    <h3 style="margin-bottom:20px;">📋 Detail Pesanan</h3>
    
    <p><b>Nama Pelanggan:</b> <?= htmlspecialchars($data['nama_lengkap']) ?></p>
    <p><b>Status:</b> <span style="padding:4px 8px; border-radius:4px; background:#f97316; color:white;"><?= ucfirst($data['status']) ?></span></p>
    <p><b>Catatan:</b> <?= htmlspecialchars($data['catatan'] ?? '-') ?></p>
    <p><b>Tanggal Pesan:</b> <?= date("d-m-Y H:i", strtotime($data['created_at'])) ?></p>
    <p><b>Bukti Pembayaran:</b> <?= $bukti ?></p>

    <h4 style="margin-top:20px;">🍽️ Item Pesanan</h4>
    <table style="width:100%; border-collapse:collapse; margin-top:10px;">
        <thead>
            <tr style="background:#1f1f1f; color:#fff;">
                <th style="padding:10px; text-align:left;">Nama Item</th>
                <th style="padding:10px; text-align:center;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d = $resDetail->fetch_assoc()): ?>
            <tr style="background:#111; color:#fff; border-bottom:1px solid #333;">
                <td style="padding:10px;"><?= htmlspecialchars($d['nama_item']) ?></td>
                <td style="padding:10px; text-align:center;"><?= $d['jumlah'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="background:#222; color:#fff; font-weight:bold;">
                <td style="padding:10px; text-align:right;">Total Harga</td>
                <td style="padding:10px; text-align:center;">Rp <?= number_format($data['total_harga'],0,',','.') ?></td>
            </tr>
        </tfoot>
    </table>
    <!-- Form Update Status -->
    <form method="POST" style="margin-top:20px;">
        <label for="status" style="font-weight:bold;">Ubah Status Pesanan:</label>
        <select name="status" id="status" 
                style="margin-left:10px; padding:6px; border-radius:6px; border:1px solid #ccc;">
            <option value="diterima" <?= $data['status']=='diterima'?'selected':'' ?>>Diterima</option>
            <option value="diproses" <?= $data['status']=='diproses'?'selected':'' ?>>Diproses</option>
            <option value="selesai" <?= $data['status']=='selesai'?'selected':'' ?>>Selesai</option>
        </select>
        <button type="submit" 
                style="padding:6px 12px; margin-left:10px; background:#16a34a; color:white; border:none; border-radius:6px;">
            ✅ Simpan
        </button>
    </form>

    <a href="index_admin.php?page_admin=pesanan/data_pesanan" 
       style="display:inline-block; margin-top:20px; padding:8px 16px; background:#f97316; color:white; border-radius:6px; text-decoration:none;">
       ⬅ Kembali
    </a>
</div>
