<?php
/**
 * config.php — Connexion PDO à la base de données MySQL.
 *
 * IMPORTANT (sécurité) :
 * - Ce fichier contient des identifiants sensibles. Un .htaccess placé dans
 *   ce même dossier (api/.htaccess) bloque déjà son accès direct via
 *   navigateur. Vérifiez qu'il est bien uploadé.
 * - Si votre offre Hostinger le permet, il est encore plus sûr de placer ce
 *   fichier UN NIVEAU AU-DESSUS du dossier public (public_html), puis de
 *   l'inclure avec un chemin absolu (require '/home/votre_compte/config.php').
 *   Sur un hébergement mutualisé simple, le .htaccess suffit généralement.
 *
 * >>> À ÉDITER avant la mise en ligne : remplacez les 4 valeurs ci-dessous
 * par celles fournies dans hPanel Hostinger (Bases de données > MySQL).
 */

// ── Identifiants de connexion (À MODIFIER) ──────────────────────────────
define('DB_HOST', 'localhost');                 // Reste "localhost" sur Hostinger
define('DB_NAME', 'u123456789_kingmarket');      // Nom de la base (préfixé u123456789_ chez Hostinger)
define('DB_USER', 'u123456789_admin');           // Utilisateur MySQL
define('DB_PASS', 'CHANGEZ_MOI_MOT_DE_PASSE');   // Mot de passe MySQL
// ── Fuseau horaire (dates de commande cohérentes) ───────────────────────
date_default_timezone_set('Africa/Algiers');

// ── Connexion PDO ────────────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // requêtes préparées natives
        ]
    );
} catch (PDOException $e) {
    // On ne renvoie JAMAIS le message d'erreur PDO brut au client (fuite
    // d'informations sur la config serveur) : on logge et on répond en JSON.
    error_log('Erreur de connexion BDD : ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur. Merci de réessayer plus tard.',
    ]);
    exit;
}
