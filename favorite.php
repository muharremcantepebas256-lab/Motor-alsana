<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$listingId = filter_input(INPUT_POST, "listing_id", FILTER_VALIDATE_INT);
$userId = (int)$_SESSION["user_id"];

if (!$listingId) {
    die("Gecersiz ilan.");
}

// Ilanin guncel fiyatini cekip, favoriye o fiyatla kaydederiz.
$listingStmt = mysqli_prepare($conn, "SELECT price FROM listings WHERE id = ?");
mysqli_stmt_bind_param($listingStmt, "i", $listingId);
mysqli_stmt_execute($listingStmt);
$listingResult = mysqli_stmt_get_result($listingStmt);
$listing = mysqli_fetch_assoc($listingResult);
mysqli_stmt_close($listingStmt);

if (!$listing) {
    die("Ilan bulunamadi.");
}

$currentPrice = (float)$listing["price"];

// Ayni kullanici ayni ilani tekrar favorilerse last_price guncellensin.
$favStmt = mysqli_prepare($conn, "INSERT INTO favorites (user_id, listing_id, last_price) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE last_price = VALUES(last_price)");
mysqli_stmt_bind_param($favStmt, "iid", $userId, $listingId, $currentPrice);
mysqli_stmt_execute($favStmt);
mysqli_stmt_close($favStmt);

header("Location: my_favorites.php");
exit;
?>
