<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/mailer.php";

// Bu dosya cron ile periyodik calistirilabilir.
// Ornek: her 10 dakikada bir kontrol.

$sql = "SELECT
            f.id AS favorite_id,
            f.last_price,
            u.email,
            l.id AS listing_id,
            l.title,
            l.price AS current_price
        FROM favorites f
        INNER JOIN users u ON u.id = f.user_id
        INNER JOIN listings l ON l.id = f.listing_id";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Sorgu hatasi: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    $lastPrice = (float)$row["last_price"];
    $currentPrice = (float)$row["current_price"];

    // Sadece fiyat dusmusse mail gonder.
    if ($currentPrice < $lastPrice) {
        $to = $row["email"];
        $subject = "Fiyat Dustu: " . $row["title"];
        $message = "Merhaba,\n\nTakip ettiginiz ilanda fiyat düstü.\n\n"
            . "Ilan: " . $row["title"] . "\n"
            . "Eski Fiyat: " . number_format($lastPrice, 2) . " TL\n"
            . "Yeni Fiyat: " . number_format($currentPrice, 2) . " TL\n\n"
            . "Ilan Linki: http://localhost/Motor%20alsana/listing.php?id=" . (int)$row["listing_id"] . "\n\n"
            . "iyi gunler.";
        // Kullanicinin kendi e-posta adresine (users.email) gonder.
        // config/mailer.php: Gmail SMTP (ayarliysa) veya fallback mail().
        send_mail($to, $subject, $message);

        // Mail sonrasi takip fiyatini yeni fiyata guncelleriz.
        $updateStmt = mysqli_prepare($conn, "UPDATE favorites SET last_price = ? WHERE id = ?");
        $favoriteId = (int)$row["favorite_id"];
        mysqli_stmt_bind_param($updateStmt, "di", $currentPrice, $favoriteId);
        mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
    }
}

echo "Fiyat kontrolu tamamlandi.";
?>
