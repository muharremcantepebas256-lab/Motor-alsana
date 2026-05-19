<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

$message = "";
$isError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $password = $_POST["password"] ?? "";

    if (!$email || strlen($password) < 6) {
        $message = "Gecerli bir email girin ve sifre en az 6 karakter olsun.";
        $isError = true;
    } else {
        $checkStmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkStmt, "s", $email);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);

        if (mysqli_stmt_num_rows($checkStmt) > 0) {
            $message = "Bu email zaten kayitli.";
            $isError = true;
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $insertStmt = mysqli_prepare($conn, "INSERT INTO users (email, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($insertStmt, "ss", $email, $hashedPassword);

            if (mysqli_stmt_execute($insertStmt)) {
                $message = "Kayit basarili! Simdi giris yapabilirsiniz.";
            } else {
                $message = "Kayit sirasinda bir hata olustu.";
                $isError = true;
            }
            mysqli_stmt_close($insertStmt);
        }
        mysqli_stmt_close($checkStmt);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayit Ol - Motor Alsana</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-inner">
        <a href="index.php" class="navbar-brand">
            <span class="brand-icon">&#9881;</span> Motor Alsana
        </a>
        <button class="navbar-toggle" id="navToggle" aria-label="Menu">&#9776;</button>
        <div class="navbar-links" id="navLinks">
            <a href="index.php" class="nav-link">Ana Sayfa</a>
            <a href="register.php" class="nav-link active">Kayit Ol</a>
            <a href="login.php" class="nav-link nav-link-accent">Giris Yap</a>
        </div>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">&#128100;</span>
            <h2>Kayit Ol</h2>
            <p>Yeni hesap olusturun</p>
        </div>
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $isError ? "alert-error" : "alert-success"; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" required placeholder="ornek@email.com">
            </div>
            <div class="form-group">
                <label for="password">Sifre</label>
                <input id="password" type="password" name="password" required minlength="6" placeholder="En az 6 karakter">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Kayit Ol</button>
        </form>
        <div class="auth-footer">
            <p>Zaten hesabiniz var mi? <a href="login.php">Giris Yap</a></p>
        </div>
    </div>
</div>

<script>
(function () {
    var navToggle = document.getElementById("navToggle");
    var navLinks = document.getElementById("navLinks");
    if (navToggle && navLinks) {
        navToggle.addEventListener("click", function () {
            navLinks.classList.toggle("open");
        });
    }
})();
</script>
</body>
</html>
