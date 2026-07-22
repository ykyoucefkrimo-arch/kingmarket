<?php
/**
 * setup.php — Création du TOUT PREMIER compte admin.
 *
 * Sécurité : ce script se désactive automatiquement dès qu'un compte admin
 * existe déjà (impossible d'en créer un second par ce biais, y compris pour
 * un attaquant qui retrouverait l'URL). Une fois votre compte créé, vous
 * pouvez même supprimer ce fichier du serveur si vous le souhaitez.
 */

require __DIR__ . '/../api/config.php';

$stmtCount = $pdo->query('SELECT COUNT(*) FROM admins');
$aucunAdmin = ((int) $stmtCount->fetchColumn()) === 0;

$erreur  = '';
$succes  = false;

if (!$aucunAdmin) {
    $erreur = 'Un compte admin existe déjà. Ce script est désactivé. Rendez-vous sur login.php.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username        = trim($_POST['username'] ?? '');
    $password        = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (mb_strlen($username) < 3) {
        $erreur = 'L\'identifiant doit contenir au moins 3 caractères.';
    } elseif (mb_strlen($password) < 8) {
        $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $passwordConfirm) {
        $erreur = 'Les deux mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (:u, :h)');
        $stmt->execute([':u' => $username, ':h' => $hash]);
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Créer le compte Admin — Smart Ink Case</title>
<link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="admin-body">
    <div class="admin-login-wrap">
        <?php if ($succes): ?>
            <div class="admin-login-card">
                <h1>Compte créé ✓</h1>
                <p class="admin-login-sub">Votre compte admin a bien été créé.</p>
                <a class="btn-primary" href="login.php" style="display:block;text-align:center;text-decoration:none;">Se connecter →</a>
            </div>
        <?php else: ?>
            <form class="admin-login-card" method="POST" action="setup.php" autocomplete="off">
                <h1>Premier compte admin</h1>
                <p class="admin-login-sub">À faire une seule fois, juste après la mise en ligne.</p>

                <?php if ($erreur): ?>
                    <div class="admin-alert admin-alert-error"><?= htmlspecialchars($erreur) ?></div>
                <?php endif; ?>

                <?php if ($aucunAdmin): ?>
                    <label for="username">Identifiant</label>
                    <input type="text" id="username" name="username" required autofocus minlength="3">

                    <label for="password">Mot de passe (8 caractères min.)</label>
                    <input type="password" id="password" name="password" required minlength="8">

                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

                    <button type="submit" class="btn-primary">Créer le compte</button>
                <?php else: ?>
                    <a class="btn-primary" href="login.php" style="display:block;text-align:center;text-decoration:none;">Aller à la connexion →</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
