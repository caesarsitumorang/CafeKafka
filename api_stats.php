<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'cafe_kafka';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "
        SELECT id, nama, deskripsi, harga, kategori, stok, gambar, 'makanan' as table_source 
        FROM makanan 
        WHERE stok > 0
        UNION ALL
        SELECT id, nama, deskripsi, harga, kategori, stok, gambar, 'minuman' as table_source 
        FROM minuman 
        WHERE stok > 0
        ORDER BY kategori, nama
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [];
    foreach ($menuItems as $item) {
        $response[] = [
            'id' => $item['id'],
            'nama' => $item['nama'],
            'deskripsi' => $item['deskripsi'],
            'harga' => (int)$item['harga'],
            'harga_formatted' => 'Rp ' . number_format($item['harga'], 0, ',', '.'),
            'kategori' => $item['kategori'],
            'stok' => (int)$item['stok'],
            'gambar' => $item['gambar'],
            'table_source' => $item['table_source']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>