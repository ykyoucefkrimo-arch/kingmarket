<?php
/**
 * livraison.php — Gestion des frais de livraison par wilaya.
 * Liste toutes les wilayas (assets/js/wilayas.json) avec un champ prix
 * chacune ; enregistrement en un seul clic (admin/save-livraison.php).
 */

require __DIR__ . '/auth.php';
require __DIR__ . '/../api/config.php';

$cheminWilayas = __DIR__ . '/../assets/js/wilayas.json';
$data = json_decode(file_get_contents($cheminWilayas), true);
$wilayas = $data['wilayas'] ?? [];
usort($wilayas, fn ($a, $b) => $a['wilaya_id'] <=> $b['wilaya_id']);

$stmt = $pdo->query('SELECT wilaya, prix FROM frais_livraison');
$prixExistants = [];
foreach ($stmt->fetchAll() as $ligne) {
    $prixExistants[$ligne['wilaya']] = (int) $ligne['prix'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Frais de livraison — Smart Ink Case</title>
<link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>
<body class="admin-body">

    <header class="admin-header">
        <div class="admin-header-inner">
            <h1>Smart Ink Case <span>— Admin</span></h1>
            <div class="admin-header-right">
                <a href="dashboard.php" class="btn-outline">← Tableau de bord</a>
                <a href="logout.php" class="btn-outline">Déconnexion</a>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <h2 style="margin-bottom:6px;">Frais de livraison par wilaya</h2>
        <p style="color:var(--couleur-texte-att);margin-bottom:20px;">
            Le prix saisi ici (en DZD) est automatiquement affiché au client sur le
            formulaire de commande et ajouté au total, selon la wilaya choisie.
            Une wilaya laissée à 0 n'ajoute aucun frais.
        </p>

        <div id="livraison-message" class="admin-alert" style="display:none;"></div>

        <form id="livraison-form">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Wilaya</th>
                            <th>Frais de livraison (DZD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wilayas as $w): ?>
                            <tr>
                                <td><?= (int) $w['wilaya_id'] ?></td>
                                <td><?= htmlspecialchars($w['wilaya_name_latin']) ?></td>
                                <td>
                                    <input
                                        type="number"
                                        min="0"
                                        step="50"
                                        name="prix[<?= htmlspecialchars($w['wilaya_name_latin']) ?>]"
                                        value="<?= (int) ($prixExistants[$w['wilaya_name_latin']] ?? 0) ?>"
                                        style="width:120px;padding:6px 10px;border:1.5px solid var(--couleur-bordure);border-radius:var(--rayon-sm);"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-primary">Enregistrer tous les tarifs</button>
            </div>
        </form>
    </main>

    <script src="../assets/js/livraison.js"></script>
</body>
</html>
