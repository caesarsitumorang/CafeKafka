<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.');window.location='login.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));

if (!$user) {
    echo "<div class='alert alert-danger'>User tidak ditemukan.</div>";
    exit;
}

$username = $user['username'];
$pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE username = '$username'"));

if (!$pelanggan) {
    echo "<div class='alert alert-danger'>Data pelanggan tidak ditemukan.</div>";
    exit;
}

$foto = !empty($pelanggan['foto']) && $pelanggan['foto'] !== '-' ? $pelanggan['foto'] : 'default.png';
?>



<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Pelanggan - Cafe Kafka</title>
<link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
  html, body {
    background: linear-gradient( #f8f9fa 0%, #e9ecef 100%);
    font-family: 'Poppins', sans-serif;
  }

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
        .profile-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            max-width: 600px;
            margin: 50px auto;
            position: relative;
        }

        .profile-photo {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            margin-top: -100px;
            z-index: 1;
            position: relative;
        }

        .edit-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            background: #d27d33ff;
        }

        h3.title {
            color:  #d27d33ff;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 30px;
            text-align: center;
        }

        .profile-group {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .profile-group i {
            width: 30px;
            color: #d27d33ff;
        }

        .profile-group label {
            font-weight: 600;
            margin-bottom: 0;
            width: 140px;
            color: #555;
        }

      .profile-group p {
    margin-bottom: 0;
    background: #f1f3f6;
    border-radius: 6px;
    padding: 8px 12px;
    flex: 1;
    color: #000; /* teks hitam */
}


        .badge-akses {
            font-weight: bold;
            color: white;
            padding: 5px 12px;
            border-radius: 10px;
        }

        .badge-member {
            background: #28a745;
        }

        .badge-umum {
            background: #6c757d;
        }
    </style>
</head>
<body>

<div class="profile-card text-center">
    <a href="index.php?page=profil/edit_profil"  class="btn btn-sm btn-primary edit-btn">
        <i class="fas fa-edit"></i> Edit
    </a>

    <img src="upload/<?= htmlspecialchars($foto) ?>" class="profile-photo" alt="Foto Profil">

    <h3 class="title"><i class="fas fa-user-circle"></i> Profil pelanggan</h3>

    <div class="profile-group">
        <i class="fas fa-id-badge"></i>
        <label>Nama Lengkap</label>
        <p><?= !empty($pelanggan['nama_lengkap']) ? htmlspecialchars($pelanggan['nama_lengkap']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Username</label>
        <p><?= !empty($pelanggan['username']) ? htmlspecialchars($pelanggan['username']) : '-' ?></p>
    </div>

     <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Jenis Kelamin</label>
        <p><?= !empty($pelanggan['jenis_kelamin']) ? htmlspecialchars($pelanggan['jenis_kelamin']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-envelope"></i>
        <label>Email</label>
        <p><?= !empty($pelanggan['email']) ? htmlspecialchars($pelanggan['email']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-phone"></i>
        <label>No HP</label>
        <p><?= !empty($pelanggan['no_hp']) ? htmlspecialchars($pelanggan['no_hp']) : '-' ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-map-marker-alt"></i>
        <label>Alamat</label>
        <p><?= !empty($pelanggan['alamat']) ? htmlspecialchars($pelanggan['alamat']) : '-' ?></p>
    </div>

    <?php if ($is_member): ?>
    <div class="profile-group">
        <i class="fas fa-info-circle"></i>
        <label>Status Member</label>
        <p><?= $status_member ?></p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
