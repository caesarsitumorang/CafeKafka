<?php
// session_start();
// require_once("config/koneksi.php");

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
//     $username = mysqli_real_escape_string($koneksi, $_POST['username']);
//     $password = $_POST['password'];

//     if (empty($username) || empty($password)) {
//         echo "<script>alert('Username atau password tidak boleh kosong!'); window.location='login.php';</script>";
//         exit;
//     }

//     $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
//     $result = mysqli_query($koneksi, $query);

//     if ($data = mysqli_fetch_assoc($result)) {
//         $_SESSION['username'] = $data['username'];
//         $_SESSION['id_user'] = $data['id_user'];
//         $_SESSION['role'] = $data['role'];

//         switch ($data['role']) {
//             case 'admin':
//                 header("Location: index_admin.php");
//                 exit;
//             case 'pelanggan':
//             default:
//                 header("Location: index.php");
//                 exit;
//         }
//     } else {
//         echo "<script>alert('Username atau password salah!'); window.location='login.php';</script>";
//         exit;
//     }
// }
 ?>


<?php
session_start();
require_once("config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        echo "<script>alert('Username atau password tidak boleh kosong!'); window.location='login.php';</script>";
        exit;
    }

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if ($data = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $data['password'])) {
            $_SESSION['username'] = $data['username'];
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['role'] = $data['role'];

            switch ($data['role']) {
                case 'admin':
                    header("Location: index_admin.php");
                    exit;
                case 'pelanggan':
                default:
                    header("Location: index.php");
                    exit;
            }
        } else {
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location='login.php';</script>";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login - Cafe Kafka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
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
    padding: 12px 40px 12px 40px; /* ada ruang kiri & kanan buat ikon */
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.input-icon {
    position: absolute;
    left: 12px;   /* posisikan ke kiri */
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
        <!-- Left Side - Branding -->
        <div class="icon-side">
            <i class="fas fa-mug-hot"></i>
            <h2>Cafe Kafka</h2>
            <p>"Nikmati kopi terbaik dengan suasana hangat di Cafe Kafka"</p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="form-side">
            <h1 class="welcome-title">Selamat Datang!</h1>
            <p>Masuk ke akun Cafe Kafka Anda</p>

            <form method="post" id="loginForm">
                <div class="form-group">
                    <input type="text" name="username" class="form-input" placeholder="Username" required>
                    <i class="fas fa-user input-icon"></i>
                </div>

                <div class="form-group">
                    <input type="password" name="password" class="form-input" placeholder="Password" required id="passwordInput">
                    <i class="fas fa-eye input-icon" id="togglePassword" style="cursor: pointer;"></i>
                </div>

                <button type="submit" name="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
                    Masuk ke Cafe Kafka
                </button>
            </form>

            <div class="divider">
                <span>atau</span>
            </div>

            <div class="form-links">
                <a href="register.php">
                    <i class="fas fa-user-plus" style="margin-right: 0.5rem;"></i>
                    Belum punya akun? Daftar di sini
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
