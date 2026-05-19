<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/motor_types.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) {
    die("Gecersiz ilan ID.");
}

require_admin();

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

$message = "";
$isError = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $brand = trim($_POST["brand"] ?? "");
    $model = trim($_POST["model"] ?? "");
    $modelYearRaw = trim($_POST["model_year"] ?? "");
    $bikeType = trim($_POST["bike_type"] ?? "");
    $mileageRaw = trim($_POST["mileage_km"] ?? "");
    $engineRaw = trim($_POST["engine_cc"] ?? "");
    $horsepowerRaw = trim($_POST["horsepower"] ?? "");
    $cylinderCountRaw = trim($_POST["cylinder_count"] ?? "");
    $color = trim($_POST["color"] ?? "");
    $originCountry = trim($_POST["origin_country"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $currentImagePath = $listing["image_path"] ?? "";
    if ($currentImagePath === null) {
        $currentImagePath = "";
    }

    $mileageKm = $mileageRaw === "" ? 0 : filter_var($mileageRaw, FILTER_VALIDATE_INT);
    $engineCc = $engineRaw === "" ? 0 : filter_var($engineRaw, FILTER_VALIDATE_INT);
    $horsepower = $horsepowerRaw === "" ? 0 : filter_var($horsepowerRaw, FILTER_VALIDATE_INT);
    $cylinderCount = $cylinderCountRaw === "" ? 0 : filter_var($cylinderCountRaw, FILTER_VALIDATE_INT);
    $modelYear = $modelYearRaw === "" ? 0 : filter_var($modelYearRaw, FILTER_VALIDATE_INT);

    if ($mileageKm === false || (int)$mileageKm < 0) $mileageKm = -1;
    if ($engineCc === false || (int)$engineCc < 0) $engineCc = -1;
    if ($horsepower === false || (int)$horsepower < 0) $horsepower = -1;
    if ($cylinderCount === false || (int)$cylinderCount < 0) $cylinderCount = -1;
    if ($modelYear === false || (int)$modelYear < 0) $modelYear = -1;

    if ($title === "" || $brand === "" || $model === "" || $bikeType === ""
        || !is_valid_motor_type($bikeType) || !is_valid_brand($brand)
        || !is_numeric($price) || (float)$price < 0 || $description === ""
        || (int)$mileageKm < 0 || (int)$engineCc < 0 || (int)$horsepower < 0
        || (int)$cylinderCount < 0 || (int)$modelYear < 0) {
        $message = "Lutfen tum zorunlu alanlari dogru sekilde doldurun.";
        $isError = true;
    } else {
        if ($color !== "" && !is_valid_color($color)) {
            $color = "";
        }
        if ($originCountry !== "" && !is_valid_origin($originCountry)) {
            $originCountry = "";
        }

        $imagePath = $currentImagePath;

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
                 SET title = ?, brand = ?, model = ?, model_year = ?, bike_type = ?, mileage_km = ?, engine_cc = ?, horsepower = ?, cylinder_count = ?, color = ?, origin_country = ?, price = ?, description = ?, image_path = NULLIF(?, '')
                 WHERE id = ?"
            );
            $priceFloat = (float)$price;
            $mileageKm = (int)$mileageKm;
            $engineCc = (int)$engineCc;
            $horsepower = (int)$horsepower;
            $cylinderCount = (int)$cylinderCount;
            $modelYear = (int)$modelYear;

            if ($imagePath === null) {
                $imagePath = "";
            }

            mysqli_stmt_bind_param(
                $updateStmt,
                "sssisiiiissdssi",
                $title,
                $brand,
                $model,
                $modelYear,
                $bikeType,
                $mileageKm,
                $engineCc,
                $horsepower,
                $cylinderCount,
                $color,
                $originCountry,
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
$brandValue = (string)($listing["brand"] ?? "");
$modelValue = htmlspecialchars((string)($listing["model"] ?? ""), ENT_QUOTES, "UTF-8");
$modelYearValue = (int)($listing["model_year"] ?? 0) > 0 ? (string)(int)$listing["model_year"] : "";
$bikeTypeValue = (string)($listing["bike_type"] ?? "");
$mileageValue = (int)($listing["mileage_km"] ?? 0) > 0 ? (string)(int)$listing["mileage_km"] : "";
$engineValue = (int)($listing["engine_cc"] ?? 0) > 0 ? (string)(int)$listing["engine_cc"] : "";
$horsepowerValue = (int)($listing["horsepower"] ?? 0) > 0 ? (string)(int)$listing["horsepower"] : "";
$cylinderCountValue = (int)($listing["cylinder_count"] ?? 0);
$colorValue = (string)($listing["color"] ?? "");
$originValue = (string)($listing["origin_country"] ?? "");
$priceValue = htmlspecialchars((string)$listing["price"] ?? "", ENT_QUOTES, "UTF-8");
$descriptionValue = htmlspecialchars((string)$listing["description"] ?? "", ENT_QUOTES, "UTF-8");
$imagePathValue = $listing["image_path"] ?? null;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilan Duzenle - Motor Alsana</title>
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
            <a href="add_listing.php" class="nav-link">Motor Ekle</a>
            <a href="my_favorites.php" class="nav-link">Favorilerim</a>
            <a href="logout.php" class="nav-link">Cikis</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="form-card">
        <h2 class="form-title">Ilan Duzenle</h2>
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $isError ? "alert-error" : "alert-success"; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($imagePathValue)): ?>
            <div class="current-image-preview">
                <img src="<?php echo htmlspecialchars($imagePathValue); ?>" alt="Mevcut Fotograf">
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Baslik <span class="required">*</span></label>
                    <input id="title" type="text" name="title" required maxlength="150" value="<?php echo $titleValue; ?>">
                </div>

                <div class="form-group">
                    <label for="brand">Marka <span class="required">*</span></label>
                    <select id="brand" name="brand" required>
                        <option value="">Marka Secin</option>
                        <?php foreach (get_brands() as $b): ?>
                            <option value="<?php echo htmlspecialchars($b, ENT_QUOTES, "UTF-8"); ?>"
                                <?php echo $brandValue === $b ? " selected" : ""; ?>><?php echo htmlspecialchars($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="model">Model <span class="required">*</span></label>
                    <input id="model" type="text" name="model" required maxlength="100" value="<?php echo $modelValue; ?>">
                </div>

                <div class="form-group">
                    <label for="model_year">Model Yili</label>
                    <input id="model_year" type="number" name="model_year" min="1950" max="2030" value="<?php echo htmlspecialchars($modelYearValue, ENT_QUOTES, "UTF-8"); ?>">
                </div>

                <div class="form-group">
                    <label for="bike_type">Motorsiklet Tipi <span class="required">*</span></label>
                    <select id="bike_type" name="bike_type" required>
                        <option value="">Tip Secin</option>
                        <?php foreach (get_motor_types() as $t): ?>
                            <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"
                                <?php echo $bikeTypeValue === $t ? " selected" : ""; ?>><?php echo htmlspecialchars($t); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mileage_km">Kilometre</label>
                    <input id="mileage_km" type="number" name="mileage_km" min="0" value="<?php echo htmlspecialchars($mileageValue, ENT_QUOTES, "UTF-8"); ?>">
                </div>

                <div class="form-group">
                    <label for="engine_cc">Silindir Hacmi (CC)</label>
                    <input id="engine_cc" type="number" name="engine_cc" min="0" value="<?php echo htmlspecialchars($engineValue, ENT_QUOTES, "UTF-8"); ?>">
                </div>

                <div class="form-group">
                    <label for="horsepower">Beygir Gucu (HP)</label>
                    <input id="horsepower" type="number" name="horsepower" min="0" value="<?php echo htmlspecialchars($horsepowerValue, ENT_QUOTES, "UTF-8"); ?>">
                </div>

                <div class="form-group">
                    <label for="cylinder_count">Silindir Sayisi</label>
                    <select id="cylinder_count" name="cylinder_count">
                        <option value="">Belirtilmemis</option>
                        <?php foreach (get_cylinder_counts() as $c): ?>
                            <option value="<?php echo $c; ?>"
                                <?php echo $cylinderCountValue === $c ? " selected" : ""; ?>><?php echo $c; ?> Silindir</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="color">Renk</label>
                    <select id="color" name="color">
                        <option value="">Renk Secin</option>
                        <?php foreach (get_colors() as $clr): ?>
                            <option value="<?php echo htmlspecialchars($clr, ENT_QUOTES, "UTF-8"); ?>"
                                <?php echo $colorValue === $clr ? " selected" : ""; ?>><?php echo htmlspecialchars($clr); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="origin_country">Mensei / Uretim Ulkesi</label>
                    <select id="origin_country" name="origin_country">
                        <option value="">Ulke Secin</option>
                        <?php foreach (get_origin_countries() as $o): ?>
                            <option value="<?php echo htmlspecialchars($o, ENT_QUOTES, "UTF-8"); ?>"
                                <?php echo $originValue === $o ? " selected" : ""; ?>><?php echo htmlspecialchars($o); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Fiyat (TL) <span class="required">*</span></label>
                    <input id="price" type="number" step="0.01" name="price" required value="<?php echo $priceValue; ?>">
                </div>
            </div>

            <div class="form-group form-group-full">
                <label for="description">Aciklama <span class="required">*</span></label>
                <textarea id="description" name="description" rows="5" required><?php echo $descriptionValue; ?></textarea>
            </div>

            <div class="form-group form-group-full">
                <label for="image">Fotograf (opsiyonel)</label>
                <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="file-input">
                <small class="form-hint">Maksimum 2MB. JPG, PNG veya WEBP.</small>
            </div>

            <div class="form-group form-group-full">
                <button type="submit" class="btn btn-primary btn-lg">Guncelle</button>
            </div>
        </form>
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
