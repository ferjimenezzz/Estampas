<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = 'info';

// Procesar formulario para agregar o eliminar estampa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add' && isset($_POST['stamp_code'])) {
        $code = strtoupper(trim($_POST['stamp_code']));
        if (preg_match('/^[A-Z]{3} \d{1,2}$/', $code)) {
            $stmt = $pdo->prepare("INSERT INTO user_stamps (user_id, stamp_code, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            if ($stmt->execute([$user_id, $code])) {
                $message = "¡Estampa $code registrada correctamente!";
                $messageType = 'success';
            } else {
                $message = "Error al registrar la estampa.";
                $messageType = 'error';
            }
        } else {
            $message = "Formato inválido. Usa el formato 'PREFIJO NUMERO', ej: 'ARG 1'.";
            $messageType = 'error';
        }
    } elseif ($action === 'remove' && isset($_POST['remove_stamp_code'])) {
        $code = strtoupper(trim($_POST['remove_stamp_code']));
        if (preg_match('/^[A-Z]{3} \d{1,2}$/', $code)) {
            $stmt = $pdo->prepare("SELECT quantity FROM user_stamps WHERE user_id = ? AND stamp_code = ?");
            $stmt->execute([$user_id, $code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                if ($row['quantity'] > 1) {
                    $stmtUpdate = $pdo->prepare("UPDATE user_stamps SET quantity = quantity - 1 WHERE user_id = ? AND stamp_code = ?");
                    $stmtUpdate->execute([$user_id, $code]);
                    $message = "¡Se ha quitado una estampa repetida de $code!";
                    $messageType = 'success';
                } else {
                    $stmtDelete = $pdo->prepare("DELETE FROM user_stamps WHERE user_id = ? AND stamp_code = ?");
                    $stmtDelete->execute([$user_id, $code]);
                    $message = "¡Estampa $code eliminada completamente de tu colección!";
                    $messageType = 'success';
                }
            } else {
                $message = "No tienes la estampa $code en tu colección.";
                $messageType = 'error';
            }
        } else {
            $message = "Formato inválido. Usa el formato 'PREFIJO NUMERO', ej: 'ARG 1'.";
            $messageType = 'error';
        }
    }
}

// Obtener estampas del usuario
$stmt = $pdo->prepare("SELECT stamp_code, quantity FROM user_stamps WHERE user_id = ?");
$stmt->execute([$user_id]);
$userStampsDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

$obtained = [];
$repeated = [];
$missing = [];

$userStampsMap = [];
foreach ($userStampsDB as $s) {
    $userStampsMap[$s['stamp_code']] = $s['quantity'];
}

