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

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $hashed_password = md5($password);

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->execute([':username' => $username, ':password' => $hashed_password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } elseif ($user['role'] == 'manager') {
                header("Location: report.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $error_message = "Username atau password salah!!!.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <link rel="stylesheet" href="log.css">
</head>

<body>
    <div class="login-container">
        <div class="login-title-container">
            <img class="gb" src="ck2.png" alt="Logo">
            <h2 class="login-title">Login</h2>
        </div>
        <form action="" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" name="login" class="login-button">Login</button>
        </form>
        <p class="register-link">belum punya akun? <a href="register.php">Register disini</a></p>
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
</body>

</html>