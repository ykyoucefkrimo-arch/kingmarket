<?php
/**
 * frais-livraison.php — Endpoint public en LECTURE SEULE : renvoie les
 * frais de livraison de toutes les wilayas déjà configurées, sous forme
 * { "Alger": 400, "Oran": 500, ... }. Aucune donnée sensible : accessible
 * sans authentification, utilisé par assets/js/main.js pour afficher le
 * total (produit + livraison) selon la wilaya choisie par le client.
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';

try {
    $stmt = $pdo->query('SELECT wilaya, prix FROM frais_livraison');
    $lignes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur lecture frais_livraison : ' . $e->getMessage());
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$resultat = [];
foreach ($lignes as $ligne) {
    $resultat[$ligne['wilaya']] = (int) $ligne['prix'];
}

echo json_encode($resultat, JSON_UNESCAPED_UNICODE);
