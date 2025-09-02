<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
html, body {
    font-family: 'Poppins', sans-serif;
    background: #414177ff;
    margin: 0;
    padding: 0;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: auto;
    padding: 30px 20px;
    color: white;
}

/* Header Section */
.page-header {
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    padding: 30px;
    border-radius: 16px;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.page-header h3 {
    font-size: 2rem;
    font-weight: 600;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
}

.page-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
    font-weight: 300;
}

/* Search Section */
.search-section {
    background: rgba(255,255,255,0.05);
    padding: 20px 30px;
    border-radius: 12px;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

#searchInput {
    width: 100%;
    max-width: 400px;
    padding: 12px 20px;
    padding-left: 50px;
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 25px;
    background: rgba(255,255,255,0.1);
    color: white;
    font-size: 1rem;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

#searchInput::placeholder {
    color: rgba(255,255,255,0.7);
}

#searchInput:focus {
    outline: none;
    border-color: #ff8c42;
    background: rgba(255,255,255,0.15);
    box-shadow: 0 0 20px rgba(255, 140, 66, 0.3);
}

.search-wrapper {
    position: relative;
    display: inline-block;
}

.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.7);
    font-size: 1.1rem;
}

/* Table Container */
.table-container {
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 0;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    overflow: hidden;
    min-height: 500px;
    border: 1px solid rgba(255,255,255,0.2);
}

.table-header {
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    padding: 25px 30px;
    color: white;
    border-bottom: none;
}

.table-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-content {
    padding: 0;
    overflow-x: auto;
}

.table-pesanan {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.table-pesanan th {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    color: #fff;
    padding: 18px 24px;
    text-align: left;
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    position: sticky;
    top: 0;
    z-index: 10;
}

.table-pesanan th:first-child {
    border-top-left-radius: 0;
}

.table-pesanan th:last-child {
    border-top-right-radius: 0;
}

.table-pesanan td {
    color: #2d3748;
    border-bottom: 1px solid #e2e8f0;
    padding: 20px 24px;
    font-size: 0.95rem;
    transition: background-color 0.2s ease;
    vertical-align: middle;
}

.table-pesanan tbody tr {
    transition: all 0.2s ease;
}

.table-pesanan tbody tr:hover {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.table-pesanan tbody tr:nth-child(even) {
    background: rgba(247, 250, 252, 0.5);
}

.table-pesanan tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
}

/* Order Items Styling */
.order-items {
    color: #4a5568;
    font-weight: 500;
    line-height: 1.4;
}

.order-items:empty::after {
    content: "Tidak ada pesanan";
    color: #a0aec0;
    font-style: italic;
}

/* Action Buttons */
.aksi-btn { 
    display: flex; 
    gap: 8px; 
    align-items: center;
}

.btn-lihat {
    padding: 10px 16px;
    font-size: 0.9rem;
    font-weight: 500;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    color: white;
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
}

.btn-lihat:hover {
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
}

.btn-lihat i {
    font-size: 0.85rem;
}

/* Pagination */
#pagination { 
    margin-top: 30px; 
    text-align: center; 
    padding: 20px 30px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

#pagination button {
    margin: 0 4px;
    padding: 10px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    color: #4a5568;
    background: white;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    min-width: 44px;
}

#pagination button:hover {
    border-color: #ff8c42;
    background: #ff8c42;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 140, 66, 0.3);
}

#pagination button.active { 
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    border-color: #ff6b35;
    color: white;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
}

/* Empty State */
.empty-state {
    text-align: center;
    color: #a0aec0;
    font-style: italic;
    padding: 40px;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* Number Badge */
.number-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    min-width: 32px;
    text-align: center;
    display: inline-block;
}

