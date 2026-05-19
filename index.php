<?php
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/motor_types.php";

function opt_filter_float(string $key): ?float
{
    if (!isset($_GET[$key]) || $_GET[$key] === "") {
        return null;
    }
    $v = filter_var($_GET[$key], FILTER_VALIDATE_FLOAT);
    return $v === false ? null : (float)$v;
}

function opt_filter_uint(string $key): ?int
{
    if (!isset($_GET[$key]) || $_GET[$key] === "") {
        return null;
    }
    $v = filter_var($_GET[$key], FILTER_VALIDATE_INT);
    if ($v === false || $v < 0) {
        return null;
    }
    return (int)$v;
}

$brandFilter = trim((string)($_GET["brand"] ?? ""));
if ($brandFilter !== "" && !is_valid_brand($brandFilter)) {
    $brandFilter = "";
}

$modelFilter = trim((string)($_GET["model"] ?? ""));

$bikeTypeFilter = trim((string)($_GET["bike_type"] ?? ""));
if ($bikeTypeFilter !== "" && !is_valid_motor_type($bikeTypeFilter)) {
    $bikeTypeFilter = "";
}

$colorFilter = trim((string)($_GET["color"] ?? ""));
if ($colorFilter !== "" && !is_valid_color($colorFilter)) {
    $colorFilter = "";
}

$originFilter = trim((string)($_GET["origin_country"] ?? ""));
if ($originFilter !== "" && !is_valid_origin($originFilter)) {
    $originFilter = "";
}

$cylinderCountFilter = opt_filter_uint("cylinder_count");
if ($cylinderCountFilter !== null && !is_valid_cylinder_count($cylinderCountFilter)) {
    $cylinderCountFilter = null;
}

$priceMin = opt_filter_float("price_min");
$priceMax = opt_filter_float("price_max");
$mileageMin = opt_filter_uint("mileage_min");
$mileageMax = opt_filter_uint("mileage_max");
$engineMin = opt_filter_uint("engine_min");
$engineMax = opt_filter_uint("engine_max");
$hpMin = opt_filter_uint("hp_min");
$hpMax = opt_filter_uint("hp_max");
$yearMin = opt_filter_uint("year_min");
$yearMax = opt_filter_uint("year_max");

$where = [];
$params = [];
$types = "";

if ($brandFilter !== "") {
    $where[] = "brand = ?";
    $params[] = $brandFilter;
    $types .= "s";
}

if ($modelFilter !== "") {
    $where[] = "model LIKE ?";
    $params[] = "%" . $modelFilter . "%";
    $types .= "s";
}

if ($bikeTypeFilter !== "") {
    $where[] = "bike_type = ?";
    $params[] = $bikeTypeFilter;
    $types .= "s";
}

if ($colorFilter !== "") {
    $where[] = "color = ?";
    $params[] = $colorFilter;
    $types .= "s";
}

if ($originFilter !== "") {
    $where[] = "origin_country = ?";
    $params[] = $originFilter;
    $types .= "s";
}

if ($cylinderCountFilter !== null) {
    $where[] = "cylinder_count = ?";
    $params[] = $cylinderCountFilter;
    $types .= "i";
}

if ($priceMin !== null) {
    $where[] = "price >= ?";
    $params[] = $priceMin;
    $types .= "d";
}

if ($priceMax !== null) {
    $where[] = "price <= ?";
    $params[] = $priceMax;
    $types .= "d";
}

if ($mileageMin !== null) {
    $where[] = "mileage_km > 0 AND mileage_km >= ?";
    $params[] = $mileageMin;
    $types .= "i";
}

if ($mileageMax !== null) {
    $where[] = "mileage_km > 0 AND mileage_km <= ?";
    $params[] = $mileageMax;
    $types .= "i";
}

if ($engineMin !== null) {
    $where[] = "engine_cc > 0 AND engine_cc >= ?";
    $params[] = $engineMin;
    $types .= "i";
}

if ($engineMax !== null) {
    $where[] = "engine_cc > 0 AND engine_cc <= ?";
    $params[] = $engineMax;
    $types .= "i";
}

if ($hpMin !== null) {
    $where[] = "horsepower > 0 AND horsepower >= ?";
    $params[] = $hpMin;
    $types .= "i";
}

if ($hpMax !== null) {
    $where[] = "horsepower > 0 AND horsepower <= ?";
    $params[] = $hpMax;
    $types .= "i";
}

