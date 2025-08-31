<?php
include "config/koneksi.php";

if (!isset($_GET['id_admin'])) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

$id_admin = intval($_GET['id_admin']);
$query = mysqli_query($koneksi, "SELECT * FROM admin WHERE id_admin='$id_admin'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email        = mysqli_real_escape_string($koneksi, $_POST['email']);
    $no_hp        = mysqli_real_escape_string($koneksi, $_POST['no_hp']);
    $updated_at   = date("Y-m-d H:i:s");

    // Password (opsional)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_sql = ", password='$password'";
    } else {
        $password_sql = "";
    }

    // Upload foto baru (path disamakan ke upload/admin/)
    $foto_sql = "";
    if (!empty($_FILES['foto']['name'])) {
        $ext_valid = ['jpg', 'jpeg', 'png', 'gif'];
        $ext       = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $ext_valid)) {
            $foto_name   = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_FILES['foto']['name']);
            $target_dir  = "upload/admin/";
            $target_file = $target_dir . $foto_name;

            // Buat folder kalau belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                // Hapus foto lama kalau ada
                if (!empty($data['foto']) && file_exists($target_dir . $data['foto'])) {
                    unlink($target_dir . $data['foto']);
                }
                $foto_sql = ", foto='$foto_name'";
            }
        }
    }

    // Update data
    $update = mysqli_query($koneksi, "
        UPDATE admin 
        SET username='$username', 
            nama_lengkap='$nama_lengkap', 
            email='$email', 
            no_hp='$no_hp', 
            updated_at='$updated_at'
            $password_sql 
            $foto_sql
        WHERE id_admin='$id_admin'
    ");

    if ($update) {
        echo "<script>alert('Data berhasil diperbarui'); window.location='index_admin.php?page_admin=administrator/data_administrator';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data');</script>";
    }
}
?>

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
    max-width: 700px;
    margin: 40px auto;
    background: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
  }
  h3 {
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    margin-bottom: 30px;
    color: black;
  }
  .form-group { margin-bottom: 1rem; }
  label {
    font-weight: 600;
    color: black;
    margin-bottom: 6px;
    display: block;
  }
  input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    color: black;
  }
  .form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
  }
  .btn-submit {
    background: #4CAF50;
    color: white;
    font-weight: bold;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
  }
  .btn-submit:hover { background: #45a049; }
  .btn-back {
    background: #ccc;
    color: black;
    font-weight: bold;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
  }
  .btn-back:hover { background: #aaa; }
  .foto-preview { margin-bottom: 10px; }
  .foto-preview img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #ddd;
  }
</style>

<div class="form-container">
  <h3>Edit Data Admin</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
    </div>
    <div class="form-group">
      <label>No HP</label>
      <input type="text" name="no_hp" value="<?= htmlspecialchars($data['no_hp']) ?>" required>
    </div>
    <div class="form-group">
      <label>Password (kosongkan jika tidak ingin diubah)</label>
      <input type="password" name="password">
    </div>
    <div class="form-group">
      <label>Foto Admin</label>
      <div class="foto-preview">
        <?php if (!empty($data['foto']) && file_exists("upload/admin/" . $data['foto'])): ?>
          <img src="upload/admin/<?= $data['foto'] ?>" alt="Foto Admin">
        <?php else: ?>
          <img src="upload/admin/default.jpg" alt="Foto Default">
        <?php endif; ?>
      </div>
      <input type="file" name="foto" accept="image/*">
    </div>
    <div class="form-buttons">
      <button type="submit" class="btn-submit">Simpan Perubahan</button>
      <a href="javascript:history.back()" class="btn-back">Kembali</a>
    </div>
  </form>
</div>
