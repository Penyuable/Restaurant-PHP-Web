<?php
session_start();

if (empty($_SESSION['checkout_summary'])) {
    echo "Rincian pembelian tidak ditemukan.";
    exit();
}

$nama_pembeli = $_SESSION['checkout_nama_pembeli'];
$jumlah_dibayarkan = $_SESSION['checkout_jumlah_dibayarkan'];
$total_harga = $_SESSION['checkout_total_harga'];
$order_summary = $_SESSION['checkout_summary'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Receipt</title>
    <link rel="stylesheet" href="mStyle.css">
</head>

<body>
    <div class="receipt">
        <h1>Struk Pembelian</h1>
        <p>Nama Pembeli: <?php echo htmlspecialchars($nama_pembeli); ?></p>
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
                <?php foreach ($order_summary as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nama']); ?></td>
                    <td>Rp <?php echo number_format($item['harga']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td>Rp <?php echo number_format($item['total']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="total">
            <strong>Total Harga: Rp <?php echo number_format($total_harga); ?></strong><br>
            <strong>Jumlah Dibayarkan: Rp <?php echo number_format($jumlah_dibayarkan); ?></strong>
        </div>
        <form action="clear_checkout.php" method="post">
            <button type="submit" class="button">Kembali ke Menu</button>
        </form>
    </div>
</body>

</html>