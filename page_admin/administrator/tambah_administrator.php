<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = $_POST['username'];
    $password     = $_POST['password'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email        = $_POST['email'];
    $no_hp        = $_POST['no_hp'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Upload foto
    $foto_name = '';
    if (!empty($_FILES['foto']['name'])) {
        $foto_name = uniqid() . '_' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], 'upload/admin/' . $foto_name);
    }

    // Simpan ke tabel admin
    $query_admin = "INSERT INTO admin (username, password, nama_lengkap, email, no_hp, foto, created_at, updated_at)
                    VALUES ('$username', '$hashed_password', '$nama_lengkap', '$email', '$no_hp', '$foto_name', NOW(), NOW())";
    $insert_admin = mysqli_query($koneksi, $query_admin);

    // Simpan juga ke tabel users
    $query_users = "INSERT INTO users (username, password, role)
                    VALUES ('$username', '$hashed_password', 'admin')";
    $insert_users = mysqli_query($koneksi, $query_users);

    if ($insert_admin && $insert_users) {
        echo "<script>alert('Admin berhasil ditambahkan!'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    } else {
        echo "<script>alert('Gagal menambahkan admin!');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Admin</title>
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
    .form-group { display: flex; flex-direction: column; }
    label { font-weight: 600; margin-bottom: 6px; color: #444; }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
      color: #333;
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
    <h4>👨‍💼 Tambah Admin</h4>
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
          <label>Foto (JPG/PNG)</label>
          <input type="file" name="foto" accept="image/*">
        </div>
      </div>

      <div class="btn-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="index_admin.php?page_admin=administrator/data_administrator" class="btn btn-secondary">Kembali</a>
      </div>
    </form>
  </div>
</body>
</html>
