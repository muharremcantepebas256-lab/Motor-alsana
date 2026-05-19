<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("Gecersiz ilan ID.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, brand, model, model_year, bike_type, mileage_km, engine_cc, horsepower, cylinder_count, color, origin_country, price, description, image_path FROM listings WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$listing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$listing) {
    die("Ilan bulunamadi.");
}

$km = (int)($listing["mileage_km"] ?? 0);
$cc = (int)($listing["engine_cc"] ?? 0);
$hp = (int)($listing["horsepower"] ?? 0);
$yr = (int)($listing["model_year"] ?? 0);
$cylinders = (int)($listing["cylinder_count"] ?? 0);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing["title"]); ?> - Motor Alsana</title>
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
            <?php if (is_logged_in()): ?>
                <a href="my_favorites.php" class="nav-link">Favorilerim</a>
                <a href="logout.php" class="nav-link">Cikis</a>
            <?php else: ?>
                <a href="login.php" class="nav-link nav-link-accent">Giris Yap</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <a href="index.php" class="back-link">&larr; Ilanlara Don</a>

    <div class="detail-card">
        <div class="detail-layout">
            <div class="detail-image-section">
                <?php if (!empty($listing["image_path"])): ?>
                    <img class="detail-image" src="<?php echo htmlspecialchars($listing["image_path"]); ?>" alt="<?php echo htmlspecialchars($listing["title"]); ?>">
                <?php else: ?>
                    <div class="detail-image-placeholder">
                        <span>&#128690;</span>
                        <p>Fotograf yok</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-info-section">
                <h1 class="detail-title"><?php echo htmlspecialchars($listing["title"]); ?></h1>
                <div class="detail-price"><?php echo number_format((float)$listing["price"], 0, ',', '.'); ?> TL</div>

                <div class="detail-specs-grid">
                    <?php if (($listing["brand"] ?? "") !== ""): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Marka</span>
                        <span class="spec-value"><?php echo htmlspecialchars($listing["brand"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (($listing["model"] ?? "") !== ""): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Model</span>
                        <span class="spec-value"><?php echo htmlspecialchars($listing["model"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($yr > 0): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Model Yili</span>
                        <span class="spec-value"><?php echo $yr; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (($listing["bike_type"] ?? "") !== ""): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Motorsiklet Tipi</span>
                        <span class="spec-value"><?php echo htmlspecialchars($listing["bike_type"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-spec">
                        <span class="spec-label">Kilometre</span>
                        <span class="spec-value"><?php echo $km > 0 ? number_format($km) . " km" : "Belirtilmemis"; ?></span>
                    </div>
                    <div class="detail-spec">
                        <span class="spec-label">Silindir Hacmi</span>
                        <span class="spec-value"><?php echo $cc > 0 ? number_format($cc) . " cc" : "Belirtilmemis"; ?></span>
                    </div>
                    <?php if ($hp > 0): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Beygir Gucu</span>
                        <span class="spec-value"><?php echo $hp; ?> HP</span>
                    </div>
                    <?php endif; ?>
                    <?php if ($cylinders > 0): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Silindir Sayisi</span>
                        <span class="spec-value"><?php echo $cylinders; ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (($listing["color"] ?? "") !== ""): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Renk</span>
                        <span class="spec-value"><?php echo htmlspecialchars($listing["color"]); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (($listing["origin_country"] ?? "") !== ""): ?>
                    <div class="detail-spec">
                        <span class="spec-label">Mensei</span>
                        <span class="spec-value"><?php echo htmlspecialchars($listing["origin_country"]); ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="detail-actions">
                    <?php if (is_logged_in()): ?>
                        <form method="POST" action="favorite.php" class="inline-form">
                            <input type="hidden" name="listing_id" value="<?php echo (int)$listing["id"]; ?>">
                            <button type="submit" class="btn btn-primary btn-lg">&#9829; Favoriye Ekle</button>
                        </form>
                    <?php else: ?>
                        <p class="login-prompt">Favoriye eklemek icin <a href="login.php">giris yapin</a>.</p>
                    <?php endif; ?>

                    <a class="btn btn-outline btn-lg" href="mailto:satici@motoralsana.com?subject=Ilan%20Hakkinda%20Bilgi&body=Merhaba,%20<?php echo rawurlencode($listing["title"]); ?>%20ilaniniz%20hakkinda%20bilgi%20alabilir%20miyim?">Iletisime Gec</a>

                    <?php if (is_admin()): ?>
                        <a class="btn btn-outline btn-lg" href="edit_listing.php?id=<?php echo (int)$listing["id"]; ?>">Duzenle</a>
                        <form method="POST" action="delete_listing.php" class="inline-form"
                              onsubmit="return confirm('Bu ilani kalici olarak silmek istediginize emin misiniz?');">
                            <input type="hidden" name="listing_id" value="<?php echo (int)$listing["id"]; ?>">
                            <button type="submit" class="btn btn-danger btn-lg">Sil</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="detail-description">
            <h2>Aciklama</h2>
            <p><?php echo nl2br(htmlspecialchars($listing["description"])); ?></p>
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
