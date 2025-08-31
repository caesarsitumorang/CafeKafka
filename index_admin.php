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
    <title>Panel Admin- Sistem Manajemen Data</title>

    <!-- Fonts & Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --light-orange: #fff4f0;
            --dark-orange: #e55a2b;
            --orange-gradient: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            
            --dark-bg: #1a1a2e;
            --darker-bg: #16213e;
            --sidebar-bg: #0f0f23;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: var(--text-primary);
            line-height: 1.6;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--darker-bg);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 2px;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 107, 53, 0.2);
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--orange-gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .sidebar-brand-icon i {
            color: white;
            font-size: 1.2rem;
        }

        .sidebar-brand-text {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .sidebar-divider {
            border: none;
            height: 1px;
            background: rgba(255, 107, 53, 0.2);
            margin: 0;
        }

        .nav-item {
            margin: 0.5rem 1rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem;
            color: #9ca3af;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 107, 53, 0.1);
            transform: translateX(4px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .nav-item.active .nav-link {
            background: var(--orange-gradient);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .nav-item.active .nav-link::after {
            content: '';
            position: absolute;
            right: -1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--primary-orange);
            border-radius: 2px;
        }

        /* Main Content */
        #content-wrapper {
            margin-left: 260px;
            width: calc(100% - 260px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 2rem;
            background: white;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: var(--light-orange);
            color: var(--primary-orange);
        }

        .topbar-title {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: var(--light-orange);
            border-radius: 50px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .user-dropdown:hover {
            background: var(--orange-gradient);
            color: white;
            border-color: var(--primary-orange);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: var(--orange-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
        }

        .user-avatar i {
            color: white;
            font-size: 1rem;
        }

        .username {
            margin-right: 8px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .user-dropdown:hover .username {
            color: white;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 8px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            display: none;
            flex-direction: column;
            padding: 0.5rem 0;
            min-width: 200px;
            z-index: 1000;
            box-shadow: var(--shadow-lg);
        }

        .dropdown.show .dropdown-menu {
            display: flex;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .dropdown-item:hover {
            background: var(--light-orange);
            color: var(--primary-orange);
        }

        .dropdown-item i {
            margin-right: 12px;
            width: 16px;
            text-align: center;
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.5rem 0;
        }

        /* Content Area */
        #content {
            flex: 1;
            padding: 2rem;
        }

        .container {
            max-width: 100%;
        }

        /* Footer */
        .footer {
            padding: 1rem 2rem;
            background: white;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* Scroll to Top */
        .scroll-to-top {
            position: fixed;
            right: 2rem;
            bottom: 2rem;
            width: 50px;
            height: 50px;
            background: var(--orange-gradient);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .scroll-to-top:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: var(--shadow-lg);
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .modal-body {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--orange-gradient);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-danger {
            background: #6b7280;
            color: white;
        }

        .btn-danger:hover {
            background: #4b5563;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            #content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-toggle {
                display: block;
            }

            .topbar-title {
                font-size: 1.25rem;
            }

            #content {
                padding: 1rem;
            }

            .user-dropdown {
                padding: 0.5rem;
            }

            .username {
                display: none;
            }
        }

        /* Welcome Card */
        .welcome-card {
            background: var(--orange-gradient);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.2);
        }

        .welcome-card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-card p {
            opacity: 0.9;
            font-size: 1rem;
        }
    </style>

    <script>
        function openModal() {
            document.getElementById('logoutModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('logoutModal').classList.remove('show');
        }

        // Close modal when clicking outside
        window.addEventListener('click', function (e) {
            const modal = document.getElementById('logoutModal');
            if (e.target === modal) {
                closeModal();
            }
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            document.getElementById('accordionSidebar').classList.toggle('show');
        }

        // Dropdown functionality
        document.addEventListener("DOMContentLoaded", function () {
            const userDropdown = document.getElementById("userDropdown");
            const dropdown = userDropdown.closest('.dropdown');

            userDropdown.addEventListener("click", function (e) {
                e.preventDefault();
                dropdown.classList.toggle('show');
            });

            document.addEventListener("click", function (e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });
        });
    </script>
</head>
<body id="page-top">

<div id="wrapper">

    <!-- Sidebar -->
    <div class="sidebar" id="accordionSidebar">
        <a class="sidebar-brand" href="index_admin.php">
            <div class="sidebar-brand-icon">
                <i class="fas fa-cogs"></i>
            </div>
            <div class="sidebar-brand-text">Panel Admin</div>
        </a>
        <hr class="sidebar-divider">

        <?php $page = isset($_GET['page_admin']) ? $_GET['page_admin'] : ''; ?>

        <div class="nav-item <?= $page == '' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Pesanan</span>
            </a>
        </div>

        <div class="nav-item <?= $page == 'pesanan/data_pesanan' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php?page_admin=penjualan/data_penjualan">
                <i class="fas fa-shopping-cart"></i>
                <span>Data Penjualan</span>
            </a>
        </div>

        <div class="nav-item <?= $page == 'makanan/data_makanan' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php?page_admin=makanan/data_makanan">
                <i class="fas fa-utensils"></i>
                <span>Data Makanan</span>
            </a>
        </div>

        <div class="nav-item <?= $page == 'minuman/data_minuman' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php?page_admin=minuman/data_minuman">
                <i class="fas fa-coffee"></i>
                <span>Data Minuman</span>
            </a>
        </div>

        <div class="nav-item <?= $page == 'pengembalian/pengembalian' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php?page_admin=pelanggan/data_pelanggan">
                <i class="fas fa-users"></i>
                <span>Data Pelanggan</span>
            </a>
        </div>

        <div class="nav-item <?= $page == 'pengembalian/pengembalian' ? 'active' : '' ?>">
            <a class="nav-link" href="index_admin.php?page_admin=administrator/data_administrator">
                <i class="fas fa-users"></i>
                <span>Data Administrator</span>
            </a>
        </div>
    </div>

    <!-- Content Wrapper -->
    <div id="content-wrapper">

        <div class="topbar">
            <button class="sidebar-toggle" id="sidebarToggleTop" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="topbar-title">Sistem Manajemen Data</h1>
            <div class="topbar-right">
                <div class="dropdown">
                    <a class="user-dropdown" id="userDropdown">
                        <span class="username"><?php echo $_SESSION['username']; ?></span>
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="index_admin.php?page_admin=profil/profil_admin">
                            <i class="fas fa-user-circle"></i> Profil Saya
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" onclick="openModal()">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div id="content">
            <div class="container">
                <?php
                if (isset($_GET['page_admin'])) {
                    $halaman = $_GET['page_admin'];
                } else {
                    $halaman = "";
                }

                if ($halaman == "") {
                    include "page_admin/home_admin.php";
                } else if (!file_exists("page_admin/$halaman.php")) {
                    include "page_admin/404.php";
                } else {
                    include "page_admin/$halaman.php";
                }
                ?>
            </div>
        </div>

        <footer class="footer">
            <span>&copy; 2025 Sistem Manajemen Data - Panel Admin</span>
        </footer>
    </div>
</div>

<a class="scroll-to-top" href="#page-top">
    <i class="fas fa-chevron-up"></i>
</a>

<!-- Logout Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h5 class="modal-title">Konfirmasi Logout</h5>
        <div class="modal-body">
            Apakah Anda yakin ingin keluar dari sistem admin?
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" onclick="closeModal()">Batal</button>
            <a class="btn btn-primary" href="logout.php">Ya, Keluar</a>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>