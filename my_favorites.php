<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

require_login();

$userId = (int)$_SESSION["user_id"];

$sql = "SELECT l.id, l.title, l.price, l.image_path, f.last_price
        FROM favorites f
        INNER JOIN listings l ON l.id = f.listing_id
        WHERE f.user_id = ?
        ORDER BY f.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$favorites = [];
while ($row = mysqli_fetch_assoc($result)) {
    $favorites[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
    <?php if (is_admin()): ?>
        <a href="add_listing.php">Motor Ekle</a>
    <?php endif; ?>
    <a href="logout.php">Cikis</a>
</header>
<div class="container">
    <h2>Favori Ilanlarim</h2>
    <?php if (empty($favorites)): ?>
        <p>Henuz favori ilaniniz yok. <a href="index.php">Ana sayfadan</a> ilan ekleyebilirsiniz.</p>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($favorites as $row): ?>
            <div class="card">
                <?php if (!empty($row["image_path"])): ?>
                    <img class="listing-image" src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Motor">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                <p>Guncel Fiyat: <span class="price"><?php echo number_format((float)$row["price"], 2); ?> TL</span></p>
                <p>Takip Ettigin Son Fiyat: <?php echo number_format((float)$row["last_price"], 2); ?> TL</p>
                <a class="btn" href="listing.php?id=<?php echo (int)$row["id"]; ?>">Detaya Git</a>
                <form method="POST" action="unfavorite.php">
                    <input type="hidden" name="listing_id" value="<?php echo (int)$row["id"]; ?>">
                    <button type="submit" class="btn btn-secondary">Favorilerden Cikar</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
