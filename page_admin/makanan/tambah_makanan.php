<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];

    // Upload gambar
    $gambar_name = '';
    if ($_FILES['gambar']['name']) {
        $gambar_name = uniqid() . '_' . $_FILES['gambar']['name'];
        move_uploaded_file($_FILES['gambar']['tmp_name'], 'upload/' . $gambar_name);
    }

    $query = "INSERT INTO makanan (nama, deskripsi, harga, kategori, stok, gambar)
              VALUES ('$nama', '$deskripsi', '$harga', '$kategori', '$stok', '$gambar_name')";
    
    $insert = mysqli_query($koneksi, $query);

    if ($insert) {
        echo "<script>alert('Makanan berhasil ditambahkan!'); window.location='index_admin.php?page_admin=makanan/data_makanan';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan makanan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Makanan</title>
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
  </style>
</head>
<body>
  <div class="form-container">
    <h4>🍽️ Tambah Makanan</h4>
    <form method="post" enctype="multipart/form-data">
      <div class="form-wrapper">
        <div class="form-group">
          <label>Nama Makanan</label>
          <input type="text" name="nama" required>
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="deskripsi" rows="3" required></textarea>
        </div>
        <div class="form-group">
          <label>Harga</label>
          <input type="number" name="harga" min="0" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="kategori" required>
            <option value="" disabled selected>Pilih Kategori</option>
            <option value="Makanan Berat">Makanan Berat</option>
            <option value="Makanan Ringan">Makanan Ringan</option>
            <option value="Cemilan">Cemilan</option>
          </select>
        </div>
        <div class="form-group">
          <label>Stok</label>
          <input type="number" name="stok" min="0" required>
        </div>
        <div class="form-group">
          <label>Gambar (JPG/PNG)</label>
          <input type="file" name="gambar" accept="image/*">
        </div>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index_admin.php?page_admin=makanan/data_makanan" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</body>
</html>
