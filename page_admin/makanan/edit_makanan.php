<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

// Ambil id makanan
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data makanan berdasarkan id
$query = $koneksi->prepare("SELECT * FROM makanan WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$makanan = $result->fetch_assoc();

if (!$makanan) {
    echo "<script>alert('Data makanan tidak ditemukan.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
    exit;
}

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama       = $_POST['nama'];
    $harga      = $_POST['harga'];
    $kategori   = $_POST['kategori'];
    $deskripsi  = $_POST['deskripsi'];

    // Cek apakah ada file gambar baru
    if (!empty($_FILES['gambar']['name'])) {
        $gambar     = uniqid() . '_' . $_FILES['gambar']['name'];
        $tmp        = $_FILES['gambar']['tmp_name'];
        $folder     = "upload/";

        // Pindahkan file
        move_uploaded_file($tmp, $folder . $gambar);

        // Update dengan gambar baru
        $update = $koneksi->prepare("UPDATE makanan SET nama=?, harga=?, kategori=?, deskripsi=?, gambar=? WHERE id=?");
        $update->bind_param("sdsssi", $nama, $harga, $kategori, $deskripsi, $gambar, $id);
    } else {
        // Update tanpa gambar
        $update = $koneksi->prepare("UPDATE makanan SET nama=?, harga=?, kategori=?, deskripsi=? WHERE id=?");
        $update->bind_param("sdssi", $nama, $harga, $kategori, $deskripsi, $id);
    }

    if ($update->execute()) {
        echo "<script>alert('Data makanan berhasil diperbarui.'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data makanan.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Makanan</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
     html, body {
      font-family: 'Poppins', sans-serif;
      background: #414177ff;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    .form-container {
      max-width: 800px;
      margin: auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
    }

    h4 {
      text-align: center;
      color: #ff7b00ff;
      font-weight: bold;
      margin-bottom: 25px;
    }

    .form-wrapper {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
    }

    label {
      font-weight: 600;
      margin-bottom: 6px;
      color: #444;
    }

    input[type="text"],
    input[type="number"],
    textarea,
    input[type="file"],
    select {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      color: #333;
    }

    textarea {
      resize: vertical;
      min-height: 80px;
    }

     .btn-group { margin-top: 30px; display: flex; justify-content: space-between; gap: 10px; }
    .btn-primary {
      background: linear-gradient(to right, #b6861fff, #ba6c00ff);
      font-weight: bold; border: none;
    }
    .btn-primary:hover { background: linear-gradient(to right, #43e97b, #38f9d7); }
    .btn-secondary { background: #a59f9fff; border: none; font-weight: bold; }
    .btn-secondary:hover { background: #ffffffff; }
    .img-preview {
      max-height: 120px;
      object-fit: contain;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h4>✏️ Edit Makanan</h4>
    <form method="post" enctype="multipart/form-data">
      <div class="form-wrapper">
        <div class="form-group">
          <label>Nama Makanan</label>
          <input type="text" name="nama" value="<?= htmlspecialchars($makanan['nama']) ?>" required>
        </div>
        <div class="form-group">
          <label>Harga</label>
          <input type="number" name="harga" value="<?= $makanan['harga'] ?>" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <input type="text" name="kategori" value="<?= htmlspecialchars($makanan['kategori']) ?>" required>
        </div>
        <div class="form-group" style="grid-column: span 2;">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="3"><?= htmlspecialchars($makanan['deskripsi']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Gambar (JPG/PNG)</label>
          <input type="file" name="gambar" accept="image/*">
          <?php if ($makanan['gambar']) { ?>
            <img src="upload/<?= $makanan['gambar'] ?>" class="img-preview">
          <?php } ?>
        </div>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="index_admin.php?page_admin=makanan/data_makanan" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</body>
</html>
