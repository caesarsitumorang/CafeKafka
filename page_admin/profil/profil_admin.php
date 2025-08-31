<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "config/koneksi.php";

// Ambil ID user dari session
$id_user = $_SESSION['id_user'] ?? null;

if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.');window.location='login.php';</script>";
    exit;
}

// Ambil data username dari tabel users
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT username FROM users WHERE id_user = '$id_user'"));
$username = $user['username'] ?? null;

if (!$username) {
    echo "<div class='alert alert-danger'>Username tidak ditemukan.</div>";
    exit;
}

// Ambil data admin berdasarkan username
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT * FROM admin 
    WHERE username = '$username'
"));

if (!$admin) {
    echo "<div class='alert alert-danger'>Data admin tidak ditemukan.</div>";
    exit;
}

// Foto profil (gunakan default.png jika kosong)
$foto = !empty($admin['foto']) && $admin['foto'] !== '-' ? $admin['foto'] : 'default.png';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #414177ff;
            font-family: 'Poppins', sans-serif;
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
        }
        h3.title {
            color: #007bff;
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
            color: #007bff;
        }
        .profile-group label {
            font-weight: 600;
            margin-bottom: 0;
            width: 150px;
            color: #555;
        }
        .profile-group p {
            margin-bottom: 0;
            background: #f1f3f6;
            border-radius: 6px;
            padding: 8px 12px;
            flex: 1;
            color: #000;
        }
    </style>
</head>
<body>

<div class="profile-card text-center">
    <a href="index_admin.php?page_admin=profil/edit_profil_admin" class="btn btn-sm btn-primary edit-btn">
        <i class="fas fa-edit"></i> Edit
    </a>

    <img src="<?= !empty($admin['foto']) ? 'upload/admin/'.htmlspecialchars($admin['foto']) : 'assets/default.jpg' ?>" 
     class="profile-photo" 
     alt="Foto Profil Admin">

    <h3 class="title"><i class="fas fa-user-shield"></i> Profil Admin</h3>

    <div class="profile-group">
        <i class="fas fa-id-badge"></i>
        <label>Nama Lengkap</label>
        <p><?= htmlspecialchars($admin['nama_lengkap']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-user"></i>
        <label>Username</label>
        <p><?= htmlspecialchars($admin['username']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-envelope"></i>
        <label>Email</label>
        <p><?= htmlspecialchars($admin['email']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-phone"></i>
        <label>No HP</label>
        <p><?= htmlspecialchars($admin['no_hp']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-calendar-plus"></i>
        <label>Bergabung</label>
        <p><?= htmlspecialchars($admin['created_at']) ?></p>
    </div>

    <div class="profile-group">
        <i class="fas fa-calendar-check"></i>
        <label>Terakhir Update</label>
        <p><?= htmlspecialchars($admin['updated_at']) ?></p>
    </div>

</div>

</body>
</html>
