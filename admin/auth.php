<?php
/**
 * auth.php — Garde de session, à inclure en tout premier sur CHAQUE page
 * admin (dashboard.php, export-csv.php...). Redirige vers login.php si
 * l'utilisateur n'est pas authentifié.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Cookie de session durci (httponly + samesite) pour limiter le vol de
    // session via XSS/CSRF basique.
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
