<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $isValid = false;
        
        // Verificar si es un hash seguro
        if (password_verify($password, $user['password'])) {
            $isValid = true;
        } 
        // Si no es un hash, verificar si es texto plano (y actualizarlo a hash por seguridad)
        elseif ($password === $user['password']) {
            $isValid = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$newHash, $user['id']]);
        }

        if ($isValid) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Álbum Mundial 2026</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <h1>Álbum 2026</h1>
            <p>Ingresa para gestionar tus estampas</p>
            <?php if ($error): ?>
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn primary">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>
