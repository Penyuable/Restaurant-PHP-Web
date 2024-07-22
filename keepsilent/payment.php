<?php
session_start();

if (!isset($_SESSION['checkout_summary']) || !isset($_SESSION['checkout_nama_pembeli']) || !isset($_SESSION['checkout_total_harga'])) {
    header("Location: index.php");
    exit();
}

$checkout_summary = $_SESSION['checkout_summary'];
$nama_pembeli = $_SESSION['checkout_nama_pembeli'];
$total_harga = $_SESSION['checkout_total_harga'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Jumlah dibayarkan adalah sama dengan total harga
    $jumlah_dibayarkan = $total_harga;

    // Store the order in the database
    $dsn = 'mysql:host=localhost;dbname=warung';
    $user = 'root';
    $pass = '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $conn = new PDO($dsn, $user, $pass, $options);

        foreach ($checkout_summary as $item) {
            $stmt = $conn->prepare("INSERT INTO penjualan (idmenu, quantity, total, nama_pembeli, jumlah_dibayarkan) VALUES (:idmenu, :quantity, :total, :nama_pembeli, :jumlah_dibayarkan)");
            $stmt->execute([
                ':idmenu' => $item['idmenu'],
                ':quantity' => $item['quantity'],
                ':total' => $item['total'],
                ':nama_pembeli' => $nama_pembeli,
                ':jumlah_dibayarkan' => $jumlah_dibayarkan
            ]);
        }

        unset($_SESSION['checkout_summary']);
        unset($_SESSION['checkout_nama_pembeli']);
        unset($_SESSION['checkout_total_harga']);

        session_destroy();
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mStyle.css">
    <title>Pembayaran</title>
</head>

<body>
    <div class="container">
        <h1>Proses Pembayaran</h1>
        <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checkout_summary as $item): ?>
                <tr class="row">
                    <td><?php echo htmlspecialchars($item['nama']); ?></td>
                    <td>Rp <?php echo number_format($item['harga']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>Rp <?php echo number_format($item['total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">Total Harga: Rp <?php echo number_format($total_harga); ?></div>
        <form action="" method="post">
            <button type="submit" class="button">Bayar</button>
        </form>
    </div>
</body>

</html>