/**
 * admin.js — Changement de statut des commandes via AJAX (dashboard.php).
 */
(function () {
    'use strict';

    const classesBadges = {
        'Nouvelle': 'badge-nouvelle',
        'Confirmée': 'badge-confirmee',
        'En livraison': 'badge-livraison',
        'Livrée': 'badge-livree',
        'Annulée': 'badge-annulee',
    };

    document.querySelectorAll('.statut-select').forEach(function (select) {
        select.addEventListener('change', function () {
            const id = select.dataset.id;
            const nouveauStatut = select.value;
            const ancienneClasse = Object.values(classesBadges).join(' ');

            select.disabled = true;

            const donnees = new URLSearchParams();
            donnees.append('id', id);
            donnees.append('statut', nouveauStatut);

            fetch('update-status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: donnees.toString(),
            })
                .then(function (reponse) { return reponse.json(); })
                .then(function (data) {
                    if (data.success) {
                        select.classList.remove(...ancienneClasse.split(' '));
                        select.classList.add(classesBadges[nouveauStatut] || '');
                    } else {
                        alert(data.message || 'Erreur lors de la mise à jour du statut.');
                    }
                })
                .catch(function () {
                    alert('Erreur réseau. Merci de réessayer.');
                })
                .finally(function () {
                    select.disabled = false;
                });
        });
    });
})();
