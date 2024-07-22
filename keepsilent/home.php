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

$_SESSION['order_summary'] = $_SESSION['order_summary'] ?? [];

function getMenuItems($conn, $kategori = null) {
    $sql = "SELECT * FROM menu";
    if ($kategori) {
        $sql .= " WHERE kategori = :kategori";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':kategori' => $kategori]);
    } else {
        $stmt = $conn->query($sql);
    }
    return $stmt->fetchAll();
}

function updateStock($conn, $idmenu, $quantity) {
    $stmt = $conn->prepare("UPDATE menu SET stok = stok + :quantity WHERE idmenu = :idmenu");
    $stmt->execute([':quantity' => $quantity, ':idmenu' => $idmenu]);
}

function processOrder($conn, $idmenu, $quantityChange) {
    $stmt = $conn->prepare("SELECT * FROM menu WHERE idmenu = :idmenu");
    $stmt->execute([':idmenu' => $idmenu]);
    $item = $stmt->fetch();

    if ($item) {
        if ($item['stok'] >= $quantityChange) {
            if (!isset($_SESSION['order_summary'][$idmenu])) {
                $_SESSION['order_summary'][$idmenu] = [
                    'idmenu' => $idmenu,
                    'nama' => $item['nama'],
                    'harga' => $item['harga'],
                    'quantity' => 0,
                ];
            }

            $_SESSION['order_summary'][$idmenu]['quantity'] += $quantityChange;

            if ($_SESSION['order_summary'][$idmenu]['quantity'] <= 0) {
                unset($_SESSION['order_summary'][$idmenu]);
            }

            updateStock($conn, $idmenu, -$quantityChange);
        } else {
            echo "<script>alert('Stok untuk {$item['nama']} tidak mencukupi.');</script>";
        }
    }
}

