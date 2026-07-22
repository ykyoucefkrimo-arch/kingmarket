<?php
/**
 * save-livraison.php — Enregistre en une fois les frais de livraison
 * (domicile + point relais) de toutes les wilayas soumises
 * (admin/livraison.php). Upsert (INSERT ... ON DUPLICATE KEY UPDATE) : la
 * wilaya est la clé primaire de frais_livraison.
 */

require __DIR__ . '/auth.php';
require __DIR__ . '/../api/config.php';
require __DIR__ . '/../api/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$prixDomicileSoumis = $_POST['prix_domicile'] ?? [];
$prixRelaisSoumis   = $_POST['prix_point_relais'] ?? [];

if (!is_array($prixDomicileSoumis) || empty($prixDomicileSoumis)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue.']);
    exit;
}

// Ne garde que les wilayas réellement reconnues (jamais confiance à une clé
// de tableau arbitraire envoyée par le client).
$wilayasValides = array_keys(charger_wilayas_communes());

function entier_positif_ou_zero($valeur): int
{
    $entier = filter_var($valeur, FILTER_VALIDATE_INT);
    return ($entier === false || $entier < 0) ? 0 : $entier;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO frais_livraison (wilaya, prix_domicile, prix_point_relais)
         VALUES (:wilaya, :prix_domicile, :prix_point_relais)
         ON DUPLICATE KEY UPDATE
            prix_domicile = VALUES(prix_domicile),
            prix_point_relais = VALUES(prix_point_relais)'
    );

    foreach ($wilayasValides as $wilaya) {
        if (!array_key_exists($wilaya, $prixDomicileSoumis)) {
            continue;
        }
        $stmt->execute([
            ':wilaya'            => $wilaya,
            ':prix_domicile'     => entier_positif_ou_zero($prixDomicileSoumis[$wilaya]),
            ':prix_point_relais' => entier_positif_ou_zero($prixRelaisSoumis[$wilaya] ?? 0),
        ]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Erreur enregistrement frais_livraison : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur lors de l\'enregistrement.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Tarifs enregistrés avec succès.']);
