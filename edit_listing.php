<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/motor_types.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    die("Gecersiz ilan ID.");
}

// Sadece admin duzenleyebilir.
require_admin();

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

$message = "";
$isError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $model = trim($_POST["model"] ?? "");
    $bikeType = trim($_POST["bike_type"] ?? "");
    $mileageRaw = trim($_POST["mileage_km"] ?? "");
    $engineRaw = trim($_POST["engine_cc"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $currentImagePath = $listing["image_path"] ?? "";
    if ($currentImagePath === null) {
        $currentImagePath = "";
    }

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
        $imagePath = $currentImagePath;

        // Yeni fotograf upload edildiyse guncelle.
        if (!empty($_FILES["image"]["name"])) {
            $uploadsDir = __DIR__ . "/uploads";
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

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
                $targetPath = $uploadsDir . "/" . $safeName;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                    $imagePath = "uploads/" . $safeName;
                } else {
                    $message = "Fotograf yuklenemedi.";
                    $isError = true;
                }
            }
        }

        if (!$isError) {
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE listings
                 SET title = ?, model = ?, bike_type = ?, mileage_km = ?, engine_cc = ?, price = ?, description = ?, image_path = NULLIF(?, '')
                 WHERE id = ?"
            );
            $priceFloat = (float)$price;
            $mileageKm = (int)$mileageKm;
            $engineCc = (int)$engineCc;

            // image_path null olmaya elverisli olsun diye '' gonderiyoruz.
            if ($imagePath === null) {
                $imagePath = "";
            }

            mysqli_stmt_bind_param(
                $updateStmt,
                "sssii" . "dssi",
                $title,
                $model,
                $bikeType,
                $mileageKm,
                $engineCc,
                $priceFloat,
                $description,
                $imagePath,
                $id
            );
            if (mysqli_stmt_execute($updateStmt)) {
                header("Location: listing.php?id=" . (int)$id);
                exit;
            }

            $message = "Ilan guncellenirken hata olustu.";
            $isError = true;
            mysqli_stmt_close($updateStmt);
        }
    }
}

$titleValue = htmlspecialchars((string)($listing["title"] ?? ""), ENT_QUOTES, "UTF-8");
$modelValue = htmlspecialchars((string)($listing["model"] ?? ""), ENT_QUOTES, "UTF-8");
$bikeTypeValue = (string)($listing["bike_type"] ?? "");
$mileageValue = (int)($listing["mileage_km"] ?? 0) > 0 ? (string)(int)$listing["mileage_km"] : "";
$engineValue = (int)($listing["engine_cc"] ?? 0) > 0 ? (string)(int)$listing["engine_cc"] : "";
$priceValue = htmlspecialchars((string)$listing["price"] ?? "", ENT_QUOTES, "UTF-8");
$descriptionValue = htmlspecialchars((string)$listing["description"] ?? "", ENT_QUOTES, "UTF-8");
$imagePathValue = $listing["image_path"] ?? null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilan Duzenle</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
    <a href="index.php">Ana Sayfa</a>
    <a href="add_listing.php">Motor Ekle</a>
    <a href="my_favorites.php">Favorilerim</a>
    <a href="logout.php">Cikis</a>
</header>

<div class="container">
    <div class="card">
        <h2>Ilan Duzenle</h2>
        <?php if (!empty($message)): ?>
            <p class="<?php echo $isError ? "error" : "success"; ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if (!empty($imagePathValue)): ?>
            <div style="margin-bottom: 12px;">
                <img class="listing-image" src="<?php echo htmlspecialchars($imagePathValue); ?>" alt="Motor Fotografi">
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Baslik</label>
            <input type="text" name="title" required maxlength="150" value="<?php echo $titleValue; ?>">

            <label>Model</label>
            <input type="text" name="model" required maxlength="100" value="<?php echo $modelValue; ?>">

            <label>Motosiklet turu</label>
            <select name="bike_type" required>
                <option value="">Secin</option>
                <?php foreach (get_motor_types() as $t): ?>
                    <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"
                        <?php echo $bikeTypeValue === $t ? " selected" : ""; ?>><?php echo htmlspecialchars($t); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Kilometre (bos = belirtilmemis)</label>
            <input type="number" name="mileage_km" min="0" value="<?php echo htmlspecialchars($mileageValue, ENT_QUOTES, "UTF-8"); ?>">

            <label>Silindir hacmi / cc (bos = belirtilmemis)</label>
            <input type="number" name="engine_cc" min="0" value="<?php echo htmlspecialchars($engineValue, ENT_QUOTES, "UTF-8"); ?>">

            <label>Fiyat (TL)</label>
            <input type="number" step="0.01" name="price" required value="<?php echo $priceValue; ?>">

            <label>Aciklama</label>
            <textarea name="description" rows="5" required><?php echo $descriptionValue; ?></textarea>

            <label>Fotograf (opsiyonel)</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

            <button type="submit">Guncelle</button>
        </form>
    </div>
</div>
</body>
</html>

