<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/motor_types.php";

// Bu sayfayi sadece giris yapmis kullanici kullanabilir.
require_login();

$message = "";
$isError = false;

$isAdmin = is_admin();
if (!$isAdmin) {
    $message = "Bu sayfaya yalnizca admin motor ilani ekleyebilir.";
    $isError = true;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $isAdmin) {
    $title = trim($_POST["title"] ?? "");
    $model = trim($_POST["model"] ?? "");
    $bikeType = trim($_POST["bike_type"] ?? "");
    $mileageRaw = trim($_POST["mileage_km"] ?? "");
    $engineRaw = trim($_POST["engine_cc"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $imagePath = "";

    $mileageKm = $mileageRaw === "" ? 0 : filter_var($mileageRaw, FILTER_VALIDATE_INT);
    $engineCc = $engineRaw === "" ? 0 : filter_var($engineRaw, FILTER_VALIDATE_INT);

    if ($mileageKm === false || (int)$mileageKm < 0) {
        $mileageKm = -1;
    }
    if ($engineCc === false || (int)$engineCc < 0) {
        $engineCc = -1;
    }

    if ($title === "" || $model === "" || $bikeType === "" || !is_valid_motor_type($bikeType)
        || !is_numeric($price) || (float)$price < 0 || $description === ""
        || (int)$mileageKm < 0 || (int)$engineCc < 0) {
        $message = "Baslik, model, tur, fiyat ve aciklama zorunludur; kilometre ve silindir bos veya gecerli bir sayi olmalidir.";
        $isError = true;
    } else {
        // Fotograf yukleme kontrolu.
        if (!empty($_FILES["image"]["name"])) {
            $allowedTypes = ["image/jpeg", "image/png", "image/webp"];
            $fileType = mime_content_type($_FILES["image"]["tmp_name"]);

            if (!in_array($fileType, $allowedTypes, true)) {
                $message = "Sadece JPG, PNG veya WEBP yukleyebilirsiniz.";
                $isError = true;
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $message = "Fotograf boyutu en fazla 2MB olabilir.";
                $isError = true;
            } else {
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $safeName = "motor_" . time() . "_" . bin2hex(random_bytes(4)) . "." . strtolower($ext);
                $targetPath = __DIR__ . "/uploads/" . $safeName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                    $imagePath = "uploads/" . $safeName;
                } else {
                    $message = "Fotograf yuklenemedi.";
                    $isError = true;
                }
            }
        }

        if (!$isError) {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO listings (title, model, bike_type, mileage_km, engine_cc, price, description, image_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $priceFloat = (float)$price;
            $mileageKm = (int)$mileageKm;
            $engineCc = (int)$engineCc;
            mysqli_stmt_bind_param(
                $stmt,
                "sssii" . "dss",
                $title,
                $model,
                $bikeType,
                $mileageKm,
                $engineCc,
                $priceFloat,
                $description,
                $imagePath
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Ilan basariyla eklendi.";
            } else {
                $message = "Ilan eklenirken hata olustu.";
                $isError = true;
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Ekle</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
    <a href="my_favorites.php">Favorilerim</a>
    <a href="logout.php">Cikis</a>
</header>
<div class="container">
    <div class="card">
        <h2>Yeni Motor ilani Ekle</h2>
        <?php if (!empty($message)): ?>
            <p class="<?php echo $isError ? "error" : "success"; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <form method="POST" enctype="multipart/form-data">
                <label>Baslik</label>
                <input type="text" name="title" required maxlength="150">

                <label>Model</label>
                <input type="text" name="model" required maxlength="100" placeholder="Ornek: Yamaha MT-07">

                <label>Motosiklet turu</label>
                <select name="bike_type" required>
                    <option value="">Secin</option>
                    <?php foreach (get_motor_types() as $t): ?>
                        <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>

                <label>Kilometre (bos birakilirsa belirtilmemis)</label>
                <input type="number" name="mileage_km" min="0" placeholder="Ornek: 18500">

                <label>Silindir hacmi / cc (bos birakilirsa belirtilmemis)</label>
                <input type="number" name="engine_cc" min="0" placeholder="Ornek: 689">

                <label>Fiyat (TL)</label>
                <input type="number" step="0.01" name="price" required>

                <label>Aciklama</label>
                <textarea name="description" rows="5" required></textarea>

                <label>Fotograf</label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

                <button type="submit">ilani Ekle</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