/* Customer Name */
.customer-name {
    font-weight: 600;
    color: #2d3748;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 20px 15px;
    }
    
    .page-header {
        padding: 20px;
        text-align: center;
    }
    
    .page-header h3 {
        font-size: 1.5rem;
    }
    
    .search-section {
        padding: 15px 20px;
    }
    
    #searchInput {
        max-width: 100%;
    }
    
    .table-pesanan th,
    .table-pesanan td {
        padding: 12px 16px;
        font-size: 0.85rem;
    }
    
    .btn-lihat {
        padding: 8px 12px;
        font-size: 0.8rem;
    }
    
    #pagination {
        padding: 15px 20px;
    }
    
    #pagination button {
        padding: 8px 12px;
        margin: 0 2px;
        font-size: 0.85rem;
    }
}
</style>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h3><i class="fas fa-clipboard-list"></i> Data Pesanan</h3>
        <p>Kelola dan pantau semua pesanan pelanggan secara real-time</p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Cari berdasarkan nama pelanggan..." />
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div class="table-header">
            <h4><i class="fas fa-list-ul"></i> Daftar Pesanan</h4>
        </div>
        
        <div class="table-content">
            <table class="table-pesanan" id="pesananTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> No</th>
                        <th><i class="fas fa-user"></i> Nama Pelanggan</th>
                        <th><i class="fas fa-utensils"></i> Pesanan</th>
                        <th><i class="fas fa-cogs"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    include "config/koneksi.php";
                    $query = "
                        SELECT 
                            p.id, 
                            p.id_pelanggan, 
                            pl.nama_lengkap,
                            GROUP_CONCAT(DISTINCT m.nama SEPARATOR ', ') AS makanan,
                            GROUP_CONCAT(DISTINCT mn.nama SEPARATOR ', ') AS minuman
                        FROM pesanan p
                        LEFT JOIN pelanggan pl ON p.id_pelanggan = pl.id_pelanggan
                        LEFT JOIN pesanan_detail pd ON p.id = pd.id_pesanan
                        LEFT JOIN makanan m ON pd.id_makanan = m.id
                        LEFT JOIN minuman mn ON pd.id_minuman = mn.id
                        WHERE p.status IN ('pending','diterima','diproses')
                        GROUP BY p.id, pl.nama_lengkap
                        ORDER BY p.id DESC
                    ";

                    $sql = mysqli_query($koneksi,$query);
                    $no = 1;
                    if($sql && mysqli_num_rows($sql) > 0){
                        while($row = mysqli_fetch_assoc($sql)){
                            $pesanan = [];
                            if (!empty($row['makanan'])) $pesanan[] = $row['makanan'];
                            if (!empty($row['minuman'])) $pesanan[] = $row['minuman'];
                            $pesanan_text = implode(', ', $pesanan) ?: "Kosong";

                            echo "<tr>
                                <td><span class='number-badge'>{$no}</span></td>
                                <td><span class='customer-name'>{$row['nama_lengkap']}</span></td>
                                <td><span class='order-items'>{$pesanan_text}</span></td>
                                <td class='aksi-btn'>
                                    <a href='index_admin.php?page_admin=detail_pesanan&id={$row['id']}' class='btn-lihat'>
                                        <i class='fas fa-eye'></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='4' class='empty-state'>
                            <i class='fas fa-inbox'></i><br>
                            Tidak ada pesanan saat ini
                        </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="pagination"></div>
    </div>
</div>

<script>
const rowsPerPage = 5;
const table = document.getElementById("pesananTable");
const tbody = table.querySelector("tbody");
let rows = Array.from(tbody.querySelectorAll("tr"));
const paginationDiv = document.getElementById("pagination");
let currentPage = 1;

function displayPage(page){
    currentPage = page;
    const start = (page-1)*rowsPerPage;
    const end = start+rowsPerPage;
    rows.forEach((row,index)=> row.style.display = (index>=start && index<end) ? "" : "none");
    renderPagination();
}

function renderPagination(){
    const totalPages = Math.ceil(rows.length/rowsPerPage);
    paginationDiv.innerHTML="";
    
    if(totalPages <= 1) return;
    
    if(currentPage>1){
        const prevBtn = document.createElement("button");
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Previous';
        prevBtn.onclick=()=>displayPage(currentPage-1);
        paginationDiv.appendChild(prevBtn);
    }
    
    for(let i=1;i<=totalPages;i++){
        const pageBtn=document.createElement("button");
        pageBtn.innerText=i;
        pageBtn.classList.toggle("active",i===currentPage);
        pageBtn.onclick=()=>displayPage(i);
        paginationDiv.appendChild(pageBtn);
    }
    
    if(currentPage<totalPages){
        const nextBtn=document.createElement("button");
        nextBtn.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
        nextBtn.onclick=()=>displayPage(currentPage+1);
        paginationDiv.appendChild(nextBtn);
    }
}

const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", ()=>{
    const filter = searchInput.value.toLowerCase();
    const allRows = Array.from(tbody.querySelectorAll("tr"));
    
    allRows.forEach(row=>{
        const nama = row.cells[1].textContent.toLowerCase();
        row.style.display = nama.includes(filter) ? "" : "none";
    });
    
    rows = allRows.filter(row => row.style.display !== "none");
    displayPage(1);
});

// Initialize
displayPage(1);

// Add smooth scroll effect for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>