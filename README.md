# Smart Ink Case — Site de vente (Cash on Delivery)

Landing page + backend de commande (paiement à la livraison) pour la coque
**Smart Ink Case**. Stack 100% compatible hébergement mutualisé Hostinger :
HTML/CSS/JS vanilla + PHP natif + MySQL. Aucun build tool, aucune dépendance
Node.js — il suffit d'uploader les fichiers.

---

## Structure du projet

```
/
├── index.html                 → Landing page (une seule page, tout est dedans)
├── assets/
│   ├── css/style.css          → Toute la feuille de style (site + admin)
│   ├── js/main.js             → Logique de la landing page
│   ├── js/admin.js            → Logique du tableau de bord admin
│   └── js/wilayas.json        → Base wilayas/communes d'Algérie
├── api/
│   ├── config.php             → Connexion PDO (À ÉDITER avant mise en ligne)
│   ├── helpers.php            → Fonctions de validation partagées
│   ├── submit-order.php       → Réception du formulaire de commande
│   ├── db_schema.sql          → Schéma SQL à importer
│   └── .htaccess              → Bloque l'accès direct aux fichiers sensibles
└── admin/
    ├── auth.php                → Garde de session (inclus par chaque page admin)
    ├── login.php                → Connexion admin
    ├── setup.php                → Création du 1er compte admin (une seule fois)
    ├── dashboard.php             → Tableau de bord (liste, filtres, statuts)
    ├── update-status.php         → Endpoint AJAX changement de statut
    ├── export-csv.php            → Export CSV des commandes filtrées
    └── logout.php                → Déconnexion
```

---

## 1. Créer la base de données MySQL sur Hostinger (hPanel)

1. Connectez-vous à **hPanel** → **Bases de données** → **Bases de données MySQL**.
2. Créez une nouvelle base (ex. `kingmarket`). Hostinger génère un nom final du type
   `u123456789_kingmarket`.
3. Créez un utilisateur MySQL avec un **mot de passe fort**, puis attachez-le à la
   base avec **tous les privilèges**.
4. Notez les 4 informations suivantes (vous en aurez besoin à l'étape 3) :
   - Hôte (généralement `localhost`)
   - Nom de la base (`u123456789_kingmarket`)
   - Utilisateur (`u123456789_admin`)
   - Mot de passe

---

## 2. Importer le schéma SQL via phpMyAdmin

1. Dans hPanel, ouvrez **phpMyAdmin** et sélectionnez votre base fraîchement créée.
2. Allez dans l'onglet **Importer**.
3. Sélectionnez le fichier `api/db_schema.sql` de ce projet.
4. Cliquez sur **Exécuter**. Deux tables sont créées :
   - `commandes` (les commandes reçues)
   - `admins` (les comptes du panneau d'administration — **vide** au départ)

---

## 3. Configurer les identifiants (api/config.php)

Ouvrez `api/config.php` et remplacez les 4 valeurs par celles notées à l'étape 1 :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456789_kingmarket');
define('DB_USER', 'u123456789_admin');
define('DB_PASS', 'votre_mot_de_passe');
```

⚠️ Ne partagez jamais ce fichier publiquement. Le `.htaccess` du dossier `api/`
bloque déjà son accès direct via navigateur, mais restez prudent (ne le
commitez pas sur un dépôt public sans précaution).

---

## 4. Uploader les fichiers sur Hostinger

**Via le Gestionnaire de fichiers (hPanel) :**
1. Compressez le contenu de ce dossier en `.zip` (à la racine du zip, pas dans
   un sous-dossier).
2. hPanel → **Gestionnaire de fichiers** → ouvrez `public_html` (ou le
   sous-dossier de votre domaine/sous-domaine).
3. Uploadez le `.zip`, puis clic droit → **Extraire**.
4. Vérifiez que `index.html` se trouve bien directement dans `public_html`
   (pas dans `public_html/kingmarketdz/`).

**Via FTP (FileZilla, etc.) :**
1. Récupérez vos identifiants FTP dans hPanel → **Comptes FTP**.
2. Connectez-vous et transférez tout le contenu du dossier vers `public_html`
   (ou le sous-dossier de votre domaine).

---

## 5. Créer le premier compte admin

1. Une fois les fichiers en ligne, ouvrez dans votre navigateur :
   `https://votredomaine.dz/admin/setup.php`
2. Choisissez un identifiant et un mot de passe (8 caractères minimum).
3. Validez : votre compte est créé.
4. **Ce script se désactive automatiquement** dès qu'un compte admin existe —
   il est donc impossible de créer un second compte par ce biais, y compris
   pour un visiteur malintentionné qui retrouverait l'URL. Vous pouvez même
   supprimer le fichier `admin/setup.php` du serveur une fois votre compte créé.
5. Connectez-vous ensuite sur `https://votredomaine.dz/admin/login.php`.

---

## 6. Vérifications avant mise en ligne définitive

- [ ] `api/config.php` contient bien vos vrais identifiants MySQL
- [ ] Le formulaire de commande (`index.html`) enregistre bien une commande de
      test (vérifiez dans `admin/dashboard.php` ou via phpMyAdmin)
- [ ] Le compte admin est créé et la connexion fonctionne
- [ ] Le prix affiché dans `index.html` (bloc offre) et dans
      `assets/js/main.js` (variable `PRIX_UNITAIRE`, utilisée pour le
      récapitulatif) correspond à votre tarif réel
- [ ] Les visuels `IMAGE: ...` (placeholders) sont remplacés par vos vraies
      photos produit

---

## Notes techniques

- **Anti-spam** : un champ honeypot caché (`site_web`) piège les robots, et
  une limite d'1 commande par IP toutes les 60 secondes est appliquée côté
  serveur (voir `api/helpers.php` → `ip_a_depasse_limite()`).
- **Validation** : le numéro de téléphone, la wilaya et la commune sont
  revalidés côté serveur (jamais confiance au JavaScript seul).
- **Sécurité** : requêtes SQL exclusivement préparées (PDO), sortie HTML
  systématiquement échappée (`htmlspecialchars`), mots de passe hashés
  (`password_hash`/`password_verify`), sessions avec cookies `httponly`.
- **Prix** : la variable `PRIX_UNITAIRE` dans `assets/js/main.js` ne sert
  qu'à l'affichage du récapitulatif côté client — elle n'a aucun impact sur
  la base de données (la table `commandes` ne stocke pas de prix, seulement
  la quantité, conformément au cahier des charges).
