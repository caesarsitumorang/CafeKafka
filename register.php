<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register - Cafe Kafka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a35002ff, #8b5c00ff);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            display: flex;
            max-width: 850px;
            overflow: hidden;
        }

        .icon-side {
            background: #ff7b00;
            color: #fff;
            padding: 40px;
            text-align: center;
            flex: 1;
        }

        .icon-side i {
            font-size: 70px;
            margin-bottom: 20px;
        }

        .icon-side h2 {
            margin: 0;
            font-size: 28px;
        }

        .icon-side p {
            font-size: 14px;
            margin-top: 10px;
        }

        .form-side {
            padding: 40px;
            flex: 1;
        }

        .welcome-title {
            font-size: 26px;
            margin-bottom: 10px;
            color: #ff7b00;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-input {
            width: 100%;
            padding: 12px 40px 12px 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .login-btn {
            background: #ff7b00;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }

        .login-btn:hover {
            background: #e06900;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            color: #aaa;
        }

        .form-links a {
            text-decoration: none;
            font-size: 14px;
            color: #ff7b00;
        }

        .form-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="icon-side">
            <i class="fas fa-user-plus"></i>
            <h2>Cafe Kafka</h2>
            <p>"Gabung sekarang dan nikmati layanan Cafe Kafka"</p>
        </div>

        <!-- Right Side - Register Form -->
        <div class="form-side">
            <h1 class="welcome-title">Buat Akun Baru</h1>
            <p>Isi data di bawah untuk mendaftar</p>

            <form class="login-form" method="post">
                <div class="form-group">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" class="form-input" placeholder="Username" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" class="form-input" placeholder="Password" required>
                </div>

                <div class="form-group">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" name="nama_lengkap" class="form-input" placeholder="Nama Lengkap" required>
                </div>

                <button type="submit" name="submit" value="Daftar" class="login-btn" id="registerBtn">
                    <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i>
                    Daftar Sekarang
                </button>
            </form>

            <div class="divider">
                <span>atau</span>
            </div>

            <div class="form-links">
                <a href="login.php">
                    <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                    Sudah punya akun? Masuk di sini
                </a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once("config/koneksi.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit']) && $_POST['submit'] == 'Daftar') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $nama_lengkap = trim($_POST['nama_lengkap']);

        if (empty($username) || empty($password) || empty($nama_lengkap)) {
            echo "<script>alert('Semua field wajib diisi!'); window.location='register.php';</script>";
            exit;
        }

        // Cek apakah username sudah digunakan
        $check = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($check) > 0) {
            echo "<script>alert('Username sudah terdaftar!'); window.location='register.php';</script>";
            exit;
        }

        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert ke tabel users (role default = pelanggan)
        $insertUser = mysqli_query($koneksi, "
            INSERT INTO users (username, password, role) 
            VALUES ('$username', '$hashedPassword', 'pelanggan')
        ");

        if ($insertUser) {
            // Insert juga ke tabel pelanggan (hanya username, password, nama lengkap)
            $insertPelanggan = mysqli_query($koneksi, "
                INSERT INTO pelanggan (username, password, nama_lengkap) 
                VALUES ('$username', '$hashedPassword', '$nama_lengkap')
            ");

            if ($insertPelanggan) {
                echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Gagal menyimpan data ke tabel pelanggan!'); window.location='register.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Gagal menyimpan data ke tabel users!'); window.location='register.php';</script>";
            exit;
        }
    }
}
?>

