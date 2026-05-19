<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

require_login();

$userId = (int)$_SESSION["user_id"];

$sql = "SELECT l.id, l.title, l.brand, l.model, l.price, l.image_path, l.bike_type, f.last_price
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
    <title>Favorilerim - Motor Alsana</title>
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
            <?php if (is_admin()): ?>
                <a href="add_listing.php" class="nav-link">Motor Ekle</a>
            <?php endif; ?>
            <a href="my_favorites.php" class="nav-link active">Favorilerim</a>
            <a href="logout.php" class="nav-link">Cikis</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="content-header">
        <h1>Favori Ilanlarim</h1>
        <p class="results-count"><?php echo count($favorites); ?> favori ilan</p>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="empty-state">
            <span class="empty-icon">&#9829;</span>
            <p>Henuz favori ilaniniz yok.</p>
            <a href="index.php" class="btn btn-primary">Ilanlara Goz At</a>
        </div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($favorites as $row):
            $currentPrice = (float)$row["price"];
            $lastPrice = (float)$row["last_price"];
            $priceDiff = $currentPrice - $lastPrice;
        ?>
            <div class="card listing-card">
                <div class="card-image-wrapper">
                    <?php if (!empty($row["image_path"])): ?>
                        <img class="card-image" src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>" loading="lazy">
                    <?php else: ?>
                        <div class="card-image-placeholder">
                            <span>&#128690;</span>
                        </div>
                    <?php endif; ?>
                    <?php if (($row["bike_type"] ?? "") !== ""): ?>
                        <span class="card-badge"><?php echo htmlspecialchars($row["bike_type"]); ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><?php echo htmlspecialchars($row["title"]); ?></h3>
                    <?php if (($row["brand"] ?? "") !== ""): ?>
                        <p class="card-meta"><?php echo htmlspecialchars($row["brand"]); ?> <?php echo htmlspecialchars($row["model"] ?? ""); ?></p>
                    <?php endif; ?>
                    <div class="card-price"><?php echo number_format($currentPrice, 0, ',', '.'); ?> TL</div>
                    <div class="price-tracking">
                        <span class="tracking-label">Takip Fiyati:</span>
                        <span class="tracking-value"><?php echo number_format($lastPrice, 0, ',', '.'); ?> TL</span>
                        <?php if ($priceDiff < 0): ?>
                            <span class="price-change price-down">&#9660; <?php echo number_format(abs($priceDiff), 0, ',', '.'); ?> TL</span>
                        <?php elseif ($priceDiff > 0): ?>
                            <span class="price-change price-up">&#9650; <?php echo number_format($priceDiff, 0, ',', '.'); ?> TL</span>
                        <?php else: ?>
                            <span class="price-change price-same">Degismedi</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-actions">
                        <a class="btn btn-primary btn-sm" href="listing.php?id=<?php echo (int)$row["id"]; ?>">Detay</a>
                        <form method="POST" action="unfavorite.php" class="inline-form">
                            <input type="hidden" name="listing_id" value="<?php echo (int)$row["id"]; ?>">
                            <button type="submit" class="btn btn-outline btn-sm">Cikar</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
