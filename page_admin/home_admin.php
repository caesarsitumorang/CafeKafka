<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
html, body {
    font-family: 'Poppins', sans-serif;
    background: #414177ff;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: auto;
    padding: 20px 0;
    color: white;
}

.table-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow-x: auto;
    min-height: 450px;
}

.table-pesanan {
    width: 100%;
    border-collapse: collapse;
}

.table-pesanan th {
      background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
    color: #fff;
    padding: 14px 16px;
    text-align: left;
}

.table-pesanan td {
    color: #333;
    border-bottom: 1px solid #333;
    padding: 12px 16px;
}


.aksi-btn { display:flex; gap:6px; }

.btn-lihat {
    padding:6px 10px;
    font-size:0.85rem;
    font-weight:500;
    border:none;
    border-radius:6px;
    cursor:pointer;
    text-decoration:none;
    color:white;
    background:#4CAF50;
}
.btn-lihat:hover {background:#43a047;}

#searchInput {
    margin:10px 0;
    padding:6px 12px;
    width:300px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:0.95rem;
}

#pagination { margin-top:15px; text-align:center; }
#pagination button {
    margin-right:5px;
    padding:6px 12px;
    border:none;
    border-radius:4px;
    cursor:pointer;
    color:white;
    background:#f97316;
}
#pagination button.active { background:#c2410c; }
</style>

<div class="container">
  <h3>📦 Data Pesanan</h3>
  <p>Lihat semua pesanan pelanggan.</p>

  <input type="text" id="searchInput" placeholder="Cari berdasarkan nama pelanggan..." />

  <div class="table-container">
    <table class="table-pesanan" id="pesananTable">
      <thead>
        <tr>
          <th>No</th>a
          <th>Nama Pelanggan</th>
          <th>Pesanan</th>
          <th>Aksi</th>
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
        <td>{$no}</td>
        <td>{$row['nama_lengkap']}</td>
        <td>{$pesanan_text}</td>
        <td class='aksi-btn'>
            <a href='index_admin.php?page_admin=detail_pesanan&id={$row['id']}' class='btn-lihat'>Lihat</a>
        </td>
    </tr>";
    $no++;
}

        } else {
            echo "<tr><td colspan='4' style='text-align:center; color:#ccc;'>Tidak ada pesanan.</td></tr>";
        }
        ?>
      </tbody>
    </table>

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
  if(currentPage>1){
    const prevBtn = document.createElement("button");
    prevBtn.innerText="Previous";
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
    nextBtn.innerText="Next";
    nextBtn.onclick=()=>displayPage(currentPage+1);
    paginationDiv.appendChild(nextBtn);
  }
}

const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", ()=>{
  const filter = searchInput.value.toLowerCase();
  rows.forEach(row=>{
    const nama = row.cells[1].textContent.toLowerCase();
    row.style.display = nama.includes(filter) ? "" : "none";
  });
  rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.style.display !== "none");
  displayPage(1);
});

displayPage(1);
</script>
