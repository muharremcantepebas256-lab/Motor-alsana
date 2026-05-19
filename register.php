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
        // E-mail daha once alinmis mi kontrolu.
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
                $message = "Kayit basarili. Simdi giris yapabilirsiniz.";
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
    <title>Kayit Ol</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
</header>
<div class="container">
    <div class="card">
        <h2>Kayit Ol</h2>
        <?php if (!empty($message)): ?>
            <p class="<?php echo $isError ? "error" : "success"; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Sifre</label>
            <input type="password" name="password" required minlength="6">

            <button type="submit">Kayit Ol</button>
        </form>
    </div>
</div>
</body>
</html>
