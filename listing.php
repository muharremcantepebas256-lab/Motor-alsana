<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("Gecersiz ilan ID.");
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, model, bike_type, mileage_km, engine_cc, price, description, image_path FROM listings WHERE id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$listing = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$listing) {
    die("ilan bulunamadi.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($listing["title"]); ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
    <?php if (is_admin()): ?>
        <a href="add_listing.php">Motor Ekle</a>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <a href="my_favorites.php">Favorilerim</a>
        <a href="logout.php">Cikis</a>
    <?php else: ?>
        <a href="login.php">Giris Yap</a>
    <?php endif; ?>
</header>

<div class="container">
    <div class="card">
        <?php if (!empty($listing["image_path"])): ?>
            <img class="listing-image" src="<?php echo htmlspecialchars($listing["image_path"]); ?>" alt="Motor Fotografi">
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($listing["title"]); ?></h1>
        <?php if (($listing["model"] ?? "") !== ""): ?>
            <p class="listing-meta"><strong>Model:</strong> <?php echo htmlspecialchars($listing["model"]); ?></p>
        <?php endif; ?>
        <?php if (($listing["bike_type"] ?? "") !== ""): ?>
            <p class="listing-meta"><strong>Tur:</strong> <?php echo htmlspecialchars($listing["bike_type"]); ?></p>
        <?php endif; ?>
        <?php
        $km = (int)($listing["mileage_km"] ?? 0);
        $cc = (int)($listing["engine_cc"] ?? 0);
        ?>
        <p class="listing-meta"><strong>Kilometre:</strong> <?php echo $km > 0 ? number_format($km) . " km" : "Belirtilmemis"; ?></p>
        <p class="listing-meta"><strong>Silindir hacmi:</strong> <?php echo $cc > 0 ? number_format($cc) . " cc" : "Belirtilmemis"; ?></p>
        <p class="price"><?php echo number_format((float)$listing["price"], 2); ?> TL</p>
        <p><?php echo nl2br(htmlspecialchars($listing["description"])); ?></p>

        <?php if (is_logged_in()): ?>
            <form method="POST" action="favorite.php">
                <input type="hidden" name="listing_id" value="<?php echo (int)$listing["id"]; ?>">
                <button type="submit">Favoriye Ekle</button>
            </form>
        <?php else: ?>
            <p>Favoriye eklemek icin once giris yapin.</p>
        <?php endif; ?>

        <?php if (is_admin()): ?>
            <div style="margin-top: 12px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <a class="btn" href="edit_listing.php?id=<?php echo (int)$listing["id"]; ?>">Düzenle</a>
                <form method="POST" action="delete_listing.php" style="margin: 0;"
                      onsubmit="return confirm('Bu ilani kalici olarak silmek istediginize emin misiniz?');">
                    <input type="hidden" name="listing_id" value="<?php echo (int)$listing["id"]; ?>">
                    <button type="submit" class="btn btn-danger">Sil</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Basit iletisim butonu (mail uygulamasi acar). -->
        <a class="btn btn-secondary" href="mailto:satici@motoralsana.com?subject=Ilan%20Hakkinda%20Bilgi&body=Merhaba,%20<?php echo rawurlencode($listing["title"]); ?>%20ilaniniz%20hakkinda%20bilgi%20alabilir%20miyim?">Iletisime Gec</a>
    </div>
</div>
</body>
</html>
