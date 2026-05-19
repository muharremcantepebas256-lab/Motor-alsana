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
    <title>Giris Yap</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
</header>
<div class="container">
    <div class="card">
        <h2>Giris Yap</h2>
        <?php if (!empty($message)): ?>
            <p class="<?php echo $isError ? "error" : "success"; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Sifre</label>
            <input type="password" name="password" required>

            <button type="submit">Giris Yap</button>
        </form>
    </div>
</div>
</body>
</html>
