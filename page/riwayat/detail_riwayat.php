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
    JOIN pelanggan p ON u.id_user = p.id_pelanggan AND u.username = p.username
    WHERE u.id_user = ?
");
$qUser->bind_param("i", $id_user);
$qUser->execute();
$resUser = $qUser->get_result()->fetch_assoc();

if (!$resUser || $resUser['role'] !== 'pelanggan') {
    echo "<p>Akses ditolak. Hanya pelanggan yang bisa melihat detail pesanan.</p>";
    exit;
}

$id_pelanggan = $resUser['id_pelanggan'];

// Ambil id pesanan dari URL
$id_pesanan = intval($_GET['id'] ?? 0);
if ($id_pesanan <= 0) {
    echo "<p>Pesanan tidak ditemukan.</p>";
    exit;
}

// Ambil data pesanan
$qPesanan = $koneksi->prepare("
    SELECT id, created_at, total_harga, status 
    FROM pesanan 
    WHERE id = ? AND id_pelanggan = ?
");
$qPesanan->bind_param("ii", $id_pesanan, $id_pelanggan);
$qPesanan->execute();
$pesanan = $qPesanan->get_result()->fetch_assoc();

if (!$pesanan) {
    echo "<p>Pesanan tidak ditemukan atau bukan milik Anda.</p>";
    exit;
}

// Ambil detail pesanan
$qDetail = $koneksi->prepare("
    SELECT dp.jumlah, dp.harga, pr.nama_produk 
    FROM detail_pesanan dp
    JOIN produk pr ON dp.id_produk = pr.id_produk
    WHERE dp.id_pesanan = ?
");
$qDetail->bind_param("i", $id_pesanan);
$qDetail->execute();
$resDetail = $qDetail->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="mb-3">Detail Pesanan #<?= $pesanan['id']; ?></h2>
            <p><strong>Tanggal:</strong> <?= date("d-m-Y", strtotime($pesanan['created_at'])); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge bg-<?= $pesanan['status']=='selesai'?'success':'warning'; ?>">
                    <?= ucfirst($pesanan['status']); ?>
                </span>
            </p>

            <h4 class="mt-4">Daftar Produk</h4>
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                <?php $total=0; while ($d = $resDetail->fetch_assoc()): 
                    $subtotal = $d['jumlah'] * $d['harga'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= $d['nama_produk']; ?></td>
                        <td><?= $d['jumlah']; ?></td>
                        <td>Rp <?= number_format($d['harga'],0,',','.'); ?></td>
                        <td>Rp <?= number_format($subtotal,0,',','.'); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

            <h5 class="text-end mt-3">
                Total: <strong>Rp <?= number_format($pesanan['total_harga'],0,',','.'); ?></strong>
            </h5>

            <a href="index.php?page=riwayat/riwayat_pesanan" class="btn btn-secondary mt-3">Kembali</a>
        </div>
    </div>
</body>
</html>
