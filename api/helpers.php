<?php
/**
 * helpers.php — Fonctions de validation partagées côté serveur.
 * Ne JAMAIS faire confiance à la validation JavaScript : tout est
 * revérifié ici avant toute insertion en base.
 */

/**
 * Valide un numéro de téléphone algérien : 05/06/07 suivi de 8 chiffres
 * (10 chiffres au total), espaces/points/tirets tolérés puis nettoyés.
 */
function nettoyer_telephone(string $telephone): string
{
    return preg_replace('/[\s.\-]/', '', $telephone);
}

function telephone_valide(string $telephone): bool
{
    return (bool) preg_match('/^0[567][0-9]{8}$/', $telephone);
}

/**
 * Charge la liste officielle des wilayas/communes (assets/js/wilayas.json)
 * et renvoie un tableau associatif [ 'NomWilaya' => ['Commune1', 'Commune2', ...] ].
 * Mis en cache statique pour éviter de relire/reparser le fichier plusieurs
 * fois par requête.
 */
function charger_wilayas_communes(): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $chemin = __DIR__ . '/../assets/js/wilayas.json';
    if (!is_readable($chemin)) {
        $cache = [];
        return $cache;
    }

    $data = json_decode(file_get_contents($chemin), true);
    if (!is_array($data) || empty($data['wilayas']) || empty($data['communes'])) {
        $cache = [];
        return $cache;
    }

    // wilaya_id -> nom
    $nomsWilayas = [];
    foreach ($data['wilayas'] as $w) {
        $nomsWilayas[$w['wilaya_id']] = $w['wilaya_name_latin'];
    }

    // nom wilaya -> [communes]
    $resultat = [];
    foreach ($data['communes'] as $c) {
        $nomWilaya = $nomsWilayas[$c['wilaya_id']] ?? null;
        if ($nomWilaya === null) {
            continue;
        }
        $resultat[$nomWilaya][] = $c['commune_name_latin'];
    }

    $cache = $resultat;
    return $cache;
}

/** Vérifie qu'une wilaya existe bien dans la liste officielle. */
function wilaya_valide(string $wilaya): bool
{
    $liste = charger_wilayas_communes();
    return array_key_exists($wilaya, $liste);
}

/** Vérifie qu'une commune appartient bien à la wilaya donnée. */
function commune_valide(string $wilaya, string $commune): bool
{
    $liste = charger_wilayas_communes();
    if (!isset($liste[$wilaya])) {
        return false;
    }
    return in_array($commune, $liste[$wilaya], true);
}

/**
 * Anti-spam simple par IP : renvoie true si l'IP a déjà passé commande
 * dans les $secondes dernières secondes (par défaut 60s = 1 commande/minute).
 */
function ip_a_depasse_limite(PDO $pdo, string $ip, int $secondes = 60): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM commandes
         WHERE ip_client = :ip
         AND date_creation >= (NOW() - INTERVAL :secondes SECOND)'
    );
    $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':secondes', $secondes, PDO::PARAM_INT);
    $stmt->execute();

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Récupère le prix de livraison (DZD) configuré pour une wilaya donnée.
 * Retourne 0 si la wilaya n'a pas (encore) de tarif défini par l'admin —
 * ne jamais faire confiance à un montant envoyé par le client.
 */
function frais_livraison_pour(PDO $pdo, string $wilaya): int
{
    $stmt = $pdo->prepare('SELECT prix FROM frais_livraison WHERE wilaya = :wilaya LIMIT 1');
    $stmt->execute([':wilaya' => $wilaya]);
    $prix = $stmt->fetchColumn();

    return $prix === false ? 0 : (int) $prix;
}

/** Récupère l'IP réelle du client (en tenant compte d'un éventuel proxy). */
function ip_client(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $cle) {
        if (!empty($_SERVER[$cle])) {
            $ip = trim(explode(',', $_SERVER[$cle])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}
