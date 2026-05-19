<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$listingId = filter_input(INPUT_POST, "listing_id", FILTER_VALIDATE_INT);
if (!$listingId) {
    die("Gecersiz ilan.");
}

$stmt = mysqli_prepare($conn, "SELECT image_path FROM listings WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $listingId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: index.php");
    exit;
}

$imagePath = (string)($row["image_path"] ?? "");
if ($imagePath !== "" && strpos($imagePath, "..") === false && strpos($imagePath, "uploads/") === 0) {
    $full = __DIR__ . "/" . $imagePath;
    if (is_file($full)) {
        @unlink($full);
    }
}

$del = mysqli_prepare($conn, "DELETE FROM listings WHERE id = ?");
mysqli_stmt_bind_param($del, "i", $listingId);
mysqli_stmt_execute($del);
mysqli_stmt_close($del);

header("Location: index.php");
exit;
