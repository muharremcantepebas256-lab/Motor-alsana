<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my_favorites.php");
    exit;
}

$listingId = filter_input(INPUT_POST, "listing_id", FILTER_VALIDATE_INT);
$userId = (int)$_SESSION["user_id"];

if (!$listingId) {
    die("Gecersiz ilan.");
}

$delStmt = mysqli_prepare($conn, "DELETE FROM favorites WHERE user_id = ? AND listing_id = ?");
mysqli_stmt_bind_param($delStmt, "ii", $userId, $listingId);
mysqli_stmt_execute($delStmt);
mysqli_stmt_close($delStmt);

header("Location: my_favorites.php");
exit;
