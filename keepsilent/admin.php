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

function getPendingOrders($conn) {
    $stmt = $conn->query("SHOW COLUMNS FROM penjualan LIKE 'status'");
    $statusExists = $stmt->fetch();

    if ($statusExists) {
        $stmt = $conn->query("SELECT * FROM penjualan WHERE status = 'Pending'");
        return $stmt->fetchAll();
    } else {
        throw new Exception("Kolom 'status' tidak ditemukan di tabel 'penjualan'.");
    }
}

function processOrderByName($conn, $nama_pembeli) {
    $stmt = $conn->prepare("UPDATE penjualan SET status = 'Processed' WHERE nama_pembeli = :nama_pembeli AND status = 'Pending'");
    $stmt->execute([':nama_pembeli' => $nama_pembeli]);
    $_SESSION['success_message'] = "Pesanan Berhasil Diproses!";
}

function cancelOrderByName($conn, $nama_pembeli) {
    // Kembalikan stok untuk item yang dibatalkan
    $stmt = $conn->prepare("SELECT idmenu, quantity FROM penjualan WHERE nama_pembeli = :nama_pembeli AND status = 'Pending'");
    $stmt->execute([':nama_pembeli' => $nama_pembeli]);
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        $stmtUpdateStock = $conn->prepare("UPDATE menu SET stok = stok + :quantity WHERE idmenu = :idmenu");
        $stmtUpdateStock->execute([':quantity' => $item['quantity'], ':idmenu' => $item['idmenu']]);
    }

    // Perbarui status pesanan menjadi 'Cancelled'
    $stmt = $conn->prepare("UPDATE penjualan SET status = 'Cancelled' WHERE nama_pembeli = :nama_pembeli AND status = 'Pending'");
    $stmt->execute([':nama_pembeli' => $nama_pembeli]);
    $_SESSION['success_message'] = "Pesanan Berhasil Dibatalkan!";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['process_order'])) {
        $nama_pembeli = $_POST['nama_pembeli'];
        try {
            processOrderByName($conn, $nama_pembeli);
        } catch (PDOException $e) {
            echo "Gagal memproses pesanan: " . $e->getMessage();
        }
    } elseif (isset($_POST['cancel_order'])) {
        $nama_pembeli = $_POST['nama_pembeli'];
        try {
            cancelOrderByName($conn, $nama_pembeli);
        } catch (PDOException $e) {
            echo "Gagal membatalkan pesanan: " . $e->getMessage();
        }
    }
}

try {
    $pendingOrders = getPendingOrders($conn);
} catch (Exception $e) {
    echo "Kesalahan: " . $e->getMessage();
    $pendingOrders = [];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin - Pesanan Masuk</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <h1>Pesanan Masuk</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-message">
        <?php echo $_SESSION['success_message']; ?>
        <?php unset($_SESSION['success_message']); ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Pembeli</th>
                <th>Total</th>
                <th>Jumlah Dibayarkan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pendingOrders)): ?>
            <tr>
                <td colspan="6">Tidak ada pesanan pending.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($pendingOrders as $order): ?>
            <tr class="tabel">
                <td><?php echo htmlspecialchars($order['id']); ?></td>
                <td><?php echo htmlspecialchars($order['nama_pembeli']); ?></td>
                <td>Rp <?php echo number_format($order['total']); ?></td>
                <td>Rp <?php echo number_format($order['jumlah_dibayarkan']); ?></td>
                <td><?php echo htmlspecialchars($order['status']); ?></td>
                <td>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="nama_pembeli"
                            value="<?php echo htmlspecialchars($order['nama_pembeli']); ?>">
                        <button type="submit" name="process_order" class="button">Proses</button>
                    </form>
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="nama_pembeli"
                            value="<?php echo htmlspecialchars($order['nama_pembeli']); ?>">
                        <button type="submit" name="cancel_order" class="button"
                            style="background-color: red;">Batal</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <form action="index.php" method="get">
        <button type="submit" name="logout" class="logout-button">Logout</button>
    </form>
</body>

</html>