/**
 * livraison.js — Enregistrement AJAX du formulaire des frais de livraison
 * (admin/livraison.php).
 */
(function () {
    'use strict';

    const form = document.getElementById('livraison-form');
    const messageEl = document.getElementById('livraison-message');
    if (!form) return;

    form.addEventListener('submit', function (evenement) {
        evenement.preventDefault();

        const bouton = form.querySelector('button[type="submit"]');
        bouton.disabled = true;
        bouton.textContent = 'Enregistrement...';

        fetch('save-livraison.php', {
            method: 'POST',
            body: new URLSearchParams(new FormData(form)),
        })
            .then(function (reponse) { return reponse.json(); })
            .then(function (data) {
                messageEl.style.display = 'block';
                messageEl.className = 'admin-alert ' + (data.success ? 'admin-alert-success' : 'admin-alert-error');
                messageEl.textContent = data.message || (data.success ? 'Enregistré.' : 'Erreur.');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(function () {
                messageEl.style.display = 'block';
                messageEl.className = 'admin-alert admin-alert-error';
                messageEl.textContent = 'Erreur réseau. Merci de réessayer.';
            })
            .finally(function () {
                bouton.disabled = false;
                bouton.textContent = 'Enregistrer tous les tarifs';
            });
    });
})();
