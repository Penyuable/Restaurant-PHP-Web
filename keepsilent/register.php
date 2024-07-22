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
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <link rel="stylesheet" href="log.css">
</head>

<body>
    <div class="login-container">
        <div class="login-title-container">
            <div class="form-container">
                <img class="gb" src="ck2.png" alt="Logo">
                <h2 class="login-title">Register</h2>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    <button type="submit" name="register" class="login-button">Register</button>
                </form>
                <p class="form-link">Kamu sudah punya akun? <a href="index.php">Login disini</a></p>
                <br>
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    if (isset($_POST['register'])) {
                        $username = htmlspecialchars($_POST['username']);
                        $password = htmlspecialchars($_POST['password']);
                        $hashed_password = md5($password);
                
                        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                        $stmt->execute([':username' => $username]);
                        $user_count = $stmt->fetchColumn();
                
                        if ($user_count == 0) {
                            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
                            $stmt->execute([':username' => $username, ':password' => $hashed_password]);
                            echo "Registration successful. <a href='index.php'>Login here</a>";
                        } else {
                            echo "Username already taken.";
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>