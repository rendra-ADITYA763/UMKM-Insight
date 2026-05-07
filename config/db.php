<?php
/**
 * Konfigurasi Database UMKM Insight
 * Menggunakan PDO untuk keamanan dan fleksibilitas
 */

$host = 'localhost';
$db   = 'umkm_insight';
$user = 'root';
$pass = 'root'; // Sesuaikan dengan password MySQL Anda (FlyEnv default: root)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Di lingkungan produksi, jangan tampilkan pesan error detail ke user
    die("Koneksi database gagal: " . $e->getMessage());
}

// Fungsi helper global untuk sanitasi input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