function ensureTableExists($conn) {
    $sql = "
        CREATE TABLE IF NOT EXISTS `penjualan` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `idmenu` INT NOT NULL,
            `quantity` INT NOT NULL,
            `total` DECIMAL(10, 2) NOT NULL,
            `tanggal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `nama_pembeli` VARCHAR(100) NOT NULL,
            `jumlah_dibayarkan` DECIMAL(10, 2) NOT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Pending'
        );
    ";
    $conn->exec($sql);
}

function checkout($conn, $nama_pembeli) {
    ensureTableExists($conn);

    $_SESSION['checkout_summary'] = [];

    foreach ($_SESSION['order_summary'] as $idmenu => $item) {
        $_SESSION['checkout_summary'][] = [
            'idmenu' => $idmenu,
            'nama' => $item['nama'],
            'harga' => $item['harga'],
            'quantity' => $item['quantity'],
            'total' => $item['harga'] * $item['quantity']
        ];
    }

    $_SESSION['checkout_nama_pembeli'] = $nama_pembeli;
    $_SESSION['checkout_total_harga'] = array_sum(array_column($_SESSION['checkout_summary'], 'total'));

    unset($_SESSION['order_summary']);

    header("Location: payment.php");
    exit();
}

function clearOrder() {
    unset($_SESSION['order_summary']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['order'])) {
        foreach ($_POST['order'] as $idmenu => $value) {
            processOrder($conn, $idmenu, 1);
        }
    } elseif (isset($_POST['clear_order'])) {
        foreach ($_SESSION['order_summary'] as $idmenu => $item) {
            updateStock($conn, $idmenu, $item['quantity']);
        }
        clearOrder();
    } elseif (isset($_POST['remove_item'])) {
        $idmenu = $_POST['idmenu'];
        updateStock($conn, $idmenu, $_SESSION['order_summary'][$idmenu]['quantity']);
        unset($_SESSION['order_summary'][$idmenu]);
    } elseif (isset($_POST['checkout'])) {
        if (isset($_POST['nama_pembeli']) && !empty($_POST['nama_pembeli'])) {
            $nama_pembeli = htmlspecialchars($_POST['nama_pembeli']);
            checkout($conn, $nama_pembeli);
        } else {
            echo "Nama pembeli harus diisi dengan benar.";
        }
    } elseif (isset($_POST['increase_quantity'])) {
        processOrder($conn, $_POST['idmenu'], 1);
    } elseif (isset($_POST['decrease_quantity'])) {
        processOrder($conn, $_POST['idmenu'], -1);
    }
}

$makanan_items = getMenuItems($conn, 'Makanan');
$minuman_items = getMenuItems($conn, 'Minuman');

$menu_items = getMenuItems($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bubblegum+Sans&display=swap" rel="stylesheet">
    <title>Restoran</title>
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
    <br>
    <br>
    <div class="nav"></div>
    <div class="container">
        <header>
            <div class="hdr">
                <img class="k" src="ck2.png" alt="Logo Restoran">
                <h1 class="txt">Menu That Pure resto</h1>
            </div>
        </header>
        <br>
        <!-- Bagian Makanan -->
        <form action="" method="post">
            <div class="menu-section">
                <div class="menu-item">
                    <h2 class="dp" id="makan">Makanan</h2>
                    <ul class="menu-list">
                        <?php foreach ($makanan_items as $item): ?>
                        <li class="menu-list-item">
                            <div class="menu-content">
                                <img class="big" src="<?php echo htmlspecialchars($item['gambar']); ?>"
                                    alt="<?php echo htmlspecialchars($item['nama']); ?>">
                                <span class="menu-name"><?php echo htmlspecialchars($item['nama']); ?></span>
                                <span class="menu-price">Rp <?php echo number_format($item['harga']); ?></span>
                                <!-- <div class="stock-box">Stok: <?php echo htmlspecialchars($item['stok']); ?></div> -->
                                <?php if ($item['stok'] > 0): ?>
                                <button type="submit" name="order[<?php echo $item['idmenu']; ?>]"
                                    class="button">Pilih</button>
                                <?php else: ?>
                                <button type="submit" name="order[<?php echo $item['idmenu']; ?>]" class="button"
                                    onclick="alert('Stok habis!')">Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </form>

        <!-- Bagian Minuman -->
        <form action="" method="post">
            <div class="menu-section">
                <div class="menu-item">
                    <h2 class="dp">Minuman</h2>
                    <ul class="menu-list">
                        <?php foreach ($minuman_items as $item): ?>
                        <li class="menu-list-item">
                            <div class="menu-content">
                                <img class="big" src="<?php echo htmlspecialchars($item['gambar']); ?>"
                                    alt="<?php echo htmlspecialchars($item['nama']); ?>">
                                <span class="menu-name"><?php echo htmlspecialchars($item['nama']); ?></span>
                                <span class="menu-price">Rp <?php echo number_format($item['harga']); ?></span>
                                <!-- <div class="stock-box">Stok: <?php echo htmlspecialchars($item['stok']); ?></div> -->
                                <?php if ($item['stok'] > 0): ?>
                                <button type="submit" name="order[<?php echo $item['idmenu']; ?>]"
                                    class="button">Pilih</button>
                                <?php else: ?>
                                <button type="submit" name="order[<?php echo $item['idmenu']; ?>]" class="button"
                                    onclick="alert('Stok habis!')">Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </form>

    </div>

    <?php if (!empty($_SESSION['order_summary'])): ?>
    <div class="order-summary">
        <h2>Ringkasan Pesanan</h2>
        <form action="" method="post">
            <table>
                <thead>
                    <tr>
                        <th>Nama Menu</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                                $total_harga = 0;
                                foreach ($_SESSION['order_summary'] as $idmenu => $item):
                                    $total_item = $item['harga'] * $item['quantity'];
                                    $total_harga += $total_item;
                                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nama']); ?></td>
                        <td>Rp <?php echo number_format($item['harga']); ?></td>
                        <td>
                            <div class="qty-buttons">
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="idmenu" value="<?php echo $idmenu; ?>">
                                    <button type="submit" name="decrease_quantity" class="qty-button">-</button>
                                </form>
                                <?php echo $item['quantity']; ?>
                                <form action="" method="post" style="display:inline;">
                                    <input type="hidden" name="idmenu" value="<?php echo $idmenu; ?>">
                                    <button type="submit" name="increase_quantity" class="qty-button">+</button>
                                </form>
                            </div>
                        </td>
                        <td>Rp <?php echo number_format($total_item); ?></td>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="idmenu" value="<?php echo $idmenu; ?>">
                                <button type="submit" name="remove_item" class="button"
                                    style="background-color: red;">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="total">Total Harga: Rp <?php echo number_format($total_harga); ?></div>
            <label for="nama_pembeli">Nama Pembeli:</label>
            <input type="text" name="nama_pembeli" id="nama_pembeli" required><br><br>
            <button type="submit" name="clear_order" class="button" style="background-color: #54486e;">Hapus
                Semua</button><br><br>
            <button type="submit" name="checkout" class="button"
                style="background-color: #54486e; height: 40px;">Bayar</button>
        </form>
    </div>
    <?php endif; ?>

    <hr>
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