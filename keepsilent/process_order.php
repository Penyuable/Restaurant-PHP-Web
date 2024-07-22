<?php
session_start();
$dsn = 'mysql:host=localhost;dbname=warung';
$user = 'root';
$pass = '';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['process_order'])) {
    if (!isset($_SESSION['checkout_summary']) || empty($_SESSION['checkout_summary'])) {
        die("Rincian pembelian tidak ditemukan.");
    }

    $nama_pembeli = $_SESSION['checkout_nama_pembeli'];
    $total_harga = $_SESSION['checkout_total_harga'];

    // Insert order into penjualan table
    $stmt = $conn->prepare("INSERT INTO penjualan (nama_pembeli, total_harga, status) VALUES (:nama_pembeli, :total_harga, 'Pending')");
    $stmt->execute([
        ':nama_pembeli' => $nama_pembeli,
        ':total_harga' => $total_harga
    ]);

    // Clear session data
    unset($_SESSION['checkout_summary']);
    unset($_SESSION['checkout_nama_pembeli']);
    unset($_SESSION['checkout_total_harga']);

    // Redirect to success page or show a success message
    header("Location: success.php");
    exit();
} else {
    die("Invalid request.");
}
?>