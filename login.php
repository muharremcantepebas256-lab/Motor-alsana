<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

$message = "";
$isError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $password = $_POST["password"] ?? "";

    if (!$email || $password === "") {
        $message = "Email ve sifre zorunludur.";
        $isError = true;
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = (int)$user["id"];
            $_SESSION["user_email"] = $email;
            header("Location: index.php");
            exit;
        } else {
            $message = "Email veya sifre hatali.";
            $isError = true;
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giris Yap - Motor Alsana</title>
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
            <a href="register.php" class="nav-link">Kayit Ol</a>
            <a href="login.php" class="nav-link active nav-link-accent">Giris Yap</a>
        </div>
    </div>
</nav>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <span class="auth-icon">&#128274;</span>
            <h2>Giris Yap</h2>
            <p>Hesabiniza giris yapin</p>
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
                <input id="password" type="password" name="password" required placeholder="Sifrenizi girin">
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Giris Yap</button>
        </form>
        <div class="auth-footer">
            <p>Hesabiniz yok mu? <a href="register.php">Kayit Ol</a></p>
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
