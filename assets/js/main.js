/**
 * main.js — Logique de la landing page Smart Ink Case (version arabe).
 * Vanilla JS uniquement, aucune dépendance externe.
 *
 * Note : la valeur envoyée au serveur (wilaya) reste en transcription
 * latine (wilaya_name_latin) afin de rester compatible avec la validation
 * PHP (api/helpers.php) et l'affichage du panneau admin (resté en
 * français). Seul le LIBELLÉ visible dans les <option> est en arabe.
 */
(function () {
    'use strict';

    /* ============================================================
       1. Wilayas (peuplées depuis assets/js/wilayas.json)
       ============================================================ */
    const selectWilaya = document.getElementById('wilaya');

    function chargerWilayas() {
        fetch('assets/js/wilayas.json')
            .then(function (reponse) { return reponse.json(); })
            .then(function (data) {
                if (!data || !Array.isArray(data.wilayas)) {
                    console.error('Format de wilayas.json inattendu.');
                    return;
                }

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

    if (selectWilaya) {
        chargerWilayas();
        selectWilaya.addEventListener('change', function () {
            majRecapitulatif();
        });
    }

    /* ============================================================
       2. Frais de livraison (peuplés depuis api/frais-livraison.php)
       ============================================================ */
    let fraisLivraisonParWilaya = {}; // { "NomLatinWilaya": { domicile: prix, point_relais: prix } }

    fetch('api/frais-livraison.php')
        .then(function (reponse) { return reponse.json(); })
        .then(function (data) {
            fraisLivraisonParWilaya = data || {};
            majRecapitulatif();
        })
        .catch(function (err) {
            console.error('Impossible de charger les frais de livraison', err);
        });

    /* ============================================================
       3. Sélecteur de quantité (1 / 2 / 3, remise visible) + récapitulatif
       ============================================================ */
    // Chaque groupe d'options (.qty-option) ne doit désactiver que les autres
    // options DU MÊME groupe (name du input radio) : la quantité et le type
    // de livraison partagent la même classe visuelle mais sont deux groupes
    // indépendants.
    const qtyOptions = document.querySelectorAll('.qty-option');
    qtyOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            const input = option.querySelector('input');
            const memeGroupe = document.querySelectorAll('.qty-option input[name="' + input.name + '"]');
            memeGroupe.forEach(function (autreInput) {
                autreInput.closest('.qty-option').classList.remove('active');
            });
            option.classList.add('active');
            input.checked = true;
            majRecapitulatif();
        });
    });

    const PRIX_UNITAIRE = 8900; // DZD — à ajuster selon votre offre réelle
    function formaterPrix(montant) {
        return montant.toLocaleString('fr-FR') + ' دج';
    }

    function majRecapitulatif() {
        const qteInput = document.querySelector('input[name="quantite"]:checked');
        const qte = qteInput ? parseInt(qteInput.value, 10) : 1;

        const produitEl    = document.getElementById('recap-produit');
        const livraisonEl  = document.getElementById('recap-livraison');
        const totalEl      = document.getElementById('recap-total');
        if (!totalEl) return;

        let remise = 0;
        if (qte === 2) remise = 0.10;
        if (qte === 3) remise = 0.18;

        const sousTotal = Math.round(PRIX_UNITAIRE * qte * (1 - remise));
        if (produitEl) produitEl.textContent = formaterPrix(sousTotal);

        const wilayaChoisie = selectWilaya ? selectWilaya.value : '';
        const typeInput = document.querySelector('input[name="type_livraison"]:checked');
        const typeLivraison = typeInput ? typeInput.value : 'domicile';

        let frais = 0;
        if (wilayaChoisie && Object.prototype.hasOwnProperty.call(fraisLivraisonParWilaya, wilayaChoisie)) {
            const valeur = fraisLivraisonParWilaya[wilayaChoisie];
            // Garde-fou : si la valeur n'est pas au format attendu (ex. cache
            // navigateur/CDN servant un ancien script avec un ancien format
            // d'API), on retombe sur 0 plutôt que d'afficher "[object Object]".
            const brut = valeur && typeof valeur === 'object' ? valeur[typeLivraison] : valeur;
            frais = typeof brut === 'number' && !isNaN(brut) ? brut : 0;
            if (livraisonEl) livraisonEl.textContent = frais > 0 ? formaterPrix(frais) : 'مجانية';
        } else if (livraisonEl) {
            livraisonEl.textContent = wilayaChoisie ? 'مجانية' : 'اختر ولايتك';
        }

        totalEl.textContent = formaterPrix(sousTotal + frais);
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
        }

        effacerErreur(champ);
        return true;
    }

    [champNomComplet, champTelephone, selectWilaya].forEach(function (champ) {
        if (!champ) return;
        champ.addEventListener('blur', function () { validerChamp(champ); });
        champ.addEventListener('input', function () { effacerErreur(champ); });
    });

    function validerFormulaireComplet() {
        const champs = [champNomComplet, champTelephone, selectWilaya];
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
        const typeLivraisonInput = form.querySelector('input[name="type_livraison"]:checked');
        const { nom, prenom } = separerNomPrenom(champNomComplet.value);

        const donnees = {
            nom: nom,
            prenom: prenom,
            telephone: nettoyerTelephone(champTelephone.value.trim()),
            wilaya: selectWilaya.value,
            quantite: qteInput ? qteInput.value : '1',
            type_livraison: typeLivraisonInput ? typeLivraisonInput.value : 'domicile',
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
