<?php
/**
 * dashboard.php — Tableau de bord admin : liste des commandes, filtres,
 * changement de statut, compteurs jour/semaine.
 */

require __DIR__ . '/auth.php'; // vérifie la session en tout premier
require __DIR__ . '/../api/config.php';

// ── Filtres (GET) ────────────────────────────────────────────────────────
$filtreStatut  = trim($_GET['statut'] ?? '');
$filtreWilaya  = trim($_GET['wilaya'] ?? '');
$filtreDateDeb = trim($_GET['date_debut'] ?? '');
$filtreDateFin = trim($_GET['date_fin'] ?? '');

$statutsValides = ['Nouvelle', 'Confirmée', 'En livraison', 'Livrée', 'Annulée'];

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

$stmt = $pdo->prepare("SELECT * FROM commandes $where ORDER BY date_creation DESC LIMIT 500");
$stmt->execute($params);
$commandes = $stmt->fetchAll();

// ── Compteurs ────────────────────────────────────────────────────────────
$compteurJour = $pdo->query(
    "SELECT COUNT(*) FROM commandes WHERE DATE(date_creation) = CURDATE()"
)->fetchColumn();

$compteurSemaine = $pdo->query(
    "SELECT COUNT(*) FROM commandes WHERE YEARWEEK(date_creation, 1) = YEARWEEK(CURDATE(), 1)"
)->fetchColumn();

$compteurTotal = $pdo->query('SELECT COUNT(*) FROM commandes')->fetchColumn();

// Liste des wilayas présentes dans les commandes (pour le filtre <select>)
$wilayasDistinctes = $pdo->query('SELECT DISTINCT wilaya FROM commandes ORDER BY wilaya')->fetchAll(PDO::FETCH_COLUMN);

function badgeStatutClasse(string $statut): string
{
    return match ($statut) {
        'Nouvelle'     => 'badge-nouvelle',
        'Confirmée'    => 'badge-confirmee',
        'En livraison' => 'badge-livraison',
        'Livrée'       => 'badge-livree',
        'Annulée'      => 'badge-annulee',
        default        => '',
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tableau de bord — Smart Ink Case</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">

    <header class="admin-header">
        <div class="admin-header-inner">
            <h1>Smart Ink Case <span>— Admin</span></h1>
            <div class="admin-header-right">
                <span>Bonjour, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
                <a href="livraison.php" class="btn-outline">Frais de livraison</a>
                <a href="logout.php" class="btn-outline">Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="admin-main">

        <section class="admin-counters">
            <div class="counter-card">
                <span class="counter-value"><?= (int) $compteurJour ?></span>
                <span class="counter-label">Commandes aujourd'hui</span>
            </div>
            <div class="counter-card">
                <span class="counter-value"><?= (int) $compteurSemaine ?></span>
                <span class="counter-label">Commandes cette semaine</span>
            </div>
            <div class="counter-card">
                <span class="counter-value"><?= (int) $compteurTotal ?></span>
                <span class="counter-label">Total commandes</span>
            </div>
        </section>

        <section class="admin-filters">
            <form method="GET" action="dashboard.php" class="filters-form">
                <div class="filter-group">
                    <label for="statut">Statut</label>
                    <select name="statut" id="statut">
                        <option value="">Tous</option>
                        <?php foreach ($statutsValides as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= $filtreStatut === $s ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="wilaya">Wilaya</label>
                    <select name="wilaya" id="wilaya">
                        <option value="">Toutes</option>
                        <?php foreach ($wilayasDistinctes as $w): ?>
                            <option value="<?= htmlspecialchars($w) ?>" <?= $filtreWilaya === $w ? 'selected' : '' ?>>
                                <?= htmlspecialchars($w) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date_debut">Du</label>
                    <input type="date" name="date_debut" id="date_debut" value="<?= htmlspecialchars($filtreDateDeb) ?>">
                </div>

                <div class="filter-group">
                    <label for="date_fin">Au</label>
                    <input type="date" name="date_fin" id="date_fin" value="<?= htmlspecialchars($filtreDateFin) ?>">
                </div>

                <div class="filter-group filter-actions">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="dashboard.php" class="btn-outline">Réinitialiser</a>
                    <a href="export-csv.php?<?= htmlspecialchars(http_build_query($_GET)) ?>" class="btn-outline">Exporter CSV</a>
                </div>
            </form>
        </section>

        <section class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Téléphone</th>
                        <th>Wilaya</th>
                        <th>Commune</th>
                        <th>Qté</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr><td colspan="8" class="table-empty">Aucune commande trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $cmd): ?>
                            <tr data-id="<?= (int) $cmd['id'] ?>">
                                <td>#<?= (int) $cmd['id'] ?></td>
                                <td><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></td>
                                <td><?= htmlspecialchars($cmd['telephone']) ?></td>
                                <td><?= htmlspecialchars($cmd['wilaya']) ?></td>
                                <td><?= htmlspecialchars($cmd['commune']) ?></td>
                                <td><?= (int) $cmd['quantite'] ?></td>
                                <td>
                                    <select class="statut-select <?= badgeStatutClasse($cmd['statut']) ?>" data-id="<?= (int) $cmd['id'] ?>">
                                        <?php foreach ($statutsValides as $s): ?>
                                            <option value="<?= htmlspecialchars($s) ?>" <?= $cmd['statut'] === $s ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($cmd['date_creation']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

    </main>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
