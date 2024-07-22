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

function getSalesReport($conn, $startDate = null, $endDate = null) {
    $sql = "SELECT p.idmenu, m.nama, SUM(p.quantity) as total_quantity, SUM(p.total) as total_sales
            FROM penjualan p
            JOIN menu m ON p.idmenu = m.idmenu";

    $params = [];
    if ($startDate && $endDate) {
        $sql .= " WHERE p.tanggal BETWEEN :startDate AND :endDate";
        $params = [':startDate' => $startDate, ':endDate' => $endDate];
    }

    $sql .= " GROUP BY p.idmenu, m.nama ORDER BY total_quantity DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTopMenuItems($conn, $limit = 5) {
    $sql = "SELECT p.idmenu, m.nama, SUM(p.quantity) as total_quantity
            FROM penjualan p
            JOIN menu m ON p.idmenu = m.idmenu
            GROUP BY p.idmenu, m.nama
            ORDER BY total_quantity DESC
            LIMIT :limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <title>Manager - Restoran</title>
    <link rel="stylesheet" href="mStyle.css">
    <style>
    .stock-box {
        border-radius: 8px;
        background-color: aliceblue;
        text-align: center;
        padding: 5px;
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <img src="ck2.png" alt="Logo Restoran" style="width: 40px; height: 40px;">
            <span class="spn">That Pure resto</span>
        </div>
        <ul class="nav-links">
            <li><a href="#makan">Menu</a></li>
            <li><a href="order_summary.php">Order</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="#foot">Contact</a></li>
            <li><a href="index.php?logout=true" class="logout-button">Logout</a></li>
        </ul>
    </div>

    <div class="container">
        <header>
            <div class="hdr">
                <img class="k" src="ck2.png" alt="Logo Restoran">
                <h1 class="txt">Laporan Penjualan</h1>
            </div>
        </header>

        <form action="" method="get">
            <label for="start_date">Tanggal Mulai:</label>
            <input type="date" name="start_date" id="start_date" required>
            <label for="end_date">Tanggal Akhir:</label>
            <input type="date" name="end_date" id="end_date" required>
            <button type="submit" name="filter" class="button">Filter</button>
        </form>

        <?php
        if (isset($_GET['filter'])) {
            $startDate = $_GET['start_date'];
            $endDate = $_GET['end_date'];
            $salesReport = getSalesReport($conn, $startDate, $endDate);
        } else {
            $salesReport = getSalesReport($conn);
        }

        $topMenuItems = getTopMenuItems($conn);
        ?>

        <h2>Penjualan Berdasarkan Tanggal</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Jumlah Terjual</th>
                    <th>Total Penjualan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesReport as $report): ?>
                <tr>
                    <td><?php echo htmlspecialchars($report['nama']); ?></td>
                    <td><?php echo htmlspecialchars($report['total_quantity']); ?></td>
                    <td>Rp <?php echo number_format($report['total_sales']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Top Menu Paling Sering Dipesan</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Jumlah Terjual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topMenuItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nama']); ?></td>
                    <td><?php echo htmlspecialchars($item['total_quantity']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer" id="foot">
        <div class="footer-content">
            <h1 class="ct">
                <a style="color: aliceblue; font-size:20px;" href="about.php">That Pure Resto</a>
            </h1>
        </div>
    </div>
</body>

</html>