// Generar todas las estampas y clasificarlas
foreach ($sections as $country => $prefix) {
    for ($i = 1; $i <= 20; $i++) {
        $code = "$prefix $i";
        if (isset($userStampsMap[$code])) {
            $qty = $userStampsMap[$code];
            if ($qty > 1) {
                $repeated[] = ['code' => $code, 'extra_qty' => $qty - 1, 'country' => $country];
            }
            $obtained[] = ['code' => $code, 'qty' => $qty, 'country' => $country];
        } else {
            $missing[] = ['code' => $code, 'country' => $country];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Álbum Mundial 2026</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar">
        <div class="logo">Álbum 2026</div>
        <div class="user-info">
            Hola, <?php echo htmlspecialchars($_SESSION['username']); ?>
            <a href="logout.php" class="btn-logout">Salir</a>
        </div>
    </nav>

    <main class="container">
        <section class="manage-section">
            <div class="action-card add-card">
                <div class="card-header">
                    <span class="icon">✨</span>
                    <h2>Registrar Estampa</h2>
                </div>
                <p>Añade una estampa nueva o repetida a tu colección.</p>
                <form method="POST" action="" class="manage-form">
                    <input type="hidden" name="action" value="add">
                    <div class="input-wrapper">
                        <input type="text" name="stamp_code" placeholder="Ej: ARG 1" required autofocus autocomplete="off">
                        <button type="submit" class="btn primary">Agregar</button>
                    </div>
                </form>
            </div>

            <div class="action-card remove-card">
                <div class="card-header">
                    <span class="icon">🗑️</span>
                    <h2>Eliminar Estampa</h2>
                </div>
                <p>Resta una repetida o elimínala por completo.</p>
                <form method="POST" action="" class="manage-form">
                    <input type="hidden" name="action" value="remove">
                    <div class="input-wrapper">
                        <input type="text" name="remove_stamp_code" placeholder="Ej: ARG 1" required autocomplete="off">
                        <button type="submit" class="btn danger">Eliminar</button>
                    </div>
                </form>
            </div>
        </section>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType; ?> manage-alert"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="stats-cards">
            <div class="card card-obtained" onclick="showTab('obtenidas')">
                <h3>Obtenidas</h3>
                <p class="number"><?php echo count($obtained); ?></p>
            </div>
            <div class="card card-missing" onclick="showTab('faltantes')">
                <h3>Faltantes</h3>
                <p class="number"><?php echo count($missing); ?></p>
            </div>
            <div class="card card-repeated" onclick="showTab('repetidas')">
                <h3>Repetidas</h3>
                <p class="number">
                    <?php 
                        $totalRepeated = array_sum(array_column($repeated, 'extra_qty'));
                        echo $totalRepeated; 
                    ?>
                </p>
            </div>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Buscar estampa (ej. ARG 1)" onkeyup="filterStamps()">
            </div>
            <div class="filter-box">
                <select id="countryFilter" onchange="filterStamps()">
                    <option value="">Todos los países</option>
                    <?php foreach ($sections as $name => $prefix): ?>
                        <option value="<?php echo $prefix; ?>"><?php echo $name; ?> (<?php echo $prefix; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('faltantes')">Faltantes</button>
            <button class="tab-btn" onclick="showTab('obtenidas')">Obtenidas</button>
            <button class="tab-btn" onclick="showTab('repetidas')">Repetidas</button>
        </div>

        <div id="faltantes" class="tab-content active">
            <h3>Estampas Faltantes (<?php echo count($missing); ?>)</h3>
            <div class="stamps-grid">
                <?php foreach ($missing as $s): ?>
                    <div class="stamp-item missing">
                        <span class="code"><?php echo $s['code']; ?></span>
                        <span class="country"><?php echo $s['country']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="obtenidas" class="tab-content" style="display:none;">
            <h3>Estampas Obtenidas (<?php echo count($obtained); ?>)</h3>
            <div class="stamps-grid">
                <?php foreach ($obtained as $s): ?>
                    <div class="stamp-item obtained">
                        <span class="code"><?php echo $s['code']; ?></span>
                        <span class="country"><?php echo $s['country']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="repetidas" class="tab-content" style="display:none;">
            <h3>Estampas Repetidas (<?php echo array_sum(array_column($repeated, 'extra_qty')); ?>)</h3>
            <div class="stamps-grid">
                <?php foreach ($repeated as $s): ?>
                    <div class="stamp-item repeated">
                        <span class="code"><?php echo $s['code']; ?></span>
                        <span class="badge"><?php echo $s['extra_qty']; ?> repetida<?php echo $s['extra_qty'] > 1 ? 's' : ''; ?></span>
                        <span class="country"><?php echo $s['country']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            document.getElementById(tabId).style.display = 'block';
            
            // Activate the corresponding button
            document.querySelectorAll('.tab-btn').forEach(btn => {
                if (btn.innerText.toLowerCase().includes(tabId)) {
                    btn.classList.add('active');
                }
            });
        }

        function filterStamps() {
            const searchTerm = document.getElementById('searchInput').value.toUpperCase();
            const countryFilter = document.getElementById('countryFilter').value;
            
            document.querySelectorAll('.stamp-item').forEach(item => {
                const code = item.querySelector('.code').innerText.toUpperCase();
                const itemCountryPrefix = code.split(' ')[0];
                
                const matchesSearch = code.includes(searchTerm);
                const matchesCountry = countryFilter === "" || itemCountryPrefix === countryFilter;
                
                if (matchesSearch && matchesCountry) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