if ($yearMin !== null) {
    $where[] = "model_year > 0 AND model_year >= ?";
    $params[] = $yearMin;
    $types .= "i";
}

if ($yearMax !== null) {
    $where[] = "model_year > 0 AND model_year <= ?";
    $params[] = $yearMax;
    $types .= "i";
}

$sqlWhere = count($where) > 0 ? ("WHERE " . implode(" AND ", $where)) : "";
$sql = "SELECT id, title, brand, model, model_year, bike_type, mileage_km, engine_cc,
               horsepower, cylinder_count, color, origin_country, price, description, image_path, created_at
        FROM listings
        $sqlWhere
        ORDER BY created_at DESC";

if ($types !== "") {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

if ($result === false) {
    die("Ilanlar yuklenemedi: " . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, "UTF-8"));
}

$hasActiveFilters = count($where) > 0;

$activeFilterLabels = [];
if ($brandFilter !== "") $activeFilterLabels[] = "Marka: " . $brandFilter;
if ($modelFilter !== "") $activeFilterLabels[] = "Model: " . $modelFilter;
if ($bikeTypeFilter !== "") $activeFilterLabels[] = "Tip: " . $bikeTypeFilter;
if ($colorFilter !== "") $activeFilterLabels[] = "Renk: " . $colorFilter;
if ($originFilter !== "") $activeFilterLabels[] = "Mensei: " . $originFilter;
if ($cylinderCountFilter !== null) $activeFilterLabels[] = "Silindir: " . $cylinderCountFilter;
if ($priceMin !== null) $activeFilterLabels[] = "Min Fiyat: " . number_format($priceMin, 0, ',', '.') . " TL";
if ($priceMax !== null) $activeFilterLabels[] = "Max Fiyat: " . number_format($priceMax, 0, ',', '.') . " TL";
if ($mileageMin !== null) $activeFilterLabels[] = "Min KM: " . number_format($mileageMin);
if ($mileageMax !== null) $activeFilterLabels[] = "Max KM: " . number_format($mileageMax);
if ($engineMin !== null) $activeFilterLabels[] = "Min CC: " . $engineMin;
if ($engineMax !== null) $activeFilterLabels[] = "Max CC: " . $engineMax;
if ($hpMin !== null) $activeFilterLabels[] = "Min HP: " . $hpMin;
if ($hpMax !== null) $activeFilterLabels[] = "Max HP: " . $hpMax;
if ($yearMin !== null) $activeFilterLabels[] = "Min Yil: " . $yearMin;
if ($yearMax !== null) $activeFilterLabels[] = "Max Yil: " . $yearMax;

