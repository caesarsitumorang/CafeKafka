<?php
$host = "103.163.138.81";
$user = "wacanawe_root";
$pass = "Cv011224!.";
$db   = "wacanawe_cafe-kafka";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>