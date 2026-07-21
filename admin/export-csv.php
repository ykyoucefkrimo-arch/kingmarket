<?php
/**
 * export-csv.php — Export CSV des commandes, en respectant les mêmes
 * filtres (statut/wilaya/dates) que le tableau de bord (transmis en GET).
 */

require __DIR__ . '/auth.php';
require __DIR__ . '/../api/config.php';

$statutsValides = ['Nouvelle', 'Confirmée', 'En livraison', 'Livrée', 'Annulée'];

$filtreStatut  = trim($_GET['statut'] ?? '');
$filtreWilaya  = trim($_GET['wilaya'] ?? '');
$filtreDateDeb = trim($_GET['date_debut'] ?? '');
$filtreDateFin = trim($_GET['date_fin'] ?? '');

$conditions = [];
$params     = [];

if ($filtreStatut !== '' && in_array($filtreStatut, $statutsValides, true)) {
    $conditions[] = 'statut = :statut';
    $params[':statut'] = $filtreStatut;
}
if ($filtreWilaya !== '') {
    $conditions[] = 'wilaya = :wilaya';
    $params[':wilaya'] = $filtreWilaya;
}
if ($filtreDateDeb !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtreDateDeb)) {
    $conditions[] = 'date_creation >= :date_debut';
    $params[':date_debut'] = $filtreDateDeb . ' 00:00:00';
}
if ($filtreDateFin !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtreDateFin)) {
    $conditions[] = 'date_creation <= :date_fin';
    $params[':date_fin'] = $filtreDateFin . ' 23:59:59';
}

$where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmt = $pdo->prepare("SELECT * FROM commandes $where ORDER BY date_creation DESC");
$stmt->execute($params);
$commandes = $stmt->fetchAll();

// ── En-têtes HTTP pour forcer le téléchargement ─────────────────────────
$nomFichier = 'commandes_smart_ink_case_' . date('Y-m-d_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomFichier . '"');

$sortie = fopen('php://output', 'w');

// BOM UTF-8 : garantit un affichage correct des accents dans Excel.
fwrite($sortie, "\xEF\xBB\xBF");

// Paramètres d'échappement explicites : requis à partir de PHP 8.4 (sinon
// avertissement de dépréciation qui polluerait le fichier CSV généré).
fputcsv($sortie, ['ID', 'Nom', 'Prénom', 'Téléphone', 'Wilaya', 'Commune', 'Quantité', 'Statut', 'Date de création', 'IP'], ',', '"', '\\');

foreach ($commandes as $cmd) {
    fputcsv($sortie, [
        $cmd['id'],
        $cmd['nom'],
        $cmd['prenom'],
        $cmd['telephone'],
        $cmd['wilaya'],
        $cmd['commune'],
        $cmd['quantite'],
        $cmd['statut'],
        $cmd['date_creation'],
        $cmd['ip_client'],
    ], ',', '"', '\\');
}

fclose($sortie);
exit;
