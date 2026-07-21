<?php
/**
 * login.php — Connexion admin (session PHP + password_verify).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require __DIR__ . '/../api/config.php';

// Déjà connecté ? Direction le tableau de bord.
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $erreur = 'Merci de remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true); // évite la fixation de session
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header('Location: dashboard.php');
            exit;
        }

        $erreur = 'Identifiants incorrects.';
    }
}

// Aucun compte admin ? On oriente vers la création initiale.
$stmtCount = $pdo->query('SELECT COUNT(*) FROM admins');
$aucunAdmin = ((int) $stmtCount->fetchColumn()) === 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion Admin — Smart Ink Case</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-login-wrap">
        <form class="admin-login-card" method="POST" action="login.php" autocomplete="off">
            <h1>Espace Admin</h1>
            <p class="admin-login-sub">Smart Ink Case — Gestion des commandes</p>

            <?php if ($aucunAdmin): ?>
                <div class="admin-alert admin-alert-warning">
                    Aucun compte admin n'existe encore.
                    <a href="setup.php">Créer le premier compte admin →</a>
                </div>
            <?php endif; ?>

            <?php if ($erreur): ?>
                <div class="admin-alert admin-alert-error"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <label for="username">Identifiant</label>
            <input type="text" id="username" name="username" required autofocus>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
    </div>
</body>
</html>
