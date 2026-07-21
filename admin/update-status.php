<?php
/**
 * update-status.php — Endpoint AJAX (appelé depuis dashboard.php) pour
 * changer le statut d'une commande via la liste déroulante.
 */

require __DIR__ . '/auth.php'; // vérifie la session, redirige sinon
require __DIR__ . '/../api/config.php';

header('Content-Type: application/json; charset=utf-8');

$statutsValides = ['Nouvelle', 'Confirmée', 'En livraison', 'Livrée', 'Annulée'];

$id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$statut = trim($_POST['statut'] ?? '');

if (!$id || !in_array($statut, $statutsValides, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

try {
    $stmt = $pdo->prepare('UPDATE commandes SET statut = :statut WHERE id = :id');
    $stmt->execute([':statut' => $statut, ':id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Statut mis à jour.']);
} catch (PDOException $e) {
    error_log('Erreur update statut : ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}
