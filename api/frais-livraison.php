<?php
/**
 * frais-livraison.php — Endpoint public en LECTURE SEULE : renvoie les
 * frais de livraison (domicile + point relais) de toutes les wilayas déjà
 * configurées, sous forme
 * { "Alger": { "domicile": 400, "point_relais": 200 }, ... }.
 * Aucune donnée sensible : accessible sans authentification, utilisé par
 * assets/js/main.js pour afficher le total selon la wilaya et le type de
 * livraison choisis par le client.
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/config.php';

try {
    $stmt = $pdo->query('SELECT wilaya, prix_domicile, prix_point_relais FROM frais_livraison');
    $lignes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Erreur lecture frais_livraison : ' . $e->getMessage());
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

$resultat = [];
foreach ($lignes as $ligne) {
    $resultat[$ligne['wilaya']] = [
        'domicile'     => (int) $ligne['prix_domicile'],
        'point_relais' => (int) $ligne['prix_point_relais'],
    ];
}

echo json_encode($resultat, JSON_UNESCAPED_UNICODE);