$totalResults = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Alsana - Motosiklet Ilanlari</title>
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
            <a href="index.php" class="nav-link active">Ana Sayfa</a>
            <?php if (is_admin()): ?>
                <a href="add_listing.php" class="nav-link">Motor Ekle</a>
            <?php endif; ?>
            <?php if (is_logged_in()): ?>
                <a href="my_favorites.php" class="nav-link">Favorilerim</a>
                <a href="logout.php" class="nav-link">Cikis</a>
            <?php else: ?>
                <a href="register.php" class="nav-link">Kayit Ol</a>
                <a href="login.php" class="nav-link nav-link-accent">Giris Yap</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="page-wrapper">
    <aside class="filters-sidebar" id="filtersSidebar">
        <div class="filters-header">
            <h3><span class="filter-icon">&#9776;</span> Filtreler</h3>
            <?php if ($hasActiveFilters): ?>
                <a href="index.php" class="btn-clear-filters">Temizle</a>
            <?php endif; ?>
        </div>

        <form class="filters-form" method="get" action="index.php">
            <div class="filter-group">
                <label for="f_brand">Marka</label>
                <select id="f_brand" name="brand">
                    <option value="">Tum Markalar</option>
                    <?php foreach (get_brands() as $b): ?>
                        <option value="<?php echo htmlspecialchars($b, ENT_QUOTES, "UTF-8"); ?>"
                            <?php echo $brandFilter === $b ? " selected" : ""; ?>><?php echo htmlspecialchars($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f_model">Model</label>
                <input id="f_model" type="text" name="model" maxlength="100"
                       value="<?php echo htmlspecialchars($modelFilter, ENT_QUOTES, "UTF-8"); ?>"
                       placeholder="Ornek: MT-07">
            </div>

            <div class="filter-group">
                <label for="f_year_min">Model Yili</label>
                <div class="filter-range">
                    <input id="f_year_min" type="number" name="year_min" min="1950" max="2030" placeholder="Min"
                           value="<?php echo $yearMin !== null ? (int)$yearMin : ""; ?>">
                    <span class="range-sep">-</span>
                    <input id="f_year_max" type="number" name="year_max" min="1950" max="2030" placeholder="Max"
                           value="<?php echo $yearMax !== null ? (int)$yearMax : ""; ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="f_kmin">Kilometre</label>
                <div class="filter-range">
                    <input id="f_kmin" type="number" name="mileage_min" min="0" placeholder="Min"
                           value="<?php echo $mileageMin !== null ? (int)$mileageMin : ""; ?>">
                    <span class="range-sep">-</span>
                    <input id="f_kmax" type="number" name="mileage_max" min="0" placeholder="Max"
                           value="<?php echo $mileageMax !== null ? (int)$mileageMax : ""; ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="f_pmin">Fiyat (TL)</label>
                <div class="filter-range">
                    <input id="f_pmin" type="number" step="1" name="price_min" placeholder="Min"
                           value="<?php echo $priceMin !== null ? htmlspecialchars((string)$priceMin, ENT_QUOTES, "UTF-8") : ""; ?>">
                    <span class="range-sep">-</span>
                    <input id="f_pmax" type="number" step="1" name="price_max" placeholder="Max"
                           value="<?php echo $priceMax !== null ? htmlspecialchars((string)$priceMax, ENT_QUOTES, "UTF-8") : ""; ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="f_emin">Silindir Hacmi (CC)</label>
                <div class="filter-range">
                    <input id="f_emin" type="number" name="engine_min" min="0" placeholder="Min"
                           value="<?php echo $engineMin !== null ? (int)$engineMin : ""; ?>">
                    <span class="range-sep">-</span>
                    <input id="f_emax" type="number" name="engine_max" min="0" placeholder="Max"
                           value="<?php echo $engineMax !== null ? (int)$engineMax : ""; ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="f_hpmin">Beygir Gucu (HP)</label>
                <div class="filter-range">
                    <input id="f_hpmin" type="number" name="hp_min" min="0" placeholder="Min"
                           value="<?php echo $hpMin !== null ? (int)$hpMin : ""; ?>">
                    <span class="range-sep">-</span>
                    <input id="f_hpmax" type="number" name="hp_max" min="0" placeholder="Max"
                           value="<?php echo $hpMax !== null ? (int)$hpMax : ""; ?>">
                </div>
            </div>

            <div class="filter-group">
                <label for="f_type">Motorsiklet Tipi</label>
                <select id="f_type" name="bike_type">
                    <option value="">Tum Tipler</option>
                    <?php foreach (get_motor_types() as $t): ?>
                        <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"
                            <?php echo $bikeTypeFilter === $t ? " selected" : ""; ?>><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f_cylinder">Silindir Sayisi</label>
                <select id="f_cylinder" name="cylinder_count">
                    <option value="">Tumu</option>
                    <?php foreach (get_cylinder_counts() as $c): ?>
                        <option value="<?php echo $c; ?>"
                            <?php echo $cylinderCountFilter === $c ? " selected" : ""; ?>><?php echo $c; ?> Silindir</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f_color">Renk</label>
                <select id="f_color" name="color">
                    <option value="">Tum Renkler</option>
                    <?php foreach (get_colors() as $clr): ?>
                        <option value="<?php echo htmlspecialchars($clr, ENT_QUOTES, "UTF-8"); ?>"
                            <?php echo $colorFilter === $clr ? " selected" : ""; ?>><?php echo htmlspecialchars($clr); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="f_origin">Mensei / Uretim Ulkesi</label>
                <select id="f_origin" name="origin_country">
                    <option value="">Tum Ulkeler</option>
                    <?php foreach (get_origin_countries() as $o): ?>
                        <option value="<?php echo htmlspecialchars($o, ENT_QUOTES, "UTF-8"); ?>"
                            <?php echo $originFilter === $o ? " selected" : ""; ?>><?php echo htmlspecialchars($o); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary btn-block">Filtrele</button>
                <a href="index.php" class="btn btn-outline btn-block">Temizle</a>
            </div>
        </form>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <div class="content-header-top">
                <h1>Motosiklet Ilanlari</h1>
                <button type="button" class="btn btn-filter-toggle" id="filterToggleBtn">
                    <span class="filter-icon">&#9776;</span> Filtrele
                </button>
            </div>
            <?php if ($hasActiveFilters): ?>
                <div class="active-filters-bar">
                    <span class="active-filters-label">Aktif Filtreler:</span>
                    <?php foreach ($activeFilterLabels as $label): ?>
                        <span class="filter-tag"><?php echo htmlspecialchars($label); ?></span>
                    <?php endforeach; ?>
                    <a href="index.php" class="filter-tag filter-tag-clear">Tumu Temizle &times;</a>
                </div>
            <?php endif; ?>
            <p class="results-count"><?php echo $totalResults; ?> ilan bulundu</p>
        </div>

        <div class="grid">
            <?php
            $hasRows = false;
            while ($row = mysqli_fetch_assoc($result)):
                $hasRows = true;
                $km = (int)($row["mileage_km"] ?? 0);
                $cc = (int)($row["engine_cc"] ?? 0);
                $hp = (int)($row["horsepower"] ?? 0);
                $yr = (int)($row["model_year"] ?? 0);
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
                        <div class="card-specs">
                            <?php if (($row["brand"] ?? "") !== ""): ?>
                                <span class="spec-item"><strong>Marka:</strong> <?php echo htmlspecialchars($row["brand"]); ?></span>
                            <?php endif; ?>
                            <?php if (($row["model"] ?? "") !== ""): ?>
                                <span class="spec-item"><strong>Model:</strong> <?php echo htmlspecialchars($row["model"]); ?></span>
                            <?php endif; ?>
                            <?php if ($yr > 0): ?>
                                <span class="spec-item"><strong>Yil:</strong> <?php echo $yr; ?></span>
                            <?php endif; ?>
                            <span class="spec-item"><strong>KM:</strong> <?php echo $km > 0 ? number_format($km) : "—"; ?></span>
                            <span class="spec-item"><strong>CC:</strong> <?php echo $cc > 0 ? number_format($cc) : "—"; ?></span>
                            <?php if ($hp > 0): ?>
                                <span class="spec-item"><strong>HP:</strong> <?php echo $hp; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-price"><?php echo number_format((float)$row["price"], 0, ',', '.'); ?> TL</div>
                        <?php
                        $shortDescription = mb_strlen($row["description"]) > 80
                            ? mb_substr($row["description"], 0, 80) . "..."
                            : $row["description"];
                        ?>
                        <p class="card-desc"><?php echo htmlspecialchars($shortDescription); ?></p>
                        <div class="card-actions">
                            <a class="btn btn-primary btn-sm" href="listing.php?id=<?php echo (int)$row["id"]; ?>">Detay</a>
                            <?php if (is_admin()): ?>
                                <a class="btn btn-outline btn-sm" href="edit_listing.php?id=<?php echo (int)$row["id"]; ?>">Duzenle</a>
                                <form method="POST" action="delete_listing.php" class="inline-form"
                                      onsubmit="return confirm('Bu ilani kalici olarak silmek istediginize emin misiniz?');">
                                    <input type="hidden" name="listing_id" value="<?php echo (int)$row["id"]; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php if (!$hasRows): ?>
                <div class="empty-state">
                    <span class="empty-icon">&#128269;</span>
                    <p><?php echo $hasActiveFilters
                        ? "Filtrelere uyan ilan bulunamadi."
                        : "Henuz ilan bulunmuyor."; ?></p>
                    <?php if ($hasActiveFilters): ?>
                        <a href="index.php" class="btn btn-outline">Filtreleri Temizle</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<div class="overlay" id="filterOverlay"></div>

<script>
(function () {
    var sidebar = document.getElementById("filtersSidebar");
    var overlay = document.getElementById("filterOverlay");
    var toggleBtn = document.getElementById("filterToggleBtn");
    var navToggle = document.getElementById("navToggle");
    var navLinks = document.getElementById("navLinks");

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener("click", function () {
            sidebar.classList.toggle("open");
            overlay.classList.toggle("open");
        });
        overlay.addEventListener("click", function () {
            sidebar.classList.remove("open");
            overlay.classList.remove("open");
        });
    }

    if (navToggle && navLinks) {
        navToggle.addEventListener("click", function () {
            navLinks.classList.toggle("open");
        });
    }
})();
</script>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    mysqli_stmt_close($stmt);
}
?>
</body>
</html>
