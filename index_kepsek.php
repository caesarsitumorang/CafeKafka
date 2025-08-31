<?php
session_start();
if (!$_SESSION['username']) {
    header("location:login.php");
    exit();
}
require_once("config/koneksi.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perpustakaan Kepala Sekolah</title>

    <!-- Fonts & Icons -->
    <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    
    <!-- Custom CSS -->
    <link href="css/index_admin.css" rel="stylesheet">

    <style>
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: rgba(15, 15, 35, 0.95);
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
        }

      .dropdown-menu {
    position: absolute;
    right: 2rem;
    top: 70px;
    background-color: #fff !important; /* Putih */
    border: 1px solid #ccc;
    border-radius: 10px;
    display: none;
    flex-direction: column;
    padding: 0.5rem 0;
    min-width: 180px;
    z-index: 99999;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    color: #000 !important; /* Teks hitam */
}

.dropdown.show .dropdown-menu {
    display: flex !important;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #000 !important; /* Teks hitam */
    background-color: #fff !important; /* Putih */
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background-color: #f1f1f1 !important; /* Hover abu muda */
    color: #000 !important; /* Tetap hitam */
    text-decoration: none;
}

.dropdown-item i {
    margin-right: 0.75rem;
    width: 16px;
    color: #000 !important;
}

.dropdown-divider {
    height: 1px;
    background: #ccc;
    margin: 0.5rem 0;
}

    </style>

   <script>
    document.addEventListener("DOMContentLoaded", function () {
        const userDropdown = document.getElementById("userDropdown");
        const dropdownMenu = document.querySelector(".dropdown-menu");

        userDropdown.addEventListener("click", function (e) {
            e.preventDefault();
            dropdownMenu.style.display = dropdownMenu.style.display === "flex" ? "none" : "flex";
        });

        // Tutup saat klik di luar
        document.addEventListener("click", function (e) {
            if (!userDropdown.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.style.display = "none";
            }
        });
    });
</script>

 <script>
  function openModal() {
    document.getElementById('logoutModal').classList.add('show');
  }

  function closeModal() {
    document.getElementById('logoutModal').classList.remove('show');
  }

  // Tutup modal jika klik di luar konten
  window.addEventListener('click', function (e) {
    const modal = document.getElementById('logoutModal');
    if (e.target === modal) {
      closeModal();
    }
  });
</script>

</head>
<body id="page-top">

    <div id="wrapper">

        <!-- Sidebar -->
        <div class="sidebar" id="accordionSidebar">
            <a class="sidebar-brand" href="index_kepsek.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text">Perpustakaan</div>
            </a>
            <hr class="sidebar-divider">

            <?php $page = isset($_GET['page_kepsek']) ? $_GET['page_kepsek'] : ''; ?>

           <div class="nav-item <?= $page == '' ? 'active' : '' ?>">
                <a class="nav-link" href="index_kepsek.php">
                    <i class="fas fa-fw fa-home"></i>
                    <span>Beranda</span>
                </a>
            </div>

             <div class="nav-item <?= $page == 'laporan' ? 'active' : '' ?>">
                <a class="nav-link" href="index_kepsek.php?page_kepsek=pengunjung/data_pengunjung">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Data Pengunjung</span>
                </a>
            </div>

             <div class="nav-item <?= $page == 'laporan' ? 'active' : '' ?>">
                <a class="nav-link" href="index_kepsek.php?page_kepsek=buku/buku">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Data Buku Fisik</span>
                </a>
            </div>

             <div class="nav-item <?= $page == 'laporan' ? 'active' : '' ?>">
                <a class="nav-link" href="index_kepsek.php?page_kepsek=buku_digital/buku_digital">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Data Buku Digital</span>
                </a>
            </div>

            <div class="nav-item <?= $page == 'laporan' ? 'active' : '' ?>">
                <a class="nav-link" href="index_kepsek.php?page_kepsek=laporan/data_peminjaman">
                    <i class="fas fa-fw fa-file-alt"></i>
                    <span>Laporan Peminjaman</span>
                </a>
            </div>


        </div>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper">

            <!-- Topbar -->
<div class="topbar">
            <button class="sidebar-toggle" id="sidebarToggleTop">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-right">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <div class="dropdown">
                            <a class="user-dropdown" id="userDropdown">
                                <!-- <span><?php echo $_SESSION['username']; ?></span> -->
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="index_kepsek.php?page_kepsek=profil/profil_kepsek">
                                    <i class="fas fa-user"></i> Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="openModal()">
                                <i class="fas fa-sign-out-alt"></i> Keluar
                                </a>

                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

            <!-- Main Content -->
            <div id="content">
                <div class="container">
                    <?php
                    if (isset($_GET['page_kepsek'])) {
                        $halaman = $_GET['page_kepsek'];
                    } else {
                        $halaman = "";
                    }

                    if ($halaman == "") {
                        include "page_kepsek/home_kepsek.php";
                    } else if (!file_exists("page_kepsek/$halaman.php")) {
                        include "page_kepsek/404.php";
                    } else {
                        include "page_kepsek/$halaman.php";
                    }
                    ?>
                </div>
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="footer">
                <span>&copy; Perpustakaan SMP - SMK MASEHI SIBOLANGIT 2025</span>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top -->
    <a class="scroll-to-top" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

  <!-- Logout Modal (menggunakan CSS custom) -->
<div id="logoutModal" class="modal">
  <div class="modal-content">
    <h5 class="modal-title">Apakah Anda yakin ingin keluar?</h5>
    <div class="modal-body">
      Pilih "Keluar" untuk mengakhiri sesi Anda.
    </div>
    <div class="modal-footer">
      <button class="btn btn-danger" onclick="closeModal()">Batal</button>
      <a class="btn btn-primary" href="logout.php">Keluar</a>
    </div>
  </div>
</div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
