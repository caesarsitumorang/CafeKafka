<?php
$host = 'localhost';
$dbname = 'cafe_kafka';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Ambil informasi rekening dari tabel data_cafe
$query = $pdo->query("SELECT * FROM data_cafe LIMIT 1");
$data_cafe = $query->fetch(PDO::FETCH_ASSOC);

// Proses submit pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $nama_pemesan = $_POST['nama_pemesan'];
    $nomor_meja = $_POST['nomor_meja'];
    $items = json_decode($_POST['items_data'], true);
    $total_harga = isset($_POST['total_harga']) ? (float)$_POST['total_harga'] : 0;

    // Upload bukti pembayaran
    $bukti_pembayaran = '';
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === 0) {
        $target_dir = "upload/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $bukti_pembayaran = 'bukti_' . time() . '.' . $file_extension;
        move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_dir . $bukti_pembayaran);
    }

    // Hitung nama makanan dan minuman
    $nama_makanan = [];
    $nama_minuman = [];
    foreach ($items as $item) {
        if ($item['type'] === 'makanan') {
            $nama_makanan[] = $item['nama'] . ' (' . $item['qty'] . ')';
        } else {
            $nama_minuman[] = $item['nama'] . ' (' . $item['qty'] . ')';
        }
    }

    // Insert ke tabel pesanan_langsung (status default 'pending')
    $stmt = $pdo->prepare("INSERT INTO pesanan_langsung 
        (nama_makanan, nama_minuman, nama_pemesan, bukti_pembayaran, total_harga, status, nomor_meja) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        implode(', ', $nama_makanan),
        implode(', ', $nama_minuman),
        $nama_pemesan,
        $bukti_pembayaran,
        $total_harga,
        'pending',
        $nomor_meja
    ]);

    $id_pesanan_langsung = $pdo->lastInsertId();

    // Insert ke tabel detail_pesanan_langsung
    foreach ($items as $item) {
        $id_makanan = $item['type'] === 'makanan' ? $item['id'] : null;
        $id_minuman = $item['type'] === 'minuman' ? $item['id'] : null;

        $stmt = $pdo->prepare("INSERT INTO detail_pesanan_langsung 
            (id_pesanan_langsung, id_makanan, id_minuman, jumlah) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_pesanan_langsung, $id_makanan, $id_minuman, $item['qty']]);
    }

    echo "<script>alert('Pesanan berhasil dikirim! Status: Pending.'); window.location.href='index.html';</script>";
    exit;
}

// Ambil data item dari URL
$items = [];
if (isset($_GET['items'])) {
    $items = json_decode(urldecode($_GET['items']), true);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Langsung - Cafe Kafka</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 850px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 20px; color: #333; text-align: center; }
        .info-rekening { background: #fff6e5; border: 1px solid #ffd580; padding: 15px; border-radius: 6px; margin-bottom: 25px; }
        .info-rekening h3 { margin-bottom: 10px; color: #ff6b35; }
        .info-rekening p { margin-bottom: 6px; }
        .cart-items { margin-bottom: 30px; }
        .cart-item { display: flex; gap: 15px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 10px; }
        .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 5px; }
        .cart-item-info { flex: 1; }
        .qty-controls { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .qty-btn { width: 30px; height: 30px; border: 1px solid #ddd; background: white; cursor: pointer; }
        .qty-btn:hover { background: #f0f0f0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .total { font-size: 1.2em; font-weight: bold; margin: 20px 0; text-align: right; }
        .btn { padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn-primary { background: #ff6b35; color: white; }
        .btn-secondary { background: #ddd; color: #333; margin-right: 10px; }
        .actions { text-align: right; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-shopping-cart"></i> Pesan Langsung</h1>

        <?php if ($data_cafe): ?>
        <div class="info-rekening">
            <h3><i class="fas fa-money-check-alt"></i> Informasi Pembayaran</h3>
            <p><strong>Nama Warung:</strong> <?= htmlspecialchars($data_cafe['nama_warung']) ?></p>
            <p><strong>Bank:</strong> <?= htmlspecialchars($data_cafe['bank']) ?></p>
            <p><strong>No. Rekening:</strong> <?= htmlspecialchars($data_cafe['no_rek']) ?></p>
        </div>
        <?php endif; ?>

        <div class="cart-items" id="cartItems"></div>
        <div class="total">
            Total: <span id="totalPrice">Rp 0</span>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="orderForm">
            <div class="form-group">
                <label>Nama Pemesan *</label>
                <input type="text" name="nama_pemesan" required>
            </div>

            <div class="form-group">
                <label>Nomor Meja *</label>
                <input type="text" name="nomor_meja" placeholder="Contoh: M3" required>
            </div>

            <div class="form-group">
                <label>Bukti Pembayaran *</label>
                <input type="file" name="bukti_pembayaran" accept="image/*" required>
            </div>
            
            <input type="hidden" name="items_data" id="itemsData">
            <input type="hidden" name="total_harga" id="totalHargaInput">
            
            <div class="actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <button type="submit" name="submit_order" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Kirim Pesanan
                </button>
            </div>
        </form>
    </div>

    <script>
        let cartItems = [];

        function loadCart() {
            const stored = localStorage.getItem('selectedItems');
            if (stored) cartItems = JSON.parse(stored);
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartItems');
            if (cartItems.length === 0) {
                container.innerHTML = '<p>Keranjang kosong. <a href="index.html">Pilih menu</a></p>';
                return;
            }

            container.innerHTML = cartItems.map((item, index) => `
                <div class="cart-item">
                    ${item.gambar ? 
                        `<img src="upload/${item.gambar}" alt="${item.nama}">` : 
                        `<div style="width:80px;height:80px;background:#ddd;border-radius:5px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-${item.type === 'makanan' ? 'utensils' : 'coffee'}" style="font-size:2em;color:#999;"></i>
                        </div>`}
                    <div class="cart-item-info">
                        <h3>${item.nama}</h3>
                        <p>${item.harga_formatted}</p>
                        <div class="qty-controls">
                            <button type="button" class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                            <span>${item.qty}</span>
                            <button type="button" class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                            <button type="button" class="qty-btn" onclick="removeItem(${index})" style="margin-left:10px;color:red;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <p style="margin-top:5px;font-weight:bold;">Subtotal: Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</p>
                    </div>
                </div>
            `).join('');

            updateTotal();
            document.getElementById('itemsData').value = JSON.stringify(cartItems);
        }

        function updateQty(index, change) {
            cartItems[index].qty += change;
            if (cartItems[index].qty < 1) cartItems[index].qty = 1;
            localStorage.setItem('selectedItems', JSON.stringify(cartItems));
            renderCart();
        }

        function removeItem(index) {
            if (confirm('Hapus item ini?')) {
                cartItems.splice(index, 1);
                localStorage.setItem('selectedItems', JSON.stringify(cartItems));
                renderCart();
            }
        }

        function updateTotal() {
            const total = cartItems.reduce((sum, item) => sum + (item.harga * item.qty), 0);
            document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalHargaInput').value = total;
        }

        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (cartItems.length === 0) {
                e.preventDefault();
                alert('Keranjang kosong!');
                return false;
            }
        });

        loadCart();
    </script>
</body>
</html>
