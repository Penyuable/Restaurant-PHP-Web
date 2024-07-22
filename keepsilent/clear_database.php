<?php
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

function clearDatabase($conn) {
    try {

        $conn->beginTransaction();

        $sql = "TRUNCATE TABLE penjualan";
        $conn->exec($sql);

        $conn->commit();

        echo "Database successfully cleared.";
    } catch (PDOException $e) {
 
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "Error clearing database: " . $e->getMessage();
    }
}

clearDatabase($conn);
?>