/**
 * main.js — Logique de la landing page Smart Ink Case (version arabe).
 * Vanilla JS uniquement, aucune dépendance externe.
 *
 * Note : les valeurs envoyées au serveur (wilaya/commune) restent en
 * transcription latine (wilaya_name_latin / commune_name_latin) afin de
 * rester compatibles avec la validation PHP (api/helpers.php) et
 * l'affichage du panneau admin (resté en français). Seul le LIBELLÉ visible
 * dans les <option> est en arabe.
 */
(function () {
    'use strict';

    /* ============================================================
       1. Wilayas / communes (peuplées depuis assets/js/wilayas.json)
       ============================================================ */
    const selectWilaya  = document.getElementById('wilaya');
    const selectCommune = document.getElementById('commune');

    let communesParWilaya = {}; // { "NomLatinWilaya": [{ valeur, libelle }, ...] }

    function chargerWilayas() {
        fetch('assets/js/wilayas.json')
            .then(function (reponse) { return reponse.json(); })
            .then(function (data) {
                if (!data || !Array.isArray(data.wilayas) || !Array.isArray(data.communes)) {
                    console.error('Format de wilayas.json inattendu.');
                    return;
                }

                // wilaya_id -> nom latin (valeur envoyée au serveur)
                const nomsLatinsWilayas = {};
                data.wilayas.forEach(function (w) {
                    nomsLatinsWilayas[w.wilaya_id] = w.wilaya_name_latin;
                });

                // Regroupe les communes par wilaya (valeur = latin, libellé = arabe)
                data.communes.forEach(function (c) {
                    const nomLatinWilaya = nomsLatinsWilayas[c.wilaya_id];
                    if (!nomLatinWilaya) return;
                    if (!communesParWilaya[nomLatinWilaya]) communesParWilaya[nomLatinWilaya] = [];
                    communesParWilaya[nomLatinWilaya].push({
                        valeur: c.commune_name_latin,
                        libelle: c.commune_name_arabic,
                    });
                });

                // Peuple le <select> Wilaya (libellé arabe, trié par numéro de wilaya)
                const wilayasTriees = data.wilayas
                    .slice()
                    .sort(function (a, b) { return a.wilaya_id - b.wilaya_id; });

                wilayasTriees.forEach(function (w) {
                    const option = document.createElement('option');
                    option.value = w.wilaya_name_latin;
                    option.textContent = w.wilaya_id + ' — ' + w.wilaya_name_arabic;
                    selectWilaya.appendChild(option);
                });
            })
            .catch(function (err) {
                console.error('Impossible de charger wilayas.json', err);
            });
    }

    function peuplerCommunes(nomLatinWilaya) {
        selectCommune.innerHTML = '<option value="">اختر البلدية</option>';

        if (!nomLatinWilaya || !communesParWilaya[nomLatinWilaya]) {
            selectCommune.disabled = true;
            return;
        }

        communesParWilaya[nomLatinWilaya]
            .slice()
            .sort(function (a, b) { return a.libelle.localeCompare(b.libelle, 'ar'); })
            .forEach(function (commune) {
                const option = document.createElement('option');
                option.value = commune.valeur;
                option.textContent = commune.libelle;
                selectCommune.appendChild(option);
            });

        selectCommune.disabled = false;
    }

    if (selectWilaya && selectCommune) {
        selectCommune.disabled = true;
        chargerWilayas();
        selectWilaya.addEventListener('change', function () {
            peuplerCommunes(selectWilaya.value);
            validerChamp(selectCommune);
        });
    }

    /* ============================================================
       2. Sélecteur de quantité (1 / 2 / 3, remise visible)
       ============================================================ */
    const qtyOptions = document.querySelectorAll('.qty-option');
    qtyOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            qtyOptions.forEach(function (o) { o.classList.remove('active'); });
            option.classList.add('active');
            option.querySelector('input').checked = true;
            majRecapitulatif();
        });
    });

    const PRIX_UNITAIRE = 8900; // DZD — à ajuster selon votre offre réelle
    function majRecapitulatif() {
        const qteInput = document.querySelector('input[name="quantite"]:checked');
        const qte = qteInput ? parseInt(qteInput.value, 10) : 1;
        const totalEl = document.getElementById('recap-total');
        if (!totalEl) return;

        let remise = 0;
        if (qte === 2) remise = 0.10;
        if (qte === 3) remise = 0.18;

        const total = Math.round(PRIX_UNITAIRE * qte * (1 - remise));
        totalEl.textContent = total.toLocaleString('fr-FR') + ' دج';
    }
    majRecapitulatif();

    /* ============================================================
       3. Validation du formulaire (temps réel + à la soumission)
       ============================================================ */
    const form = document.getElementById('commande-form');
    if (!form) return;

    const champNomComplet = document.getElementById('nom_complet');
    const champTelephone  = document.getElementById('telephone');

    const REGEX_TELEPHONE = /^0[567][0-9]{8}$/;

    function nettoyerTelephone(valeur) {
        return valeur.replace(/[\s.\-]/g, '');
    }

    // Un seul champ "Nom et prénom" côté visiteur : le premier mot devient
    // le prénom, le reste devient le nom, pour rester compatible avec
    // l'API (api/submit-order.php) et la base de données sans rien y changer.
    function separerNomPrenom(nomComplet) {
        const mots = nomComplet.trim().split(/\s+/);
        const prenom = mots.shift() || '';
        const nom = mots.join(' ') || prenom;
        return { nom, prenom };
    }

    function afficherErreur(champ, message) {
        const groupe = champ.closest('.form-group');
        if (!groupe) return;
        groupe.classList.add('has-error');
        const erreurEl = groupe.querySelector('.form-error');
        if (erreurEl) erreurEl.textContent = message;
    }

    function effacerErreur(champ) {
        const groupe = champ.closest('.form-group');
        if (!groupe) return;
        groupe.classList.remove('has-error');
    }

    function validerChamp(champ) {
        if (!champ) return true;

        switch (champ.id) {
            case 'nom_complet': {
                const mots = champ.value.trim().split(/\s+/).filter(Boolean);
                if (champ.value.trim().length < 2 || mots.length < 2) {
                    afficherErreur(champ, 'الرجاء إدخال الاسم واللقب كاملين.');
                    return false;
                }
                break;
            }
            case 'telephone': {
                const valeur = nettoyerTelephone(champ.value.trim());
                if (!REGEX_TELEPHONE.test(valeur)) {
                    afficherErreur(champ, 'رقم غير صالح (مثال: 0555 12 34 56).');
                    return false;
                }
                break;
            }
            case 'wilaya':
                if (!champ.value) {
                    afficherErreur(champ, 'الرجاء اختيار ولايتك.');
                    return false;
                }
                break;
            case 'commune':
                if (!champ.value) {
                    afficherErreur(champ, 'الرجاء اختيار بلديتك.');
                    return false;
                }
                break;
        }

        effacerErreur(champ);
        return true;
    }

    [champNomComplet, champTelephone, selectWilaya, selectCommune].forEach(function (champ) {
        if (!champ) return;
        champ.addEventListener('blur', function () { validerChamp(champ); });
        champ.addEventListener('input', function () { effacerErreur(champ); });
    });

    function validerFormulaireComplet() {
        const champs = [champNomComplet, champTelephone, selectWilaya, selectCommune];
        let valide = true;
        champs.forEach(function (champ) {
            if (!validerChamp(champ)) valide = false;
        });
        return valide;
    }

    /* ============================================================
       4. Soumission AJAX
       ============================================================ */
    const boutonSubmit   = form.querySelector('button[type="submit"]');
    const messageErreur  = document.getElementById('form-server-error');
    const messageSucces  = document.getElementById('form-confirmation');
    const TEXTE_BOUTON_DEFAUT = boutonSubmit ? boutonSubmit.textContent : 'اطلب الآن';

    form.addEventListener('submit', function (evenement) {
        evenement.preventDefault();

        if (messageErreur) messageErreur.classList.remove('visible');

        if (!validerFormulaireComplet()) {
            return;
        }

        const qteInput = form.querySelector('input[name="quantite"]:checked');
        const { nom, prenom } = separerNomPrenom(champNomComplet.value);

        const donnees = {
            nom: nom,
            prenom: prenom,
            telephone: nettoyerTelephone(champTelephone.value.trim()),
            wilaya: selectWilaya.value,
            commune: selectCommune.value,
            quantite: qteInput ? qteInput.value : '1',
            site_web: form.querySelector('input[name="site_web"]').value, // honeypot
        };

        boutonSubmit.disabled = true;
        boutonSubmit.textContent = 'جارٍ الإرسال...';

        fetch('api/submit-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(donnees),
        })
            .then(function (reponse) { return reponse.json().then(function (data) { return { statut: reponse.status, data: data }; }); })
            .then(function (resultat) {
                if (resultat.data.success) {
                    form.style.display = 'none';
                    if (messageSucces) messageSucces.classList.add('visible');
                } else {
                    if (messageErreur) {
                        messageErreur.textContent = resultat.data.message || 'حدث خطأ. يرجى المحاولة مرة أخرى.';
                        messageErreur.classList.add('visible');
                    }
                }
            })
            .catch(function () {
                if (messageErreur) {
                    messageErreur.textContent = 'خطأ في الشبكة. تحقق من اتصالك وحاول مرة أخرى.';
                    messageErreur.classList.add('visible');
                }
            })
            .finally(function () {
                boutonSubmit.disabled = false;
                boutonSubmit.textContent = TEXTE_BOUTON_DEFAUT;
            });
    });

    /* ============================================================
       5. FAQ accordéon
       ============================================================ */
    document.querySelectorAll('.faq-item').forEach(function (item) {
        const question = item.querySelector('.faq-question');
        const reponse  = item.querySelector('.faq-answer');

        question.addEventListener('click', function () {
            const estOuvert = item.classList.contains('open');

            document.querySelectorAll('.faq-item.open').forEach(function (autre) {
                autre.classList.remove('open');
                autre.querySelector('.faq-answer').style.maxHeight = null;
            });

            if (!estOuvert) {
                item.classList.add('open');
                reponse.style.maxHeight = reponse.scrollHeight + 'px';
            }
        });
    });

    /* ============================================================
       6. Compte à rebours (urgence commerciale)
       ============================================================ */
    const countdownEl = document.getElementById('countdown');
    if (countdownEl) {
        // Se termine à minuit, chaque jour (crée une urgence "offre du jour")
        function tempsRestant() {
            const maintenant = new Date();
            const minuit = new Date(maintenant);
            minuit.setHours(24, 0, 0, 0);
            return minuit - maintenant;
        }

        function formatDeux(nombre) {
            return String(nombre).padStart(2, '0');
        }

        function majCompteARebours() {
            const restant = tempsRestant();
            const heures = Math.floor(restant / (1000 * 60 * 60));
            const minutes = Math.floor((restant / (1000 * 60)) % 60);
            const secondes = Math.floor((restant / 1000) % 60);

            const elH = document.getElementById('cd-heures');
            const elM = document.getElementById('cd-minutes');
            const elS = document.getElementById('cd-secondes');
            if (elH) elH.textContent = formatDeux(heures);
            if (elM) elM.textContent = formatDeux(minutes);
            if (elS) elS.textContent = formatDeux(secondes);
        }

        majCompteARebours();
        setInterval(majCompteARebours, 1000);
    }
})();
