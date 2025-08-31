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

$pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE username = '".$user['username']."'"));
if (!$pelanggan) exit("Data pelanggan tidak ditemukan.");

$id_pelanggan = $pelanggan['id_pelanggan'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $password = $_POST['password'] ?? '';
    $foto = $pelanggan['foto'];

    if (!empty($_FILES['foto']['name'])) {
        $uploadDir = "upload/";
        $fileName = time() . "_" . basename($_FILES["foto"]["name"]);
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFile)) $foto = $fileName;
    }

    $query_pelanggan = "UPDATE pelanggan SET 
        username='$username',
        nama_lengkap='$nama_lengkap',
        jenis_kelamin='$jenis_kelamin',
        email='$email',
        no_hp='$no_hp',
        alamat='$alamat',
        foto='$foto'
        WHERE id_pelanggan='$id_pelanggan'";

    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $query_user = "UPDATE users SET username='$username', password='$password_hash' WHERE id_user='$id_user'";
    } else {
        $query_user = "UPDATE users SET username='$username' WHERE id_user='$id_user'";
    }

    $update1 = mysqli_query($koneksi, $query_pelanggan);
    $update2 = mysqli_query($koneksi, $query_user);

    if ($update1 && $update2) {
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='index.php?page=profil/profil_pelanggan';</script>";
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
    background: #f5f6fa;
    margin: 0;
    padding: 0;
}
.profile-container {
    display: flex;
    flex-wrap: wrap;
    max-width: 1100px;
    margin: 50px auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
}
.profile-left {
    flex: 1 1 300px;
    background: #d27d33;
    color: white;
    text-align: center;
    padding: 40px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.profile-left img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
    border: 4px solid white;
}
.profile-left h3 {
    margin: 5px 0;
}
.profile-left p {
    font-size: 0.9rem;
    color: #f2f2f2;
    margin: 2px 0;
}
.profile-right {
    flex: 2 1 600px;
    padding: 40px;
}
.profile-right h3 {
    margin-bottom: 25px;
    color: #333;
    font-size: 22px;
    font-weight: 600;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
}
.form-group {
    display: flex;
    flex-direction: column;
}
.form-group label {
    margin-bottom: 6px;
    font-weight: 500;
    color: #444;
}
.form-group input, 
.form-group select, 
.form-group textarea {
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    outline: none;
    transition: all 0.2s;
}
.form-group input:focus, 
.form-group select:focus, 
.form-group textarea:focus {
    border-color: #d27d33;
    box-shadow: 0 0 5px rgba(210,125,51,0.4);
}
textarea {
    resize: none;
    min-height: 80px;
}
.btn-submit {
    margin-top: 25px;
    padding: 14px 22px;
    background: #d27d33;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}
.btn-submit:hover {
    background: #33336b;
}
@media(max-width: 768px){
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="profile-container">
    <!-- Left Section -->
    <div class="profile-left">
        <img src="<?= !empty($pelanggan['foto']) ? 'upload/'.$pelanggan['foto'] : 'assets/default.jpg' ?>" alt="Foto Profil">
        <h3><?= $pelanggan['nama_lengkap'] ?> </h3>
    </div>

    <!-- Right Section -->
    <div class="profile-right">
        <h3>Edit Profil</h3>
        <form method="post" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= $pelanggan['username'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= $pelanggan['nama_lengkap'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $pelanggan['email'] ?>" required>
                </div>
                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" value="<?= $pelanggan['no_hp'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" required>
                        <option value="" disabled>Pilih Jenis Kelamin</option>
                        <option value="Laki-Laki" <?= $pelanggan['jenis_kelamin']=='Laki-Laki'?'selected':'' ?>>Laki-Laki</option>
                        <option value="Perempuan" <?= $pelanggan['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat"><?= $pelanggan['alamat'] ?></textarea>
                </div>

                <div class="form-group">
                    <label>Foto Profil</label>
                    <input type="file" name="foto" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin ganti)</label>
                    <input type="password" name="password" placeholder="Masukkan password baru">
                </div>
            </div>

            <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
        </form>
    </div>
</div>
