<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/motor_types.php";

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
    $imagePath = "";

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
                "INSERT INTO listings (title, brand, model, model_year, bike_type, mileage_km, engine_cc, horsepower, cylinder_count, color, origin_country, price, description, image_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $priceFloat = (float)$price;
            $mileageKm = (int)$mileageKm;
            $engineCc = (int)$engineCc;
            $horsepower = (int)$horsepower;
            $cylinderCount = (int)$cylinderCount;
            $modelYear = (int)$modelYear;
            mysqli_stmt_bind_param(
                $stmt,
                "sssisiiiissdss",
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
    <title>Motor Ekle - Motor Alsana</title>
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
            <a href="add_listing.php" class="nav-link active">Motor Ekle</a>
            <a href="my_favorites.php" class="nav-link">Favorilerim</a>
            <a href="logout.php" class="nav-link">Cikis</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="form-card">
        <h2 class="form-title">Yeni Motor Ilani Ekle</h2>
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $isError ? "alert-error" : "alert-success"; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="title">Baslik <span class="required">*</span></label>
                        <input id="title" type="text" name="title" required maxlength="150" placeholder="Ilan basligi">
                    </div>

                    <div class="form-group">
                        <label for="brand">Marka <span class="required">*</span></label>
                        <select id="brand" name="brand" required>
                            <option value="">Marka Secin</option>
                            <?php foreach (get_brands() as $b): ?>
                                <option value="<?php echo htmlspecialchars($b, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="model">Model <span class="required">*</span></label>
                        <input id="model" type="text" name="model" required maxlength="100" placeholder="Ornek: MT-07">
                    </div>

                    <div class="form-group">
                        <label for="model_year">Model Yili</label>
                        <input id="model_year" type="number" name="model_year" min="1950" max="2030" placeholder="Ornek: 2023">
                    </div>

                    <div class="form-group">
                        <label for="bike_type">Motorsiklet Tipi <span class="required">*</span></label>
                        <select id="bike_type" name="bike_type" required>
                            <option value="">Tip Secin</option>
                            <?php foreach (get_motor_types() as $t): ?>
                                <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($t); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mileage_km">Kilometre</label>
                        <input id="mileage_km" type="number" name="mileage_km" min="0" placeholder="Ornek: 18500">
                    </div>

                    <div class="form-group">
                        <label for="engine_cc">Silindir Hacmi (CC)</label>
                        <input id="engine_cc" type="number" name="engine_cc" min="0" placeholder="Ornek: 689">
                    </div>

                    <div class="form-group">
                        <label for="horsepower">Beygir Gucu (HP)</label>
                        <input id="horsepower" type="number" name="horsepower" min="0" placeholder="Ornek: 73">
                    </div>

                    <div class="form-group">
                        <label for="cylinder_count">Silindir Sayisi</label>
                        <select id="cylinder_count" name="cylinder_count">
                            <option value="">Belirtilmemis</option>
                            <?php foreach (get_cylinder_counts() as $c): ?>
                                <option value="<?php echo $c; ?>"><?php echo $c; ?> Silindir</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Renk</label>
                        <select id="color" name="color">
                            <option value="">Renk Secin</option>
                            <?php foreach (get_colors() as $clr): ?>
                                <option value="<?php echo htmlspecialchars($clr, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($clr); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="origin_country">Mensei / Uretim Ulkesi</label>
                        <select id="origin_country" name="origin_country">
                            <option value="">Ulke Secin</option>
                            <?php foreach (get_origin_countries() as $o): ?>
                                <option value="<?php echo htmlspecialchars($o, ENT_QUOTES, "UTF-8"); ?>"><?php echo htmlspecialchars($o); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="price">Fiyat (TL) <span class="required">*</span></label>
                        <input id="price" type="number" step="0.01" name="price" required placeholder="Ornek: 185000">
                    </div>
                </div>

                <div class="form-group form-group-full">
                    <label for="description">Aciklama <span class="required">*</span></label>
                    <textarea id="description" name="description" rows="5" required placeholder="Ilan detaylarini yazin..."></textarea>
                </div>

                <div class="form-group form-group-full">
                    <label for="image">Fotograf</label>
                    <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="file-input">
                    <small class="form-hint">Maksimum 2MB. JPG, PNG veya WEBP.</small>
                </div>

                <div class="form-group form-group-full">
                    <button type="submit" class="btn btn-primary btn-lg">Ilani Ekle</button>
                </div>
            </form>
        <?php endif; ?>
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
