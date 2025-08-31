<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include "config/koneksi.php";

$id_user = $_SESSION['id_user'] ?? null;
if (!$id_user) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location='login.php';</script>";
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'"));
if (!$user) exit("Data user tidak ditemukan.");

$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM admin WHERE username = '".$user['username']."'"));
if (!$admin) exit("Data admin tidak ditemukan.");

$id_admin = $admin['id_admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $password = $_POST['password'] ?? '';
    $foto = $admin['foto'];

    if (!empty($_FILES['foto']['name'])) {
        $uploadDir = "upload/admin/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) $foto = $fileName;
    }

    // Update tabel admin
    $query_admin = "UPDATE admin SET 
        username='$username',
        nama_lengkap='$nama_lengkap',
        email='$email',
        no_hp='$no_hp',
        foto='$foto',
        updated_at=NOW()
        WHERE id_admin='$id_admin'";

    // Update tabel users
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query_user = "UPDATE users SET username='$username', password='$password_hash' WHERE id_user='$id_user'";
    } else {
        $query_user = "UPDATE users SET username='$username' WHERE id_user='$id_user'";
    }

    $update1 = mysqli_query($koneksi, $query_admin);
    $update2 = mysqli_query($koneksi, $query_user);

    if ($update1 && $update2) {
        echo "<script>alert('Profil admin berhasil diperbarui!'); window.location='index_admin.php?page_admin=profil/profil_admin';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menyimpan perubahan.</div>";
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #33336b;
    margin: 0;
    padding: 0;
}
.profile-container {
    display: flex;
    max-width: 1000px;
    margin: 50px auto;
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
}
.profile-left {
    flex: 1;
    background: #fa933eff;
    color: white;
    text-align: center;
    padding: 30px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.profile-left img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
    border: 3px solid white;
}
.profile-left h3 {
    margin: 5px 0;
}
.profile-left p {
    font-size: 0.9rem;
    color: #ffffffff;
    margin: 3px 0;
}
.profile-right {
    flex: 2;
    padding: 30px;
}
.profile-right h3 {
    margin-bottom: 20px;
    color: #ffffffff;
}
.form-group {
    margin-bottom: 15px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}
.form-group input {
    width: 100%;
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 15px;
}
.form-row {
    display: flex;
    gap: 20px;
}
.form-row .form-group { flex: 1; }
.btn-submit {
    padding: 10px 20px;
    background: #33336b;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}
.btn-submit:hover {
    background: #d27d33;
}
</style>

<div class="profile-container">
    <!-- Left Section -->
    <div class="profile-left">
        <img src="<?= !empty($admin['foto']) ? 'upload/admin/'.$admin['foto'] : 'assets/default.jpg' ?>" alt="Foto Admin">
        <h3><?= $admin['nama_lengkap'] ?></h3>
        <p><?= $admin['username'] ?></p>
        <p><?= $admin['email'] ?></p>
    </div>

    <!-- Right Section -->
    <div class="profile-right">
        <h3>Edit Profil Admin</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= $admin['username'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= $admin['nama_lengkap'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $admin['email'] ?>" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" value="<?= $admin['no_hp'] ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin ganti)</label>
                    <input type="password" name="password" placeholder="Masukkan password baru">
                </div>
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>
    </div>
</div>
