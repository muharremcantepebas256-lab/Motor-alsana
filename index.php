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

$modelFilter = trim((string)($_GET["model"] ?? ""));
$bikeTypeFilter = trim((string)($_GET["bike_type"] ?? ""));
if ($bikeTypeFilter !== "" && !is_valid_motor_type($bikeTypeFilter)) {
    $bikeTypeFilter = "";
}

$priceMin = opt_filter_float("price_min");
$priceMax = opt_filter_float("price_max");
$mileageMin = opt_filter_uint("mileage_min");
$mileageMax = opt_filter_uint("mileage_max");
$engineMin = opt_filter_uint("engine_min");
$engineMax = opt_filter_uint("engine_max");

$where = [];
$params = [];
$types = "";

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

$sqlWhere = count($where) > 0 ? ("WHERE " . implode(" AND ", $where)) : "";
$sql = "SELECT id, title, model, bike_type, mileage_km, engine_cc, price, description, image_path, created_at
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

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Alsana - Ana Sayfa</title>
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
        <a href="register.php">Kayit Ol</a>
        <a href="login.php">Giris Yap</a>
    <?php endif; ?>
</header>

<div class="container">
    <h1>Motosiklet İlanlari</h1>

    <div class="grid">
        <?php
        $hasRows = false;
        while ($row = mysqli_fetch_assoc($result)):
            $hasRows = true;
            ?>
            <div class="card">
                <?php if (!empty($row["image_path"])): ?>
                    <img class="listing-image" src="<?php echo htmlspecialchars($row["image_path"]); ?>" alt="Motor Fotografi">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($row["title"]); ?></h3>
                <?php if (($row["model"] ?? "") !== ""): ?>
                    <p class="listing-meta"><strong>Model:</strong> <?php echo htmlspecialchars($row["model"]); ?></p>
                <?php endif; ?>
                <?php if (($row["bike_type"] ?? "") !== ""): ?>
                    <p class="listing-meta"><strong>Tur:</strong> <?php echo htmlspecialchars($row["bike_type"]); ?></p>
                <?php endif; ?>
                <?php
                $km = (int)($row["mileage_km"] ?? 0);
                $cc = (int)($row["engine_cc"] ?? 0);
                ?>
                <p class="listing-meta"><strong>Kilometre:</strong> <?php echo $km > 0 ? number_format($km) . " km" : "Belirtilmemis"; ?></p>
                <p class="listing-meta"><strong>Silindir:</strong> <?php echo $cc > 0 ? number_format($cc) . " cc" : "Belirtilmemis"; ?></p>
                <p class="price"><?php echo number_format((float)$row["price"], 2); ?> TL</p>
                <?php
                $shortDescription = strlen($row["description"]) > 120
                    ? substr($row["description"], 0, 120) . "..."
                    : $row["description"];
                ?>
                <p><?php echo nl2br(htmlspecialchars($shortDescription)); ?></p>
                <a class="btn" href="listing.php?id=<?php echo (int)$row["id"]; ?>">Detaya Git</a>
                <?php if (is_admin()): ?>
                    <a class="btn" href="edit_listing.php?id=<?php echo (int)$row["id"]; ?>">Düzenle</a>
                    <form method="POST" action="delete_listing.php" class="inline-delete-form"
                          onsubmit="return confirm('Bu ilani kalici olarak silmek istediginize emin misiniz?');">
                        <input type="hidden" name="listing_id" value="<?php echo (int)$row["id"]; ?>">
                        <button type="submit" class="btn btn-danger">Sil</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        <?php if (!$hasRows): ?>
            <p class="grid-empty-msg"><?php echo $hasActiveFilters
                ? "Filtrelere uyan ilan bulunamadi."
                : "Henuz ilan bulunmuyor."; ?></p>
        <?php endif; ?>
    </div>

    <div class="listing-toolbar listing-toolbar-below">
        <button type="button" id="toggle-filters" class="btn btn-secondary toggle-filters-btn"
                aria-expanded="<?php echo $hasActiveFilters ? "true" : "false"; ?>"
                aria-controls="filters-panel">
            <?php echo $hasActiveFilters ? "Filtreyi Gizle" : "Filtrele"; ?>
        </button>
        <?php if ($hasActiveFilters): ?>
            <span class="filter-active-note">Secili filtreler uygulanmis.</span>
        <?php endif; ?>
    </div>

    <div id="filters-panel" class="card filters-card filters-panel-below"
        <?php echo $hasActiveFilters ? "" : " hidden"; ?>>
        <h3 style="margin-top: 0;">Filtre kriterleri</h3>
        <form class="filters-form" method="get" action="index.php">
            <div>
                <label for="f_model">Model</label>
                <input id="f_model" type="text" name="model" maxlength="100"
                       value="<?php echo htmlspecialchars($modelFilter, ENT_QUOTES, "UTF-8"); ?>"
                       placeholder="Ornek: MT-07">
            </div>
            <div>
                <label for="f_type">Turu</label>
                <select id="f_type" name="bike_type">
                    <option value="">Tumu</option>
                    <?php foreach (get_motor_types() as $t): ?>
                        <option value="<?php echo htmlspecialchars($t, ENT_QUOTES, "UTF-8"); ?>"
                            <?php echo $bikeTypeFilter === $t ? " selected" : ""; ?>><?php echo htmlspecialchars($t); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="f_pmin">Min. fiyat (TL)</label>
                <input id="f_pmin" type="number" step="0.01" name="price_min"
                       value="<?php echo $priceMin !== null ? htmlspecialchars((string)$priceMin, ENT_QUOTES, "UTF-8") : ""; ?>">
            </div>
            <div>
                <label for="f_pmax">Max. fiyat (TL)</label>
                <input id="f_pmax" type="number" step="0.01" name="price_max"
                       value="<?php echo $priceMax !== null ? htmlspecialchars((string)$priceMax, ENT_QUOTES, "UTF-8") : ""; ?>">
            </div>
            <div>
                <label for="f_kmin">Min. km</label>
                <input id="f_kmin" type="number" name="mileage_min" min="0"
                       value="<?php echo $mileageMin !== null ? (int)$mileageMin : ""; ?>">
            </div>
            <div>
                <label for="f_kmax">Max. km</label>
                <input id="f_kmax" type="number" name="mileage_max" min="0"
                       value="<?php echo $mileageMax !== null ? (int)$mileageMax : ""; ?>">
            </div>
            <div>
                <label for="f_emin">Min. silindir (cc)</label>
                <input id="f_emin" type="number" name="engine_min" min="0"
                       value="<?php echo $engineMin !== null ? (int)$engineMin : ""; ?>">
            </div>
            <div>
                <label for="f_emax">Max. silindir (cc)</label>
                <input id="f_emax" type="number" name="engine_max" min="0"
                       value="<?php echo $engineMax !== null ? (int)$engineMax : ""; ?>">
            </div>
            <div class="filters-actions">
                <button type="submit" class="btn">Listeyi Filtrele</button>
                <a class="btn btn-secondary" href="index.php">Temizle</a>
            </div>
        </form>
        <p class="filter-hint">Km veya silindir hacmi filtresi kullanildiginda, bu bilgisi girilmemis ilanlar listede gorunmez.</p>
    </div>

    <script>
    (function () {
        var panel = document.getElementById("filters-panel");
        var btn = document.getElementById("toggle-filters");
        if (!panel || !btn) return;

        function syncButton(open) {
            btn.setAttribute("aria-expanded", open ? "true" : "false");
            btn.textContent = open ? "Filtreyi Gizle" : "Filtrele";
            if (open) {
                panel.removeAttribute("hidden");
            } else {
                panel.setAttribute("hidden", "");
            }
        }

        btn.addEventListener("click", function () {
            var open = panel.hasAttribute("hidden");
            syncButton(open);
            if (open) {
                panel.scrollIntoView({ behavior: "smooth", block: "nearest" });
            }
        });
    })();
    </script>
</div>
<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    mysqli_stmt_close($stmt);
}
?>
</body>
</html>
