<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* Sama persis dengan sebelumnya */
html, body {
  font-family: 'Poppins', sans-serif;
  background: #414177ff;
  margin: 0;
  padding: 0;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.container {
  max-width: 1200px;
  margin: auto;
  flex: 1;
  padding: 20px 0;
}
.table-container {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  min-height: 450px;
}
.scroll-horizontal {
  overflow-x: auto;
}
.table-buku {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
  border-radius: 12px;
  overflow: hidden;
}
.table-buku thead th {
  background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
  color: white;
  font-weight: 600;
  text-align: left;
  padding: 14px 16px;
  white-space: nowrap;
}
.table-buku th,
.table-buku td {
  padding: 12px 16px;
  vertical-align: middle;
  border-bottom: 1px solid #f0f0f0;
  font-size: 0.95rem;
  color: #333;
}
.table-buku tbody tr:hover {
  background: #f7f9fc;
  transition: 0.2s;
}
.table-buku tbody tr:nth-child(even) {
  background: #fafafa;
}
.aksi-btn {
  display: flex;
  gap: 6px;
}
.btn-edit,
.btn-hapus {
  padding: 6px 10px;
  font-size: 0.85rem;
  font-weight: 500;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-edit {
  background: #4CAF50;
  color: white;
}
.btn-edit:hover {
  background: #43a047;
}
.btn-hapus {
  background: #e53935;
  color: white;
}
.btn-hapus:hover {
  background: #d32f2f;
}
.btn-tambah-buku {
  background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
  border: none;
  color: white;
  font-weight: 600;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  text-decoration: none;
  transition: 0.3s;
  font-size: 0.9rem;
}
.btn-tambah-buku:hover {
  background: linear-gradient(135deg, #a75600ff 0%, #ffd000ff 100%);
}
.top-action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}
.top-action-bar h3 {
  margin: 0;
  font-size: 1.4rem;
  color: white;
}
.top-action-bar p {
  color: rgba(255, 255, 255, 0.85);
  font-size: 0.9rem;
  margin: 0;
}
#searchInput {
  margin-bottom: 15px;
  padding: 6px 12px;
  width: 300px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 0.95rem;
}
#pagination {
  margin-top: 15px;
  text-align: center;
}
#pagination button {
  margin-right: 5px;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: white;
  background: #a97f00ff;
}
#pagination button.active {
  background: #f58f00ff;
}
footer {
  background: #00365c;
  color: white;
  text-align: center;
  padding: 15px 0;
}
</style>

<div class="container">
  <div class="top-action-bar">
    <div>
      <h3> Data Admin</h3>
      <p>Lihat dan kelola semua administrator sistem.</p>
    </div>
     <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="index_admin.php?page_admin=administrator/tambah_administrator" class="btn-tambah-buku">
        <i class="fas fa-plus-circle"></i> Tambah Admin
      </a>
    </div>
  </div>

  <!-- Search input -->
  <input type="text" id="searchInput" placeholder="Cari admin berdasarkan nama..." />

 <div class="table-container">
  <div class="scroll-horizontal">
    <table class="table-buku" id="bukuTable">
      <thead>
        <tr>
          <th>No</th>
          <th>Foto</th> 
          <th>Username</th>
          <th>Nama Lengkap</th>
          <th>Email</th>
          <th>No HP</th>
          <th>Bergabung Pada</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        include "config/koneksi.php";
        $no = 1;
        $query = "SELECT * FROM admin ORDER BY nama_lengkap ASC";
        $sql = mysqli_query($koneksi, $query);

        if ($sql && mysqli_num_rows($sql) > 0) {
          while ($row = mysqli_fetch_assoc($sql)) {
           $foto = !empty($row['foto']) && file_exists("upload/admin/".$row['foto']) 
        ? $row['foto'] 
        : "default.jpg";

echo "<tr>
  <td>{$no}</td>
  <td>
    <img src='upload/admin/{$foto}' alt='Foto Admin' 
         style='width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #ddd;'>
  </td>
  <td>{$row['username']}</td>
  <td>{$row['nama_lengkap']}</td>
  <td>{$row['email']}</td>
  <td>{$row['no_hp']}</td>
  <td>{$row['created_at']}</td>
  <td class='aksi-btn'>
    <a href='index_admin.php?page_admin=administrator/edit_administrator&id_admin={$row['id_admin']}' class='btn-edit'>Edit</a>
    <a href='index_admin.php?page_admin=administrator/hapus_administrator&username={$row['username']}' class='btn-hapus' onclick='return confirm(\"Yakin ingin menghapus admin ini?\")'>Hapus</a>
  </td>
</tr>";

            $no++;
          }
        } else {
          echo "<tr><td colspan='9' style='text-align:center; color:#666;'>Tidak ada data admin.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div id="pagination"></div>
</div>

</div>

<script>
const rowsPerPage = 5;
const table = document.getElementById("bukuTable");
const tbody = table.querySelector("tbody");
let rows = Array.from(tbody.querySelectorAll("tr"));
const paginationDiv = document.getElementById("pagination");
let currentPage = 1;

function displayPage(page) {
  currentPage = page;
  const start = (page - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  rows.forEach((row, index) => row.style.display = (index >= start && index < end) ? "" : "none");
  renderPagination();
}

function renderPagination() {
  const totalPages = Math.ceil(rows.length / rowsPerPage);
  paginationDiv.innerHTML = "";
  if (currentPage > 1) {
    const prevBtn = document.createElement("button");
    prevBtn.innerText = "Previous";
    prevBtn.onclick = () => displayPage(currentPage - 1);
    paginationDiv.appendChild(prevBtn);
  }
  for (let i = 1; i <= totalPages; i++) {
    const pageBtn = document.createElement("button");
    pageBtn.innerText = i;
    pageBtn.classList.toggle("active", i === currentPage);
    pageBtn.onclick = () => displayPage(i);
    paginationDiv.appendChild(pageBtn);
  }
  if (currentPage < totalPages) {
    const nextBtn = document.createElement("button");
    nextBtn.innerText = "Next";
    nextBtn.onclick = () => displayPage(currentPage + 1);
    paginationDiv.appendChild(nextBtn);
  }
}

const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", () => {
  const filter = searchInput.value.toLowerCase();
  rows.forEach(row => {
    const nama = row.cells[3].textContent.toLowerCase();
    row.style.display = nama.includes(filter) ? "" : "none";
  });
  rows = Array.from(tbody.querySelectorAll("tr")).filter(row => row.style.display !== "none");
  displayPage(1);
});

displayPage(1);
</script>
