<?php
/**
 * livraison.php — Gestion des frais de livraison par wilaya.
 * Liste toutes les wilayas (assets/js/wilayas.json) avec deux champs prix
 * chacune (domicile / point relais) ; enregistrement en un seul clic
 * (admin/save-livraison.php).
 */

require __DIR__ . '/auth.php';
require __DIR__ . '/../api/config.php';

$cheminWilayas = __DIR__ . '/../assets/js/wilayas.json';
$data = json_decode(file_get_contents($cheminWilayas), true);
$wilayas = $data['wilayas'] ?? [];
usort($wilayas, fn ($a, $b) => $a['wilaya_id'] <=> $b['wilaya_id']);

$stmt = $pdo->query('SELECT wilaya, prix_domicile, prix_point_relais FROM frais_livraison');
$prixExistants = [];
foreach ($stmt->fetchAll() as $ligne) {
    $prixExistants[$ligne['wilaya']] = [
        'domicile'     => (int) $ligne['prix_domicile'],
        'point_relais' => (int) $ligne['prix_point_relais'],
    ];
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
            Les prix saisis ici (en DZD) sont automatiquement affichés au client sur le
            formulaire de commande et ajoutés au total, selon la wilaya et le type de
            livraison (domicile ou point relais) choisis. Une valeur laissée à 0 n'ajoute
            aucun frais pour ce type.
        </p>

        <div id="livraison-message" class="admin-alert" style="display:none;"></div>

        <form id="livraison-form">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Wilaya</th>
                            <th>Livraison à domicile (DZD)</th>
                            <th>Point relais (DZD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wilayas as $w):
                            $nomLatin = $w['wilaya_name_latin'];
                            $prixDomicile = $prixExistants[$nomLatin]['domicile'] ?? 0;
                            $prixRelais   = $prixExistants[$nomLatin]['point_relais'] ?? 0;
                        ?>
                            <tr>
                                <td><?= (int) $w['wilaya_id'] ?></td>
                                <td><?= htmlspecialchars($nomLatin) ?></td>
                                <td>
                                    <input
                                        type="number"
                                        min="0"
                                        step="50"
                                        name="prix_domicile[<?= htmlspecialchars($nomLatin) ?>]"
                                        value="<?= $prixDomicile ?>"
                                        style="width:120px;padding:6px 10px;border:1.5px solid var(--couleur-bordure);border-radius:var(--rayon-sm);"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        min="0"
                                        step="50"
                                        name="prix_point_relais[<?= htmlspecialchars($nomLatin) ?>]"
                                        value="<?= $prixRelais ?>"
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

    <script src="../assets/js/livraison.js?v=2"></script>
</body>
</html>
