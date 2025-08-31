<?php
ob_start();
session_start();

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once("config/koneksi.php");

// Routing halaman dengan aman
$halaman = $_GET['page'] ?? '';
$halaman = trim($halaman);
$halaman = str_replace(['..', './', '//'], '', $halaman); // cegah directory traversal
$path = "page/$halaman.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cafe Kafka - Pelanggan</title>

  <!-- Fonts & Icons -->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #0f0f23;
      color: #fff;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }

    /* Navbar */
    .navbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #d27d33;
      color: #ffffff;
      padding: 1rem 2rem;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      box-shadow: 0 3px 8px rgba(0,0,0,0.4);
      height: 60px;
    }

    .navbar-brand {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.4rem;
      font-weight: 700;
      color: #fff;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .navbar-menu {
      display: flex;
      gap: 2rem;
      align-items: center;
    }

    .navbar-menu a {
      color: #eee;
      text-decoration: none;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      font-size: .95rem;
      padding: 0.5rem 1rem;
      border-radius: 6px;
    }

    .navbar-menu a:hover,
    .navbar-menu a.active {
      color: #ffd166;
      background: rgba(255,255,255,0.1);
    }

    /* User Menu */
    .user-menu {
      position: relative;
    }

    .user-button {
      cursor: pointer;
      color: #fff;
      font-weight: 600;
      padding: .5rem 1rem;
      border-radius: 6px;
      transition: background 0.3s;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .user-button:hover {
      background: rgba(255,255,255,0.15);
    }

    .dropdown-menu {
      position: absolute;
      right: 0;
      top: calc(100% + 8px);
      background: #1e1e2f;
      color: #fff;
      border-radius: 8px;
      display: none;
      flex-direction: column;
      min-width: 200px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
      padding: 0.5rem 0;
      border: 1px solid rgba(255,255,255,0.1);
    }

    .dropdown-menu.show {
      display: flex;
    }

    .dropdown-item {
      padding: 0.9rem 1.2rem;
      color: #eee;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      font-size: 0.95rem;
      transition: background 0.3s;
    }

    .dropdown-item:hover {
      background: #ffa806;
      color: #000;
    }

    .dropdown-divider {
      height: 1px;
      background: rgba(255,255,255,0.1);
      margin: 0.5rem 0;
    }

    /* Main Container */
    .container {
      padding: 2rem;
      margin-top: 80px; /* Space untuk navbar fixed */
      min-height: calc(100vh - 80px);
    }

    /* Dialog Styles */
    .dialog {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(3px);
      z-index: 2000;
      justify-content: center;
      align-items: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .dialog.show {
      display: flex;
      opacity: 1;
    }

    .dialog-content {
      background: #fff;
      color: #333;
      padding: 30px;
      border-radius: 12px;
      width: 400px;
      max-width: 90%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      transform: scale(0.9);
      transition: transform 0.3s ease;
    }

    .dialog.show .dialog-content {
      transform: scale(1);
    }

    .dialog-title {
      margin: 0 0 15px;
      font-size: 1.4rem;
      font-weight: 600;
      color: #333;
    }

    .dialog-text {
      margin: 0 0 25px;
      color: #666;
      font-size: 1rem;
      line-height: 1.5;
    }

    .dialog-footer {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .btn {
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s ease;
      border: none;
      min-width: 100px;
    }

    .btn.cancel {
      background: #f8f9fa;
      color: #6c757d;
      border: 1px solid #dee2e6;
    }

    .btn.cancel:hover {
      background: #e9ecef;
      color: #495057;
    }

    .btn.confirm {
      background: #dc3545;
      color: white;
    }

    .btn.confirm:hover {
      background: #c82333;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .navbar {
        padding: 1rem;
        flex-direction: column;
        height: auto;
        gap: 1rem;
      }

      .navbar-menu {
        gap: 1rem;
      }

      .navbar-menu a {
        font-size: 0.9rem;
        padding: 0.4rem 0.8rem;
      }

      .container {
        margin-top: 120px;
        padding: 1rem;
      }

      .dialog-content {
        width: 320px;
        padding: 25px;
      }

      .dialog-title {
        font-size: 1.2rem;
      }

      .btn {
        padding: 10px 20px;
        font-size: 13px;
      }
    }

    @media (max-width: 480px) {
      .navbar-menu {
        flex-wrap: wrap;
        justify-content: center;
      }

      .container {
        margin-top: 140px;
      }

      .dialog-footer {
        flex-direction: column;
      }

      .btn {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <div class="navbar">
    <a href="index.php" class="navbar-brand">
     
    </a>
    <div class="navbar-menu">
      <a href="index.php" class="<?= empty($halaman) ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Beranda
      </a>
      <a href="index.php?page=pesanan/pesanan" class="<?= ($halaman == 'pesanan/pesanan') ? 'active' : '' ?>">
        <i class="fas fa-receipt"></i> Pesanan
      </a>
      <a href="index.php?page=keranjang/keranjang" class="<?= ($halaman == 'keranjang/keranjang') ? 'active' : '' ?>">
        <i class="fas fa-shopping-cart"></i> Keranjang
      </a>
      <a href="index.php?page=riwayat/riwayat_pesanan" class="<?= ($halaman == 'riwayat/riwayat_pesanan') ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Riwayat
      </a>
    </div>
    <div class="user-menu">
      <div class="user-button" id="userDropdown">
        <i class="fas fa-user"></i> Navigasi <i class="fas fa-chevron-down"></i>
      </div>
      <div class="dropdown-menu" id="dropdownMenu">
        <a class="dropdown-item" href="index.php?page=profil/profil_pelanggan">
          <i class="fas fa-user-edit"></i> Profil Saya
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="#" onclick="opendialog(); return false;">
          <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <?php
    if (empty($halaman)) {
        include "page/home.php";
    } elseif (file_exists($path)) {
        include $path;
    } else {
        include "page/404.php";
    }
    ?>
  </div>
  
  <!-- Dialog Logout -->
  <div id="logoutdialog" class="dialog">
    <div class="dialog-content">
      <h3 class="dialog-title">
        <i class="fas fa-sign-out-alt" style="color: #dc3545; margin-right: 10px;"></i>
        Konfirmasi Logout
      </h3>
      <p class="dialog-text">Apakah Anda yakin ingin keluar dari sesi ini?</p>
      <div class="dialog-footer">
        <button class="btn cancel" onclick="closedialog()">
          <i class="fas fa-times"></i> Batal
        </button>
        <a class="btn confirm" href="logout.php">
          <i class="fas fa-check"></i> Keluar
        </a>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    // Dropdown functionality
    const userDropdown = document.getElementById("userDropdown");
    const dropdownMenu = document.getElementById("dropdownMenu");

    if (userDropdown && dropdownMenu) {
      userDropdown.addEventListener("click", function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle("show");
      });

      // Close dropdown when clicking outside
      document.addEventListener("click", function(e) {
        if (!userDropdown.contains(e.target) && !dropdownMenu.contains(e.target)) {
          dropdownMenu.classList.remove("show");
        }
      });

      // Close dropdown when pressing Escape
      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") {
          dropdownMenu.classList.remove("show");
        }
      });
    }

    // Dialog functionality
    function opendialog() {
      const dialog = document.getElementById('logoutdialog');
      if (dialog) {
        // Close dropdown first
        dropdownMenu.classList.remove("show");
        
        dialog.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
      }
    }

    function closedialog() {
      const dialog = document.getElementById('logoutdialog');
      if (dialog) {
        dialog.classList.remove('show');
        document.body.style.overflow = 'auto'; // Restore scrolling
      }
    }

    // Close dialog when clicking on overlay
    document.getElementById('logoutdialog').addEventListener('click', function(e) {
      if (e.target === this) {
        closedialog();
      }
    });

    // Close dialog with Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closedialog();
      }
    });

    // Prevent dialog from showing on page load
    document.addEventListener('DOMContentLoaded', function() {
      const dialog = document.getElementById('logoutdialog');
      if (dialog) {
        dialog.classList.remove('show');
      }
    });
  </script>
</body>
</html>
<?php ob_end_flush(); ?>