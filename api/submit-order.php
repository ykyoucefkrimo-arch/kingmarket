<?php
/**
 * submit-order.php — Réception et validation de la commande (Cash on Delivery).
 * Reçoit un POST (JSON ou form-data), valide TOUS les champs côté serveur,
 * insère en base via requête préparée, renvoie une réponse JSON.
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';
require __DIR__ . '/helpers.php';

function repondre(bool $success, string $message, int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Seule la méthode POST est acceptée ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(false, 'طريقة غير مسموح بها.', 405);
}

// ── Lecture des données (accepte form-data classique ET JSON) ──────────
$entree = $_POST;
if (empty($entree)) {
    $brut = file_get_contents('php://input');
    $json = json_decode($brut, true);
    if (is_array($json)) {
        $entree = $json;
    }
}

// ── Honeypot anti-spam : champ caché, doit rester vide pour un humain ───
// Le champ s'appelle "site_web" côté formulaire (voir index.html) : un bot
// qui remplit tous les champs automatiquement le remplira aussi, trahissant
// sa nature. On répond avec un succès factice pour ne pas l'alerter.
if (!empty($entree['site_web'])) {
    repondre(true, 'تم تسجيل الطلب.'); // faux positif volontaire pour les bots
}

// ── Anti-spam : limite de fréquence par IP (1 commande / 60 secondes) ──
$ip = ip_client();
if (ip_a_depasse_limite($pdo, $ip, 60)) {
    repondre(false, 'لقد قمت بتسجيل طلب مؤخرًا. يرجى الانتظار دقيقة قبل إعادة المحاولة.', 429);
}

// ── Validation de chaque champ ──────────────────────────────────────────
$erreurs = [];

$nom = trim($entree['nom'] ?? '');
if ($nom === '' || mb_strlen($nom) > 100) {
    $erreurs[] = 'اللقب غير صالح.';
}

$prenom = trim($entree['prenom'] ?? '');
if ($prenom === '' || mb_strlen($prenom) > 100) {
    $erreurs[] = 'الاسم غير صالح.';
}

$telephone = nettoyer_telephone(trim($entree['telephone'] ?? ''));
if (!telephone_valide($telephone)) {
    $erreurs[] = 'رقم الهاتف غير صالح (مثال: 0555 12 34 56).';
}

$wilaya = trim($entree['wilaya'] ?? '');
if ($wilaya === '' || !wilaya_valide($wilaya)) {
    $erreurs[] = 'الولاية المختارة غير صالحة.';
}

$commune = trim($entree['commune'] ?? '');
if ($commune === '' || ($wilaya !== '' && !commune_valide($wilaya, $commune))) {
    $erreurs[] = 'البلدية المختارة غير صالحة.';
}

$quantite = filter_var($entree['quantite'] ?? 1, FILTER_VALIDATE_INT);
if ($quantite === false || $quantite < 1 || $quantite > 3) {
    $erreurs[] = 'يجب أن تكون الكمية 1 أو 2 أو 3.';
}

$typeLivraison = trim($entree['type_livraison'] ?? 'domicile');
if (!type_livraison_valide($typeLivraison)) {
    $erreurs[] = 'نوع التوصيل غير صالح.';
}

if (!empty($erreurs)) {
    repondre(false, implode(' ', $erreurs), 422);
}

// Le prix de livraison est toujours recalculé côté serveur depuis la table
// frais_livraison (jamais confiance à un montant envoyé par le client).
$fraisLivraison = frais_livraison_pour($pdo, $wilaya, $typeLivraison);

// ── Insertion en base (requête préparée) ────────────────────────────────
try {
    $stmt = $pdo->prepare(
        'INSERT INTO commandes (nom, prenom, telephone, wilaya, commune, quantite, frais_livraison, type_livraison, statut, date_creation, ip_client)
         VALUES (:nom, :prenom, :telephone, :wilaya, :commune, :quantite, :frais_livraison, :type_livraison, :statut, NOW(), :ip)'
    );
    $stmt->execute([
        ':nom'             => $nom,
        ':prenom'          => $prenom,
        ':telephone'       => $telephone,
        ':wilaya'          => $wilaya,
        ':commune'         => $commune,
        ':quantite'        => $quantite,
        ':frais_livraison' => $fraisLivraison,
        ':type_livraison'  => $typeLivraison,
        ':statut'          => 'Nouvelle',
        ':ip'              => $ip,
    ]);
} catch (PDOException $e) {
    error_log('Erreur insertion commande : ' . $e->getMessage());
    repondre(false, 'حدث خطأ في الخادم أثناء التسجيل. يرجى إعادة المحاولة.', 500);
}

repondre(true, 'تم تسجيل طلبك بنجاح! سيتصل بك فريقنا قريبًا لتأكيد تفاصيل التوصيل.');
