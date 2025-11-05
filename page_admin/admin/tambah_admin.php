<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $detail_alamat = $_POST['detail_alamat'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    // Upload foto ke folder upload/admin/
    $foto_name = '';
    if (!empty($_FILES['foto']['name'])) {
        $target_dir = "upload/admin/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
        $foto_name = uniqid() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], $target_dir . $foto_name);
    }

    // Simpan ke tabel admin
    $queryAdmin = "INSERT INTO admin (username, password, nama_lengkap, email, no_hp, foto, created_at, updated_at, detail_alamat, latitude, longitude)
                   VALUES ('$username', '$password', '$nama_lengkap', '$email', '$no_hp', '$foto_name', '$created_at', '$updated_at', '$detail_alamat', '$latitude', '$longitude')";
    
    $insertAdmin = mysqli_query($koneksi, $queryAdmin);

    // Jika berhasil disimpan ke tabel admin, simpan juga ke tabel user
    if ($insertAdmin) {
        $queryUser = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'admin')";
        mysqli_query($koneksi, $queryUser);

        echo "<script>alert('Admin berhasil ditambahkan!'); window.location='index_admin.php?page_admin=admin/data_admin';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan admin!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Data Admin</title>
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
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
      max-width: 900px;
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

    input, textarea {
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

    .btn-group { 
      margin-top: 30px; 
      display: flex; 
      justify-content: space-between; 
      gap: 10px; 
    }
    .btn-primary {
      background: linear-gradient(to right, #b6861fff, #ba6c00ff);
      font-weight: bold; border: none;
    }
    .btn-primary:hover { background: linear-gradient(to right, #43e97b, #38f9d7); }
    .btn-secondary { background: #a59f9fff; border: none; font-weight: bold; }
    .btn-secondary:hover { background: #ffffffff; }

    .btn-lokasi {
      margin-top: 8px;
      background-color: #ff7b00ff;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 12px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-lokasi:hover { background-color: #d66900ff; }
  </style>
</head>
<body>
  <div class="form-container">
    <h4>👨‍💼 Tambah Data Admin</h4>
    <form method="post" enctype="multipart/form-data">
      <div class="form-wrapper">
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input type="text" name="nama_lengkap" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>No HP</label>
          <input type="text" name="no_hp" required>
        </div>
        <div class="form-group">
          <label>Detail Alamat</label>
          <textarea name="detail_alamat" placeholder="Tulis alamat lengkap admin" required></textarea>
          <button type="button" class="btn-lokasi" onclick="ambilLokasi()">📍 Ambil Lokasi Saat Ini</button>
          <input type="hidden" name="latitude" id="latitude">
          <input type="hidden" name="longitude" id="longitude">
          <small id="statusLokasi" style="color:#555; margin-top:5px;"></small>
        </div>
        <div class="form-group">
          <label>Foto (JPG/PNG)</label>
          <input type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index_admin.php?page_admin=admin/data_admin" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>

  <script>
  function ambilLokasi() {
    const status = document.getElementById('statusLokasi');
    if (navigator.geolocation) {
      status.innerText = "Mengambil lokasi...";
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          document.getElementById('latitude').value = pos.coords.latitude;
          document.getElementById('longitude').value = pos.coords.longitude;
          status.innerText = "Lokasi berhasil diambil ✅";
        },
        (err) => {
          status.innerText = "❌ Gagal mengambil lokasi. Pastikan izin lokasi aktif.";
        }
      );
    } else {
      status.innerText = "Browser tidak mendukung geolocation.";
    }
  }
  </script>
</body>
</html>
