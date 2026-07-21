<?php
/**
 * save-livraison.php — Enregistre en une fois les frais de livraison de
 * toutes les wilayas soumises (admin/livraison.php). Upsert (INSERT ... ON
 * DUPLICATE KEY UPDATE) : la wilaya est la clé primaire de frais_livraison.
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

$prixSoumis = $_POST['prix'] ?? [];
if (!is_array($prixSoumis) || empty($prixSoumis)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Aucune donnée reçue.']);
    exit;
}

// Ne garde que les wilayas réellement reconnues (jamais confiance à une clé
// de tableau arbitraire envoyée par le client).
$wilayasValides = array_keys(charger_wilayas_communes());

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO frais_livraison (wilaya, prix) VALUES (:wilaya, :prix)
         ON DUPLICATE KEY UPDATE prix = VALUES(prix)'
    );

    foreach ($prixSoumis as $wilaya => $prix) {
        if (!in_array($wilaya, $wilayasValides, true)) {
            continue;
        }
        $prixEntier = filter_var($prix, FILTER_VALIDATE_INT);
        if ($prixEntier === false || $prixEntier < 0) {
            $prixEntier = 0;
        }
        $stmt->execute([':wilaya' => $wilaya, ':prix' => $prixEntier]);
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
